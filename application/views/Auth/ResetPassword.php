<?php
$resetPasswordError = (string) $this->session->flashdata('reset_password_error');
if ($resetPasswordError !== '') {
    $this->session->unset_userdata('reset_password_error');
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Ganti Password - Database Project TKM</title>
    <link rel="icon" type="image/png" href="<?= base_url('assets/dist/img/zeyn-logo.png?v=20260602') ?>">
    <link rel="shortcut icon" type="image/png" href="<?= base_url('assets/dist/img/zeyn-logo.png?v=20260602') ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
    <link rel="stylesheet" href="<?= base_url('assets') ?>/dist/css/adminlte.min.css">
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
    </style>
</head>
<body class="hold-transition login-page" style="background: linear-gradient(rgba(0, 0, 0, 0.74), rgba(0, 0, 0, 0.41)), url('<?= base_url("assets/img/IMG_1247.JPG") ?>') no-repeat center center fixed; background-size: cover;">
    <div class="login-box">
        <div class="login-logo">
            <a href="<?= site_url('Auth') ?>">
                <span class="brand-mark-wrap">
                    <img src="<?= base_url('assets/dist/img/zeyn-logo.png') ?>" alt="ZEYN Logo">
                </span>
                <span class="brand-text-custom">ZEYN</span>
                <span class="brand-tagline-custom">ZERO-ERROR EXECUTION, YIELDING NETWORKS</span>
            </a>
        </div>
        <div class="card card-outline card-primary">
            <div class="card-header text-center">
                <b>Masukkan Password Baru</b>
            </div>
            <div class="card-body">
                <?php if ($resetPasswordError !== ''): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($resetPasswordError, ENT_QUOTES) ?></div>
                <?php endif; ?>
                <form id="reset_password_form" method="post" action="<?= site_url('Auth/resetPassword?token=' . urlencode((string) $token)) ?>">
                    <div class="form-group">
                        <label>Password Baru</label>
                        <div class="input-group">
                            <input id="new_password" type="password" class="form-control" name="new_password" minlength="8" required>
                            <div class="input-group-append">
                                <button type="button" class="input-group-text password-toggle js-toggle-password" data-target="#new_password" aria-label="Tampilkan password">
                                    <i class="fas fa-eye-slash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Konfirmasi Password</label>
                        <div class="input-group">
                            <input id="confirm_password" type="password" class="form-control" name="confirm_password" minlength="8" required>
                            <div class="input-group-append">
                                <button type="button" class="input-group-text password-toggle js-toggle-password" data-target="#confirm_password" aria-label="Tampilkan password">
                                    <i class="fas fa-eye-slash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div id="reset_password_match_error" class="alert alert-danger py-2 d-none">Konfirmasi password tidak sama.</div>
                    <button type="submit" class="btn btn-primary btn-block">Simpan Password</button>
                </form>
            </div>
        </div>
    </div>
    <div class="login-page-footer"><span>'A Product of PT. Technology Karya Mandiri'</span></div>
    <script>
        (function () {
            var buttons = document.querySelectorAll('.js-toggle-password');
            var form = document.getElementById('reset_password_form');
            var newPassword = document.getElementById('new_password');
            var confirmPassword = document.getElementById('confirm_password');
            var matchError = document.getElementById('reset_password_match_error');

            function syncPasswordMatchError() {
                if (!matchError || !newPassword || !confirmPassword) {
                    return true;
                }

                var isValid = confirmPassword.value === '' || newPassword.value === confirmPassword.value;
                matchError.classList.toggle('d-none', isValid);
                confirmPassword.classList.toggle('is-invalid', !isValid);
                return isValid;
            }

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

            if (newPassword && confirmPassword) {
                newPassword.addEventListener('input', syncPasswordMatchError);
                confirmPassword.addEventListener('input', syncPasswordMatchError);
            }

            if (form) {
                form.addEventListener('submit', function (event) {
                    var isMatched = newPassword && confirmPassword && newPassword.value === confirmPassword.value;
                    if (!isMatched) {
                        event.preventDefault();
                        if (matchError) {
                            matchError.classList.remove('d-none');
                        }
                        if (confirmPassword) {
                            confirmPassword.classList.add('is-invalid');
                            confirmPassword.focus();
                        }
                    }
                });
            }
        })();
    </script>
</body>
</html>


