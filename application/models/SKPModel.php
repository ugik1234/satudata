<?php
defined('BASEPATH') or exit('No direct script access allowed');

class SKPModel extends CI_Model
{

    public function getAll($filter = [])
    {
        $this->db->select("u.*,pen.nama as nama_penilai, count(r.id_skp) as skp");
        $this->db->from('skp as u');
        $this->db->join('skp_child r', 'u.id_skp = r.id_skp');
        $this->db->join('users p', 'p.id = u.id_user');
        $this->db->join('users pen', 'pen.id = u.id_penilai');
        $this->db->group_by('id_skp');

        if (!empty($filter['id_skp'])) $this->db->where('u.id_skp', $filter['id_skp']);
        if (!empty($filter['my_skp'])) $this->db->where('u.id_user', $this->session->userdata()['id']);
        $res = $this->db->get();
        // echo json_encode($res->result_array());
        // die();
        // 
        return  DataStructure::keyValue($res->result_array(), 'id_skp');
    }


    public function getDetail($filter = [])
    {
        $this->db->select("r.*, p.*, u.*, skp_a.kegiatan as kegiatan_atasan,pen.nama as nama_penilai, ");
        $this->db->from('skp as u');
        $this->db->join('skp_child r', 'u.id_skp = r.id_skp');
        $this->db->join('skp_child skp_a', 'skp_a.id_skp_child = r.id_skp_atasan', 'LEFT');
        $this->db->join('users p', 'p.id = u.id_user');
        $this->db->join('users pen', 'pen.id = u.id_penilai');
        if (!empty($filter['id_skp'])) $this->db->where('u.id_skp', $filter['id_skp']);
        if (!empty($filter['my_skp'])) $this->db->where('u.id_user', $this->session->userdata()['id']);
        $res = $this->db->get();
        // echo json_encode($res->result_array());
        // die();
        // 
        return DataStructure::SKPStyle($res->result_array());
    }

    public function add($data)
    {

        $id_user = $this->session->userdata()['id'];
        // $data['data'] = $ses['id_satuan'];
        // $data['id_bidang'] = $ses['id_bidang'];
        $res_data['periode_start'] = $data['periode_start'];
        $res_data['id_penilai'] = $data['id_penilai'];
        $res_data['id_user'] = $id_user;
        $this->db->insert('skp', DataStructure::slice($res_data, [
            'date', 'id_user', 'id_penilai', 'periode_start'
        ], FALSE));

        $id_skp = $this->db->insert_id();
        $i = 0;

        foreach ($data['kegiatan'] as $p) {
            if (!empty($data['kegiatan'][$i]) or !empty($data['kuantitas'][$i]) or !empty($data['kualitas'][$i]) or !empty($data['waktu'][$i])) {
                $d_tujuan = array(
                    'id_skp' => $id_skp,
                    'kegiatan' => $data['kegiatan'][$i],
                    'id_skp_atasan' => $data['id_skp_atasan'][$i],
                    'jenis_keg' => $data['jenis_keg'][$i],

                    'iki_kuantitas' => $data['iki_kuantitas'][$i],
                    'min_kuantitas' => $data['min_kuantitas'][$i],
                    'max_kuantitas' => $data['max_kuantitas'][$i],
                    'ket_kuantitas' => $data['ket_kuantitas'][$i],

                    'iki_kualitas' => $data['iki_kualitas'][$i],
                    'min_kualitas' => $data['min_kualitas'][$i],
                    'max_kualitas' => $data['max_kualitas'][$i],
                    'ket_kualitas' => $data['ket_kualitas'][$i],

                    'iki_waktu' => $data['iki_waktu'][$i],
                    'min_waktu' => $data['min_waktu'][$i],
                    'max_waktu' => $data['max_waktu'][$i],
                    'ket_waktu' => $data['ket_waktu'][$i],
                );
                $this->db->insert('skp_child', $d_tujuan);
            }
            $i++;
        }
    }

    public function edit($data)
    {

        $id_user = $this->session->userdata()['id'];
        $res_data['periode_start'] = $data['periode_start'];
        $res_data['id_penilai'] = $data['id_penilai'];
        $res_data['id_user'] = $id_user;
        $this->db->set(DataStructure::slice($data, [
            'date', 'id_user', 'id_penilai', 'periode_start'
        ], FALSE));

        $this->db->where('id_skp', $data['id_skp']);
        $this->db->update('skp',);

        $id_sppd = $data['id_skp'];
        $i = 0;
        foreach ($data['kegiatan'] as $p) {
            $d_tujuan = array(
                'id_skp' => $id_sppd,
                'kegiatan' => $data['kegiatan'][$i],
                'jenis_keg' => $data['jenis_keg'][$i],
                'id_skp_atasan' => $data['id_skp_atasan'][$i],
                'iki_kuantitas' => $data['iki_kuantitas'][$i],
                'min_kuantitas' => $data['min_kuantitas'][$i],
                'max_kuantitas' => $data['max_kuantitas'][$i],
                'ket_kuantitas' => $data['ket_kuantitas'][$i],

                'iki_kualitas' => $data['iki_kualitas'][$i],
                'min_kualitas' => $data['min_kualitas'][$i],
                'max_kualitas' => $data['max_kualitas'][$i],
                'ket_kualitas' => $data['ket_kualitas'][$i],

                'iki_waktu' => $data['iki_waktu'][$i],
                'min_waktu' => $data['min_waktu'][$i],
                'max_waktu' => $data['max_waktu'][$i],
                'ket_waktu' => $data['ket_waktu'][$i],
            );

            if (!empty($data['kegiatan'][$i]) and empty($data['id_skp_child'][$i])) {
                $this->db->insert('skp_child', $d_tujuan);
            } else
            if (!empty($data['kegiatan'][$i]) and !empty($data['id_skp_child'][$i])) {
                $this->db->set($d_tujuan);
                $this->db->where('id_skp_child', $data['id_skp_child'][$i]);
                $this->db->update('skp_child');
            } else if (!empty($data['id_skp_child'][$i])) {
                $this->db->where('id_skp_child', $data['id_skp_child'][$i]);
                $this->db->delete('skp_child');
            }
            $i++;
        }
        ExceptionHandler::handleDBError($this->db->error(), "Tambah User", "User");

        return $id_sppd;
    }
}
