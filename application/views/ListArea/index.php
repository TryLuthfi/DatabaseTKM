<?php
$success = $this->session->flashdata('success');
$error = $this->session->flashdata('error');
$queryString = http_build_query($filters);

if (!function_exists('area_h')) {
    function area_h($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('area_selected')) {
    function area_selected($actual, $expected)
    {
        return (string) $actual === (string) $expected ? ' selected' : '';
    }
}
?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark"><?= area_h($judul) ?></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('Dashboard') ?>">Dashboard</a></li>
                        <li class="breadcrumb-item active">ListArea</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <?php if (!$isReady): ?>
                <div class="alert alert-warning">
                    Tabel master wilayah belum lengkap. Pastikan tabel md_provinsi_indonesia, md_kokab_indonesia, md_kec_indonesia, dan md_dusun_indonesia tersedia.
                </div>
            <?php else: ?>
                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?= area_h($success) ?>
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                    </div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <?= area_h($error) ?>
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-info"><i class="fas fa-map"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Provinsi</span>
                                <span class="info-box-number"><?= number_format((int) ($summary['province'] ?? 0), 0, ',', '.') ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-success"><i class="fas fa-city"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Kab / Kota</span>
                                <span class="info-box-number"><?= number_format((int) ($summary['regency'] ?? 0), 0, ',', '.') ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-warning"><i class="fas fa-map-signs"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Kecamatan</span>
                                <span class="info-box-number"><?= number_format((int) ($summary['district'] ?? 0), 0, ',', '.') ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-danger"><i class="fas fa-home"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Desa / Kelurahan</span>
                                <span class="info-box-number"><?= number_format((int) ($summary['village'] ?? 0), 0, ',', '.') ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title mb-0">Filter Area</h3>
                    </div>
                    <div class="card-body">
                        <form method="get" action="<?= base_url('ListArea') ?>" id="area-filter-form">
                            <div class="row">
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Regional</label>
                                        <select name="regional" id="filter_regional" class="form-control area-select">
                                            <option value="">Semua Regional</option>
                                            <?php foreach ($regionalOptions as $regional): ?>
                                                <option value="<?= area_h($regional) ?>"<?= area_selected($filters['regional'], $regional) ?>><?= area_h($regional) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Provinsi</label>
                                        <select name="province_id" id="filter_province" class="form-control area-select">
                                            <option value="">Semua Provinsi</option>
                                            <?php foreach ($provinceOptions as $province): ?>
                                                <option value="<?= area_h($province['id']) ?>"<?= area_selected($filters['province_id'], $province['id']) ?>><?= area_h($province['name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Kab / Kota</label>
                                        <select name="regency_id" id="filter_regency" class="form-control area-select">
                                            <option value="">Semua Kab/Kota</option>
                                            <?php foreach ($regencyOptions as $regency): ?>
                                                <option value="<?= area_h($regency['id']) ?>"<?= area_selected($filters['regency_id'], $regency['id']) ?>><?= area_h($regency['name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Kecamatan</label>
                                        <select name="district_id" id="filter_district" class="form-control area-select">
                                            <option value="">Semua Kecamatan</option>
                                            <?php foreach ($districtOptions as $district): ?>
                                                <option value="<?= area_h($district['id']) ?>"<?= area_selected($filters['district_id'], $district['id']) ?>><?= area_h($district['name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Desa / Kelurahan</label>
                                        <select name="village_id" id="filter_village" class="form-control area-select">
                                            <option value="">Semua Desa/Kelurahan</option>
                                            <?php foreach ($villageOptions as $village): ?>
                                                <option value="<?= area_h($village['id']) ?>"<?= area_selected($filters['village_id'], $village['id']) ?>><?= area_h($village['name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Pencarian</label>
                                        <input type="text" name="keyword" value="<?= area_h($filters['keyword']) ?>" class="form-control" placeholder="Nama wilayah">
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end">
                                <a href="<?= base_url('ListArea') ?>" class="btn btn-outline-secondary mr-2"><i class="fas fa-sync-alt mr-1"></i>Reset</a>
                                <button type="submit" class="btn btn-primary"><i class="fas fa-filter mr-1"></i>Terapkan</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header p-0 border-bottom-0">
                        <ul class="nav nav-tabs" id="area-tabs" role="tablist">
                            <li class="nav-item"><a class="nav-link active" data-toggle="pill" href="#tab-province">Provinsi</a></li>
                            <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#tab-regency">Kab / Kota</a></li>
                            <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#tab-district">Kecamatan</a></li>
                            <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#tab-village">Desa / Kelurahan</a></li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="tab-province">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h3 class="card-title mb-0">Master Provinsi</h3>
                                    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modal-add-province"><i class="fas fa-plus mr-1"></i>Tambah</button>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped area-table">
                                        <thead><tr><th style="width:70px">No</th><th>Kode</th><th>Provinsi</th><th style="width:120px">Aksi</th></tr></thead>
                                        <tbody>
                                            <?php $no = 1; foreach ($provinceRows as $row): ?>
                                                <tr>
                                                    <td><?= $no++ ?></td>
                                                    <td><?= area_h($row['id']) ?></td>
                                                    <td><?= area_h($row['name']) ?></td>
                                                    <td>
                                                        <button type="button" class="btn btn-sm btn-warning js-edit-province" data-toggle="modal" data-target="#modal-edit-province" data-id="<?= area_h($row['id']) ?>" data-name="<?= area_h($row['name']) ?>"><i class="fas fa-edit"></i></button>
                                                        <a href="<?= base_url('ListArea/deleteProvince/' . rawurlencode($row['id']) . '?query_string=' . rawurlencode($queryString)) ?>" class="btn btn-sm btn-danger js-delete-area"><i class="fas fa-trash"></i></a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="tab-regency">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h3 class="card-title mb-0">Master Kab / Kota</h3>
                                    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modal-add-regency"><i class="fas fa-plus mr-1"></i>Tambah</button>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped area-table">
                                        <thead><tr><th style="width:70px">No</th><th>Kode</th><th>Provinsi</th><th>Kab / Kota</th><th style="width:120px">Aksi</th></tr></thead>
                                        <tbody>
                                            <?php $no = 1; foreach ($regencyRows as $row): ?>
                                                <tr>
                                                    <td><?= $no++ ?></td>
                                                    <td><?= area_h($row['id']) ?></td>
                                                    <td><?= area_h($row['province_name']) ?></td>
                                                    <td><?= area_h($row['name']) ?></td>
                                                    <td>
                                                        <button type="button" class="btn btn-sm btn-warning js-edit-regency" data-toggle="modal" data-target="#modal-edit-regency" data-id="<?= area_h($row['id']) ?>" data-name="<?= area_h($row['name']) ?>" data-province_id="<?= area_h($row['province_id']) ?>" data-province_name="<?= area_h($row['province_name']) ?>"><i class="fas fa-edit"></i></button>
                                                        <a href="<?= base_url('ListArea/deleteRegency/' . rawurlencode($row['id']) . '?query_string=' . rawurlencode($queryString)) ?>" class="btn btn-sm btn-danger js-delete-area"><i class="fas fa-trash"></i></a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="tab-district">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h3 class="card-title mb-0">Master Kecamatan</h3>
                                    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modal-add-district"><i class="fas fa-plus mr-1"></i>Tambah</button>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped area-table">
                                        <thead><tr><th style="width:70px">No</th><th>Kode</th><th>Provinsi</th><th>Kab / Kota</th><th>Kecamatan</th><th style="width:120px">Aksi</th></tr></thead>
                                        <tbody>
                                            <?php $no = 1; foreach ($districtRows as $row): ?>
                                                <tr>
                                                    <td><?= $no++ ?></td>
                                                    <td><?= area_h($row['id']) ?></td>
                                                    <td><?= area_h($row['province_name']) ?></td>
                                                    <td><?= area_h($row['regency_name']) ?></td>
                                                    <td><?= area_h($row['name']) ?></td>
                                                    <td>
                                                        <button type="button" class="btn btn-sm btn-warning js-edit-district" data-toggle="modal" data-target="#modal-edit-district" data-id="<?= area_h($row['id']) ?>" data-name="<?= area_h($row['name']) ?>" data-province_id="<?= area_h($row['province_id']) ?>" data-province_name="<?= area_h($row['province_name']) ?>" data-regency_id="<?= area_h($row['regency_id']) ?>" data-regency_name="<?= area_h($row['regency_name']) ?>"><i class="fas fa-edit"></i></button>
                                                        <a href="<?= base_url('ListArea/deleteDistrict/' . rawurlencode($row['id']) . '?query_string=' . rawurlencode($queryString)) ?>" class="btn btn-sm btn-danger js-delete-area"><i class="fas fa-trash"></i></a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="tab-village">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        <h3 class="card-title mb-0">Master Desa / Kelurahan</h3>
                                        <small class="text-muted">Maksimal tampil <?= (int) $maxRows ?> baris per filter.</small>
                                    </div>
                                    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modal-add-village"><i class="fas fa-plus mr-1"></i>Tambah</button>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped area-table">
                                        <thead><tr><th style="width:70px">No</th><th>Kode</th><th>Provinsi</th><th>Kab / Kota</th><th>Kecamatan</th><th>Desa / Kelurahan</th><th style="width:120px">Aksi</th></tr></thead>
                                        <tbody>
                                            <?php $no = 1; foreach ($villageRows as $row): ?>
                                                <tr>
                                                    <td><?= $no++ ?></td>
                                                    <td><?= area_h($row['id']) ?></td>
                                                    <td><?= area_h($row['province_name']) ?></td>
                                                    <td><?= area_h($row['regency_name']) ?></td>
                                                    <td><?= area_h($row['district_name']) ?></td>
                                                    <td><?= area_h($row['name']) ?></td>
                                                    <td>
                                                        <button type="button" class="btn btn-sm btn-warning js-edit-village" data-toggle="modal" data-target="#modal-edit-village" data-id="<?= area_h($row['id']) ?>" data-name="<?= area_h($row['name']) ?>" data-province_id="<?= area_h($row['province_id']) ?>" data-province_name="<?= area_h($row['province_name']) ?>" data-regency_id="<?= area_h($row['regency_id']) ?>" data-regency_name="<?= area_h($row['regency_name']) ?>" data-district_id="<?= area_h($row['district_id']) ?>" data-district_name="<?= area_h($row['district_name']) ?>"><i class="fas fa-edit"></i></button>
                                                        <a href="<?= base_url('ListArea/deleteVillage/' . rawurlencode($row['id']) . '?query_string=' . rawurlencode($queryString)) ?>" class="btn btn-sm btn-danger js-delete-area"><i class="fas fa-trash"></i></a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<?php if ($isReady): ?>
    <form method="post" action="<?= base_url('ListArea/saveProvince') ?>" class="modal fade" id="modal-add-province">
        <input type="hidden" name="query_string" value="<?= area_h($queryString) ?>">
        <div class="modal-dialog"><div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Tambah Provinsi</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
            <div class="modal-body">
                <div class="form-group"><label>Kode Provinsi</label><input type="text" name="id" class="form-control" maxlength="2" required></div>
                <div class="form-group mb-0"><label>Nama Provinsi</label><input type="text" name="name" class="form-control text-uppercase" required></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Simpan</button></div>
        </div></div>
    </form>

    <form method="post" action="<?= base_url('ListArea/updateProvince') ?>" class="modal fade" id="modal-edit-province">
        <input type="hidden" name="query_string" value="<?= area_h($queryString) ?>">
        <div class="modal-dialog"><div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Edit Provinsi</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
            <div class="modal-body">
                <div class="form-group"><label>Kode Provinsi</label><input type="text" id="edit_province_id" class="form-control" readonly></div>
                <div class="form-group mb-0"><label>Nama Provinsi</label><input type="text" name="name" id="edit_province_name" class="form-control text-uppercase" required></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Simpan</button></div>
        </div></div>
    </form>

    <form method="post" action="<?= base_url('ListArea/saveRegency') ?>" class="modal fade" id="modal-add-regency">
        <input type="hidden" name="query_string" value="<?= area_h($queryString) ?>">
        <div class="modal-dialog"><div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Tambah Kab / Kota</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
            <div class="modal-body">
                <div class="form-group"><label>Provinsi</label><select name="province_id" class="form-control js-province-select" required></select></div>
                <div class="form-group"><label>Kode Kab / Kota</label><input type="text" name="id" class="form-control" maxlength="4" required></div>
                <div class="form-group mb-0"><label>Nama Kab / Kota</label><input type="text" name="name" class="form-control text-uppercase" required></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Simpan</button></div>
        </div></div>
    </form>

    <form method="post" action="<?= base_url('ListArea/updateRegency') ?>" class="modal fade" id="modal-edit-regency">
        <input type="hidden" name="query_string" value="<?= area_h($queryString) ?>">
        <div class="modal-dialog"><div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Edit Kab / Kota</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
            <div class="modal-body">
                <div class="form-group"><label>Kode Kab / Kota</label><input type="text" id="edit_regency_id" class="form-control" readonly></div>
                <div class="form-group"><label>Provinsi</label><select name="province_id" id="edit_regency_province" class="form-control js-province-select" required></select></div>
                <div class="form-group mb-0"><label>Nama Kab / Kota</label><input type="text" name="name" id="edit_regency_name" class="form-control text-uppercase" required></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Simpan</button></div>
        </div></div>
    </form>

    <form method="post" action="<?= base_url('ListArea/saveDistrict') ?>" class="modal fade" id="modal-add-district">
        <input type="hidden" name="query_string" value="<?= area_h($queryString) ?>">
        <div class="modal-dialog"><div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Tambah Kecamatan</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
            <div class="modal-body">
                <div class="form-group"><label>Provinsi</label><select class="form-control js-province-select js-area-parent-province" required></select></div>
                <div class="form-group"><label>Kab / Kota</label><select name="regency_id" class="form-control js-regency-select" required></select></div>
                <div class="form-group"><label>Kode Kecamatan</label><input type="text" name="id" class="form-control" maxlength="7" required></div>
                <div class="form-group mb-0"><label>Nama Kecamatan</label><input type="text" name="name" class="form-control text-uppercase" required></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Simpan</button></div>
        </div></div>
    </form>

    <form method="post" action="<?= base_url('ListArea/updateDistrict') ?>" class="modal fade" id="modal-edit-district">
        <input type="hidden" name="query_string" value="<?= area_h($queryString) ?>">
        <div class="modal-dialog"><div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Edit Kecamatan</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
            <div class="modal-body">
                <div class="form-group"><label>Kode Kecamatan</label><input type="text" id="edit_district_id" class="form-control" readonly></div>
                <div class="form-group"><label>Provinsi</label><select id="edit_district_province" class="form-control js-province-select js-area-parent-province" required></select></div>
                <div class="form-group"><label>Kab / Kota</label><select name="regency_id" id="edit_district_regency" class="form-control js-regency-select" required></select></div>
                <div class="form-group mb-0"><label>Nama Kecamatan</label><input type="text" name="name" id="edit_district_name" class="form-control text-uppercase" required></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Simpan</button></div>
        </div></div>
    </form>

    <form method="post" action="<?= base_url('ListArea/saveVillage') ?>" class="modal fade" id="modal-add-village">
        <input type="hidden" name="query_string" value="<?= area_h($queryString) ?>">
        <div class="modal-dialog"><div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Tambah Desa / Kelurahan</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
            <div class="modal-body">
                <div class="form-group"><label>Provinsi</label><select class="form-control js-province-select js-area-parent-province" required></select></div>
                <div class="form-group"><label>Kab / Kota</label><select class="form-control js-regency-select js-area-parent-regency" required></select></div>
                <div class="form-group"><label>Kecamatan</label><select name="district_id" class="form-control js-district-select" required></select></div>
                <div class="form-group"><label>Kode Desa / Kelurahan</label><input type="text" name="id" class="form-control" maxlength="10" required></div>
                <div class="form-group mb-0"><label>Nama Desa / Kelurahan</label><input type="text" name="name" class="form-control text-uppercase" required></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Simpan</button></div>
        </div></div>
    </form>

    <form method="post" action="<?= base_url('ListArea/updateVillage') ?>" class="modal fade" id="modal-edit-village">
        <input type="hidden" name="query_string" value="<?= area_h($queryString) ?>">
        <div class="modal-dialog"><div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Edit Desa / Kelurahan</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
            <div class="modal-body">
                <div class="form-group"><label>Kode Desa / Kelurahan</label><input type="text" id="edit_village_id" class="form-control" readonly></div>
                <div class="form-group"><label>Provinsi</label><select id="edit_village_province" class="form-control js-province-select js-area-parent-province" required></select></div>
                <div class="form-group"><label>Kab / Kota</label><select id="edit_village_regency" class="form-control js-regency-select js-area-parent-regency" required></select></div>
                <div class="form-group"><label>Kecamatan</label><select name="district_id" id="edit_village_district" class="form-control js-district-select" required></select></div>
                <div class="form-group mb-0"><label>Nama Desa / Kelurahan</label><input type="text" name="name" id="edit_village_name" class="form-control text-uppercase" required></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Simpan</button></div>
        </div></div>
    </form>

    <script>
        $(function () {
            var urls = {
                province: '<?= base_url('ListArea/getProvinceOptions') ?>',
                regency: '<?= base_url('ListArea/getRegencyOptions') ?>',
                district: '<?= base_url('ListArea/getDistrictOptions') ?>',
                village: '<?= base_url('ListArea/getVillageOptions') ?>'
            };

            function initDataTables() {
                if (!$.fn.DataTable) {
                    return;
                }
                $('.area-table').DataTable({
                    pageLength: 10,
                    lengthMenu: [10, 25, 50, 100],
                    ordering: true,
                    autoWidth: false,
                    responsive: true
                });
            }

            function setOption($select, value, text) {
                $select.empty();
                if (value) {
                    $select.append(new Option(text || value, value, true, true));
                }
                $select.trigger('change.select2');
            }

            function initSelect2($select, url, dataBuilder, placeholder) {
                if (!$.fn.select2 || !$select.length) {
                    return;
                }
                if ($select.hasClass('select2-hidden-accessible')) {
                    $select.select2('destroy');
                }
                $select.select2({
                    width: '100%',
                    placeholder: placeholder,
                    allowClear: true,
                    dropdownParent: $select.closest('.modal-content').length ? $select.closest('.modal-content') : $(document.body),
                    ajax: {
                        url: url,
                        dataType: 'json',
                        delay: 200,
                        data: function (params) {
                            return $.extend({ q: params.term || '' }, dataBuilder ? dataBuilder($select) : {});
                        },
                        processResults: function (data) {
                            return { results: data.results || [] };
                        }
                    }
                });
            }

            function initModalSelects($modal) {
                initSelect2($modal.find('.js-province-select'), urls.province, function () {
                    return { regional: $('#filter_regional').val() || '' };
                }, 'Pilih Provinsi');

                initSelect2($modal.find('.js-regency-select'), urls.regency, function ($select) {
                    return { province_id: $select.closest('.modal-body').find('.js-area-parent-province').val() || '' };
                }, 'Pilih Kab / Kota');

                initSelect2($modal.find('.js-district-select'), urls.district, function ($select) {
                    return { regency_id: $select.closest('.modal-body').find('.js-area-parent-regency, .js-regency-select').val() || '' };
                }, 'Pilih Kecamatan');
            }

            $('.modal').on('shown.bs.modal', function () {
                initModalSelects($(this));
            });

            $(document).on('change', '.js-area-parent-province', function () {
                var $body = $(this).closest('.modal-body');
                $body.find('.js-regency-select').val(null).trigger('change');
                $body.find('.js-district-select').val(null).trigger('change');
            });

            $(document).on('change', '.js-area-parent-regency, .js-regency-select', function () {
                $(this).closest('.modal-body').find('.js-district-select').val(null).trigger('change');
            });

            $('.js-edit-province').on('click', function () {
                var id = $(this).data('id');
                $('#modal-edit-province').attr('action', '<?= base_url('ListArea/updateProvince/') ?>' + encodeURIComponent(id));
                $('#edit_province_id').val(id);
                $('#edit_province_name').val($(this).data('name'));
            });

            $('.js-edit-regency').on('click', function () {
                var $btn = $(this);
                $('#modal-edit-regency').attr('action', '<?= base_url('ListArea/updateRegency/') ?>' + encodeURIComponent($btn.data('id')));
                $('#edit_regency_id').val($btn.data('id'));
                $('#edit_regency_name').val($btn.data('name'));
                setOption($('#edit_regency_province'), $btn.data('province_id'), $btn.data('province_name'));
            });

            $('.js-edit-district').on('click', function () {
                var $btn = $(this);
                $('#modal-edit-district').attr('action', '<?= base_url('ListArea/updateDistrict/') ?>' + encodeURIComponent($btn.data('id')));
                $('#edit_district_id').val($btn.data('id'));
                $('#edit_district_name').val($btn.data('name'));
                setOption($('#edit_district_province'), $btn.data('province_id'), $btn.data('province_name'));
                setOption($('#edit_district_regency'), $btn.data('regency_id'), $btn.data('regency_name'));
            });

            $('.js-edit-village').on('click', function () {
                var $btn = $(this);
                $('#modal-edit-village').attr('action', '<?= base_url('ListArea/updateVillage/') ?>' + encodeURIComponent($btn.data('id')));
                $('#edit_village_id').val($btn.data('id'));
                $('#edit_village_name').val($btn.data('name'));
                setOption($('#edit_village_province'), $btn.data('province_id'), $btn.data('province_name'));
                setOption($('#edit_village_regency'), $btn.data('regency_id'), $btn.data('regency_name'));
                setOption($('#edit_village_district'), $btn.data('district_id'), $btn.data('district_name'));
            });

            $('.js-delete-area').on('click', function (e) {
                if (!confirm('Hapus data master area ini?')) {
                    e.preventDefault();
                }
            });

            $('#filter_regional').on('change', function () {
                $('#filter_province, #filter_regency, #filter_district, #filter_village').val('');
            });

            $('#filter_province').on('change', function () {
                $('#filter_regency, #filter_district, #filter_village').val('');
            });

            $('#filter_regency').on('change', function () {
                $('#filter_district, #filter_village').val('');
            });

            $('#filter_district').on('change', function () {
                $('#filter_village').val('');
            });

            $('.text-uppercase').on('input', function () {
                this.value = this.value.toUpperCase();
            });

            initDataTables();
        });
    </script>
<?php endif; ?>
