<?php
defined('BASEPATH') or exit('No direct script access allowed');

class UserModel extends CI_Model
{

    public function getAllUser($filter = [])
    {
        $this->db->select("u.*,r.*, nama_bag, nama_bidang, nama_satuan, approv_lv_1, approv_lv_2, approv_lv_3, approv_lv_4, ppk, jabatan, pangkat_gol");
        $this->db->from('users as u');
        $this->db->join('roles r', 'u.id_role = r.id_role');
        $this->db->join('satuan s', 'u.id_satuan = s.id_satuan', 'LEFT');
        $this->db->join('bidang bd', 'u.id_bidang = bd.id_bidang', 'LEFT');
        $this->db->join('bagian bg', 'u.id_bagian = bg.id_bagian', 'LEFT');
        if (empty($filter['is_login'])) {
            $this->db->select("NULL as password", FALSE);
        }
        if (isset($filter['is_not_self'])) $this->db->where('u.id_user !=', $this->session->userdata('id_user'));
        if (isset($filter['username'])) $this->db->where('u.username', $filter['username']);
        // if (isset($filter['username_email'])) $this->db->where('u.username = "' . $filter['username_email'] . '" OR email = "' . $filter['username_email'] . '"');

        if (isset($filter['id_user'])) $this->db->where('u.id_user', $filter['id_user']);
        if (isset($filter['except_roles'])) $this->db->where_not_in('u.id_role', $filter['except_roles']);
        if (isset($filter['just_roles'])) $this->db->where_in('u.id_role', $filter['just_roles']);
        if (!empty($filter['id_role'])) $this->db->where('u.id_role', $filter['id_role']);
        $res = $this->db->get();
        // var_dump($res->result_array());
        // die();
        return DataStructure::keyValue($res->result_array(), 'id');
    }


    public function activatorUser($data)
    {
        $this->db->select("*");
        $this->db->from('user_temp as u');
        $this->db->where("u.id ", $data['id']);
        $this->db->where("u.activator ", $data['activator']);
        $res = $this->db->get();
        $res = $res->result_array();
        if (empty($res)) {
            throw new UserException('Activation failed or has active please check your email', USER_NOT_FOUND_CODE);
        } else {
            $this->cekUserByEmailBuyer($res[0]);
            $this->cekUserByEmailSeller($res[0]);
            $this->cekUserByUsername($res[0]['username']);
            if ($res[0]['jenis_akun'] == 'B') {
                $res[0]['id_role'] = '12';
                $res[0]['password'] = $res[0]['password_hash'];
                $res[0]['id_user'] = $this->addUser($res[0]);
                $this->db->where('id', $res[0]['id']);
                $this->db->delete('user_temp');
                return $res[0];
            } else if ($res[0]['jenis_akun'] == 'S') {
                $res[0]['id_role'] = '2';
                $res[0]['password'] = $res[0]['password_hash'];
                $res[0]['id_user'] = $this->addUser($res[0]);
                //	$this->addPerusahaan($res[0]);
                $this->db->where('id', $res[0]['id']);
                $this->db->delete('user_temp');
                return $res[0];
            }
        };
    }

    public function getUser($idUser = NULL)
    {
        $row = $this->getAllUser(['id_user' => $idUser]);
        if (empty($row)) {
            throw new UserException("User yang kamu cari tidak ditemukan", USER_NOT_FOUND_CODE);
        }
        return $row[$idUser];
    }

    public function cekUserByUsername($username = NULL)
    {
        $row = $this->getAllUser(['username' => $username, 'is_login' => TRUE]);
        if (!empty($row)) {
            throw new UserException("User yang kamu daftarkan sudah ada", USER_NOT_FOUND_CODE);
        }
    }

    public function cekUserByEmailBuyer($data)
    {
        $this->db->select("email");
        $this->db->from('buyer as u');
        $this->db->where('u.email', $data['email']);
        $res = $this->db->get();
        $row = $res->result_array();
        if (!empty($row)) {
            throw new UserException("Email yang kamu daftarkan sudah ada", USER_NOT_FOUND_CODE);
        }
    }

    public function cekUserByEmailSeller($data)
    {

        $this->db->select("email");
        $this->db->from('perusahaan as u');
        $this->db->where('u.email', $data['email']);
        $res = $this->db->get();
        $row = $res->result_array();
        if (!empty($row)) {
            throw new UserException("Email yang kamu daftarkan sudah ada", USER_NOT_FOUND_CODE);
        }
    }

    public function getUserByUsername($username = NULL)
    {
        $row = $this->getAllUser(['username' => $username, 'is_login' => TRUE]);
        if (empty($row)) {
            throw new UserException("User yang kamu cari tidak ditemukan", USER_NOT_FOUND_CODE);
        }
        return array_values($row)[0];
    }

    public function login($loginData)
    {
        $user = $this->getUserByUsername($loginData['username']);
        if (md5($loginData['password']) !== $user['password'])
            throw new UserException("Password yang kamu masukkan salah.", WRONG_PASSWORD_CODE);
        return $user;
    }

    public function addUser($data)
    {
        $data['password'] = md5($data['password']);

        $this->db->insert('users', DataStructure::slice($data, ['username', 'password', 'nama', 'email', 'nip', 'alamat', 'no_hp', 'status', 'id_role', 'id_satuan', 'id_bagian', 'id_bidang', 'pangkat_gol', 'jabatan'], TRUE));

        // $this->db->set(DataStructure::slice($data, ['username', 'nama', 'email', 'nip', 'alamat', 'no_hp', 'status', 'id_role']));
        ExceptionHandler::handleDBError($this->db->error(), "Tambah User", "User");

        $id_user = $this->db->insert_id();


        return $id_user;
    }

    public function registerUser($data)
    {
        $this->cekUserByUsername($data['username']);
        $this->cekUserByEmailBuyer($data);
        $this->cekUserByEmailSeller($data);
        $data['password_hash'] = $data['password'];
        $data['password'] = md5($data['password']);
        $permitted_activtor = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $data['activator'] =  substr(str_shuffle($permitted_activtor), 0, 20);
        // echo $act;
        $this->db->insert('user_temp', DataStructure::slice($data, [
            'username', 'nama', 'password', 'password_hash', 'activator', 'email', 'nama_perusahaan', 'region', 'alamat', 'jenis_akun'
        ], TRUE));
        ExceptionHandler::handleDBError($this->db->error(), "Tambah User", "User");

        $data['id']  = $this->db->insert_id();

        return $data;
    }

    public function editUser($data)
    {
        if (!empty($data['password'])) $this->db->set('password', md5($data['password']));
        $this->db->set(DataStructure::slice($data, [
            'username', 'nama', 'email', 'nip', 'alamat',
            'no_hp', 'status', 'id_role', 'id_satuan', 'id_bagian', 'id_bidang', 'pangkat_gol', 'jabatan', 'signature', 'photo',
            'pend_jenjang',  'pend_jurusan', 'j_k', 'tempat_lahir', 'tanggal_lahir', 'tmt_kerja', 'jenis_pegawai'
        ]));
        $this->db->where('id', $data['id']);
        $this->db->update('users');

        ExceptionHandler::handleDBError($this->db->error(), "Ubah User", "User");

        return $data['id'];
    }



    public function deleteUser($data)
    {
        $this->db->where('id', $data['id']);
        $this->db->delete('users');

        ExceptionHandler::handleDBError($this->db->error(), "Hapus User", "User");
    }

    public function changePassword($data)
    {
        $idUser = $this->session->userdata('nama_role') == 'admin' ? $data['id_user'] : $this->session->userdata('id_user');
        $this->db->set('password', md5($data['password']));
        $this->db->where('id_user', $idUser);
        $this->db->update('users');
    }

    public function changeUsername($data)
    {
        $this->db->set('username', $data['username_new']);
        $this->db->where('username', $data['username']);
        $this->db->update('users');

        ExceptionHandler::handleDBError($this->db->error(), "Ganti Username", "User");
    }

    public function deleteBatch($ids)
    {
        $this->db->where_in('id_user', $ids);
        $this->db->delete('users');

        ExceptionHandler::handleDBError($this->db->error(), "Hapus User", "User");
    }
    public function getDetailRole($para_user_id = '')
    {
        $this->db->select('menu.*, menulist.*, hak_aksess.id_hak_aksess, view, hk_create, hk_update, hk_delete');
        $this->db->from('menu');
        $this->db->join('menulist', "menulist.id_menu = menu.id_menu", 'LEFT');
        $this->db->join('hak_aksess', "hak_aksess.id_menulist = menulist.id_menulist AND hak_aksess.id_role = '" . $para_user_id . "'", 'LEFT');

        $res = $this->db->get();
        if ($res->num_rows() < 1) {
            return NULL;
        }
        $ret = DataStructure::groupByRecursive2(
            $res->result_array(),
            ['id_menu'],
            ['id_menulist'],
            [
                ['id_menu', 'label_menu', 'icon'],
                ['id_menulist', 'url', 'label_menulist', 'id_hak_aksess', 'hk_create', 'hk_update', 'hk_delete', 'view']
            ],
            ['child'],
            false
        );
        return $ret;
    }

    public function edit_hak_akses($data)
    {
        $this->db->set('nama_role', $data['nama_role']);
        $this->db->set('level', $data['level']);
        $this->db->where('id_role', $data['id_role']);
        $this->db->update('roles');

        $this->db->where('id_role', $data['id_role']);
        $this->db->delete('hak_aksess');
        foreach ($data['hak_aksess'] as $key => $dt) {
            $tmp_hk = array(
                'id_role' => $data['id_role'],
                'id_menulist' => $key,
                'view' => (!empty($dt['view']) ? 1 : 0),
                'hk_update' => (!empty($dt['hk_update']) ? 1 : 0),
                'hk_delete' => (!empty($dt['hk_delete']) ? 1 : 0),
                'hk_create' => (!empty($dt['hk_create']) ? 1 : 0),
            );
            $this->db->insert('hak_aksess', $tmp_hk);
        }
    }

    public function add_hak_akses($data)
    {
        $this->db->set('nama_role', $data['nama_role']);
        // $this->db->where('id_role', $data['id_role']);
        $this->db->insert('roles');
        $id = $this->db->insert_id();

        // $this->db->where('id_role', $data['id_role']);
        // $this->db->delete('hak_aksess');
        foreach ($data['hak_aksess'] as $key => $dt) {
            $tmp_hk = array(
                'id_role' => $id,
                'id_menulist' => $key,
                'view' => (!empty($dt['view']) ? 1 : 0),
                'hk_update' => (!empty($dt['hk_update']) ? 1 : 0),
                'hk_delete' => (!empty($dt['hk_delete']) ? 1 : 0),
                'hk_create' => (!empty($dt['hk_create']) ? 1 : 0),
            );
            $this->db->insert('hak_aksess', $tmp_hk);
        }
    }



    public function addPosition($data)
    {

        $this->db->insert('user_position', DataStructure::slice($data, [
            'id_user', 'id_role', 'id_satuan', 'id_bidang', 'id_bagian', 'approv_lv_1', 'approv_lv_2', 'approv_lv_3', 'approv_lv_4', 'ppk', 'status', 'pangkat_gol', 'jabatan'
        ], FALSE));
        ExceptionHandler::handleDBError($this->db->error(), "Tambah User", "User");

        $id_user = $this->db->insert_id();

        return $id_user;
    }

    public function editPosition($data)
    {
        if (!empty($data['password'])) $this->db->set('password', md5($data['password']));
        $this->db->set(DataStructure::slice($data, ['username', 'nama', 'email', 'nip', 'alamat', 'no_hp', 'status', 'id_role']));
        $this->db->where('id', $data['id']);
        $this->db->update('users');

        ExceptionHandler::handleDBError($this->db->error(), "Ubah User", "User");

        return $data['id'];
    }

    public function deletePosition($data)
    {
        $this->db->where('id_user', $data['id_user']);
        $this->db->delete('users');

        ExceptionHandler::handleDBError($this->db->error(), "Hapus User", "User");
    }
}
