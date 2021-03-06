<div id='sidebar_wrapper_func' class="sidebar-wrapper">
    <div>
        <div class="logo-wrapper">
            <a href="<?= base_url() ?>">
                <!-- <h3>DINKES</h3> -->
                <img class="img-fluid for-light" src="<?= base_url() ?>assets/images/logo/logo.png" alt="" />
                <img class="img-fluid for-dark" src="<?= base_url() ?>assets/images/logo/logo_dark.png" alt="" />
            </a>
            <div class="back-btn"><i class="fa fa-angle-left"></i></div>
            <div class="toggle-sidebar">
                <i class="status_toggle middle sidebar-toggle" data-feather="grid">
                </i>
            </div>
        </div>
        <div class="logo-icon-wrapper">
            <a href="<?= base_url() ?>"><img class="img-fluid" src="<?= base_url() ?>assets/images/logo/logo-icon.png" alt="" /></a>
        </div>
        <nav class="sidebar-main">
            <div class="left-arrow" id="left-arrow">
                <i data-feather="arrow-left"></i>
            </div>

            <div id="sidebar-menu">
                <ul class="sidebar-links" id="simple-bar">
                    <li class="back-btn">
                        <a href="index.html"><img class="img-fluid" src="<?= base_url() ?>assets/images/logo/logo-icon.png" alt="" /></a>
                        <div class="mobile-back text-end">
                            <span>Back</span><i class="fa fa-angle-right ps-2" aria-hidden="true"></i>
                        </div>
                    </li>
                    <li class="sidebar-list">
                        <a class="sidebar-link sidebar-title link-nav" href="<?= base_url() ?>dashboard"><i data-feather="home"> </i><span>DASHBOARD</span></a>
                    </li>
                    <li class="sidebar-list">
                        <a style="" class="sidebar-link sidebar-title link-nav" id="sidebar_aktifitas" href="<?= base_url() ?>aktifitas"><i data-feather="box"> </i><span>Aktifitas Harian</span></a>
                    </li>
                    <li class="sidebar-list">
                        <a class="sidebar-link sidebar-title link-nav" id="sidebar_skp" href="<?= base_url() ?>skp"><i data-feather="box"> </i><span>Sasaran Kerja</span></a>
                    </li>
                    <li class="sidebar-list">
                        <a class="sidebar-link sidebar-title link-nav" id="sidebar_permohonan" href="<?= base_url() ?>permohonan"><i data-feather="box"> </i><span>Permohonan Approval</span></a>
                    </li>
                    <li class="sidebar-list">
                        <a class="sidebar-link sidebar-title link-nav" href="<?= base_url() ?>perjalanan-dinas"><i data-feather="box"> </i><span>Perjalanan Dinas Saya</span></a>
                    </li>
                    <?php $menu = User_Access($this->session->userdata('id_role'));
                    if (!empty($menu))
                        foreach ($menu as $m) {
                            echo ' <li class="sidebar-list">
                        <label class="badge badge-light-primary"></label><a class="sidebar-link sidebar-title" id="menu_' . $m['id_menu'] . '" href="#"><i data-feather="' . $m['icon'] . '"></i><span class="">' . $m['label_menu'] . ' </span></a>
                        <ul class="sidebar-submenu" id="opmenu_' . $m['id_menu'] . '" >';
                            foreach ($m['child'] as $mc) {
                                echo '<li><a id="submenu_' . $mc['id_menulist'] . '" href="' . base_url() . $mc['url'] . '">' . $mc['label_menulist'] . '</a></li>';
                            }
                            echo '</ul> </li>';
                        }
                    ?>
                </ul>
            </div>
            <div class="right-arrow" id="right-arrow">
                <i data-feather="arrow-right"></i>
            </div>
        </nav>
    </div>
</div>