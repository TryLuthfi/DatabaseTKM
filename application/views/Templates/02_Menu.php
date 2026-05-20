<?php
$id_menu = $this->uri->segment('1');
$canAccessBudgeting = $this->session->userdata('nama_level') == "Super Admin" || has_validation_access('Budgeting');
$canAccessBilco = $this->session->userdata('nama_level') == "Super Admin" || has_validation_access('BILCO');
$disabledBudgetLinkClass = $canAccessBudgeting ? '' : ' menu-access-disabled';
$disabledBudgetLinkAttr = $canAccessBudgeting ? '' : ' tabindex="-1" aria-disabled="true" onclick="return false;"';
$disabledBilcoLinkClass = $canAccessBilco ? '' : ' menu-access-disabled';
$disabledBilcoLinkAttr = $canAccessBilco ? '' : ' tabindex="-1" aria-disabled="true" onclick="return false;"';
?>
<div class="wrapper premium-shell">
    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-dark premium-topbar">
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
                <a href="<?= base_url('Dashboard_Logistik_Stok/revamp') ?>" class="nav-link">Logistik</a>
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
    <aside class="main-sidebar sidebar-dark-primary elevation-4 premium-sidebar">
        <!-- Brand Logo -->
        <a href="<?= base_url('Dashboard') ?>" class="brand-link premium-brand-link">
            <img src="<?= base_url('assets') ?>/dist/img/logotkmsolid.png" alt="AdminLTE Logo"
                class="brand-image img-circle elevation-3 premium-brand-image" style="opacity: .9">
            <span class="brand-text font-weight-light premium-brand-text">PT. TKM</span>
        </a>

        <!-- Sidebar -->
        <div class="sidebar" style="padding-top: 20px;">
            <!-- Sidebar user panel (optional) -->
            <div class="user-panel mt-3 pb-3 mb-3 d-flex premium-user-panel">
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
                    <li class="nav-item has-treeview <?php if (
                        $id_menu == 'Master_Logistik_Lokasi_Gudang' ||
                        $id_menu == 'Master_Logistik_Kode_Item' ||
                        $id_menu == 'Master_Logistik_Sumber_Material' ||
                        $id_menu == 'Master_Logistik_Pabrik' ||
                        $id_menu == 'Logistik_Purchase_Request' ||
                        $id_menu == 'Logistik_Pesanan_Pabrik_Detail' ||
                        $id_menu == 'Logistik_Nota_Dinas_Po' ||
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
                            $id_menu == 'Logistik_Pesanan_Pabrik_Detail' ||
                            $id_menu == 'Logistik_Nota_Dinas_Po' ||
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
                                <span class="badge badge-info right">4</span>
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
                                        <span class="badge badge-info right">4</span>
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

                            <!-- PR / PO -->
                            <li class="nav-item has-treeview <?php if (
                                $id_menu == 'Logistik_Purchase_Request' ||
                                $id_menu == 'Logistik_Nota_Dinas_Po' ||
                                $id_menu == 'Logistik_Pesanan_Pabrik' ||
                                $id_menu == 'Logistik_Pesanan_Pabrik_Detail'
                            ) {
                                echo 'menu-open';
                            } ?>">
                                <a href="#" class="nav-link <?php if (
                                    $id_menu == 'Logistik_Purchase_Request' ||
                                    $id_menu == 'Logistik_Nota_Dinas_Po' ||
                                    $id_menu == 'Logistik_Pesanan_Pabrik' ||
                                    $id_menu == 'Logistik_Pesanan_Pabrik_Detail'
                                ) {
                                    echo 'active';
                                } ?>">
                                    <i class="far fa-folder nav-icon"></i>
                                    <p>PR / PO
                                        <i class="right fas fa-angle-left"></i>
                                        <span class="badge badge-info right">3</span>
                                    </p>
                                </a>
                                <ul class="nav nav-treeview">
                                    <li class="nav-item">
                                        <a href="<?= base_url('Logistik_Purchase_Request') ?>" class="nav-link <?php if ($id_menu == 'Logistik_Purchase_Request')
                                              echo 'active'; ?>">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Purchase Request</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="<?= base_url('Logistik_Nota_Dinas_Po') ?>" class="nav-link <?php if ($id_menu == 'Logistik_Nota_Dinas_Po')
                                              echo 'active'; ?>">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Nota Dinas</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="<?= base_url('Logistik_Pesanan_Pabrik') ?>" class="nav-link <?php if ($id_menu == 'Logistik_Pesanan_Pabrik' || $id_menu == 'Logistik_Pesanan_Pabrik_Detail')
                                              echo 'active'; ?>">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Purchase Order</p>
                                        </a>
                                    </li>
                                </ul>
                            </li>

                            <!-- Stok Material -->
                            <li class="nav-item has-treeview <?php if (
                                $id_menu == 'Dashboard_Logistik_Stok' ||
                                $id_menu == 'Logistik_Stok_Detail'
                            ) {
                                echo 'menu-open';
                            } ?>">
                                <a href="#" class="nav-link <?php if (
                                    $id_menu == 'Dashboard_Logistik_Stok' ||
                                    $id_menu == 'Logistik_Stok_Detail'
                                ) {
                                    echo 'active';
                                } ?>">
                                    <i class="far fa-folder nav-icon"></i>
                                    <p>Stok Material
                                        <i class="right fas fa-angle-left"></i>
                                        <span class="badge badge-info right">2</span>
                                    </p>
                                </a>
                                <ul class="nav nav-treeview">
                                    <li class="nav-item">
                                        <a href="<?= base_url('Dashboard_Logistik_Stok/revamp') ?>" class="nav-link <?php if (($id_menu == 'Dashboard_Logistik_Stok' && $this->uri->segment(2) != 'transit_history') || $id_menu == 'Logistik_Stok_Detail')
                                              echo 'active'; ?>">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Stok On Hand</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="<?= base_url('Dashboard_Logistik_Stok/transit_history') ?>" class="nav-link <?php if ($id_menu == 'Dashboard_Logistik_Stok' && $this->uri->segment(2) == 'transit_history')
                                              echo 'active'; ?>">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Stok Pengiriman</p>
                                        </a>
                                    </li>
                                </ul>
                            </li>

                            <!-- Stock Opname -->
                            <li class="nav-item">
                                <a href="<?= base_url('StockOpname/revamp') ?>" class="nav-link <?php if ($id_menu == 'StockOpname')
                                      echo 'active'; ?>">
                                    <i class="far fa-star nav-icon"></i>
                                    <p>Stok Opname</p>
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
                        $id_menu == 'MyRepublik_Project' ||
                        $id_menu == 'DRM_MyRep' ||
                        $id_menu == 'Monitoring_RFS_MyRep' ||
                        $id_menu == 'ATP_MyRep' ||
                        $id_menu == 'Checklist_Dokument_MyRep' ||
                        $id_menu == 'BAK_MyRep' ||
                        $id_menu == 'VALSAL_MyRep' ||
                        $id_menu == 'Batch_Approval_MyRep' ||
                        $id_menu == 'Implementasi_BOQ_MyRep' ||
                        $id_menu == 'PO_MyRep' ||
                        $id_menu == 'DRM_MyRep' ||
                        $id_menu == 'Kontrak_Payung' ||
                        $id_menu == 'SPK'
                    ) ? 'menu-open' : '' ?>">
                        <a href="#" class="nav-link <?= (
                            $id_menu == 'Fiberstar_PO' ||
                            $id_menu == 'Fiberstar_Project' ||
                            $id_menu == 'Fiberstar_Project_Detail' ||
                            $id_menu == 'Fiberstar_Kompensasi' ||
                            $id_menu == 'MyRepublik_PO' ||
                            $id_menu == 'MyRepublik_Project' ||
                            $id_menu == 'DRM_MyRep' ||
                            $id_menu == 'Monitoring_RFS_MyRep' ||
                            $id_menu == 'ATP_MyRep' ||
                            $id_menu == 'Checklist_Dokument_MyRep' ||
                            $id_menu == 'BAK_MyRep' ||
                            $id_menu == 'VALSAL_MyRep' ||
                            $id_menu == 'Batch_Approval_MyRep' ||
                            $id_menu == 'Implementasi_BOQ_MyRep' ||
                            $id_menu == 'PO_MyRep' ||
                            $id_menu == 'DRM_MyRep' ||
                            $id_menu == 'Kontrak_Payung' ||
                            $id_menu == 'SPK'
                        ) ? 'active' : '' ?>">
                            <i class="nav-icon fas fa-project-diagram"></i>
                            <p>
                                Project
                                <i class="fas fa-angle-left right"></i>
                                <span class="badge badge-info right">2</span>
                            </p>
                        </a>

                        <ul class="nav nav-treeview">
                            <li class="nav-item has-treeview <?= ($id_menu == 'Kontrak_Payung' || $id_menu == 'SPK') ? 'menu-open' : '' ?>">
                                <a href="#" class="nav-link <?= ($id_menu == 'Kontrak_Payung' || $id_menu == 'SPK') ? 'active' : '' ?>">
                                    <i class="nav-icon fas fa-file-signature"></i>
                                    <p>
                                        Kontrak & SPK
                                        <i class="fas fa-angle-left right"></i>
                                        <span class="badge badge-info right">2</span>
                                    </p>
                                </a>
                                <ul class="nav nav-treeview">
                                    <li class="nav-item">
                                        <a href="<?= base_url('Kontrak_Payung') ?>"
                                            class="nav-link <?= ($id_menu == 'Kontrak_Payung') ? 'active' : '' ?>">
                                            <i class="far fa-dot-circle nav-icon"></i>
                                            <p>Kontrak Payung (PKS)</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="<?= base_url('SPK') ?>"
                                            class="nav-link <?= ($id_menu == 'SPK') ? 'active' : '' ?>">
                                            <i class="far fa-dot-circle nav-icon"></i>
                                            <p>SPK</p>
                                        </a>
                                    </li>
                                </ul>
                            </li>
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
                                ) ? 'active' : '' ?>" style="pointer-events: none">
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
                                $id_menu == 'MyRepublik_PO' ||
                                $id_menu == 'BAK_MyRep' ||
                                $id_menu == 'VALSAL_MyRep' ||
                                $id_menu == 'Batch_Approval_MyRep' ||
                                $id_menu == 'DRM_MyRep' ||
                                $id_menu == 'Implementasi_BOQ_MyRep' ||
                                $id_menu == 'PO_MyRep' ||
                                $id_menu == 'Monitoring_RFS_MyRep' ||
                                $id_menu == 'ATP_MyRep' ||
                                $id_menu == 'Checklist_Dokument_MyRep' ||
                                $id_menu == 'BAK_MyRep' ||
                                $id_menu == 'VALSAL_MyRep' ||
                                $id_menu == 'Batch_Approval_MyRep' ||
                                $id_menu == 'DRM_MyRep'
                            ) ? 'menu-open' : '' ?>">
                                <a href="#" class="nav-link <?= (
                                    $id_menu == 'MyRepublik_Project' ||
                                    $id_menu == 'MyRepublik_PO' ||
                                    $id_menu == 'BAK_MyRep' ||
                                    $id_menu == 'VALSAL_MyRep' ||
                                    $id_menu == 'Batch_Approval_MyRep' ||
                                    $id_menu == 'DRM_MyRep' ||
                                    $id_menu == 'Implementasi_BOQ_MyRep' ||
                                    $id_menu == 'PO_MyRep' ||
                                    $id_menu == 'Monitoring_RFS_MyRep' ||
                                    $id_menu == 'ATP_MyRep' ||
                                    $id_menu == 'Checklist_Dokument_MyRep' ||
                                    $id_menu == 'BAK_MyRep' ||
                                    $id_menu == 'VALSAL_MyRep' ||
                                    $id_menu == 'Batch_Approval_MyRep' ||
                                    $id_menu == 'DRM_MyRep'
                                ) ? 'active' : '' ?>">
                                    <i class="nav-icon fas fa-file-invoice-dollar"></i>
                                    <p>
                                        My Republik
                                        <i class="fas fa-angle-left right"></i>
                                        <span class="badge badge-info right"><?= $this->session->userdata('nama_level') == "Super Admin" ? '12' : '11' ?></span>
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
                                    <li class="nav-item">
                                        <a href="<?= base_url('BAK_MyRep') ?>"
                                            class="nav-link <?= ($id_menu == 'BAK_MyRep') ? 'active' : '' ?>">
                                            <i class="far fa-dot-circle nav-icon"></i>
                                            <p>BAK</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="<?= base_url('VALSAL_MyRep') ?>"
                                            class="nav-link <?= ($id_menu == 'VALSAL_MyRep') ? 'active' : '' ?>">
                                            <i class="far fa-dot-circle nav-icon"></i>
                                            <p>VALSAL</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="<?= base_url('Batch_Approval_MyRep') ?>"
                                            class="nav-link <?= ($id_menu == 'Batch_Approval_MyRep') ? 'active' : '' ?>">
                                            <i class="far fa-dot-circle nav-icon"></i>
                                            <p>Batch Approval</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="<?= base_url('DRM_MyRep') ?>"
                                            class="nav-link <?= ($id_menu == 'DRM_MyRep') ? 'active' : '' ?>">
                                            <i class="far fa-dot-circle nav-icon"></i>
                                            <p>DRM</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="<?= base_url('Implementasi_BOQ_MyRep') ?>"
                                            class="nav-link <?= ($id_menu == 'Implementasi_BOQ_MyRep') ? 'active' : '' ?>">
                                            <i class="far fa-dot-circle nav-icon"></i>
                                            <p>Implementasi BOQ</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="<?= base_url('PO_MyRep') ?>"
                                            class="nav-link <?= ($id_menu == 'PO_MyRep') ? 'active' : '' ?>">
                                            <i class="far fa-dot-circle nav-icon"></i>
                                            <p>PO MyRep</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="<?= base_url('Monitoring_RFS_MyRep') ?>"
                                            class="nav-link <?= ($id_menu == 'Monitoring_RFS_MyRep') ? 'active' : '' ?>">
                                            <i class="far fa-dot-circle nav-icon"></i>
                                            <p>Monitoring RFS MYREP</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="<?= base_url('ATP_MyRep') ?>"
                                            class="nav-link <?= ($id_menu == 'ATP_MyRep') ? 'active' : '' ?>">
                                            <i class="far fa-dot-circle nav-icon"></i>
                                            <p>ATP</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="<?= base_url('Checklist_Dokument_MyRep') ?>"
                                            class="nav-link <?= ($id_menu == 'Checklist_Dokument_MyRep') ? 'active' : '' ?>">
                                            <i class="far fa-dot-circle nav-icon"></i>
                                            <p>Checklist Dokument</p>
                                        </a>
                                    </li>
                                    <?php if (false && $this->session->userdata('nama_level') == "Super Admin") { ?>
                                        <li class="nav-item">
                                            <a href="<?= base_url('MyRepublik_Project#table_myrep_cluster_list') ?>"
                                                class="nav-link <?= ($id_menu == 'MyRepublik_Project') ? 'active' : '' ?>">
                                                <i class="far fa-trash-alt nav-icon text-danger"></i>
                                                <p>Hapus Cluster MyRep</p>
                                            </a>
                                        </li>
                                    <?php } ?>
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
                    } ?>" style="pointer-events: none">

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
                        $id_menu == 'Budget_MasterAkunBiaya' ||
                        $id_menu == 'Budget_MasterPic' ||
                        $id_menu == 'Budget_Cashflow' ||
                        $id_menu == 'Budget_Report' ||
                        $id_menu == 'Budget_MasterBudgetYears'
                    ) {
                        echo 'menu-open';
                    } ?>">

                        <a href="#" class="nav-link <?php if (
                            $id_menu == 'Budget_MasterAkunBiaya' ||
                            $id_menu == 'Budget_MasterPic' ||
                            $id_menu == 'Budget_Cashflow' ||
                            $id_menu == 'Budget_Report' ||
                            $id_menu == 'Budget_MasterBudgetYears'
                        ) {
                            echo 'active';
                        } ?>">
                            <i class="nav-icon fas fa-boxes"></i>
                            <p>
                                Budget Ops
                                <i class="right fas fa-angle-left"></i>
                                <span class="badge badge-info right">6</span>
                            </p>
                        </a>

                        <ul class="nav nav-treeview">

                            <li class="nav-item has-treeview <?php if (
                                $id_menu == 'Budget_MasterAkunBiaya' ||
                                $id_menu == 'Budget_MasterPic' ||
                                $id_menu == 'Budget_MasterBudgetYears' ||
                                $id_menu == 'Budget_Cashflow' ||
                                $id_menu == 'Budget_Report'
                            ) {
                                echo 'menu-open';
                            } ?>">
                                <a href="#" class="nav-link <?php if (
                                    $id_menu == 'Budget_MasterAkunBiaya' ||
                                    $id_menu == 'Budget_MasterPic' ||
                                    $id_menu == 'Budget_MasterBudgetYears' ||
                                    $id_menu == 'Budget_Cashflow' ||
                                    $id_menu == 'Budget_Report'
                                ) {
                                    echo 'active';
                                } ?>">
                                    <i class="far fa-folder nav-icon"></i>
                                    <p>Master Budgeting
                                        <i class="right fas fa-angle-left"></i>
                                        <span class="badge badge-info right">4</span>
                                    </p>
                                </a>
                                <ul class="nav nav-treeview">
                                    <li class="nav-item">
                                        <a href="<?= base_url('Budget_MasterAkunBiaya') ?>" class="nav-link<?= $disabledBudgetLinkClass ?> <?php if ($id_menu == 'Budget_MasterAkunBiaya')
                                              echo ' active'; ?>"<?= $disabledBudgetLinkAttr ?>>
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Master Item</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="<?= base_url('Budget_MasterPic') ?>" class="nav-link<?= $disabledBudgetLinkClass ?> <?php if ($id_menu == 'Budget_MasterPic')
                                              echo ' active'; ?>"<?= $disabledBudgetLinkAttr ?>>
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Master PIC</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="<?= base_url('Budget_MasterBudgetYears') ?>" class="nav-link<?= $disabledBudgetLinkClass ?> <?php if ($id_menu == 'Budget_MasterBudgetYears')
                                              echo ' active'; ?>"<?= $disabledBudgetLinkAttr ?>>
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Master Budget
                                            </p>
                                        </a>
                                    </li>
                                </ul>
                            </li>

                            <li class="nav-item">
                                <a href="<?= base_url('Budget_Cashflow') ?>" class="nav-link<?= $disabledBudgetLinkClass ?> <?php if ($id_menu == 'Budget_Cashflow')
                                      echo ' active'; ?>"<?= $disabledBudgetLinkAttr ?>>
                                    <i class="far fa-file nav-icon"></i>
                                    <p>Cashflow</p>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="<?= base_url('Budget_Report') ?>" class="nav-link<?= $disabledBudgetLinkClass ?> <?php if ($id_menu == 'Budget_Report')
                                      echo ' active'; ?>"<?= $disabledBudgetLinkAttr ?>>
                                    <i class="far fa-file nav-icon"></i>
                                    <p>Dashboard Budget</p>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li class="nav-header">Billing & Collection</li>
                    <li class="nav-item">
                        <a href="<?= base_url('BillingPayment') ?>" class="nav-link<?= $disabledBilcoLinkClass ?> <?php if ($id_menu == 'BillingPayment') {
                              echo " active";
                          } ?>"<?= $disabledBilcoLinkAttr ?>>
                            <i class="nav-icon fas fa-file-invoice-dollar"></i>
                            <p>
                                Account Receivable
                            </p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= base_url('PO_Monitor') ?>" class="nav-link<?= $disabledBilcoLinkClass ?> <?php if ($id_menu == 'PO_Monitor') {
                              echo " active";
                          } ?>"<?= $disabledBilcoLinkAttr ?>>
                            <i class="nav-icon fas fa-clipboard-list"></i>
                            <p>
                                PO Monitor
                            </p>
                        </a>
                    </li>

                </ul>
            </nav>
            <!-- /.sidebar-menu -->
        </div>
        <!-- /.sidebar -->
    </aside>

    <style>
        .premium-shell .main-header,
        .premium-shell .main-sidebar,
        .premium-shell .brand-link,
        .premium-shell .user-panel,
        .premium-shell .nav-link,
        .premium-shell .nav-header,
        .premium-shell .badge {
            font-family: "Plus Jakarta Sans", "Source Sans Pro", sans-serif;
        }

        .premium-topbar {
            border-bottom: 1px solid rgba(148, 163, 184, 0.14);
            background:
                radial-gradient(circle at top right, rgba(96, 165, 250, 0.14), transparent 26%),
                linear-gradient(135deg, rgba(8, 23, 39, 0.97), rgba(15, 61, 96, 0.95));
            box-shadow: 0 16px 34px rgba(15, 23, 42, 0.14);
            backdrop-filter: blur(12px);
        }

        .premium-topbar .nav-link {
            color: rgba(241, 245, 249, 0.88) !important;
            font-weight: 600;
            letter-spacing: 0.01em;
        }

        .premium-topbar .nav-link:hover,
        .premium-topbar .nav-link:focus {
            color: #ffffff !important;
        }

        .premium-sidebar {
            border-right: 1px solid rgba(148, 163, 184, 0.14);
            background:
                radial-gradient(circle at top left, rgba(59, 130, 246, 0.18), transparent 18%),
                linear-gradient(180deg, #081423 0%, #0d2237 46%, #102d46 100%);
            box-shadow: 16px 0 36px rgba(15, 23, 42, 0.12);
        }

        .premium-brand-link {
            display: flex;
            align-items: center;
            gap: 0.9rem;
            min-height: 72px;
            padding: 0.85rem 1rem;
            border-bottom: 1px solid rgba(148, 163, 184, 0.12);
            background: rgba(255, 255, 255, 0.04);
        }

        .premium-brand-image {
            float: none !important;
            margin: 0 !important;
            width: 40px;
            height: 40px;
            padding: 0.16rem;
            background: rgba(255, 255, 255, 0.08);
            box-shadow: 0 12px 24px rgba(15, 23, 42, 0.22);
        }

        .premium-brand-text {
            font-size: 1.02rem;
            font-weight: 800 !important;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: #f8fafc !important;
        }

        .premium-sidebar .sidebar {
            padding: 0 0.8rem 1rem;
        }

        .premium-user-panel {
            margin: 1rem 0.25rem 1rem !important;
            padding: 0.85rem 0.9rem !important;
            align-items: center;
            gap: 0.78rem;
            border: 1px solid rgba(148, 163, 184, 0.16);
            border-radius: 16px;
            background: linear-gradient(145deg, rgba(255, 255, 255, 0.08), rgba(255, 255, 255, 0.035));
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.06);
        }

        .premium-user-panel .image img {
            width: 38px;
            height: 38px;
            object-fit: cover;
            border: 2px solid rgba(255, 255, 255, 0.16);
        }

        .premium-user-panel .info {
            padding: 0 !important;
        }

        .premium-user-panel .info a {
            color: #f8fafc !important;
            font-weight: 700;
            font-size: 0.94rem;
        }

        .premium-sidebar .nav-header {
            padding: 1rem 0.9rem 0.45rem;
            font-size: 0.7rem;
            font-weight: 800;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: rgba(191, 219, 254, 0.72);
        }

        .premium-sidebar .nav-sidebar .nav-link {
            display: flex;
            align-items: center;
            min-height: 46px;
            margin-bottom: 0.18rem;
            padding: 0.74rem 0.88rem;
            border-radius: 14px;
            color: rgba(226, 232, 240, 0.9);
            font-size: 0.94rem;
            font-weight: 600;
            transition: background-color 0.2s ease, color 0.2s ease, transform 0.2s ease;
        }

        .premium-sidebar .nav-sidebar .nav-link .nav-icon {
            margin-right: 0.7rem;
            font-size: 0.98rem !important;
            color: rgba(147, 197, 253, 0.84);
        }

        .premium-sidebar .nav-sidebar .nav-link p {
            display: flex;
            align-items: center;
            width: 100%;
            margin: 0;
            line-height: 1.3;
        }

        .premium-sidebar .nav-sidebar .nav-link p .right {
            margin-left: auto;
        }

        .premium-sidebar .nav-sidebar .nav-link:hover,
        .premium-sidebar .nav-sidebar .nav-link:focus {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.075);
            transform: translateX(2px);
        }

        .premium-sidebar .nav-sidebar .nav-link:hover .nav-icon,
        .premium-sidebar .nav-sidebar .nav-link:focus .nav-icon {
            color: #ffffff;
        }

        .premium-sidebar .nav-sidebar .nav-link.active {
            color: #ffffff !important;
            background:
                radial-gradient(circle at top right, rgba(255, 255, 255, 0.16), transparent 34%),
                linear-gradient(135deg, #2563eb 0%, #3b82f6 45%, #38bdf8 100%);
            box-shadow: 0 14px 28px rgba(37, 99, 235, 0.24);
        }

        .premium-sidebar .nav-sidebar .nav-link.active .nav-icon {
            color: #ffffff !important;
        }

        .premium-sidebar .nav-treeview {
            margin-left: 0.5rem;
            padding-left: 0.75rem;
            border-left: 1px solid rgba(148, 163, 184, 0.12);
        }

        .premium-sidebar .nav-treeview .nav-link {
            min-height: 42px;
            padding: 0.64rem 0.8rem;
            font-size: 0.91rem;
            border-radius: 12px;
        }

        .premium-sidebar .nav-sidebar .badge.badge-info {
            min-width: 24px;
            padding: 0.24rem 0.42rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.16);
            color: #e0f2fe;
            font-size: 0.68rem;
            font-weight: 700;
        }

        .menu-access-disabled {
            opacity: 0.58;
            cursor: not-allowed;
        }

        .menu-access-disabled:hover,
        .menu-access-disabled:focus {
            background-color: rgba(255, 255, 255, 0.08) !important;
        }

        @media (max-width: 991.98px) {
            .premium-sidebar .sidebar {
                padding: 0 0.65rem 1rem;
            }

            .premium-user-panel {
                margin-left: 0;
                margin-right: 0;
            }

            .premium-brand-text {
                letter-spacing: 0.12em;
            }
        }
    </style>
