<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Database Project TKM</title>
    <link rel="icon" type="image/png" href="<?= base_url('assets/dist/img/zeyn-logo.png?v=20260602') ?>">
    <link rel="shortcut icon" type="image/png" href="<?= base_url('assets/dist/img/zeyn-logo.png?v=20260602') ?>">
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
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
        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        html,
        body {
            width: 100%;
            height: 100%;
            overflow: hidden;
        }

        body.auth-page {
            margin: 0;
            height: 100vh;
            height: 100dvh;
            background: #ffffff;
            color: #1f2937;
            font-family: "Source Sans Pro", Arial, sans-serif;
        }

        .auth-shell {
            display: flex;
            width: 100%;
            height: 100vh;
            height: 100dvh;
            overflow: hidden;
        }

        @supports (-webkit-touch-callout: none) {
            body.auth-page,
            .auth-shell {
                height: -webkit-fill-available;
            }
        }

        html {
            min-height: -webkit-fill-available;
        }

        .auth-brand-panel {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 54%;
            height: 100%;
            min-height: 0;
            overflow: hidden;
            background:
                linear-gradient(90deg, rgba(15, 23, 42, 0.6), rgba(15, 23, 42, 0.6)),
                url('<?= base_url("assets/img/IMG_1247.JPG") ?>') no-repeat center center;
            background-size: cover;
            padding: 48px;
        }

        .auth-brand-content {
            position: relative;
            z-index: 2;
            text-align: center;
            color: #ffffff;
        }

        .form-illustration {
            position: absolute;
            z-index: 1;
            pointer-events: none;
            opacity: 0.72;
            animation: floatNetwork 8s ease-in-out infinite;
        }

        .form-illustration--top {
            top: 74px;
            right: 42px;
            width: 250px;
            height: 160px;
        }

        .form-illustration--bottom {
            left: 38px;
            bottom: 58px;
            width: 270px;
            height: 175px;
            opacity: 0.58;
            animation-delay: -3s;
        }

        .form-illustration .line {
            position: absolute;
            height: 2px;
            background: rgba(15, 23, 42, 0.12);
            border-radius: 999px;
            transform-origin: left center;
        }

        .form-illustration .node {
            position: absolute;
            width: 12px;
            height: 12px;
            border: 2px solid rgba(37, 99, 235, 0.2);
            border-radius: 50%;
            background: rgba(37, 99, 235, 0.05);
            box-shadow: 0 0 18px rgba(37, 99, 235, 0.08);
        }

        .form-illustration .node-lg {
            width: 18px;
            height: 18px;
        }

        .form-illustration--top .line-1 {
            top: 42px;
            left: 28px;
            width: 150px;
            transform: rotate(12deg);
        }

        .form-illustration--top .line-2 {
            top: 92px;
            left: 88px;
            width: 150px;
            transform: rotate(-26deg);
        }

        .form-illustration--top .line-3 {
            top: 120px;
            left: 36px;
            width: 120px;
            transform: rotate(31deg);
        }

        .form-illustration--top .node-1 {
            top: 34px;
            left: 18px;
        }

        .form-illustration--top .node-2 {
            top: 62px;
            left: 168px;
        }

        .form-illustration--top .node-3 {
            top: 106px;
            left: 30px;
        }

        .form-illustration--top .node-4 {
            top: 48px;
            left: 232px;
        }

        .form-illustration--bottom .line-1 {
            top: 54px;
            left: 40px;
            width: 170px;
            transform: rotate(23deg);
        }

        .form-illustration--bottom .line-2 {
            top: 124px;
            left: 84px;
            width: 160px;
            transform: rotate(-18deg);
        }

        .form-illustration--bottom .line-3 {
            top: 62px;
            left: 162px;
            width: 105px;
            transform: rotate(72deg);
        }

        .form-illustration--bottom .node-1 {
            top: 42px;
            left: 32px;
        }

        .form-illustration--bottom .node-2 {
            top: 82px;
            left: 198px;
        }

        .form-illustration--bottom .node-3 {
            top: 132px;
            left: 78px;
        }

        .form-illustration--bottom .node-4 {
            top: 20px;
            left: 246px;
        }

        .brand-mark-row {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 18px;
            margin-bottom: 22px;
        }

        .brand-mark-wrap {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 70px;
            height: 70px;
            background: #ffffff;
            border-radius: 18px;
            box-shadow: 0 18px 36px rgba(0, 0, 0, 0.24);
        }

        .brand-mark-wrap img {
            width: 56px;
            max-width: 100%;
            height: auto;
        }

        .brand-mark-wrap-tkm img {
            width: 60px;
        }

        .brand-text-custom {
            display: block;
            color: #ffffff;
            font-size: 36px;
            font-weight: 800;
            letter-spacing: 5px;
            line-height: 1;
        }

        .brand-tagline-custom {
            display: block;
            margin-top: 12px;
            color: rgba(255, 255, 255, 0.58);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1.6px;
            text-transform: uppercase;
        }

        .brand-company {
            margin-top: 26px;
            color: rgba(255, 255, 255, 0.38);
            font-size: 13px;
            font-weight: 700;
        }

        .auth-form-panel {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            flex: 1 1 auto;
            height: 100%;
            min-height: 0;
            overflow: hidden;
            background: #ffffff;
            padding: 48px 40px;
        }

        .auth-form-wrap {
            position: relative;
            z-index: 2;
            box-sizing: border-box;
            width: 100%;
            max-width: 340px;
            padding: 44px 38px 34px;
            background: #ffffff;
            border: 1px solid #eef2f7;
            border-radius: 22px;
            box-shadow: 0 20px 55px rgba(15, 23, 42, 0.08);
            animation: loginCardIn 0.72s ease-out both;
        }

        .auth-title {
            margin: 0;
            color: #111827;
            font-size: 24px;
            font-weight: 800;
            line-height: 1.2;
            text-align: center;
            animation: loginItemIn 0.5s ease-out 0.1s both;
        }

        .auth-subtitle {
            margin: 6px 0 22px;
            color: #9ca3af;
            font-size: 12px;
            font-weight: 700;
            text-align: center;
            animation: loginItemIn 0.5s ease-out 0.18s both;
        }

        .auth-form-wrap .alert {
            border-radius: 10px;
            font-size: 14px;
            margin-bottom: 18px;
        }

        .auth-form-wrap .text-danger {
            display: block;
            margin: -4px 0 6px;
            font-size: 12px;
        }

        .auth-form-wrap .input-group {
            position: relative;
            isolation: isolate;
            min-height: 44px;
            margin-bottom: 13px;
            overflow: hidden;
            background: transparent;
            border: 0;
            border-radius: 10px;
            box-shadow: 0 6px 18px rgba(15, 23, 42, 0.03);
            transition: border-color 0.18s ease, box-shadow 0.18s ease, transform 0.18s ease;
            animation: loginItemIn 0.5s ease-out both;
        }

        .auth-form-wrap .input-group::before,
        .auth-form-wrap .input-group::after {
            content: "";
            position: absolute;
            pointer-events: none;
        }

        .auth-form-wrap .input-group::before {
            --input-light-angle: 0deg;
            inset: 0;
            z-index: -2;
            border-radius: 10px;
            background: conic-gradient(
                from var(--input-light-angle),
                rgba(229, 234, 242, 1) 0deg,
                rgba(229, 234, 242, 1) 312deg,
                rgba(96, 165, 250, 0.2) 326deg,
                rgba(37, 99, 235, 0.95) 344deg,
                rgba(125, 211, 252, 0.18) 360deg
            );
            opacity: 0;
            transition: opacity 0.18s ease;
        }

        .auth-form-wrap .input-group::after {
            inset: 1px;
            z-index: -1;
            border-radius: 9px;
            background: #ffffff;
        }

        .auth-form-wrap .input-group:focus-within {
            box-shadow: 0 10px 24px rgba(37, 99, 235, 0.13);
            transform: translateY(-1px);
        }

        .auth-form-wrap .input-group:focus-within::before {
            opacity: 1;
            animation: inputLightOrbit 4.8s linear infinite;
        }

        .auth-form-wrap form .input-group:nth-of-type(1) {
            animation-delay: 0.26s;
        }

        .auth-form-wrap form .input-group:nth-of-type(2) {
            animation-delay: 0.34s;
        }

        .auth-form-wrap .form-control,
        .auth-form-wrap .input-group-text {
            min-height: 42px;
            border: 0;
            background: transparent;
            color: #111827;
            font-size: 13px;
        }

        .auth-form-wrap .form-control {
            border-radius: 0;
            font-weight: 700;
        }

        .auth-form-wrap .form-control::placeholder {
            color: #a7b0bf;
            opacity: 1;
        }

        .auth-form-wrap .input-group-text {
            width: 42px;
            justify-content: center;
            color: #b7c1d0;
            border-radius: 0;
        }

        .auth-submit {
            min-height: 46px;
            margin-top: 12px;
            border: 1px solid #0f172a;
            border-radius: 10px;
            background: #0f172a;
            color: #ffffff;
            font-size: 14px;
            font-weight: 800;
            box-shadow: 0 12px 24px rgba(15, 23, 42, 0.18);
            transition: background-color 0.18s ease, border-color 0.18s ease, transform 0.18s ease, box-shadow 0.18s ease;
            animation: loginItemIn 0.5s ease-out 0.42s both;
        }

        .auth-submit:hover,
        .auth-submit:focus {
            border-color: #111827;
            background: #111827;
            color: #ffffff;
            transform: translateY(-1px);
            box-shadow: 0 15px 28px rgba(15, 23, 42, 0.24);
        }

        .auth-submit:active {
            transform: translateY(0);
            box-shadow: 0 9px 18px rgba(15, 23, 42, 0.18);
        }

        .password-toggle.input-group-text {
            width: 42px;
            justify-content: center;
            color: #b7c1d0;
            cursor: pointer;
        }

        .password-toggle.input-group-text:focus {
            box-shadow: none;
            outline: 0;
        }

        @media (max-width: 767.98px) {
            .auth-shell {
                flex-direction: column;
            }

            .auth-brand-panel {
                flex: 0 0 auto;
                height: 42%;
                min-height: 0;
                padding: 36px 24px;
            }

            .form-illustration {
                display: none;
            }

            .auth-form-panel {
                height: 58%;
                min-height: 0;
                padding: 34px 24px;
            }

            .auth-form-wrap {
                padding: 34px 24px 28px;
            }

            .brand-mark-wrap {
                width: 64px;
                height: 64px;
            }

            .brand-mark-wrap img {
                width: 52px;
            }

            .brand-mark-wrap-tkm img {
                width: 56px;
            }

            .brand-text-custom {
                font-size: 32px;
            }

            .brand-tagline-custom {
                font-size: 11px;
            }

            .auth-title {
                font-size: 23px;
            }
        }

        .auth-card-footer {
            margin: 34px 0 0;
            color: #b3bdcc;
            font-size: 10px;
            font-weight: 700;
            text-align: center;
            animation: loginItemIn 0.5s ease-out 0.5s both;
        }

        @keyframes loginCardIn {
            from {
                opacity: 0;
                transform: translateY(18px) scale(0.98);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @keyframes loginItemIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes floatNetwork {
            0%,
            100% {
                transform: translate3d(0, 0, 0);
            }

            50% {
                transform: translate3d(0, -10px, 0);
            }
        }

        @keyframes inputLightOrbit {
            to {
                --input-light-angle: 360deg;
            }
        }

        @property --input-light-angle {
            syntax: "<angle>";
            inherits: false;
            initial-value: 0deg;
        }

        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                scroll-behavior: auto !important;
                transition-duration: 0.01ms !important;
            }
        }
    </style>
</head>

<body class="hold-transition auth-page">
    <main class="auth-shell">
        <section class="auth-brand-panel" aria-label="ZEYN brand">
            <div class="auth-brand-content">
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
                <div class="brand-company">PT. Technology Karya Mandiri</div>
            </div>
        </section>

        <section class="auth-form-panel" aria-label="Login form">
            <div class="form-illustration form-illustration--top" aria-hidden="true">
                <span class="line line-1"></span>
                <span class="line line-2"></span>
                <span class="line line-3"></span>
                <span class="node node-1"></span>
                <span class="node node-2 node-lg"></span>
                <span class="node node-3"></span>
                <span class="node node-4"></span>
            </div>
            <div class="form-illustration form-illustration--bottom" aria-hidden="true">
                <span class="line line-1"></span>
                <span class="line line-2"></span>
                <span class="line line-3"></span>
                <span class="node node-1"></span>
                <span class="node node-2 node-lg"></span>
                <span class="node node-3"></span>
                <span class="node node-4"></span>
            </div>
            <div class="auth-form-wrap">
                <h1 class="auth-title">Selamat datang</h1>
                <p class="auth-subtitle">Masuk ke akun Anda untuk melanjutkan</p>

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

                <form action="<?= site_url('Auth') ?>" method="post">
                    <span class="text text-danger"><?= form_error('username') ?></span>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <div class="input-group-text">
                                <span class="fas fa-user"></span>
                            </div>
                        </div>
                        <input type="text" class="form-control" placeholder="Username" name="username">
                    </div>
                    <span class="text text-danger"><?= form_error('pass') ?></span>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>
                        <input id="login_password" type="password" class="form-control" placeholder="Password" name="pass">
                        <div class="input-group-append">
                            <button type="button" class="input-group-text password-toggle js-toggle-password" data-target="#login_password" aria-label="Tampilkan password">
                                <i class="fas fa-eye-slash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <button type="submit" class="btn btn-block auth-submit">Masuk</button>
                        </div>
                    </div>
                </form>
                <p class="auth-card-footer">&copy; 2026 PT. Technology Karya Mandiri. All rights reserved.</p>
            </div>
        </section>
    </main>
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
