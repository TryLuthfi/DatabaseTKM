<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Database Project TKM</title>
    <link rel="icon" type="image/png" href="<?= base_url('assets/dist/img/zeyn-logo.png?v=20260602') ?>">
    <link rel="shortcut icon" type="image/png" href="<?= base_url('assets/dist/img/zeyn-logo.png?v=20260602') ?>">
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
    <style>
        .login-logo {
            margin-bottom: 12px;
            text-align: center;
        }

        .login-logo img {
            width: 92px;
            max-width: 100%;
            height: auto;
        }

        .login-logo .brand-mark-wrap {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 106px;
            height: 106px;
            background: #ffffff;
            border-radius: 18px;
            box-shadow: 0 10px 24px rgba(0, 0, 0, 0.35);
        }

        .login-logo .brand-text-custom {
            display: block;
            margin-top: 6px;
            color: #ffffff;
            font-size: 30px;
            font-weight: 700;
            letter-spacing: 4px;
            line-height: 1;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.45);
        }

        .login-logo .brand-tagline-custom {
            display: block;
            margin-top: 6px;
            color: #ffffff;
            font-size: 11px;
            font-weight: 500;
            letter-spacing: 1.4px;
            text-transform: uppercase;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.45);
        }

        .password-toggle.input-group-text {
            width: 40px;
            justify-content: center;
            color: #6c757d;
            background-color: #fff;
            cursor: pointer;
        }

        .password-toggle.input-group-text:focus {
            box-shadow: none;
            outline: 0;
        }

        .login-page-footer {
            position: fixed;
            left: 0;
            right: 0;
            bottom: 0;
            padding-bottom: 18px;
            text-align: center;
            font-size: 12px;
            color: #ffffff;
            font-weight: 600;
            letter-spacing: 0.4px;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.65);
            z-index: 2;
        }

        .login-page-footer span {
            display: inline-block;
            padding: 7px 12px;
            background: rgba(0, 0, 0, 0.58);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 999px;
            backdrop-filter: blur(2px);
        }
    </style>
</head>

<body class="hold-transition login-page" style="background: linear-gradient(rgba(0, 0, 0, 0.74), rgba(0, 0, 0, 0.41)), url('<?= base_url("assets/img/IMG_1247.JPG") ?>') no-repeat center center fixed; background-size: cover;">
    <div class="login-box">
        <div class="login-logo">
            <a>
                <span class="brand-mark-wrap">
                    <img src="<?= base_url('assets/dist/img/zeyn-logo.png') ?>" alt="ZEYN Logo">
                </span>
                <span class="brand-text-custom">ZEYN</span>
                <span class="brand-tagline-custom">ZERO-ERROR EXECUTION, YIELDING NETWORKS</span>
            </a>
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
            <?php if ($this->session->flashdata('reset_password_success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong>Sukses</strong> <?= htmlspecialchars((string) $this->session->flashdata('reset_password_success'), ENT_QUOTES) ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>
            <div class="card-body login-card-body">
                <p class="login-box-msg">Silahkan masuk</p>

                <form action="<?= site_url('Auth') ?>" method="post">
                    <span class="text text-danger"><?= form_error('username') ?></span>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" placeholder="Username" name="username">
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-user"></span>
                            </div>
                        </div>
                    </div>
                    <span class="text text-danger"><?= form_error('pass') ?></span>
                    <div class="input-group mb-3">
                        <input id="login_password" type="password" class="form-control" placeholder="Password" name="pass">
                        <div class="input-group-append">
                            <button type="button" class="input-group-text password-toggle js-toggle-password" data-target="#login_password" aria-label="Tampilkan password">
                                <i class="fas fa-eye-slash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary btn-block">Masuk</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="login-page-footer"><span>'A Product of PT. Technology Karya Mandiri'</span></div>
    </div>
</body>

<script src="<?= base_url('assets') ?>/plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="<?= base_url('assets') ?>/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="<?= base_url('assets') ?>/dist/js/adminlte.min.js"></script>
<script>
    (function () {
        var buttons = document.querySelectorAll('.js-toggle-password');
        buttons.forEach(function (button) {
            button.addEventListener('click', function () {
                var target = document.querySelector(button.getAttribute('data-target'));
                var icon = button.querySelector('i');
                if (!target || !icon) {
                    return;
                }

                var visible = target.getAttribute('type') === 'text';
                target.setAttribute('type', visible ? 'password' : 'text');
                icon.classList.toggle('fa-eye', !visible);
                icon.classList.toggle('fa-eye-slash', visible);
                button.setAttribute('aria-label', visible ? 'Tampilkan password' : 'Sembunyikan password');
            });
        });
    })();
</script>

</html>

