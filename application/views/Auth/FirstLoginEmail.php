<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Verifikasi Email - Database Project TKM</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
    <link rel="stylesheet" href="<?= base_url('assets') ?>/dist/css/adminlte.min.css">
</head>
<body class="hold-transition login-page" style="background: linear-gradient(rgba(0, 0, 0, 0.74), rgba(0, 0, 0, 0.41)), url('<?= base_url("assets/img/IMG_1247.JPG") ?>') no-repeat center center fixed; background-size: cover;">
    <div class="login-box" style="max-width: 520px;">
        <div class="card card-outline card-primary">
            <div class="card-header text-center">
                <b>Verifikasi Email Kantor</b>
            </div>
            <div class="card-body">
                <?php if (!empty($reset_link_success)): ?>
                    <div class="alert alert-success"><?= htmlspecialchars((string) $reset_link_success, ENT_QUOTES) ?></div>
                <?php elseif (!empty($reset_link_error)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars((string) $reset_link_error, ENT_QUOTES) ?></div>
                <?php endif; ?>

                <p class="mb-2">Halo, <strong><?= htmlspecialchars((string) $nama_karyawan, ENT_QUOTES) ?></strong>.</p>
                <p class="text-muted mb-3">Masukkan email kantor Anda. Tombol kirim aktif hanya jika sesuai data akun.</p>

                <form method="post" action="<?= site_url('Auth/sendFirstLoginLink') ?>">
                    <div class="form-group">
                        <label>Email Kantor</label>
                        <input id="email_kantor" type="email" class="form-control" name="email_kantor" placeholder="nama@tkm.co.id" required>
                    </div>
                    <button id="btn_kirim" type="submit" class="btn btn-primary btn-block" disabled>Kirim Link Ganti Password</button>
                </form>
            </div>
        </div>
    </div>

    <script src="<?= base_url('assets') ?>/plugins/jquery/jquery.min.js"></script>
    <script>
        (function () {
            var expected = <?= json_encode((string) $email_kantor) ?>.toLowerCase().trim();
            var input = document.getElementById('email_kantor');
            var button = document.getElementById('btn_kirim');
            function syncButton() {
                var value = (input.value || '').toLowerCase().trim();
                button.disabled = !(expected !== '' && value === expected);
            }
            input.addEventListener('input', syncButton);
            syncButton();
        })();
    </script>
</body>
</html>
