<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="x-ua-compatible" content="ie=edge">

  <title><?= $title ?></title>
  <link rel="icon" type="image/png" href="<?= base_url('assets/dist/img/zeyn-logo.png?v=20260602') ?>">
  <link rel="shortcut icon" type="image/png" href="<?= base_url('assets/dist/img/zeyn-logo.png?v=20260602') ?>">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="<?= base_url('assets') ?>/dist/css/adminlte.min.css">
  <link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
  <link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/select2/css/select2.min.css">
  <link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">

  <script src="<?= base_url('assets') ?>/plugins/jquery/jquery.min.js"></script>
  <script src="<?= base_url('assets') ?>/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="<?= base_url('assets') ?>/plugins/datatables/jquery.dataTables.min.js"></script>
  <script src="<?= base_url('assets') ?>/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
  <script src="<?= base_url('assets') ?>/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
  <script src="<?= base_url('assets') ?>/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
  <script src="<?= base_url('assets') ?>/plugins/select2/js/select2.full.min.js"></script>

  <style>
    :root {
      --emr-bg: #eef4f8;
      --emr-surface: #ffffff;
      --emr-line: rgba(148, 163, 184, 0.24);
      --emr-text: #0f172a;
      --emr-muted: #64748b;
      --emr-primary: #1d4ed8;
      --emr-shadow: 0 20px 42px rgba(15, 23, 42, 0.08);
    }

    html {
      font-size: 15px;
    }

    body {
      min-height: 100vh;
      margin: 0;
      font-family: "Plus Jakarta Sans", "Source Sans Pro", sans-serif;
      background:
        radial-gradient(circle at top right, rgba(45, 212, 191, 0.14), transparent 20%),
        linear-gradient(180deg, #f7fbff 0%, var(--emr-bg) 100%);
      color: var(--emr-text);
      text-rendering: optimizeLegibility;
      -webkit-font-smoothing: antialiased;
      -moz-osx-font-smoothing: grayscale;
    }

    .emr-shell {
      min-height: 100vh;
    }

    .emr-topbar {
      position: sticky;
      top: 0;
      z-index: 1020;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 1rem;
      min-height: 64px;
      padding: .85rem 1.25rem;
      background: rgba(255, 255, 255, .92);
      border-bottom: 1px solid var(--emr-line);
      backdrop-filter: blur(14px);
    }

    .emr-brand {
      display: flex;
      align-items: center;
      gap: .75rem;
      min-width: 0;
    }

    .emr-brand img {
      width: 34px;
      height: 34px;
      object-fit: contain;
    }

    .emr-brand__title {
      font-weight: 800;
      line-height: 1.1;
    }

    .emr-brand__subtitle {
      color: var(--emr-muted);
      font-size: .78rem;
      font-weight: 600;
    }

    .emr-account {
      display: flex;
      align-items: center;
      justify-content: flex-end;
      gap: 1rem;
      min-width: 0;
    }

    .emr-account__logos {
      display: flex;
      align-items: center;
      gap: 0;
      min-width: 0;
      padding: .15rem .35rem;
      background: #fff;
      border: 1px solid var(--emr-line);
      border-radius: 12px;
    }

    .emr-account__logo-ekamora {
      width: 170px;
      height: 40px;
      object-fit: contain;
    }

    .emr-account__logo-tkm {
      width: 170px;
      height: 40px;
      object-fit: contain;
    }

    .emr-logout-btn {
      height: 40px;
      display: inline-flex;
      align-items: center;
      gap: .35rem;
      padding: .45rem .75rem;
      font-weight: 700;
    }

    .emr-fullscreen-btn {
      width: 40px;
      height: 40px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      flex: 0 0 auto;
      border: 1px solid var(--emr-line);
      border-radius: 12px;
      background: #fff;
      color: #2563eb;
      box-shadow: 0 8px 18px rgba(15, 23, 42, .06);
    }

    .emr-fullscreen-btn:hover {
      border-color: #93c5fd;
      background: #eff6ff;
      color: #1d4ed8;
    }

    .emr-fullscreen-btn i {
      font-size: .95rem;
      line-height: 1;
    }

    .emr-main {
      width: 100%;
      margin: 0;
      padding: 1rem;
    }

    .card,
    .small-box,
    .modal-content,
    .info-box {
      border: 1px solid var(--emr-line);
      border-radius: 14px;
      box-shadow: var(--emr-shadow);
    }

    .table thead th {
      vertical-align: middle;
    }

    .btn,
    .form-control,
    .custom-select {
      border-radius: 10px;
    }

    @media (max-width: 575.98px) {
      .emr-topbar {
        align-items: flex-start;
        flex-direction: column;
      }

      .emr-account {
        width: 100%;
        justify-content: space-between;
        gap: .65rem;
      }

      .emr-account__logos {
        flex: 1;
        justify-content: flex-start;
        gap: 0;
        padding: .2rem .5rem;
      }

      .emr-account__logo-ekamora {
        width: 128px;
        height: 34px;
      }

      .emr-account__logo-tkm {
        width: 128px;
        height: 34px;
      }

      .emr-main {
        padding: .65rem;
      }
    }
  </style>
</head>

<body>
  <div class="emr-shell">
    <header class="emr-topbar">
      <div class="emr-brand">
        <img src="<?= base_url('assets/dist/img/zeyn-logo.png?v=20260602') ?>" alt="Zeyn">
        <div>
          <div class="emr-brand__title">PO EMMR - TKM</div>
          <div class="emr-brand__subtitle">PO Monitoring</div>
        </div>
      </div>
      <div class="emr-account">
        <div class="emr-account__logos">
          <img class="emr-account__logo-ekamora" src="<?= base_url('assets/dist/img/logoweb.png') ?>" alt="MoraRepublic">
          <img class="emr-account__logo-tkm" src="<?= base_url('assets/dist/img/logotkmsolid.png') ?>" alt="TKM">
        </div>
        <button type="button" class="emr-fullscreen-btn" id="emr-fullscreen-toggle" title="Fullscreen" aria-label="Fullscreen">
          <i class="fas fa-expand"></i>
        </button>
        <a href="<?= base_url('Auth/logout') ?>" class="btn btn-outline-danger btn-sm emr-logout-btn">
          <i class="fas fa-sign-out-alt mr-1"></i> Logout
        </a>
      </div>
    </header>
    <main class="emr-main">
      <script>
        (function () {
          var button = document.getElementById('emr-fullscreen-toggle');
          if (!button) {
            return;
          }

          var icon = button.querySelector('i');

          function fullscreenElement() {
            return document.fullscreenElement || document.webkitFullscreenElement || document.msFullscreenElement || null;
          }

          function requestFullscreen(element) {
            if (element.requestFullscreen) {
              return element.requestFullscreen();
            }
            if (element.webkitRequestFullscreen) {
              return element.webkitRequestFullscreen();
            }
            if (element.msRequestFullscreen) {
              return element.msRequestFullscreen();
            }
            return null;
          }

          function exitFullscreen() {
            if (document.exitFullscreen) {
              return document.exitFullscreen();
            }
            if (document.webkitExitFullscreen) {
              return document.webkitExitFullscreen();
            }
            if (document.msExitFullscreen) {
              return document.msExitFullscreen();
            }
            return null;
          }

          function syncFullscreenButton() {
            var isFullscreen = !!fullscreenElement();
            button.setAttribute('title', isFullscreen ? 'Exit fullscreen' : 'Fullscreen');
            button.setAttribute('aria-label', isFullscreen ? 'Exit fullscreen' : 'Fullscreen');
            if (icon) {
              icon.className = isFullscreen ? 'fas fa-compress' : 'fas fa-expand';
            }
          }

          button.addEventListener('click', function () {
            if (fullscreenElement()) {
              exitFullscreen();
            } else {
              requestFullscreen(document.documentElement);
            }
          });

          document.addEventListener('fullscreenchange', syncFullscreenButton);
          document.addEventListener('webkitfullscreenchange', syncFullscreenButton);
          document.addEventListener('MSFullscreenChange', syncFullscreenButton);
          syncFullscreenButton();
        })();
      </script>
