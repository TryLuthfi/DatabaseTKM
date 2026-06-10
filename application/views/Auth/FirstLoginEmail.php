<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Verifikasi Email - Database Project TKM</title>
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

        .login-logo .brand-mark-row {
            display: inline-flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            gap: 40px;
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

        .login-logo .brand-mark-wrap-tkm img {
            width: 96px;
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
    </style>
</head>
<body class="hold-transition login-page" style="background: linear-gradient(rgba(0, 0, 0, 0.74), rgba(0, 0, 0, 0.41)), url('<?= base_url("assets/img/IMG_1247.JPG") ?>') no-repeat center center fixed; background-size: cover;">
    <div class="login-box" style="max-width: 520px;">
        <div class="login-logo">
            <a href="<?= site_url('Auth') ?>">
                <span class="brand-mark-row">
                    <span class="brand-mark-wrap">
                        <img src="<?= base_url('assets/dist/img/zeyn-logo.png') ?>" alt="ZEYN Logo">
                    </span>
                    <span class="brand-mark-wrap brand-mark-wrap-tkm">
                        <img src="<?= base_url('assets/dist/img/solid%20logo%20tkm%20landscape%20transparent.png') ?>" alt="TKM Logo">
                    </span>
                </span>
                <span class="brand-text-custom">ZEYN</span>
                <span class="brand-tagline-custom">ZERO-ERROR EXECUTION, YIELDING NETWORKS</span>
            </a>
        </div>
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

                <form id="form_kirim_link" method="post" action="<?= site_url('Auth/sendFirstLoginLink') ?>">
                    <div class="form-group">
                        <label>Email Kantor</label>
                        <input id="email_kantor" type="email" class="form-control" name="email_kantor" placeholder="nama@tkm.co.id" required>
                    </div>
                    <button id="btn_kirim" type="submit" class="btn btn-primary btn-block" disabled>Kirim Link Ganti Password</button>
                    <div id="reset_link_countdown" class="text-center text-muted small mt-2"></div>
                </form>
            </div>
        </div>
    </div>
    <div class="login-page-footer"><span>'A Product of PT. Technology Karya Mandiri'</span></div>

    <script src="<?= base_url('assets') ?>/plugins/jquery/jquery.min.js"></script>
    <script>
        (function () {
            var expected = <?= json_encode((string) $email_kantor) ?>.toLowerCase().trim();
            var cooldownRemaining = <?= (int) ($reset_link_cooldown_remaining ?? 0) ?>;
            var input = document.getElementById('email_kantor');
            var button = document.getElementById('btn_kirim');
            var form = document.getElementById('form_kirim_link');
            var countdown = document.getElementById('reset_link_countdown');

            function renderCountdown() {
                if (!countdown) {
                    return;
                }

                if (cooldownRemaining > 0) {
                    countdown.textContent = 'Kirim ulang tersedia dalam ' + cooldownRemaining + ' detik.';
                    return;
                }

                countdown.textContent = '';
            }

            function syncButton() {
                var value = (input.value || '').toLowerCase().trim();
                button.disabled = cooldownRemaining > 0 || !(expected !== '' && value === expected);
            }

            if (cooldownRemaining > 0) {
                renderCountdown();
                var timer = window.setInterval(function () {
                    cooldownRemaining--;
                    if (cooldownRemaining <= 0) {
                        cooldownRemaining = 0;
                        window.clearInterval(timer);
                    }
                    renderCountdown();
                    syncButton();
                }, 1000);
            }

            input.addEventListener('input', syncButton);
            form.addEventListener('submit', function () {
                button.disabled = true;
                button.textContent = 'Mengirim...';
            });
            syncButton();
        })();
    </script>
</body>
</html>


