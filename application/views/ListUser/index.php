<?php
      $status = $this->session->flashdata('status');
      $error_log = $this->session->flashdata('error_log');
      $nilai_po = 0;
      $rkap = 345000000000;
      $persentase_po = 0;
      $nilai_invoice = 0;
      $sisa_invoice = 0;
      $total = 1;
      $total_nilai_invoice = 0;
      $total_sisa_invoice = 0;
?>

<!-- <?php $now = date('Y-m-d') . " 00:00:00"; ?> -->
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">

    <section class="content">
      <div class="content-header">
          <div class="container-fluid">
              <div class="row mb-2">
                  <div class="col-sm-6">
                      <h1 class="m-0 text-dark"><?= $judul ?></h1>
                  </div>
              </div>
          </div>
      </div>

      <div class="container-fluid">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h5 class="m-0 text-dark">List Jabatan</h5>
                </div>
            </div>
        </div>

        <div class="row">
          <?php foreach ($count_jabatan as $countJabatan) : ?>
            <div class="col-12 col-sm-6 col-md-3">
              <a>
                <div class="info-box">
                  <span class="info-box-icon bg-info elevation-1"><i class="fas fa-money-bill-wave"></i></span>
                  <div class="info-box-content">
                    <span style="color:blue" class="info-box-text"><?= $countJabatan['nama_jabatan'] ?></span>
                    <span style="color:blue" class="info-box-number">
                      <?= number_format($countJabatan['jumlah_jabatan'],0,".")." Person" ?>
                    </span>
                  </div>
                </div>
              </a>
            </div>
          <?php endforeach ?>

        </div>

        <div class="row">
          <div class="col-lg-6">
            <div class="card">
              <div class="card-header border-0">
                <div class="d-flex justify-content-between">
                  <h1 class="card-title">Persentase Jabatan</h1>
                  <a href="javascript:void(0);">View Report</a>
                </div>
              </div>
              <div class="card-body">
                <div class="d-flex">
                  <p class="d-flex flex-column">
                    <span class="text-bold text-lg">20 Users</span>
                    <span>Jumlah User</span>
                  </p>
                </div>
                <!-- /.d-flex -->

                <div class="position-relative mb-4">
                  <canvas id="user_chart_bar" height="200"></canvas>
                </div>

                <div class="d-flex flex-row justify-content-end">
                  <span class="mr-2">
                    <i class="fas fa-square text-primary"></i> Jumlah User
                  </span>
                  <span class="mr-2">
                    <i class="fas fa-square text-gray"></i> Jumlah Active User
                  </span>
                </div>
              </div>
            </div>

          </div>
        </div>

        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h5 class="m-0 text-dark">List Active User</h5>
                </div>
            </div>
        </div>

        <div class="row">
          <?php foreach ($count_active_user as $countActiveUser) : ?>
            <div class="col-12 col-sm-6 col-md-3">
              <a>
                <div class="info-box">
                  <span class="info-box-icon bg-info elevsation-1"><i class="fas fa-money-bill-wave"></i></span>
                  <div class="info-box-content">
                    <span style="color:blue" class="info-box-text"><?= $countActiveUser['status_user'] ?></span>
                    <span style="color:blue" class="info-box-number">
                      <?= number_format($countActiveUser['jumlahActiveUser'],0,".")." Person" ?>
                    </span>
                  </div>
                </div>
              </a>
            </div>
          <?php endforeach ?>
        </div>

      </div>
    </section>

    <section class="content">
      <div class="content-header">
          <div class="container-fluid">
              <div class="row mb-2">
                  <div class="col-sm-6">
                      <h1 class="m-0 text-dark">Manage User</h1>
                  </div>
              </div>
          </div>
      </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Info boxes -->
            <div class="row">
                <!-- fix for small devices only -->
                <div class="clearfix hidden-md-up"></div>

                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="row">
                            <div  class="col-6">
                                <h3 class="card-title">List User Dashboard </h3>
                            </div>
                                <div class="col-6">
                                    <a href="#" class="btn btn-success float-right text-bold" data-target="#modal-lg-tambah" data-toggle="modal">Tambah &nbsp;<i class="fas fa-plus"></i> </a>
                                </div>
                            </div>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body table-scrollable">
                            <table id="tabel_pemasukan" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama</th>
                                        <th>Username</th>
                                        <th>Level</th>
                                        <th>Jabatan</th>
                                        <th>Status User</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    foreach ($rincian_user as $data) :
                                     ?>
                                        <tr>
                                            <td><?= $total++ ?></td>
                                            <td><?= $data['nama_user'] ?></td>
                                            <td><?= $data['username_user']?></td>
                                            <td><?= $data['nama_level'] ?></td>
                                            <td><?= $data['nama_jabatan'] ?></td>
                                            <td><?= $data['status_user'] ?></td>
                                            <td>
                                                <a href="<?php echo site_url('ListUser/delete/' . $data['id_user']); ?>" id="tombol_hapus" class="btn btn-danger tombol_hapus"><i class=" fas fa-trash"></i></a>
                                                <a href="#" class="btn btn-warning" data-target="#modal-lg-edit<?= $data['id_user'] ?>" data-toggle="modal"><i class="fas fa-edit"></i></a>
                                            </td>
                                        </tr>

                                    <?php endforeach; ?>

                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="1">Total</th>
                                        <th colspan="6"><?= number_format($total-1, 0, ',', '.') ?></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <!-- /.card-body -->
                    </div>
                    <!-- /.card -->

            <!-- modal untuk tambah data -->
            <form action=" <?php echo base_url('ListUser/add') ?>" method="post">
                <div class="modal fade" id="modal-lg-tambah">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title">Tambah List User</h4>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="form-group">
                                    <label class="col-form-label">Nama User</label>
                                    <input type="text" class="form-control" name="nama_user" autocomplete="off" placeholder="Nama User">
                                </div>
                                <div class="form-group">
                                    <label class="col-form-label">Username</label>
                                    <input type="text" class="form-control" name="username_user" autocomplete="off" placeholder="Username">
                                </div>
                                <div class="form-group">
                                    <label class="col-form-label">Password</label>
                                    <input type="password" class="form-control" name="password_user" autocomplete="off" placeholder="Password">
                                </div>
                                <div class="form-group">
                                    <label class="col-form-label">Level</label>
                                    <select name="id_level" class="form-control" >
                                    <?php foreach ($rincian_level as $level) : ?>
                                      <option value="<?php echo $level['id_level']?>" > <?php echo $level['nama_level']?></option>
                                    <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="col-form-label">Level</label>
                                    <select name="id_jabatan" class="form-control" >
                                    <?php foreach ($rincian_jabatan as $jabatan) : ?>
                                      <option value="<?php echo $jabatan['id_jabatan']?>" > <?php echo $jabatan['nama_jabatan']?></option>
                                    <?php endforeach; ?>
                                    </select>
                                </div
                                <div class="form-group">
                                    <label class="col-form-label">Status</label>
                                    <select name="status_user" class="form-control" >
                                      <option value="Active">Active</option>
                                      <option value="Non Active">Non Active</option>
                                    </select>
                                </div>


                                <div class="modal-footer">
                                    <button type="button" class="btn btn-danger" data-dismiss="modal">Batal</button>

                                    <button type="submit" name="btnSubmitPOFiberstar" class="btn btn-primary"><i class="fa fa-spinner fa-spin loading" style="display:none"></i> Tambah</button>
                                </div>
                            </div>
                        </div>
                        <!-- /.modal-content -->
                    </div>
                    <!-- /.modal-dialog -->
                </div>
            </form>

            <!-- modal untuk edit data -->
            <?php $tgl = date('Y-m-d'); ?>
            <?php foreach ($rincian_user as $data) : ?>
                <form action="<?php echo site_url('ListUser/edit/'.$data['id_user']); ?>" method="post">
                    <div class="modal fade" id="modal-lg-edit<?= $data['id_user'] ?>">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title">Edit Rincian</h4>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <div class="form-group">
                                        <label class="col-form-label">Nama User</label>
                                        <input type="text" class="form-control" name="nama_user" autocomplete="off" value="<?= $data['nama_user'] ?>">
                                    </div>
                                    <div class="form-group">
                                        <label class="col-form-label">Username</label>
                                        <input type="text" class="form-control" name="username_user" autocomplete="off" value="<?= $data['username_user'] ?>">
                                    </div>
                                    <div class="form-group">
                                        <label class="col-form-label">Password</label>
                                        <input type="password" class="form-control" name="password_user" autocomplete="off" value="<?= $data['password_user'] ?>">
                                    </div>
                                    <div class="form-group">
                                        <label class="col-form-label">Person In Control</label>
                                        <select name="id_level" class="form-control" >
                                          <?php foreach ($rincian_level as $data2) : ?>
                                            <option value="<?php echo $data2['id_level']?>" <?php if ($data['id_level'] == $data2['id_level']) { ?>selected <?php } ?> > <?php echo $data2['nama_level']?></option>
                                          <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-form-label">Person In Control</label>
                                        <select name="id_jabatan" class="form-control" >
                                          <?php foreach ($rincian_jabatan as $data3) : ?>
                                            <option value="<?php echo $data3['id_jabatan']?>" <?php if ($data['id_jabatan'] == $data3['id_jabatan']) { ?>selected <?php } ?> > <?php echo $data3['nama_jabatan']?></option>
                                          <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-form-label">Status</label>
                                        <select name="status_user" class="form-control" >
                                          <option value="Active" <?php if ($data['status_user'] == 'Active') { ?>selected <?php } ?> >Active</option>
                                          <option value="Non Active" <?php if ($data['status_user'] == 'Non Active') { ?>selected <?php } ?> >Non Active</option>
                                        </select>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-danger" data-dismiss="modal">Batal</button>

                                        <button type="submit" name="btnEdit" class="btn btn-primary"><i class="fa fa-spinner fa-spin loading" style="display:none"></i> Simpan</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            <?php endforeach; ?>



            <!-- COBA PANGGIL DATA MSQL -->
            <div class="row">
                <!-- ISI -->
            </div>
        </div>
    </section>

    <section class="content">
      <div class="content-header">
          <div class="container-fluid">
              <div class="row mb-2">
                  <div class="col-sm-6">
                      <h1 class="m-0 text-dark">Manage Level</h1>
                  </div>
              </div>
          </div>
      </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <!-- Info boxes -->
            <div class="row">
                <!-- fix for small devices only -->
                <div class="clearfix hidden-md-up"></div>

                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="row">
                            <div  class="col-6">
                                <h3 class="card-title">List Level </h3>
                            </div>
                                <div class="col-6">
                                    <a href="#" class="btn btn-success float-right text-bold" data-target="#modal-lg-tambah" data-toggle="modal">Tambah &nbsp;<i class="fas fa-plus"></i> </a>
                                </div>
                            </div>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body table-scrollable">
                            <table id="tabel_pemasukan" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Level</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $total = 1;
                                    foreach ($rincian_level as $data) :
                                     ?>
                                        <tr>
                                            <td><?= $total++ ?></td>
                                            <td><?= $data['nama_level'] ?></td>
                                            <td>
                                                <a href="<?php echo site_url('ListUser/delete/' . $data['id_level']); ?>" id="tombol_hapus" class="btn btn-danger tombol_hapus"><i class=" fas fa-trash"></i></a>
                                                <a href="#" class="btn btn-warning" data-target="#modal-lg-edit<?= $data['id_level'] ?>" data-toggle="modal"><i class="fas fa-edit"></i></a>
                                            </td>
                                        </tr>

                                    <?php endforeach; ?>

                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="1">Total</th>
                                        <th colspan="1"><?= number_format($total-1, 0, ',', '.') ?></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <!-- /.card-body -->
                    </div>
                    <!-- /.card -->

            <!-- modal untuk tambah data -->
            <form action=" <?php echo base_url('ListUser/add') ?>" method="post">
                <div class="modal fade" id="modal-lg-tambah">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title">Tambah List User</h4>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="form-group">
                                    <label class="col-form-label">Nama User</label>
                                    <input type="text" class="form-control" name="nama_user" autocomplete="off" placeholder="Nama User">
                                </div>
                                <div class="form-group">
                                    <label class="col-form-label">Username</label>
                                    <input type="text" class="form-control" name="username_user" autocomplete="off" placeholder="Username">
                                </div>
                                <div class="form-group">
                                    <label class="col-form-label">Password</label>
                                    <input type="password" class="form-control" name="password_user" autocomplete="off" placeholder="Password">
                                </div>
                                <div class="form-group">
                                    <label class="col-form-label">Level</label>
                                    <select name="id_level" class="form-control" >
                                      <option value="1">Super Admin</option>
                                      <option value="2">Admin</option>
                                      <option value="3" selected>User</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="col-form-label">Jabatan</label>
                                    <select name="jabatan_user" class="form-control" >
                                      <option value="Project Manager">Project Manager</option>
                                      <option value="Site Manager">Site Manager</option>
                                      <option value="Supervisor">Supervisor</option>
                                      <option value="Admin Project">Admin Project</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="col-form-label">Status</label>
                                    <select name="status_user" class="form-control" >
                                      <option value="Active">Active</option>
                                      <option value="Non Active">Non Active</option>
                                    </select>
                                </div>


                                <div class="modal-footer">
                                    <button type="button" class="btn btn-danger" data-dismiss="modal">Batal</button>

                                    <button type="submit" name="btnSubmitPOFiberstar" class="btn btn-primary"><i class="fa fa-spinner fa-spin loading" style="display:none"></i> Tambah</button>
                                </div>
                            </div>
                        </div>
                        <!-- /.modal-content -->
                    </div>
                    <!-- /.modal-dialog -->
                </div>
            </form>

            <!-- modal untuk edit data -->
            <?php $tgl = date('Y-m-d'); ?>
            <?php foreach ($rincian_user as $data) :
            ?>
                <form action="<?php echo site_url('ListUser/edit/'.$data['id_user']); ?>" method="post">
                    <div class="modal fade" id="modal-lg-edit<?= $data['id_user'] ?>">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title">Edit Rincian</h4>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <div class="form-group">
                                        <label class="col-form-label">Nama User</label>
                                        <input type="text" class="form-control" name="nama_user" autocomplete="off" value="<?= $data['nama_user'] ?>">
                                    </div>
                                    <div class="form-group">
                                        <label class="col-form-label">Username</label>
                                        <input type="text" class="form-control" name="username_user" autocomplete="off" value="<?= $data['username_user'] ?>">
                                    </div>
                                    <div class="form-group">
                                        <label class="col-form-label">Password</label>
                                        <input type="password" class="form-control" name="password_user" autocomplete="off" value="<?= $data['password_user'] ?>">
                                    </div>
                                    <div class="form-group">
                                        <label class="col-form-label">Level</label>
                                        <select name="id_level" class="form-control" >
                                          <option value="1" <?php if ($data['id_level'] == '1') { ?>selected <?php } ?> >Super Admin</option>
                                          <option value="2" <?php if ($data['id_level'] == '2') { ?>selected <?php } ?> >Admin</option>
                                          <option value="3" <?php if ($data['id_level'] == '3') { ?>selected <?php } ?> >User</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-form-label">Level</label>
                                        <select name="jabatan_user" class="form-control" >
                                          <option value="Project Manager" <?php if ($data['jabatan_user'] == 'Project Manager') { ?>selected <?php } ?> >Project Manager</option>
                                          <option value="Site Manager" <?php if ($data['jabatan_user'] == 'Site Manager') { ?>selected <?php } ?> >Site Manager</option>
                                          <option value="Supervisor" <?php if ($data['jabatan_user'] == 'Supervisor') { ?>selected <?php } ?> >Supervisor</option>
                                          <option value="Admin Project" <?php if ($data['jabatan_user'] == 'Admin Project') { ?>selected <?php } ?> >Admin Project</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-form-label">Status</label>
                                        <select name="status_user" class="form-control" >
                                          <option value="Active" <?php if ($data['status_user'] == 'Active') { ?>selected <?php } ?> >Active</option>
                                          <option value="Non Active" <?php if ($data['status_user'] == 'Non Active') { ?>selected <?php } ?> >Non Active</option>
                                        </select>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-danger" data-dismiss="modal">Batal</button>

                                        <button type="submit" name="btnEdit" class="btn btn-primary"><i class="fa fa-spinner fa-spin loading" style="display:none"></i> Simpan</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            <?php endforeach; ?>



            <!-- COBA PANGGIL DATA MSQL -->
            <div class="row">
                <!-- ISI -->
            </div>
        </div>
    </section>

    <section class="content">
      <div class="content-header">
          <div class="container-fluid">
              <div class="row mb-2">
                  <div class="col-sm-6">
                      <h1 class="m-0 text-dark">Manage Jabatan</h1>
                  </div>
              </div>
          </div>
      </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <!-- Info boxes -->
            <div class="row">
                <!-- fix for small devices only -->
                <div class="clearfix hidden-md-up"></div>

                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="row">
                            <div  class="col-6">
                                <h3 class="card-title">List Jabatan </h3>
                            </div>
                                <div class="col-6">
                                    <a href="#" class="btn btn-success float-right text-bold" data-target="#modal-lg-tambah" data-toggle="modal">Tambah &nbsp;<i class="fas fa-plus"></i> </a>
                                </div>
                            </div>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body table-scrollable">
                            <table id="tabel_pemasukan" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Level</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $total = 1;
                                    foreach ($rincian_jabatan as $data) :
                                     ?>
                                        <tr>
                                            <td><?= $total++ ?></td>
                                            <td><?= $data['nama_jabatan'] ?></td>
                                            <td>
                                                <a href="<?php echo site_url('ListUser/delete/' . $data['id_jabatan']); ?>" id="tombol_hapus" class="btn btn-danger tombol_hapus"><i class=" fas fa-trash"></i></a>
                                                <a href="#" class="btn btn-warning" data-target="#modal-lg-edit<?= $data['id_jabatan'] ?>" data-toggle="modal"><i class="fas fa-edit"></i></a>
                                            </td>
                                        </tr>

                                    <?php endforeach; ?>

                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="1">Total</th>
                                        <th colspan="1"><?= number_format($total-1, 0, ',', '.') ?></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <!-- /.card-body -->
                    </div>
                    <!-- /.card -->

            <!-- modal untuk tambah data -->
            <form action=" <?php echo base_url('ListUser/add') ?>" method="post">
                <div class="modal fade" id="modal-lg-tambah">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title">Tambah List User</h4>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="form-group">
                                    <label class="col-form-label">Nama User</label>
                                    <input type="text" class="form-control" name="nama_user" autocomplete="off" placeholder="Nama User">
                                </div>
                                <div class="form-group">
                                    <label class="col-form-label">Username</label>
                                    <input type="text" class="form-control" name="username_user" autocomplete="off" placeholder="Username">
                                </div>
                                <div class="form-group">
                                    <label class="col-form-label">Password</label>
                                    <input type="password" class="form-control" name="password_user" autocomplete="off" placeholder="Password">
                                </div>
                                <div class="form-group">
                                    <label class="col-form-label">Level</label>
                                    <select name="id_level" class="form-control" >
                                      <option value="1">Super Admin</option>
                                      <option value="2">Admin</option>
                                      <option value="3" selected>User</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="col-form-label">Jabatan</label>
                                    <select name="jabatan_user" class="form-control" >
                                      <option value="Project Manager">Project Manager</option>
                                      <option value="Site Manager">Site Manager</option>
                                      <option value="Supervisor">Supervisor</option>
                                      <option value="Admin Project">Admin Project</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="col-form-label">Status</label>
                                    <select name="status_user" class="form-control" >
                                      <option value="Active">Active</option>
                                      <option value="Non Active">Non Active</option>
                                    </select>
                                </div>


                                <div class="modal-footer">
                                    <button type="button" class="btn btn-danger" data-dismiss="modal">Batal</button>

                                    <button type="submit" name="btnSubmitPOFiberstar" class="btn btn-primary"><i class="fa fa-spinner fa-spin loading" style="display:none"></i> Tambah</button>
                                </div>
                            </div>
                        </div>
                        <!-- /.modal-content -->
                    </div>
                    <!-- /.modal-dialog -->
                </div>
            </form>

            <!-- modal untuk edit data -->
            <?php $tgl = date('Y-m-d'); ?>
            <?php foreach ($rincian_user as $data) :
            ?>
                <form action="<?php echo site_url('ListUser/edit/'.$data['id_user']); ?>" method="post">
                    <div class="modal fade" id="modal-lg-edit<?= $data['id_user'] ?>">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title">Edit Rincian</h4>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <div class="form-group">
                                        <label class="col-form-label">Nama User</label>
                                        <input type="text" class="form-control" name="nama_user" autocomplete="off" value="<?= $data['nama_user'] ?>">
                                    </div>
                                    <div class="form-group">
                                        <label class="col-form-label">Username</label>
                                        <input type="text" class="form-control" name="username_user" autocomplete="off" value="<?= $data['username_user'] ?>">
                                    </div>
                                    <div class="form-group">
                                        <label class="col-form-label">Password</label>
                                        <input type="password" class="form-control" name="password_user" autocomplete="off" value="<?= $data['password_user'] ?>">
                                    </div>
                                    <div class="form-group">
                                        <label class="col-form-label">Level</label>
                                        <select name="id_level" class="form-control" >
                                          <option value="1" <?php if ($data['id_level'] == '1') { ?>selected <?php } ?> >Super Admin</option>
                                          <option value="2" <?php if ($data['id_level'] == '2') { ?>selected <?php } ?> >Admin</option>
                                          <option value="3" <?php if ($data['id_level'] == '3') { ?>selected <?php } ?> >User</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-form-label">Level</label>
                                        <select name="jabatan_user" class="form-control" >
                                          <option value="Project Manager" <?php if ($data['jabatan_user'] == 'Project Manager') { ?>selected <?php } ?> >Project Manager</option>
                                          <option value="Site Manager" <?php if ($data['jabatan_user'] == 'Site Manager') { ?>selected <?php } ?> >Site Manager</option>
                                          <option value="Supervisor" <?php if ($data['jabatan_user'] == 'Supervisor') { ?>selected <?php } ?> >Supervisor</option>
                                          <option value="Admin Project" <?php if ($data['jabatan_user'] == 'Admin Project') { ?>selected <?php } ?> >Admin Project</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-form-label">Status</label>
                                        <select name="status_user" class="form-control" >
                                          <option value="Active" <?php if ($data['status_user'] == 'Active') { ?>selected <?php } ?> >Active</option>
                                          <option value="Non Active" <?php if ($data['status_user'] == 'Non Active') { ?>selected <?php } ?> >Non Active</option>
                                        </select>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-danger" data-dismiss="modal">Batal</button>

                                        <button type="submit" name="btnEdit" class="btn btn-primary"><i class="fa fa-spinner fa-spin loading" style="display:none"></i> Simpan</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            <?php endforeach; ?>



            <!-- COBA PANGGIL DATA MSQL -->
            <div class="row">
                <!-- ISI -->
            </div>
        </div>
    </section>

</div>
<!-- /.content-wrapper -->

<?php $this->session->set_flashdata('status', 'kosong'); ?>

<!-- Control Sidebar -->
<aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
</aside>
<!-- /.control-sidebar -->
<script src="https://code.jquery.com/jquery-3.5.1.js"></script>
<script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.22/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@7.12.15/dist/sweetalert2.all.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.4.1/js/bootstrap-datepicker.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/progressbar.js@1.1.0/dist/progressbar.min.js"></script>


<script>
    $('#1').datepicker({
        inputs: $('input[name=tanggal_berangkat]'),
        format: 'dd/mm/yyyy'
    })
    $('#2').datepicker({
        inputs: $('input[name=utanggal_berangkat]'),
        format: 'dd/mm/yyyy'
    })
</script>
<script type="text/javascript">
    $(function() {

        // format angka rupiah
        $('[data-mask]').inputmask("currency", {
            prefix: " Rp. ",
            digitsOptional: true
        })

        // notifikasi allert sukses atau tidak
        <?php if ($status == 'sukses_tambah') { ?>
            swal("Success!", "Berhasil Ditambah!", "success");
        <?php } else if ($status == 'sukses_hapus') { ?>
            swal("Success!", "Berhasil Dihapus!", "success");
        <?php } else if ($status == 'sukses_edit') { ?>
            swal("Success!", "Berhasil Edit Data!", "success");
        <?php } else if ($status == 'gagal_tambah') { ?>
            swal("Gagal!", "Gagal Menambah Data!", "warning");
        <?php } else if ($status == 'gagal_edit') { ?>
            swal("Gagal!", "Gagal Mengedit Data!", "warning");
        <?php } else if ($status == 'gagal_hapus') { ?>
            swal("Gagal!", "Gagal Menghapus Data!", "warning");
        <?php } else { ?>
        <?php } ?>

    });

    $('.tombol_hapus').on('click', function (e) {
    e.preventDefault();
    const href = $(this).attr('href');
    swal({
        title: 'Apakah anda yakin',
        text: "data akan dihapus!",
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e74c3c',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Delete'
    }).then((result) => {
        if (result.value) {
            document.location.href = href;
        }
    })

});
</script>
<script type="text/javascript">
            $(document).ready(function(){

                // Format mata uang.
                $( '.nilai_po2' ).mask('000.000.000', {reverse: true});

            })
        </script>

        <script>
    // Membuat circle progress bar
    var bar = new ProgressBar.Circle('#progress-bar-container', {
        color: '#FF5733', // Warna progress bar
        strokeWidth: 10, // Ketebalan garis
        trailWidth: 10,  // Ketebalan garis latar belakang
        easing: 'easeInOut',  // Animasi progress bar
        duration: 1400,  // Durasi animasi dalam milidetik
        from: { color: '#ddd', width: 10 },
        to: { color: '#FF5733', width: 10 },
        step: function(state, circle) {
            circle.path.setAttribute('stroke', state.color);
            circle.path.setAttribute('stroke-width', state.width);
            var value = Math.round(circle.value() * 100);
            circle.setText(value + '%');
        }
    });

    // Mengatur nilai progress bar
    bar.animate(<?= $persentase_po ?>);  // Nilai antara 0.0 hingga 1.0 (70% dalam contoh ini)
</script>
<script src="https://code.jquery.com/jquery-3.5.1.js"></script>
<script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.22/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@7.12.15/dist/sweetalert2.all.min.js"></script>

<link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/fontawesome-free/css/all.min.css">
<!-- overlayScrollbars -->
<link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
<!-- Theme style -->
<link rel="stylesheet" href="<?= base_url('assets') ?>/dist/css/adminlte.min.css">
<!-- Google Font: Source Sans Pro -->
<link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
<!-- DataTables -->
<link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
<!-- daterange picker -->
<!-- <link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/daterangepicker/daterangepicker.css"> -->
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/select2/css/select2.min.css">
<link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
<link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/jquery/jquery.min.js"></script>
<!-- jQuery UI 1.11.4 -->
<link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/jquery-ui/jquery-ui.min.js"></script>
<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
<script>
  $.widget.bridge('uibutton', $.ui.button)
</script>
<!-- Bootstrap 4 -->
<link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/bootstrap/js/bootstrap.bundle.min.js">
<!-- ChartJS -->
<link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/chart.js/Chart.min.js">
<!-- Sparkline -->
<link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/sparklines/sparkline.js">
<!-- JQVMap -->
<link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/jqvmap/jquery.vmap.min.js">
<link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/jqvmap/maps/jquery.vmap.usa.js">
<!-- jQuery Knob Chart -->
<link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/jquery-knob/jquery.knob.min.js">
<!-- daterangepicker -->
<link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/moment/moment.min.js">
<link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/daterangepicker/daterangepicker.js">
<!-- Tempusdominus Bootstrap 4 -->
<link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js">
<!-- Summernote -->
<link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/summernote/summernote-bs4.min.js">
<!-- overlayScrollbars -->
<link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js">
<!-- AdminLTE for demo purposes -->
<link rel="stylesheet" href="<?= base_url('assets') ?>/dist/js/demo.js">
<!-- AdminLTE dashboard demo (This is only for demo purposes) -->
<link rel="stylesheet" href="<?= base_url('assets') ?>/dist/js/pages/dashboard.js">

<script src="<?= base_url('assets') ?>/plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap -->
<script src="<?= base_url('assets') ?>/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>

<!-- OPTIONAL SCRIPTS -->
<script src="<?= base_url('assets') ?>/plugins/chart.js/Chart.min.js"></script>
<!-- AdminLTE for demo purposes -->
<script src="<?= base_url('assets') ?>/dist/js/demo.js"></script>
<!-- AdminLTE dashboard demo (This is only for demo purposes) -->
<script src="<?= base_url('assets') ?>/dist/js/pages/dashboardListUser.js"></script>

<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
<!-- Font Awesome Icons -->
<link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
