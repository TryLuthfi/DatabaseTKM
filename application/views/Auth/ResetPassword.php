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
</head>
<body class="hold-transition login-page" style="background: linear-gradient(rgba(0, 0, 0, 0.74), rgba(0, 0, 0, 0.41)), url('<?= base_url("assets/img/IMG_1247.JPG") ?>') no-repeat center center fixed; background-size: cover;">
    <div class="login-box">
        <div class="card card-outline card-primary">
            <div class="card-header text-center">
                <b>Set Password Baru</b>
            </div>
            <div class="card-body">
                <?php if ($this->session->flashdata('reset_password_error')): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars((string) $this->session->flashdata('reset_password_error'), ENT_QUOTES) ?></div>
                <?php endif; ?>
                <form method="post" action="<?= site_url('Auth/resetPassword?token=' . urlencode((string) $token)) ?>">
                    <div class="form-group">
                        <label>Password Baru</label>
                        <input type="password" class="form-control" name="new_password" minlength="8" required>
                    </div>
                    <div class="form-group">
                        <label>Konfirmasi Password</label>
                        <input type="password" class="form-control" name="confirm_password" minlength="8" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Simpan Password</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
