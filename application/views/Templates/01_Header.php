<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="x-ua-compatible" content="ie=edge">

  <title><?= $title ?></title>
  <link rel="icon" type="image/png" href="<?= base_url('assets/dist/img/zeyn-logo.png') ?>">
  <link rel="shortcut icon" type="image/png" href="<?= base_url('assets/dist/img/zeyn-logo.png') ?>">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">

  <link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
  <link rel="stylesheet" href="<?= base_url('assets') ?>/dist/css/adminlte.min.css">
  <link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
  <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
  <link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/select2/css/select2.min.css">
  <link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
  <link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/jquery-ui/jquery-ui.min.css">

  <script src="<?= base_url('assets') ?>/plugins/jquery/jquery.min.js"></script>
  <script src="<?= base_url('assets') ?>/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="<?= base_url('assets') ?>/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
  <script src="<?= base_url('assets') ?>/dist/js/adminlte.js"></script>

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <style>
    :root {
      --shell-bg: #eef4f8;
      --shell-surface: #ffffff;
      --shell-line: rgba(148, 163, 184, 0.18);
      --shell-text: #0f172a;
      --shell-muted: #64748b;
      --shell-primary: #1d4ed8;
      --shell-primary-soft: rgba(29, 78, 216, 0.12);
      --shell-shadow: 0 22px 44px rgba(15, 23, 42, 0.08);
    }

    html {
      font-size: 15px;
    }

    body {
      font-family: "Plus Jakarta Sans", "Source Sans Pro", sans-serif;
      background:
        radial-gradient(circle at top right, rgba(96, 165, 250, 0.12), transparent 18%),
        linear-gradient(180deg, #f5f8fc 0%, #edf3f8 100%);
      color: var(--shell-text);
      text-rendering: optimizeLegibility;
      -webkit-font-smoothing: antialiased;
      -moz-osx-font-smoothing: grayscale;
    }

    .content-wrapper,
    .main-footer {
      background: transparent;
    }

    .card,
    .small-box,
    .info-box,
    .modal-content,
    .dropdown-menu {
      border-radius: 18px;
    }

    .card,
    .small-box,
    .info-box {
      border: 1px solid var(--shell-line);
      box-shadow: var(--shell-shadow);
    }

    .content-header h1,
    h1, h2, h3, h4, h5, h6 {
      letter-spacing: -0.02em;
    }

    .table thead th {
      vertical-align: middle;
    }

    .btn:focus,
    .form-control:focus,
    .custom-select:focus,
    .select2-container--bootstrap4.select2-container--focus .select2-selection {
      box-shadow: 0 0 0 0.18rem rgba(29, 78, 216, 0.16) !important;
    }

    .select2-container--bootstrap4 .select2-selection {
      border-radius: 12px;
      min-height: 42px;
      border-color: rgba(148, 163, 184, 0.32);
    }

    .select2-container--bootstrap4 .select2-selection--single {
      min-height: 42px;
      padding: .375rem .75rem;
      display: flex;
      align-items: center;
    }

    .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
      color: var(--shell-text);
      padding-left: 0;
      line-height: 1.4;
    }

    .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow {
      height: 40px;
      top: 0;
      right: 6px;
    }

    .select2-container--bootstrap4 .select2-selection--multiple {
      min-height: 42px;
      padding: .2rem .45rem;
      display: flex;
      align-items: center;
      flex-wrap: wrap;
      gap: .25rem;
    }

    .select2-container--bootstrap4 .select2-selection--multiple .select2-search__field {
      margin-top: 0 !important;
    }

    .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__choice {
      margin-top: 0;
      border-radius: 999px;
      padding: .2rem .5rem;
    }

    .form-control,
    .custom-select {
      border-radius: 12px;
      min-height: 42px;
      border-color: rgba(148, 163, 184, 0.32);
    }

    .modal-dialog.modal-xxl {
      width: 78vw;
      max-width: 78vw;
    }

    @media (max-width: 767.98px) {
      .modal-dialog.modal-xxl {
        width: calc(100vw - 1rem);
        max-width: calc(100vw - 1rem);
        margin: .5rem auto;
      }
    }
  </style>
</head>

<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
