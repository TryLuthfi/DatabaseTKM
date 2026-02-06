<?php
$id_menu = $this->uri->segment('1');
?>
<div class="wrapper">
    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-grey-dark navbar-dark">
        <!-- Left navbar links -->
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
            <li class="nav-item d-none d-sm-inline-block" style="pointer-events: none">
                <a href="<?= base_url('Dashboard') ?>" class="nav-link">Dashboard</a>
            </li>
            <li class="nav-item d-none d-sm-inline-block" style="pointer-events: none">
                <a href="<?= base_url('Fiberstar_Project') ?>" class="nav-link">Fiberstars</a>
            </li>
            <li class="nav-item d-none d-sm-inline-block" style="pointer-events: none">
                <a href="<?= base_url('MyRepublik_Project') ?>" class="nav-link">My Republik</a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <a href="<?= base_url('Dashboard_Logistik_Stok') ?>" class="nav-link">Logistik</a>
            </li>
        </ul>
        <ul class="navbar-nav ml-auto">
            <li class="nav-item dropdown">
                <a class="nav-link" data-toggle="dropdown" href="#">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                    <div class="dropdown-divider"></div>
                    <a href="<?= base_url('Auth/logout') ?>" class="dropdown-item dropdown-footer">Logout</a>
                </div>
            </li>
        </ul>
    </nav>
    <!-- /.navbar -->

    <!-- Main Sidebar Container -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <!-- Brand Logo -->
        <a href="index3.html" class="brand-link">
            <img src="<?= base_url('assets') ?>/dist/img/logotkmsolid.png" alt="AdminLTE Logo"
                class="brand-image img-circle elevation-3" style="opacity: .8">
            <span class="brand-text font-weight-light">PT. TKM</span>
        </a>

        <!-- Sidebar -->
        <div class="sidebar">
            <!-- Sidebar user panel (optional) -->
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="image">
                    <img src="<?= base_url('assets') ?>/dist/img/avatar5.png" class="img-circle elevation-2"
                        alt="User Image">
                </div>
                <div class="info">
                    <a href="#" class="d-block"><?= $this->session->userdata('nama_user') ?></a>
                </div>
            </div>


            <!-- Sidebar Menu -->
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                    data-accordion="false">
                    <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->
                    <li class="nav-item" style="pointer-events: none">
                        <a href="<?= base_url('Dashboard') ?>" class="nav-link <?php if ($id_menu == 'Dashboard') {
                              echo "active";
                          } ?>">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>
                                Dashboard
                            </p>
                        </a>
                    </li>

                    <?php if ($this->session->userdata('nama_level') == "Super Admin") { ?>
                        <li class="nav-item has-treeview <?php if ($id_menu == 'ListUser' || $id_menu == 'ListBowheer' || $id_menu == 'ListArea') {
                            echo "menu-open";
                        } ?>">
                            <a href="#" class="nav-link <?php if ($id_menu == 'ListUser' || $id_menu == 'ListBowheer' || $id_menu == 'ListArea') {
                                echo "active";
                            } ?>">
                                <i class="nav-icon fas fa-money-check-alt"></i>
                                <p>
                                    Super Admin
                                    <i class="fas fa-angle-left right"></i>
                                    <span class="badge badge-info right">3</span>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                </li>
                            </ul>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="<?= base_url('ListArea') ?>" class="nav-link <?php if ($id_menu == 'ListArea') {
                                          echo "active";
                                      } ?>">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>List Area</p>
                                    </a>
                                </li>
                            </ul>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="<?= base_url('ListUser') ?>" class="nav-link <?php if ($id_menu == 'ListUser') {
                                          echo "active";
                                      } ?>">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>List User</p>
                                    </a>
                                </li>
                            </ul>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="<?= base_url('ListBowheer') ?>" class="nav-link <?php if ($id_menu == 'ListBowheer') {
                                          echo "active";
                                      } ?>">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>List Bowheer</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    <?php } ?>
                    <li class="nav-header">Logistik</li>
                    <li class="nav-item has-treeview <?php if (
                        $id_menu == 'Master_Logistik_Lokasi_Gudang' ||
                        $id_menu == 'Master_Logistik_Kode_Item' ||
                        $id_menu == 'Master_Logistik_Sumber_Material' ||
                        $id_menu == 'Master_Logistik_Pabrik' ||
                        $id_menu == 'Logistik_Purchase_Request' ||
                        $id_menu == 'Logistik_Pesanan_Pabrik' ||
                        $id_menu == 'Dashboard_Logistik_Stok' ||
                        $id_menu == 'Logistik_Stok_Detail' ||
                        $id_menu == 'StockOpname'
                    ) {
                        echo 'menu-open';
                    } ?>">

                        <a href="#" class="nav-link <?php if (
                            $id_menu == 'Master_Logistik_Lokasi_Gudang' ||
                            $id_menu == 'Master_Logistik_Kode_Item' ||
                            $id_menu == 'Master_Logistik_Sumber_Material' ||
                            $id_menu == 'Master_Logistik_Pabrik' ||
                            $id_menu == 'Logistik_Purchase_Request' ||
                            $id_menu == 'Logistik_Pesanan_Pabrik' ||
                            $id_menu == 'Dashboard_Logistik_Stok' ||
                            $id_menu == 'Logistik_Stok_Detail' ||
                            $id_menu == 'StockOpname'
                        ) {
                            echo 'active';
                        } ?>">
                            <i class="nav-icon fas fa-boxes"></i>
                            <p>
                                Logistik
                                <i class="right fas fa-angle-left"></i>
                                <span class="badge badge-info right">5</span>
                            </p>
                        </a>

                        <ul class="nav nav-treeview">
                            <!-- Master Logistik -->
                            <li class="nav-item has-treeview <?php if (
                                $id_menu == 'Master_Logistik_Lokasi_Gudang' ||
                                $id_menu == 'Master_Logistik_Kode_Item' ||
                                $id_menu == 'Master_Logistik_Sumber_Material' ||
                                $id_menu == 'Master_Logistik_Pabrik'
                            ) {
                                echo 'menu-open';
                            } ?>">
                                <a href="#" class="nav-link <?php if (
                                    $id_menu == 'Master_Logistik_Lokasi_Gudang' ||
                                    $id_menu == 'Master_Logistik_Kode_Item' ||
                                    $id_menu == 'Master_Logistik_Sumber_Material' ||
                                    $id_menu == 'Master_Logistik_Pabrik'
                                ) {
                                    echo 'active';
                                } ?>">
                                    <i class="far fa-folder nav-icon"></i>
                                    <p>Master Logistik
                                        <i class="right fas fa-angle-left"></i>
                                        <span class="badge badge-info right">2</span>
                                    </p>
                                </a>
                                <ul class="nav nav-treeview">
                                    <li class="nav-item">
                                        <a href="<?= base_url('Master_Logistik_Lokasi_Gudang') ?>" class="nav-link <?php if ($id_menu == 'Master_Logistik_Lokasi_Gudang')
                                              echo 'active'; ?>">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Lokasi Gudang</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="<?= base_url('Master_Logistik_Kode_Item') ?>" class="nav-link <?php if ($id_menu == 'Master_Logistik_Kode_Item')
                                              echo 'active'; ?>">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Kode Item</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="<?= base_url('Master_Logistik_Sumber_Material') ?>" class="nav-link <?php if ($id_menu == 'Master_Logistik_Sumber_Material')
                                              echo 'active'; ?>">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Sumber Material</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="<?= base_url('Master_Logistik_Pabrik') ?>" class="nav-link <?php if ($id_menu == 'Master_Logistik_Pabrik')
                                              echo 'active'; ?>">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Pabrik</p>
                                        </a>
                                    </li>
                                </ul>
                            </li>

                            <!-- Purchase Request -->
                            <li class="nav-item">
                                <a href="<?= base_url('Logistik_Purchase_Request') ?>" class="nav-link <?php if ($id_menu == 'Logistik_Purchase_Request')
                                      echo 'active'; ?>">
                                    <i class="far fa-file nav-icon"></i>
                                    <p>Purchase Request</p>
                                </a>
                            </li>

                            <!-- Purchase Order -->
                            <li class="nav-item" style="pointer-events: none">
                                <a href="<?= base_url('Logistik_Pesanan_Pabrik') ?>" class="nav-link <?php if ($id_menu == 'Logistik_Pesanan_Pabrik')
                                      echo 'active'; ?>">
                                    <i class="far fa-file nav-icon"></i>
                                    <p>Purchase Order</p>
                                </a>
                            </li>

                            <!-- Stock Material -->
                            <li class="nav-item">
                                <a href="<?= base_url('Dashboard_Logistik_Stok') ?>" class="nav-link <?php if ($id_menu == 'Dashboard_Logistik_Stok')
                                      echo 'active'; ?>">
                                    <i class="far fa-file nav-icon"></i>
                                    <p>Stock Material</p>
                                </a>
                            </li>

                            <!-- Stock Opname -->
                            <li class="nav-item">
                                <a href="<?= base_url('StockOpname') ?>" class="nav-link <?php if ($id_menu == 'StockOpname')
                                      echo 'active'; ?>">
                                    <i class="far fa-file nav-icon"></i>
                                    <p>Stock Opname</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-header">General Affair</li>
                    <li class="nav-item has-treeview <?= (
                        $id_menu == 'Master_GA_Aset' ||
                        $id_menu == 'GA_Aset_Kendaraan' ||
                        $id_menu == 'GA_Alat_Terminasi' ||
                        $id_menu == 'GA_Sarana_Kerja' ||
                        $id_menu == 'asd'
                    ) ? 'menu-open' : '' ?>">
                        <a href="#" class="nav-link <?= (
                            $id_menu == 'Master_GA_Aset' ||
                            $id_menu == 'GA_Aset_Kendaraan' ||
                            $id_menu == 'GA_Alat_Terminasi' ||
                            $id_menu == 'GA_Sarana_Kerja' ||
                            $id_menu == 'asd'
                        ) ? 'active' : '' ?>">
                            <i class="nav-icon fas fa-building"></i>
                            <p>
                                General Affair
                                <i class="fas fa-angle-left right"></i>
                                <span class="badge badge-info right">6</span>
                            </p>
                        </a>

                        <ul class="nav nav-treeview">
                            <!-- MASTER GA -->
                            <li class="nav-item has-treeview <?= ($id_menu == 'Master_GA_Aset') ? 'menu-open' : '' ?>">
                                <a href="#" class="nav-link <?= ($id_menu == 'Master_GA_Aset') ? 'active' : '' ?>">
                                    <i class="far fa-folder nav-icon"></i>
                                    <p>
                                        Master GA
                                        <i class="fas fa-angle-left right"></i>
                                        <span class="badge badge-info right">2</span>
                                    </p>
                                </a>
                                <ul class="nav nav-treeview">
                                    <li class="nav-item">
                                        <a href="<?= base_url('Master_GA_Aset') ?>"
                                            class="nav-link <?= ($id_menu == 'Master_GA_Aset') ? 'active' : '' ?>">
                                            <i class="far fa-dot-circle nav-icon"></i>
                                            <p>Kode Aset</p>
                                        </a>
                                    </li>
                                </ul>
                            </li>

                            <!-- ASEt KENDARAAN -->
                            <li class="nav-item">
                                <a href="<?= base_url('GA_Aset_Kendaraan') ?>"
                                    class="nav-link <?= ($id_menu == 'GA_Aset_Kendaraan') ? 'active' : '' ?>">
                                    <i class="nav-icon fas fa-file-invoice-dollar"></i>
                                    <p>Aset Kendaraan</p>
                                </a>
                            </li>

                            <li
                                class="nav-item has-treeview <?= ($id_menu == 'GA_Aset_Terminasi') ? 'menu-open' : '' ?>">
                                <a href="#" class="nav-link <?= ($id_menu == 'GA_Aset_Terminasi') ? 'active' : '' ?>">
                                    <i class="far fa-folder nav-icon"></i>
                                    <p>
                                        Alker Saker
                                        <i class="fas fa-angle-left right"></i>
                                        <span class="badge badge-info right">2</span>
                                    </p>
                                </a>
                                <ul class="nav nav-treeview">
                                    <li class="nav-item">
                                        <a href="<?= base_url('GA_Aset_Kantor') ?>"
                                            class="nav-link <?= ($id_menu == 'GA_Aset_Kantor') ? 'active' : '' ?>">
                                            <i class="far fa-dot-circle nav-icon"></i>
                                            <p>Alat Kerja Kantor</p>
                                        </a>
                                    </li>
                                </ul>

                                <ul class="nav nav-treeview">
                                    <li class="nav-item">
                                        <a href="<?= base_url('GA_Alat_Terminasi') ?>"
                                            class="nav-link <?= ($id_menu == 'GA_Alat_Terminasi') ? 'active' : '' ?>">
                                            <i class="far fa-dot-circle nav-icon"></i>
                                            <p>Alat Terminasi</p>
                                        </a>
                                    </li>
                                </ul>

                                <ul class="nav nav-treeview">
                                    <li class="nav-item">
                                        <a href="<?= base_url('GA_Sarana_Kerja') ?>"
                                            class="nav-link <?= ($id_menu == 'GA_Sarana_Kerja') ? 'active' : '' ?>">
                                            <i class="far fa-dot-circle nav-icon"></i>
                                            <p>Sarana Kerja</p>
                                        </a>
                                    </li>
                                </ul>
                            </li>

                            <li
                                class="nav-item has-treeview <?= ($id_menu == 'GA_Aset_Terminasi') ? 'menu-open' : '' ?>">
                                <a href="#" class="nav-link <?= ($id_menu == 'GA_Aset_Terminasi') ? 'active' : '' ?>">
                                    <i class="far fa-folder nav-icon"></i>
                                    <p>
                                        Inventaris Kantor
                                        <i class="fas fa-angle-left right"></i>
                                        <span class="badge badge-info right">2</span>
                                    </p>
                                </a>
                                <ul class="nav nav-treeview">
                                    <li class="nav-item" style="pointer-events: none">
                                        <a href="<?= base_url('GA_Aset_Terminasi') ?>"
                                            class="nav-link <?= ($id_menu == 'GA_Aset_Terminasi') ? 'active' : '' ?>">
                                            <i class="far fa-dot-circle nav-icon"></i>
                                            <p>Perlengkapan Kantor</p>
                                        </a>
                                    </li>
                                </ul>

                                <ul class="nav nav-treeview">
                                    <li class="nav-item">
                                        <a href="<?= base_url('GA_Seragam_Kantor') ?>"
                                            class="nav-link <?= ($id_menu == 'GA_Seragam_Kantor') ? 'active' : '' ?>">
                                            <i class="far fa-dot-circle nav-icon"></i>
                                            <p>Seragam Kantor</p>
                                        </a>
                                    </li>
                                </ul>
                            </li>

                            <!-- GUDANG -->
                            <li
                                class="nav-item has-treeview <?= ($id_menu == 'inhouse' || $id_menu == 'Subcon') ? 'menu-open' : '' ?>">
                                <a href="#"
                                    class="nav-link <?= ($id_menu == 'inhouse' || $id_menu == 'Subcon') ? 'active' : '' ?>">
                                    <i class="far fa-folder nav-icon"></i>
                                    <p>
                                        Gudang
                                        <i class="fas fa-angle-left right"></i>
                                        <span class="badge badge-info right">2</span>
                                    </p>
                                </a>
                                <ul class="nav nav-treeview">
                                    <li class="nav-item" style="pointer-events: none;">
                                        <a href="<?= base_url('inhouse') ?>"
                                            class="nav-link <?= ($id_menu == 'inhouse') ? 'act ive' : '' ?>">
                                            <i class="far fa-dot-circle nav-icon"></i>
                                            <p>Inhouse</p>
                                        </a>
                                    </li>
                                    <li class="nav-item" style="pointer-events: none;">
                                        <a href="<?= base_url('Subcon') ?>"
                                            class="nav-link <?= ($id_menu == 'Subcon') ? 'active' : '' ?>">
                                            <i class="far fa-dot-circle nav-icon"></i>
                                            <p>Subcon</p>
                                        </a>
                                    </li>
                                </ul>
                            </li>

                        </ul>
                    </li>
                    <li class="nav-header">Control Project</li>
                    <li class="nav-item has-treeview <?= (
                        $id_menu == 'Fiberstar_PO' ||
                        $id_menu == 'Fiberstar_Project' ||
                        $id_menu == 'Fiberstar_Project_Detail' ||
                        $id_menu == 'Fiberstar_Kompensasi' ||
                        $id_menu == 'MyRepublik_PO' ||
                        $id_menu == 'MyRepublik_Project'
                    ) ? 'menu-open' : '' ?>">
                        <a href="#" class="nav-link <?= (
                            $id_menu == 'Fiberstar_PO' ||
                            $id_menu == 'Fiberstar_Project' ||
                            $id_menu == 'Fiberstar_Project_Detail' ||
                            $id_menu == 'Fiberstar_Kompensasi' ||
                            $id_menu == 'MyRepublik_PO' ||
                            $id_menu == 'MyRepublik_Project'
                        ) ? 'active' : '' ?>">
                            <i class="nav-icon fas fa-project-diagram"></i>
                            <p>
                                Project
                                <i class="fas fa-angle-left right"></i>
                                <span class="badge badge-info right">2</span>
                            </p>
                        </a>

                        <ul class="nav nav-treeview">
                            <!-- Fiberstar -->
                            <li class="nav-item has-treeview <?= (
                                $id_menu == 'Fiberstar_PO' ||
                                $id_menu == 'Fiberstar_Project' ||
                                $id_menu == 'Fiberstar_Project_Detail' ||
                                $id_menu == 'Fiberstar_Kompensasi'
                            ) ? 'menu-open' : '' ?>">
                                <a href="#" class="nav-link <?= (
                                    $id_menu == 'Fiberstar_PO' ||
                                    $id_menu == 'Fiberstar_Project' ||
                                    $id_menu == 'Fiberstar_Project_Detail' ||
                                    $id_menu == 'Fiberstar_Kompensasi'
                                ) ? 'active' : '' ?>">
                                    <i class="nav-icon fas fa-file-invoice-dollar"></i>
                                    <p>
                                        Fiberstar
                                        <i class="fas fa-angle-left right"></i>
                                        <span class="badge badge-info right">3</span>
                                    </p>
                                </a>
                                <ul class="nav nav-treeview">
                                    <li class="nav-item">
                                        <a href="<?= base_url('Fiberstar_Project') ?>"
                                            class="nav-link <?= ($id_menu == 'Fiberstar_Project' || $id_menu == 'Fiberstar_Project_Detail') ? 'active' : '' ?>">
                                            <i class="far fa-dot-circle nav-icon"></i>
                                            <p>List Project</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="<?= base_url('Fiberstar_PO') ?>"
                                            class="nav-link <?= ($id_menu == 'Fiberstar_PO') ? 'active' : '' ?>">
                                            <i class="far fa-dot-circle nav-icon"></i>
                                            <p>PO & Invoice</p>
                                        </a>
                                    </li>
                                    <li class="nav-item" style="pointer-events: none;">
                                        <a href="<?= base_url('Fiberstar_Kompensasi') ?>"
                                            class="nav-link <?= ($id_menu == 'Fiberstar_Kompensasi') ? 'active' : '' ?>">
                                            <i class="far fa-dot-circle nav-icon"></i>
                                            <p>Kompensasi</p>
                                        </a>
                                    </li>
                                </ul>
                            </li>

                            <!-- My Republik -->
                            <li class="nav-item has-treeview <?= (
                                $id_menu == 'MyRepublik_Project' ||
                                $id_menu == 'MyRepublik_PO'
                            ) ? 'menu-open' : '' ?>">
                                <a href="#" class="nav-link <?= (
                                    $id_menu == 'MyRepublik_Project' ||
                                    $id_menu == 'MyRepublik_PO'
                                ) ? 'active' : '' ?>">
                                    <i class="nav-icon fas fa-file-invoice-dollar"></i>
                                    <p>
                                        My Republik
                                        <i class="fas fa-angle-left right"></i>
                                        <span class="badge badge-info right">2</span>
                                    </p>
                                </a>
                                <ul class="nav nav-treeview">
                                    <li class="nav-item">
                                        <a href="<?= base_url('MyRepublik_Project') ?>"
                                            class="nav-link <?= ($id_menu == 'MyRepublik_Project') ? 'active' : '' ?>">
                                            <i class="far fa-dot-circle nav-icon"></i>
                                            <p>List Project</p>
                                        </a>
                                    </li>
                                    <li class="nav-item" style="pointer-events: none;">
                                        <a href="<?= base_url('MyRepublik_PO') ?>"
                                            class="nav-link <?= ($id_menu == 'MyRepublik_PO') ? 'active' : '' ?>">
                                            <i class="far fa-dot-circle nav-icon"></i>
                                            <p>PO & Invoice</p>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-header">TARGET 110 M</li>
                    <li class="nav-item">
                        <a href="<?= base_url('TargetInvoice') ?>" class="nav-link <?php if ($id_menu == 'TargetInvoice') {
                              echo "active";
                          } ?>">
                            <i class="nav-icon fas fa-file-invoice-dollar"></i>
                            <p>
                                Target Invoice
                            </p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= base_url('RincianInvoice') ?>" class="nav-link <?php if ($id_menu == 'RincianInvoice') {
                              echo "active";
                          } ?>">
                            <i class="nav-icon fas fa-file-invoice-dollar"></i>
                            <p>
                                Rincian Invoice
                            </p>
                        </a>
                    </li>

                    <li class="nav-header">Development</li>
                    <li class="nav-item" style="pointer-events: none">
                        <a href="<?= base_url('Backup') ?>" class="nav-link <?php if ($id_menu == 'forbidden') {
                              echo "active";
                          } ?>">
                            <i class="nav-icon fas fa-file-invoice-dollar"></i>
                            <p>
                                Database
                            </p>
                        </a>
                    </li>
                    <li class="nav-header">Gul House</li>
                    <li class="nav-item has-treeview <?php if (
                        $id_menu == 'GHDashboard' ||
                        $id_menu == 'GHRoomsAssets' ||
                        $id_menu == 'GHTenantsRooms' ||
                        $id_menu == 'GHRooms' ||
                        $id_menu == 'GHAssets' ||
                        $id_menu == 'GHTenants'
                    ) {
                        echo 'menu-open';
                    } ?>">

                        <a href="#" class="nav-link <?php if (
                            $id_menu == 'GHDashboard' ||
                            $id_menu == 'GHRoomsAssets' ||
                            $id_menu == 'GHTenantsRooms' ||
                            $id_menu == 'GHRooms' ||
                            $id_menu == 'GHAssets' ||
                            $id_menu == 'GHTenants'
                        ) {
                            echo 'active';
                        } ?>">
                            <i class="nav-icon fas fa-boxes"></i>
                            <p>
                                Gul House
                                <i class="right fas fa-angle-left"></i>
                                <span class="badge badge-info right">5</span>
                            </p>
                        </a>

                        <ul class="nav nav-treeview">

                            <li class="nav-item">
                                <a href="<?= base_url('GHDashboard') ?>" class="nav-link <?php if ($id_menu == 'GHDashboard')
                                      echo 'active'; ?>">
                                    <i class="far fa-file nav-icon"></i>
                                    <p>Dashboard Gull House</p>
                                </a>
                            </li>
                            <!-- Master Logistik -->
                            <li class="nav-item has-treeview <?php if (
                                $id_menu == 'GHRooms' ||
                                $id_menu == 'GHAssets' ||
                                $id_menu == 'GHTenants'
                            ) {
                                echo 'menu-open';
                            } ?>">
                                <a href="#" class="nav-link <?php if (
                                    $id_menu == 'GHRooms' ||
                                    $id_menu == 'GHAssets' ||
                                    $id_menu == 'GHTenants'
                                ) {
                                    echo 'active';
                                } ?>">
                                    <i class="far fa-folder nav-icon"></i>
                                    <p>Master Data
                                        <i class="right fas fa-angle-left"></i>
                                        <span class="badge badge-info right">3</span>
                                    </p>
                                </a>
                                <ul class="nav nav-treeview">
                                    <li class="nav-item">
                                        <a href="<?= base_url('GHRooms') ?>" class="nav-link <?php if ($id_menu == 'GHRooms')
                                              echo 'active'; ?>">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Master Rooms</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="<?= base_url('GHTenants') ?>" class="nav-link <?php if ($id_menu == 'GHTenants')
                                              echo 'active'; ?>">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Master Tenants
                                            </p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="<?= base_url('GHAssets') ?>" class="nav-link <?php if ($id_menu == 'GHAssets')
                                              echo 'active'; ?>">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Master Asets</p>
                                        </a>
                                    </li>
                                </ul>
                            </li>

                            <li class="nav-item">
                                <a href="<?= base_url('GHRoomsAssets') ?>" class="nav-link <?php if ($id_menu == 'GHRoomsAssets')
                                      echo 'active'; ?>">
                                    <i class="far fa-file nav-icon"></i>
                                    <p>List Rooms - Assets</p>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="<?= base_url('GHTenantsRooms') ?>" class="nav-link <?php if ($id_menu == 'GHTenantsRooms')
                                      echo 'active'; ?>">
                                    <i class="far fa-file nav-icon"></i>
                                    <p>List Tenants - Rooms</p>
                                </a>
                            </li>

                            <!-- Purchase Order -->
                            <li class="nav-item" style="pointer-events: none">
                                <a href="<?= base_url('aLogistik_Pesanan_Pabrik') ?>" class="nav-link <?php if ($id_menu == 'aLogistik_Pesanan_Pabrik')
                                      echo 'active'; ?>">
                                    <i class="far fa-file nav-icon"></i>
                                    <p>Pembayaran Kos</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-header">Budgeting</li>
                    <li class="nav-item has-treeview <?php if (
                        $id_menu == 'GHDashboard' ||
                        $id_menu == 'GHRoomsAssets' ||
                        $id_menu == 'GHTenantsRooms' ||
                        $id_menu == 'GHRooms' ||
                        $id_menu == 'GHAssets' ||
                        $id_menu == 'GHTenants'
                    ) {
                        echo 'menu-open';
                    } ?>">

                        <a href="#" class="nav-link <?php if (
                            $id_menu == 'GHDashboard' ||
                            $id_menu == 'GHRoomsAssets' ||
                            $id_menu == 'GHTenantsRooms' ||
                            $id_menu == 'GHRooms' ||
                            $id_menu == 'GHAssets' ||
                            $id_menu == 'GHTenants'
                        ) {
                            echo 'active';
                        } ?>">
                            <i class="nav-icon fas fa-boxes"></i>
                            <p>
                                Budget Ops
                                <i class="right fas fa-angle-left"></i>
                                <span class="badge badge-info right">5</span>
                            </p>
                        </a>

                        <ul class="nav nav-treeview">

                            <li class="nav-item">
                                <a href="<?= base_url('Budget_Cashflow') ?>" class="nav-link <?php if ($id_menu == 'Budget_Cashflow')
                                      echo 'active'; ?>">
                                    <i class="far fa-file nav-icon"></i>
                                    <p>List Cashflow</p>
                                </a>
                            </li>
                            <!-- Master Logistik -->
                            <li class="nav-item has-treeview <?php if (
                                $id_menu == 'GHRooms' ||
                                $id_menu == 'GHAssets' ||
                                $id_menu == 'GHTenants'
                            ) {
                                echo 'menu-open';
                            } ?>">
                                <a href="#" class="nav-link <?php if (
                                    $id_menu == 'GHRooms' ||
                                    $id_menu == 'GHAssets' ||
                                    $id_menu == 'GHTenants'
                                ) {
                                    echo 'active';
                                } ?>">
                                    <i class="far fa-folder nav-icon"></i>
                                    <p>Master Budgeting
                                        <i class="right fas fa-angle-left"></i>
                                        <span class="badge badge-info right">3</span>
                                    </p>
                                </a>
                                <ul class="nav nav-treeview">
                                    <li class="nav-item">
                                        <a href="<?= base_url('Budget_MasterAkunBiaya') ?>" class="nav-link <?php if ($id_menu == 'Budget_MasterAkunBiaya')
                                              echo 'active'; ?>">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Master Akun Biaya</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="<?= base_url('GHTenants') ?>" class="nav-link <?php if ($id_menu == 'GHTenants')
                                              echo 'active'; ?>">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Master Tenants
                                            </p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="<?= base_url('GHAssets') ?>" class="nav-link <?php if ($id_menu == 'GHAssets')
                                              echo 'active'; ?>">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Master Asets</p>
                                        </a>
                                    </li>
                                </ul>
                            </li>

                            <li class="nav-item">
                                <a href="<?= base_url('Budget_Report') ?>" class="nav-link <?php if ($id_menu == 'Budget_Report')
                                      echo 'active'; ?>">
                                    <i class="far fa-file nav-icon"></i>
                                    <p>Report Cashflow Monthly</p>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="<?= base_url('GHTenantsRooms') ?>" class="nav-link <?php if ($id_menu == 'GHTenantsRooms')
                                      echo 'active'; ?>">
                                    <i class="far fa-file nav-icon"></i>
                                    <p>List Tenants - Rooms</p>
                                </a>
                            </li>

                            <!-- Purchase Order -->
                            <li class="nav-item" style="pointer-events: none">
                                <a href="<?= base_url('aLogistik_Pesanan_Pabrik') ?>" class="nav-link <?php if ($id_menu == 'aLogistik_Pesanan_Pabrik')
                                      echo 'active'; ?>">
                                    <i class="far fa-file nav-icon"></i>
                                    <p>Pembayaran Kos</p>
                                </a>
                            </li>
                        </ul>
                    </li>

                </ul>
            </nav>
            <!-- /.sidebar-menu -->
        </div>
        <!-- /.sidebar -->
    </aside>

    <script src="<?= base_url('assets') ?>/plugins/jquery/jquery.min.js"></script>
    <!-- Bootstrap -->
    <script src="<?= base_url('assets') ?>/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- AdminLTE -->
    <script src="<?= base_url('assets') ?>dist/js/adminlte.js"></script>

    <!-- OPTIONAL SCRIPTS -->
    <script src="<?= base_url('assets') ?>/plugins/chart.js/Chart.min.js"></script>
    <script src="<?= base_url('assets') ?>/dist/js/demo.js"></script>
    <script src="<?= base_url('assets') ?>/dist/js/pages/dashboard3.js"></script>