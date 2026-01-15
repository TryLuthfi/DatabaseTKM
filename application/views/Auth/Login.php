<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Database Project TKM</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/fontawesome-free/css/all.min.css">
    <!-- Ionicons -->
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <!-- icheck bootstrap -->
    <link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="<?= base_url('assets') ?>/dist/css/adminlte.min.css">
    <!-- Google Font: Source Sans Pro -->
    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
</head>

<body class="hold-transition login-page"
    style="background: linear-gradient(rgba(0, 0, 0, 0.74), rgba(0, 0, 0, 0.41)), url('<?= base_url("assets/img/IMG_1247.JPG") ?>') no-repeat center center fixed; background-size: cover;">
    <div class="login-box">
        <div class="login-logo">
            <a style="color: white;"><b>Database</b>Project TKM</a>
        </div>
        <!-- /.login-logo -->
        <div class="card">
            <?php if ($this->session->flashdata('error_log') == 'salah'): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Gagal</strong> Password yang anda masukkan salah
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>
            <?php if ($this->session->flashdata('error_log') == 'tidak_ada'): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Gagal</strong> Akun anda tidak ditemukan
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>
            <div class="card-body login-card-body">
                <p class="login-box-msg">Silahkan masuk</p>

                <form action="<?= base_url('') ?>" method="post" id="formLogin">

                    <span class="text text-danger"><?= form_error('username') ?></span>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" placeholder="Username" name="username" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-user"></span>
                            </div>
                        </div>
                    </div>

                    <span class="text text-danger"><?= form_error('pass') ?></span>
                    <div class="input-group mb-3">
                        <input type="password" class="form-control" placeholder="Password" name="pass" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary btn-block" id="btnLogin">
                                <span id="btnText">Masuk</span>
                                <span id="btnLoading" class="spinner-border spinner-border-sm d-none"></span>
                            </button>
                        </div>
                    </div>

                </form>
                <div id="loadingOverlay">
                    <div class="loading-content">
                        <div class="spinner-border text-light" role="status"></div>
                        <p class="mt-3">Memproses login...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

<script>
    document.getElementById('formLogin').addEventListener('submit', function (e) {
        e.preventDefault(); // ⛔ STOP TOTAL SUBMIT

        document.getElementById('loadingOverlay').style.display = 'block';
        document.getElementById('btnLogin').disabled = true;

        // sengaja tidak ada redirect
        // loading akan terus tampil
    });
</script>

<script src="<?= base_url('assets') ?>/plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="<?= base_url('assets') ?>/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="<?= base_url('assets') ?>/dist/js/adminlte.min.js"></script>

</html>

<style>
    #loadingOverlay {
        display: none;
        position: fixed;
        z-index: 9999;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.6);
    }

    .loading-content {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        text-align: center;
        color: white;
    }
</style>