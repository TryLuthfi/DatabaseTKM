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
            <li class="nav-item d-none d-sm-inline-block">
                <a href="<?= base_url('Dashboard') ?>" class="nav-link">Dashboard</a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <a href="<?= base_url('Fiberstar_Project') ?>" class="nav-link">Fiberstars</a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <a href="<?= base_url('MyRepublik_Project') ?>" class="nav-link">My Republik</a>
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
                    <li class="nav-item">
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

                    <?php if ($this->session->userdata('nama_level') == "Super Admin") { ?>
                        <li class="nav-item has-treeview <?php if ($id_menu == 'Master_Logistik_Lokasi_Gudang' || $id_menu == 'Master_Logistik_Kode_Item' || $id_menu == 'Master_Logistik_Sumber_Material' || $id_menu == 'Master_Logistik_Pabrik') {
                            echo "menu-open";
                        } ?>">
                            <a href="#" class="nav-link <?php if ($id_menu == 'Master_Logistik_Lokasi_Gudang' || $id_menu == 'Master_Logistik_Kode_Item' || $id_menu == 'Master_Logistik_Sumber_Material' || $id_menu == 'Master_Logistik_Pabrik') {
                                echo "active";
                            } ?>">
                                <i class="nav-icon fas fa-money-check-alt"></i>
                                <p>
                                    Master Logistik
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
                                    <a href="<?= base_url('Master_Logistik_Lokasi_Gudang') ?>" class="nav-link <?php if ($id_menu == 'Master_Logistik_Lokasi_Gudang') {
                                          echo "active";
                                      } ?>">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Lokasi Gudang</p>
                                    </a>
                                </li>
                            </ul>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="<?= base_url('Master_Logistik_Kode_Item') ?>" class="nav-link <?php if ($id_menu == 'Master_Logistik_Kode_Item') {
                                          echo "active";
                                      } ?>">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Kode Item</p>
                                    </a>
                                </li>
                            </ul>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="<?= base_url('Master_Logistik_Sumber_Material') ?>" class="nav-link <?php if ($id_menu == 'Master_Logistik_Sumber_Material') {
                                          echo "active";
                                      } ?>">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Sumber Material</p>
                                    </a>
                                </li>
                            </ul>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="<?= base_url('Master_Logistik_Pabrik') ?>" class="nav-link <?php if ($id_menu == 'Master_Logistik_Pabrik') {
                                          echo "active";
                                      } ?>">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Pabrik</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    <?php } ?>

                    <li class="nav-item">
                        <a href="<?= base_url('Logistik_Pesanan_Pabrik') ?>" class="nav-link <?php if ($id_menu == 'Logistik_Pesanan_Pabrik') {
                              echo "active";
                          } ?>">
                            <i class="nav-icon fas fa-file-invoice-dollar"></i>
                            <p>
                                Pesanan Pabrik
                            </p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="<?= base_url('Dashboard_Logistik_Stok') ?>" class="nav-link <?php if ($id_menu == 'Dashboard_Logistik_Stok') {
                              echo "active";
                          } ?>">
                            <i class="nav-icon fas fa-file-invoice-dollar"></i>
                            <p>
                                Dashboard Logistik
                            </p>
                        </a>
                    </li>

                    <li class="nav-header">Project</li>

                    <li class="nav-item has-treeview <?php if ($id_menu == 'Fiberstar_PO' || $id_menu == 'Fiberstar_Project' || $id_menu == 'Fiberstar_Project_Detail') {
                        echo "menu-open";
                    } ?>">
                        <a href="#" class="nav-link <?php if ($id_menu == 'Fiberstar_PO' || $id_menu == 'Fiberstar_Project' || $id_menu == 'Fiberstar_Project_Detail') {
                            echo "active";
                        } ?>">
                            <i class="nav-icon fas fa-money-check-alt"></i>
                            <p>
                                Fiberstar
                                <i class="fas fa-angle-left right"></i>
                                <span class="badge badge-info right">
                                    <?php if ($this->session->userdata('nama_level') == "Super Admin") { ?>2<?php } else { ?>2<?php } ?></span>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                            </li>
                        </ul>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="<?= base_url('Fiberstar_Project') ?>" class="nav-link <?php if ($id_menu == 'Fiberstar_Project' || $id_menu == 'Fiberstar_Project_Detail') {
                                      echo "active";
                                  } ?>">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>List Project</p>
                                </a>
                            </li>
                        </ul>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="<?= base_url('Fiberstar_PO') ?>" class="nav-link <?php if ($id_menu == 'Fiberstar_PO') {
                                      echo "active";
                                  } ?>">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>PO & Invoice</p>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li class="nav-item has-treeview <?php if ($id_menu == 'MyRepublik_PO' || $id_menu == 'MyRepublik_Project') {
                        echo "menu-open";
                    } ?>">
                        <a href="#" class="nav-link <?php if ($id_menu == 'MyRepublik_PO' || $id_menu == 'MyRepublik_Project') {
                            echo "active";
                        } ?>">
                            <i class="nav-icon fas fa-money-check-alt"></i>
                            <p>
                                My Republik
                                <i class="fas fa-angle-left right"></i>
                                <span class="badge badge-info right">2</span>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                            </li>
                        </ul>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="<?= base_url('MyRepublik_Project') ?>" class="nav-link <?php if ($id_menu == 'MyRepublik_Project') {
                                      echo "active";
                                  } ?>">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>List Project</p>
                                </a>
                            </li>
                        </ul>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a class="nav-link <?php if ($id_menu == 'MyRepublik_PO') {
                                    echo "active";
                                } ?>">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>PO & Invoice</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-header">Report</li>
                    <li class="nav-item">
                        <a href="<?= base_url('Neraca') ?>" class="nav-link <?php if ($id_menu == 'Neraca') {
                              echo "active";
                          } ?>">
                            <i class="nav-icon fas fa-file-invoice-dollar"></i>
                            <p>
                                Pendapatan
                            </p>
                        </a>
                        <a href="<?= base_url('Neraca') ?>" class="nav-link <?php if ($id_menu == 'Neraca') {
                              echo "active";
                          } ?>">
                            <i class="nav-icon fas fa-file-invoice-dollar"></i>
                            <p>
                                Invoice
                            </p>
                        </a>
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