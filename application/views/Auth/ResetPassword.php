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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
    <link rel="stylesheet" href="<?= base_url('assets') ?>/dist/css/adminlte.min.css">
    <style>
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
            <a style="color: white;"><b>Database</b>Project TKM</a>
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
