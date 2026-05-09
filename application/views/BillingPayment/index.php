<?php
$status = $this->session->flashdata('status');
$error_log = $this->session->flashdata('error_log');

$bulan_order = [
    'JANUARI',
    'FEBRUARI',
    'MARET',
    'APRIL',
    'MEI',
    'JUNI',
    'JULI',
    'AGUSTUS',
    'SEPTEMBER',
    'OKTOBER',
    'NOVEMBER',
    'DESEMBER'
];

$total = 1;

$bowheer_options = [];
foreach (($masterBowheerList ?? []) as $row) {
    if (!empty($row['id_bowheer']) && !isset($bowheer_options[$row['id_bowheer']])) {
        $bowheer_options[$row['id_bowheer']] = [
            'id' => $row['id_bowheer'],
            'text' => $row['nama_bowheer']
        ];
    }
}
$bowheer_options = array_values($bowheer_options);

$unique_bowheer = array_unique(array_column($getAllData, 'nama_bowheer'));
$unique_regional = array_unique(array_column($getAllData, 'regional_payment'));
$unique_city = array_unique(array_column($getAllData, 'area_payment'));
$unique_priority = array_unique(array_column($getAllData, 'priority'));
$unique_status = array_unique(array_column($getAllData, 'status_invoice'));

?>

<meta name="format-detection" content="telephone=no">
<div id="globalLoader">
    <div class="loader-content">
        <div class="spinner"></div>
    </div>
</div>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12 ">
                    <h1 class="m-0 text-dark" style="text-align: center;">INVOICE DUE DATE TRACKING</h1>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>

    <div class="container-fluid">

        <div class="row">
            <div class="col-md-12">
                <div class="card card-outline card-primary shadow-sm billing-filter-card">
                    <div class="card-header billing-filter-card__header">
                        <div>
                            <h3 class="card-title mb-1">Filter Data</h3>
                        </div>

                        <div class="card-tools billing-card-tools">
                            <button id="cardfiltercollapse" type="button" class="btn btn-tool billing-filter-collapse"
                                data-card-widget="collapse">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>

                    <div class="card-body billing-filter-card__body">
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label class="billing-field-label">Project / Bowheer</label>
                                        <select id="filter_bowheer_up" class="select2 billing-filter-select" multiple="multiple"
                                            data-placeholder="Pilih bowheer" style="width: 100%;">
                                            <?php foreach ($unique_bowheer as $bowheer): ?>
                                                <option value="<?= $bowheer ?>"><?= $bowheer ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label class="billing-field-label">Regional</label>
                                        <select id="filter_regional_up" class="select2 billing-filter-select" multiple="multiple"
                                            data-placeholder="Pilih regional" style="width: 100%;">
                                            <?php foreach ($unique_regional as $regional): ?>
                                                <option value="<?= $regional ?>"><?= $regional ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label class="billing-field-label">Kota</label>
                                        <select id="filter_city_up" class="select2 billing-filter-select" multiple="multiple"
                                            data-placeholder="Pilih kota" style="width: 100%;">
                                            <?php foreach ($unique_city as $city): ?>
                                                <option value="<?= $city ?>"><?= $city ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label class="billing-field-label">Priority</label>
                                        <select id="filter_priority_up" class="select2 billing-filter-select" multiple="multiple"
                                            data-placeholder="Pilih Prioritas" style="width: 100%;">
                                            <?php foreach ($unique_priority as $priority): ?>
                                                <option value="<?= $priority ?>"><?= $priority ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-sm-12">
                                    <div class="billing-filter-actions">
                                        <button id="btnFilterDataProject" type="button" class="btn budget-btn budget-btn--primary billing-filter-btn">
                                            <i class="fa fa-spinner fa-spin loading mr-1" style="display:none"></i>
                                            <i class="fas fa-search mr-1"></i> Search
                                        </button>
                                        <button type="button" id="reset_filter" class="btn budget-btn budget-btn--ghost billing-filter-btn"
                                            data-dismiss="modal">
                                            <i class="fas fa-redo-alt mr-1"></i> Reset
                                        </button>
                                        <button type="button" class="btn budget-btn budget-btn--success billing-filter-btn"
                                        data-target="#modal-download-billing-report" data-toggle="modal">
                                            <i class="fas fa-file-download mr-1"></i> Download Report
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Info boxes -->
            <div class="row billing-summary-row billing-summary-row--hero justify-content-center">
                <div class="col-12 col-sm-8 col-lg-5 col-xl-4 billing-summary-col billing-summary-col--hero">
                    <div class="info-box mb-3 premium-summary-card premium-summary-card--hero premium-total">
                        <div class="info-box-content">
                            <span class="info-box-text">TOTAL BILLING</span>
                            <span class="summary-caption">Akumulasi outstanding invoice seluruh prioritas</span>
                            <span class="info-box-number" id="dashboardTotalBilling">Rp. 0</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row billing-summary-row billing-summary-row--detail">
                <div class="col-12 col-sm-6 col-xl-3 billing-summary-col">
                    <div class="info-box mb-3 premium-summary-card premium-p1">
                        <span class="info-box-icon premium-summary-icon">
                            <i class="fas fa-bolt"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">BILLING ( P1 )</span>
                            <span class="summary-caption">Tagihan diatas 45 hari sejak jatuh tempo</span>
                            <span class="info-box-number" id="dashboardBillingP1">Rp. 0</span>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-xl-3 billing-summary-col">
                    <div class="info-box mb-3 premium-summary-card premium-p2">
                        <span class="info-box-icon premium-summary-icon">
                            <i class="fas fa-fire-alt"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">BILLING ( P2 )</span>
                            <span class="summary-caption">Tagihan 31-45 hari sejak jatuh tempo</span>
                            <span class="info-box-number" id="dashboardBillingP2">Rp. 0</span>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-xl-3 billing-summary-col">
                    <div class="info-box mb-3 premium-summary-card premium-p3">
                        <span class="info-box-icon premium-summary-icon">
                            <i class="fas fa-hourglass-half"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">BILLING ( P3 )</span>
                            <span class="summary-caption">Tagihan 1-30 hari sejak jatuh tempo</span>
                            <span class="info-box-number" id="dashboardBillingP3">Rp. 0</span>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-xl-3 billing-summary-col">
                    <div class="info-box mb-3 premium-summary-card premium-bjt">
                        <span class="info-box-icon premium-summary-icon">
                            <i class="fas fa-shield-alt"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">BILLING ( BJT )</span>
                            <span class="summary-caption">Belum jatuh tempo</span>
                            <span class="info-box-number" id="dashboardBillingBJT">Rp. 0</span>
                        </div>
                    </div>
                </div>
            </div>
    </section>

    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1 class="m-0 text-dark" style="text-align: center;">INVOICE PRIORITY DETAILS</h1>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Info boxes -->
            <div class="row">

                <div class="col-12">
                    <div class="card card-outline card-primary shadow-sm billing-detail-card">
                        <div class="card-header billing-detail-card__header">
                            <div>
                                <h3 class="card-title mb-1">Details</h3>
                            </div>

                            <div class="card-tools billing-card-tools">
                                <button id="carddetailcollapse" type="button" class="btn btn-tool billing-filter-collapse"
                                    data-card-widget="collapse">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body table-responsive text-nowrap">
                            <table id="tabel_targetpriority_bowheer" class="table table-bordered table-striped">
                                <thead style="text-align: center;" class="bg-info">
                                    <tr>
                                        <th rowspan="2"
                                            style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                            No</th>
                                        <th rowspan="2"
                                            style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                            BOWHEER</th>
                                        <th colspan="4"
                                            style="text-align:center; background-color: aqua; color: #000000;">RINCIAN
                                            TAGIHAN</th>
                                        <th rowspan="2"
                                            style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                            GRAND TOTAL</th>
                                    </tr>
                                    <tr>
                                        <th style="color: #000000;">TAGIHAN ( P1 )<br>> 45 Hari</th>
                                        <th style="color: #000000;">TAGIHAN ( P2 )<br>31 - 45 Hari</th>
                                        <th style="color: #000000;">TAGIHAN ( P3 )<br>0 - 30 Hari</th>
                                        <th style="color: #000000;">TAGIHAN ( BJT )<br>< 0 Hari</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    foreach ($getTargetPriorityBowheer as $data):
                                        ?>
                                        <tr>
                                            <td><?= $total++ ?></td>
                                            <td><?= $data['nama_bowheer'] ?></td>
                                            <td><?php if ($data['total_p1'] == "0") {
                                                echo "-";
                                            } else {
                                                echo number_format(floatval($data['total_p1']), 0, ",", ".");
                                            } ?></td>
                                            </td>
                                            <td><?php
                                            if ($data['total_p2'] == "0") {
                                                echo "-";
                                            } else {
                                                echo number_format(floatval($data['total_p2']), 0, ",", ".");
                                            }
                                            ?></td>
                                            <td><?php
                                            if ($data['total_p3'] == "0") {
                                                echo "-";
                                            } else {
                                                echo number_format(floatval($data['total_p3']), 0, ",", ".");
                                            }
                                            ?></td>
                                            <td><?php
                                            if ($data['total_bjt'] == "0") {
                                                echo "-";
                                            } else {
                                                echo number_format(floatval($data['total_bjt']), 0, ",", ".");
                                            }
                                            ?></td>
                                            <td><?php
                                            if ($data['total_all'] == "0") {
                                                echo "-";
                                            } else {
                                                echo number_format(floatval($data['total_all']), 0, ",", ".");
                                            }
                                            ?></td>
                                        </tr>

                                        <?php
                                    endforeach; ?>

                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="2">Total</th>
                                        <th id="totalP1"></th>
                                        <th id="totalP2"></th>
                                        <th id="totalP3"></th>
                                        <th id="totalBJT"></th>
                                        <th id="totalAll"></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>



    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 justify-content-center">
                <div class="billing-detail-toolbar">

                    <button type="button" class="btn budget-btn budget-btn--primary billing-detail-toolbar__btn" data-toggle="modal"
                        data-target="#modal-download-report">
                        <i class="fas fa-plus-circle mr-2"></i> Add Invoice
                    </button>

                    <button type="button" class="btn budget-btn budget-btn--success billing-detail-toolbar__btn" data-toggle="modal"
                        data-target="#modal-payment">
                        <i class="fas fa-money-bill-wave mr-2"></i> Add Payment
                    </button>

                </div>
            </div>
        </div>
    </div>

    <section class="content">

        <div class="container-fluid">
            <!-- Info boxes -->
            <div class="row">
                <!-- fix for small devices only -->
                <div class="clearfix hidden-md-up"></div>

                <div class="col-12">
                    <div class="card card-outline card-primary shadow-sm billing-workbench-card">
                        <div class="card-header billing-workbench-card__header">
                            <div>
                                <h3 class="card-title mb-1">Invoice Detail Workbench</h3>
                            </div>
                        </div>

                        <div class="billing-workbench-tabs-wrap">
                            <ul class="nav nav-pills" id="invoice-status-tab" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active tab-status-invoice" data-status="open"
                                        href="javascript:void(0)">Outstanding</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link tab-status-invoice" data-status="partial"
                                        href="javascript:void(0)">Partial Payment</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link tab-status-invoice" data-status="paid"
                                        href="javascript:void(0)">Full Payment</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link tab-status-invoice" data-status="all"
                                        href="javascript:void(0)">All Invoice</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link tab-status-invoice" data-status="reject"
                                        href="javascript:void(0)">Reject Payment</a>
                                </li>
                            </ul>
                        </div>

                        <div class="card-body billing-workbench-card__body">
                            <div class="detail-table-shell billing-workbench-table-shell">
                                <div class="table-responsive">
                                <table id="tabel_list_open_invoice" class="table table-bordered table-hover js-billing-detail-table">
                                    <thead class="bg-info">
                                        <tr>
                                            <th>No</th>
                                            <th>Bowheer</th>
                                            <th>Invoice</th>
                                            <th>Price</th>
                                            <th>Regional</th>
                                            <th>Area</th>
                                            <th>Date Invoice</th>
                                            <th>Date Submit</th>
                                            <th>Due Date</th>
                                            <th>Aging Today</th>
                                            <th>Aging Due Date</th>
                                            <th>Priority</th>
                                            <th>PO Number</th>
                                            <th>Status Invoice</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="10" style="text-align:right">Total:</th>
                                            <th id="totalTarget">0</th>
                                            <th id="totalAchieved">0</th>
                                            <th id="totalSisa">0</th>
                                            <th id="totalTargetPercent">0%</th>
                                            <th id="totalAchievedPercent">0%</th>
                                        </tr>
                                    </tfoot>
                                </table>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <!-- ISI -->
                        </div>
                    </div>
    </section>

    <!-- MODAL TAMBAH INVOICE -->
    <div class="modal fade" id="modal-download-report" data-backdrop="static" tabindex="-1" role="dialog"
        aria-labelledby="exampleModalLongTitle" aria-hidden="true">
        <div class="modal-dialog modal-xxl" role="document">
            <div class="modal-content billing-workflow-modal">
                <div class="modal-header billing-workflow-modal__header">
                    <div>
                        <span class="billing-workflow-modal__eyebrow">Invoice Intake</span>
                        <h5 class="modal-title mb-1" id="modalTitle">Tambah Invoice</h5>
                        <p class="mb-0 billing-workflow-modal__subtitle">Kelola input invoice manual maupun import Excel dalam workflow yang lebih nyaman dipindai.</p>
                    </div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body billing-workflow-modal__body">
                    <ul class="nav nav-tabs" id="invoiceUploadTab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="tab-manual-invoice-link" data-toggle="tab"
                                href="#tab-manual-invoice" role="tab">Upload Manual Batch</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tab-excel-invoice-link" data-toggle="tab"
                                href="#tab-excel-invoice" role="tab">Upload Excel Drag & Drop</a>
                        </li>
                    </ul>

                    <div class="tab-content pt-3">
                        <div class="tab-pane fade show active" id="tab-manual-invoice" role="tabpanel">
                            <form id="formManualInvoiceBatch">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <p class="mb-0 text-muted">Tambah beberapa invoice sekaligus, lalu simpan dalam satu
                                        proses.</p>
                                    <button type="button" class="btn btn-primary" id="btnAddManualInvoiceRow">
                                        Tambah Baris
                                    </button>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped" id="table_manual_invoice">
                                        <thead>
                                            <tr class="text-center">
                                                <th>No</th>
                                                <th>Bowheer</th>
                                                <th>No Invoice</th>
                                                <th>Tgl Create</th>
                                                <th>Tgl Submit</th>
                                                <th>PO Number</th>
                                                <th>PO Date</th>
                                                <th>Invoice Est</th>
                                                <th>Invoice Nett</th>
                                                <th>Regional</th>
                                                <th>Area</th>
                                                <th>Deskripsi</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr id="emptyManualInvoiceRow">
                                                <td colspan="13" class="text-center text-muted">Belum ada invoice
                                                    ditambahkan</td>
                                            </tr>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th colspan="7" class="text-right">Total</th>
                                                <th id="manualInvoiceEstTotal" class="text-right">0,00</th>
                                                <th id="manualInvoiceNettTotal" class="text-right">0,00</th>
                                                <th colspan="4"></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>

                                <div class="d-flex justify-content-end mt-3">
                                    <button type="submit" class="btn btn-success">Simpan Invoice Manual</button>
                                </div>
                            </form>
                        </div>

                        <div class="tab-pane fade" id="tab-excel-invoice" role="tabpanel">
                            <form id="formPreviewInvoiceImport" enctype="multipart/form-data">
                                <div class="d-flex justify-content-end mb-3">
                                    <a href="<?= base_url('BillingPayment/downloadBowheerReference') ?>"
                                        class="btn btn-outline-info mr-2">
                                        Download Referensi Bowheer
                                    </a>
                                    <a href="<?= base_url('BillingPayment/downloadInvoiceImportTemplate') ?>"
                                        class="btn btn-outline-success">
                                        Download Format CSV
                                    </a>
                                </div>

                                <div id="invoiceDropzone" class="invoice-dropzone text-center">
                                    <input type="file" id="invoiceExcelFile" name="file_excel" accept=".xls,.xlsx,.csv"
                                        hidden>
                                    <h5 class="mb-2">Drop file Excel atau CSV di sini</h5>
                                    <p class="text-muted mb-3">atau klik tombol berikut untuk memilih file `.xls`, `.xlsx`, atau `.csv`
                                    </p>
                                    <label for="invoiceExcelFile" class="btn btn-outline-primary mb-0" id="btnChooseInvoiceExcel">
                                        Pilih File Excel
                                    </label>
                                </div>

                                <div class="alert alert-light border mt-3 mb-3">
                                    <strong>Header yang didukung:</strong>
                                    `id_bowheer` atau `nama_bowheer`, `no_invoice`, `tgl_create_invoice`,
                                    `tgl_submit_invoice`, `po_number`, `po_tgl`, `invoice_price_est`,
                                    `invoice_price_nett`, `regional_payment`, `area_payment`, `deskripsi_payment`.
                                    <br>
                                    <strong>Saran:</strong> gunakan `id_bowheer` dari file referensi agar mapping
                                    bowheer selalu tepat. Jika memakai `nama_bowheer`, nama harus sama persis dengan data
                                    master.
                                </div>
                            </form>

                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div id="invoiceImportSummary" class="text-muted">Belum ada file dipreview</div>
                                <button type="button" class="btn btn-success" id="btnSaveImportedInvoice" disabled>
                                    Import ke Database
                                </button>
                            </div>

                            <input type="hidden" id="invoiceImportRowsJson">

                            <div class="table-responsive">
                                <table class="table table-bordered table-striped" id="table_invoice_import_preview">
                                    <thead>
                                        <tr class="text-center">
                                            <th>Row</th>
                                            <th>Bowheer ID</th>
                                            <th>No Invoice</th>
                                            <th>Tgl Submit</th>
                                            <th>PO Number</th>
                                            <th>Invoice Nett</th>
                                            <th>Regional</th>
                                            <th>Area</th>
                                            <th>Status</th>
                                            <th>Keterangan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr id="emptyImportPreviewRow">
                                            <td colspan="10" class="text-center text-muted">Preview import akan tampil
                                                di sini</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer billing-workflow-modal__footer">
                    <button type="button" class="btn budget-btn budget-btn--ghost" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-download-billing-report" data-backdrop="static" tabindex="-1" role="dialog"
        aria-labelledby="modalBillingReportLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content billing-report-modal">
                <form id="formDownloadBillingReport" method="GET" target="_blank"
                    action="<?= base_url('BillingPayment/downloadBillingReport') ?>">
                    <div class="modal-header billing-report-modal__header">
                        <div>
                            <span class="billing-report-modal__eyebrow">Billing Report</span>
                            <h5 class="modal-title mb-1" id="modalBillingReportLabel">Download Billing Report</h5>
                            <p class="mb-0 billing-report-modal__subtitle">Susun export invoice berdasarkan filter aktif agar file report yang diunduh lebih presisi dan siap dibagikan.</p>
                        </div>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="billing-report-section">
                            <div class="billing-report-section__title">Filter Report</div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="billing-field-label">Bowheer</label>
                                        <select name="bowheer[]" id="report_filter_bowheer" class="select2" multiple="multiple"
                                            data-placeholder="Pilih bowheer" style="width: 100%;">
                                            <?php foreach ($unique_bowheer as $bowheer): ?>
                                                <option value="<?= $bowheer ?>"><?= $bowheer ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="billing-field-label">Regional</label>
                                        <select name="regional[]" id="report_filter_regional" class="select2"
                                            multiple="multiple" data-placeholder="Pilih regional" style="width: 100%;">
                                            <?php foreach ($unique_regional as $regional): ?>
                                                <option value="<?= $regional ?>"><?= $regional ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="billing-field-label">Area</label>
                                        <select name="city[]" id="report_filter_city" class="select2" multiple="multiple"
                                            data-placeholder="Pilih area" style="width: 100%;">
                                            <?php foreach ($unique_city as $city): ?>
                                                <option value="<?= $city ?>"><?= $city ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="billing-field-label">Priority</label>
                                        <select name="priority[]" id="report_filter_priority" class="select2"
                                            multiple="multiple" data-placeholder="Pilih priority" style="width: 100%;">
                                            <?php foreach ($unique_priority as $priority): ?>
                                                <option value="<?= $priority ?>"><?= $priority ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group mb-0">
                                        <label class="billing-field-label">Status Invoice</label>
                                        <select name="status_invoice" id="report_filter_status" class="form-control billing-report-input">
                                            <option value="open">Open</option>
                                            <option value="partial">Partial</option>
                                            <option value="paid">Paid</option>
                                            <option value="reject">Reject</option>
                                            <option value="all">Semua Status</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer billing-report-modal__footer">
                        <button type="button" class="btn budget-btn budget-btn--ghost" data-dismiss="modal">
                            <i class="fas fa-times mr-1"></i> Cancel
                        </button>
                        <button type="submit" class="btn budget-btn budget-btn--success">
                            <i class="fas fa-file-excel mr-1"></i> Download Excel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-payment" tabindex="-1" role="dialog" aria-labelledby="modalPaymentLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xxl" role="document">
            <div class="modal-content billing-workflow-modal">
                <div class="modal-header billing-workflow-modal__header">
                    <div>
                        <span class="billing-workflow-modal__eyebrow">Payment Batch</span>
                        <h5 class="modal-title mb-1" id="modalPaymentLabel">Tambah Pencairan Invoice</h5>
                        <p class="mb-0 billing-workflow-modal__subtitle">Gunakan input manual atau import Excel untuk proses payment yang lebih cepat dan tetap mudah dicek.</p>
                    </div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body billing-workflow-modal__body">
                    <ul class="nav nav-tabs" id="paymentUploadTab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="tab-manual-payment-link" data-toggle="tab"
                                href="#tab-manual-payment" role="tab">Upload Manual Batch</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tab-excel-payment-link" data-toggle="tab"
                                href="#tab-excel-payment" role="tab">Import Excel</a>
                        </li>
                    </ul>

                    <div class="tab-content pt-3">
                        <div class="tab-pane fade show active" id="tab-manual-payment" role="tabpanel">
                            <form id="formBatchPayment">
                                <div class="form-group">
                                    <label>Pilih Nomor Invoice</label>
                                    <select id="select_invoice_payment" class="form-control" multiple="multiple"
                                        style="width: 100%;"></select>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped" id="table_selected_invoice">
                                        <thead>
                                            <tr class="text-center">
                                                <th style="width: 60px;">No</th>
                                                <th>Nama Bowheer</th>
                                                <th>No Invoice</th>
                                                <th>No PO</th>
                                                <th style="width: 180px;">Invoice Price</th>
                                                <th style="width: 180px;">Payment Price</th>
                                                <th style="width: 180px;">Date Payment</th>
                                                <th style="width: 140px;">Status Payment</th>
                                                <th style="width: 80px;">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr id="emptyRow">
                                                <td colspan="9" class="text-center text-muted">Belum ada invoice dipilih</td>
                                            </tr>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th colspan="4" class="text-right">Total</th>
                                                <th id="totalInvoicePrice" class="text-right">0</th>
                                                <th id="totalPaymentPrice" class="text-right">0</th>
                                                <th colspan="3"></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>

                                <div class="d-flex justify-content-end mt-3">
                                    <button type="submit" class="btn btn-success">Simpan Pembayaran</button>
                                </div>
                            </form>
                        </div>

                        <div class="tab-pane fade" id="tab-excel-payment" role="tabpanel">
                            <form id="formPreviewPaymentImport" enctype="multipart/form-data">
                                <div class="d-flex justify-content-end mb-3">
                                    <a href="<?= base_url('BillingPayment/downloadPaymentImportTemplate') ?>"
                                        class="btn btn-outline-success">
                                        Download Format CSV
                                    </a>
                                </div>

                                <div id="paymentDropzone" class="invoice-dropzone text-center">
                                    <input type="file" id="paymentExcelFile" name="file_excel" accept=".xls,.xlsx,.csv"
                                        hidden>
                                    <h5 class="mb-2">Drop file Excel atau CSV di sini</h5>
                                    <p class="text-muted mb-3">atau klik tombol berikut untuk memilih file `.xls`, `.xlsx`, atau `.csv`
                                    </p>
                                    <label for="paymentExcelFile" class="btn btn-outline-primary mb-0">
                                        Pilih File Excel
                                    </label>
                                </div>

                                <div class="alert alert-light border mt-3 mb-3">
                                    <strong>Header yang didukung:</strong>
                                    `no_invoice`, `tgl_payment_invoice`, `invoice_price_payment`, `status_invoice`.
                                    <br>
                                    <strong>Status yang didukung:</strong> `paid`, `partial`, `reject`.
                                </div>
                            </form>

                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div id="paymentImportSummary" class="text-muted">Belum ada file dipreview</div>
                                <button type="button" class="btn btn-success" id="btnSaveImportedPayment" disabled>
                                    Import ke Database
                                </button>
                            </div>

                            <input type="hidden" id="paymentImportRowsJson">

                            <div class="table-responsive">
                                <table class="table table-bordered table-striped" id="table_payment_import_preview">
                                    <thead>
                                        <tr class="text-center">
                                            <th>No Invoice</th>
                                            <th>Tanggal Payment</th>
                                            <th>Invoice Price</th>
                                            <th>Payment Price</th>
                                            <th>Selisih</th>
                                            <th>Status Payment</th>
                                            <th>Status</th>
                                            <th>Keterangan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr id="emptyPaymentImportPreviewRow">
                                            <td colspan="8" class="text-center text-muted">Preview import akan tampil di sini</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer billing-workflow-modal__footer">
                    <button type="button" class="btn budget-btn budget-btn--ghost" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-edit-partial-payment" tabindex="-1" role="dialog"
        aria-labelledby="modalEditPartialPaymentLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content billing-workflow-modal">
                <form id="formEditPartialPayment">
                    <div class="modal-header billing-workflow-modal__header">
                        <div>
                            <span class="billing-workflow-modal__eyebrow">Partial Payment</span>
                            <h5 class="modal-title mb-1" id="modalEditPartialPaymentLabel">Edit Partial Payment</h5>
                            <p class="mb-0 billing-workflow-modal__subtitle">Sesuaikan nominal, sisa invoice, tanggal payment, dan status secara lebih jelas.</p>
                        </div>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body billing-workflow-modal__body">
                        <input type="hidden" name="id_billing" id="edit_partial_id_billing">

                        <div class="form-group">
                            <label>Nama Bowheer</label>
                            <input type="text" class="form-control" id="edit_partial_bowheer" readonly>
                        </div>

                        <div class="form-group">
                            <label>No Invoice</label>
                            <input type="text" class="form-control" id="edit_partial_no_invoice" readonly>
                        </div>

                        <div class="form-group">
                            <label>Nilai Invoice</label>
                            <input type="text" class="form-control text-right" id="edit_partial_invoice_price" readonly>
                        </div>

                        <div class="form-group">
                            <label>Total Pembayaran</label>
                            <input type="text" class="form-control text-right" name="invoice_price_payment"
                                id="edit_partial_payment_price" autocomplete="off">
                        </div>

                        <div class="form-group">
                            <label>Sisa Invoice</label>
                            <input type="text" class="form-control text-right" id="edit_partial_remaining" readonly>
                        </div>

                        <div class="form-group">
                            <label>Tanggal Payment</label>
                            <input type="date" class="form-control" name="tgl_payment_invoice"
                                id="edit_partial_payment_date">
                        </div>

                        <div class="form-group">
                            <label>Status Payment</label>
                            <select class="form-control" name="status_invoice" id="edit_partial_status_invoice">
                                <option value="partial">Partial</option>
                                <option value="paid">Paid</option>
                                <option value="reject">Reject</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer billing-workflow-modal__footer">
                        <button type="button" class="btn budget-btn budget-btn--ghost" data-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn budget-btn budget-btn--primary">Update Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-detail-partial-payment" tabindex="-1" role="dialog"
        aria-labelledby="modalDetailPartialPaymentLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content billing-workflow-modal">
                <div class="modal-header billing-workflow-modal__header">
                    <div>
                        <span class="billing-workflow-modal__eyebrow">Partial Detail</span>
                        <h5 class="modal-title mb-1" id="modalDetailPartialPaymentLabel">Detail Partial Payment</h5>
                        <p class="mb-0 billing-workflow-modal__subtitle">Lihat snapshot nilai invoice, pembayaran, dan sisa outstanding dengan lebih cepat.</p>
                    </div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body billing-workflow-modal__body">
                    <div class="form-group">
                        <label>Nama Bowheer</label>
                        <input type="text" class="form-control" id="detail_partial_bowheer" readonly>
                    </div>
                    <div class="form-group">
                        <label>No Invoice</label>
                        <input type="text" class="form-control" id="detail_partial_no_invoice" readonly>
                    </div>
                    <div class="form-group">
                        <label>Invoice Nett</label>
                        <input type="text" class="form-control text-right" id="detail_partial_invoice_nett" readonly>
                    </div>
                    <div class="form-group">
                        <label>Payment</label>
                        <input type="text" class="form-control text-right" id="detail_partial_payment" readonly>
                    </div>
                    <div class="form-group">
                        <label>Sisa</label>
                        <input type="text" class="form-control text-right" id="detail_partial_remaining" readonly>
                    </div>
                </div>
                <div class="modal-footer billing-workflow-modal__footer">
                    <button type="button" class="btn budget-btn budget-btn--ghost" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-edit-billing" tabindex="-1" role="dialog"
        aria-labelledby="modalEditBillingLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content billing-workflow-modal">
                <form id="formEditBillingInvoice">
                    <div class="modal-header billing-workflow-modal__header">
                        <div>
                            <span class="billing-workflow-modal__eyebrow">Invoice Editor</span>
                            <h5 class="modal-title mb-1" id="modalEditBillingLabel">Edit Invoice</h5>
                            <p class="mb-0 billing-workflow-modal__subtitle">Perbarui informasi invoice, payment, regional, dan deskripsi dalam form yang lebih modern.</p>
                        </div>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body billing-workflow-modal__body">
                        <input type="hidden" name="id_billing" id="edit_billing_id">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Bowheer</label>
                                    <select class="form-control" name="id_bowheer" id="edit_billing_bowheer"></select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>No Invoice</label>
                                    <input type="text" class="form-control" name="no_invoice" id="edit_billing_no_invoice">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Tgl Create Invoice</label>
                                    <input type="date" class="form-control" name="tgl_create_invoice" id="edit_billing_tgl_create">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Tgl Submit Invoice</label>
                                    <input type="date" class="form-control" name="tgl_submit_invoice" id="edit_billing_tgl_submit">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Tgl Payment</label>
                                    <input type="date" class="form-control" name="tgl_payment_invoice" id="edit_billing_tgl_payment">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>No PO</label>
                                    <input type="text" class="form-control" name="po_number" id="edit_billing_po_number">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tgl PO</label>
                                    <input type="date" class="form-control" name="po_tgl" id="edit_billing_po_tgl">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Invoice Est</label>
                                    <input type="text" class="form-control text-right edit-billing-amount" name="invoice_price_est" id="edit_billing_invoice_est" autocomplete="off">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Invoice Nett</label>
                                    <input type="text" class="form-control text-right edit-billing-amount" name="invoice_price_nett" id="edit_billing_invoice_nett" autocomplete="off">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Payment Price</label>
                                    <input type="text" class="form-control text-right edit-billing-amount" name="invoice_price_payment" id="edit_billing_invoice_payment" autocomplete="off">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Status Invoice</label>
                                    <select class="form-control" name="status_invoice" id="edit_billing_status_invoice">
                                        <option value="open">Open</option>
                                        <option value="partial">Partial</option>
                                        <option value="paid">Paid</option>
                                        <option value="reject">Reject</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Regional Payment</label>
                                    <input type="text" class="form-control" name="regional_payment" id="edit_billing_regional">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Area Payment</label>
                                    <input type="text" class="form-control" name="area_payment" id="edit_billing_area">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Deskripsi Payment</label>
                                    <textarea class="form-control" name="deskripsi_payment" id="edit_billing_deskripsi" rows="3"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer billing-workflow-modal__footer">
                        <button type="button" class="btn budget-btn budget-btn--ghost" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn budget-btn budget-btn--primary">Update Invoice</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- /.content-wrapper -->

<?php $this->session->set_flashdata('status', 'kosong'); ?>

<!-- Control Sidebar -->
<aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
</aside>

<script>
    let activeStatus = 'open';
    let latestBillingRows = {};
    const bowheerOptions = <?= json_encode($bowheer_options) ?>;

    $(document).on('click', '.tab-status-invoice', function () {
        $('.tab-status-invoice').removeClass('active');
        $(this).addClass('active');

        activeStatus = $(this).data('status');

        $('#btnFilterDataProject').trigger('click');
    });
</script>

<script>

    $(function () {
        //Initialize Select2 Elements
        $('.select2').select2()
        $('.select2bs4').select2({
            theme: 'bootstrap4'
        })

        // notifikasi allert sukses atau tidak
        <?php if ($status == 'sukses_tambah') { ?>
            swal("Success!", "Berhasil Ditambah!", "success");
        <?php } else if ($status == 'sukses_hapus') { ?>
                swal("Success!", "Berhasil Dihapus!", "success");
        <?php } else if ($status == 'sukses_edit') { ?>
                    swal("Success!", "Berhasil Edit Data!", "success");
        <?php } else if ($status == 'gagal_tambah') { ?>
                        swal("Gagal!", "Gagal Menambah Data!", "warning");
        <?php } else if ($status == 'gagal_edit') { ?>
                            swal("Gagal!", "Gagal Mengedit Data!", "warning");
        <?php } else if ($status == 'gagal_hapus') { ?>
                                swal("Gagal!", "Gagal Menghapus Data!", "warning");
        <?php } else { ?>
        <?php } ?>
    })

    $(document).ready(function () {
        $('#tabel_list_open_invoice').DataTable({
            paging: true,
            pageLength: 10,
            info: true,
            searching: true,
            lengthChange: true,
            autoWidth: false,     // aktifkan scroll horizontal otomatis
            responsive: false,   // matikan agar kolom tetap sejajar
            ordering: true,
            initComplete: function () {
                // pastikan wrapper scroll ikut lebar layar
                $('.dataTables_scrollHead, .dataTables_scrollBody')
                    .css('width', '100%');
            }
        });
        $('#tabel_targetpriority_bowheer').DataTable({
            paging: true,
            pageLength: 10,
            info: true,
            searching: true,
            lengthChange: true,
            autoWidth: false,     // aktifkan scroll horizontal otomatis
            responsive: false,   // matikan agar kolom tetap sejajar
            ordering: true,
            initComplete: function () {
                // pastikan wrapper scroll ikut lebar layar
                $('.dataTables_scrollHead, .dataTables_scrollBody')
                    .css('width', '100%');
            }
        });
    });


    $(document).ready(function () {
        $.fn.dataTable.ext.errMode = 'none';

        const table = $('#tabel_targetpriority_bowheer').DataTable({
            footerCallback: function () {
                updateTotal();
            },
            columnDefs: [
                { orderable: false, targets: 0 } // Kolom No tidak bisa di-sort manual
            ],
            order: [[1, 'asc']]
        });

        // Tambah nomor otomatis di kolom pertama
        table.on('order.dt search.dt', function () {
            table.column(0, { search: 'applied', order: 'applied' }).nodes().each(function (cell, i) {
                cell.innerHTML = i + 1;
            });
        }).draw();

        function parseValue(val) {
            if (!val) return 0;

            return parseFloat(
                val.toString()
                    .replace(/\./g, '')   // hapus titik ribuan
                    .replace(',', '.')    // jaga kalau ada desimal
            ) || 0;
        }

        // Fungsi utama untuk hitung total otomatis
        function updateTotal() {
            const data = table.rows({ search: 'applied' }).data();

            let totalP1 = 0;
            let totalP2 = 0;
            let totalP3 = 0;
            let totalBJT = 0;
            let totalAll = 0;

            data.each(function (row) {
                totalP1 += parseValue(row[2]);
                totalP2 += parseValue(row[3]);
                totalP3 += parseValue(row[4]);
                totalBJT += parseValue(row[5]);
                totalAll += parseValue(row[6]);
            });

            // isi footer sesuai kolom
            $(table.column(2).footer()).text(formatTitik(totalP1));
            $(table.column(3).footer()).text(formatTitik(totalP2));
            $(table.column(4).footer()).text(formatTitik(totalP3));
            $(table.column(5).footer()).text(formatTitik(totalBJT));
            $(table.column(6).footer()).text(formatTitik(totalAll));
        }

        // Jalankan ulang total setiap kali tabel berubah
        table.on('draw', function () {
            updateTotal();
        });

        // Hitung total pertama kali
        updateTotal();
    });

    $(document).ready(function () {
        $('.card[data-card-widget="collapsed"]').addClass('card-tools');
    });
</script>

<script>
    var allData = <?= json_encode($getAllData) ?>;

    $(document).ready(function () {
        // === PETA KOLOM DAN FILTER ===
        const colMap = {
            filter_pic: 'pic_user',
            filter_bowheer: 'nama_bowheer',
            filter_regional: 'regional_target',
            filter_city: 'area_target',
            filter_month: 'month_target',
            filter_week: 'week_target'
        };

        const currentSelections = {
            filter_pic: [],
            filter_bowheer: [],
            filter_regional: [],
            filter_city: [],
            filter_month: [],
            filter_week: []
        };

        $('.select2').select2({
            theme: 'bootstrap4',
            width: '100%',
            allowClear: true,
            placeholder: function () {
                return $(this).data('placeholder') || 'Pilih';
            }
        });

        // === Helper Filter Antar-Select ===
        function getFilteredDataByAllSelections(excludeId = null) {
            return allData.filter(item => {
                for (const selId in currentSelections) {
                    if (selId === excludeId) continue;
                    const selectedVals = currentSelections[selId] || [];
                    if (selectedVals.length === 0) continue;
                    const col = colMap[selId];
                    if (!selectedVals.map(String).includes(String(item[col]))) return false;
                }
                return true;
            });
        }

        function populateSelectFromFilteredData(selectId) {
            const $sel = $('#' + selectId);
            const col = colMap[selectId];
            const filtered = getFilteredDataByAllSelections(selectId);
            const unique = [...new Set(filtered.map(it => it[col]).filter(v => v !== null && v !== undefined && v !== ''))];
            const prevSelected = ($sel.val() || []).map(String);

            $sel.empty();
            unique.forEach(v => $sel.append(`<option value="${v}">${v}</option>`));

            const toSelect = unique.filter(u => prevSelected.includes(String(u)));
            $sel.val(toSelect.length ? toSelect : null);
            $sel.trigger('change.select2');
        }

        function onSelectChange(changedId) {
            currentSelections[changedId] = ($('#' + changedId).val() || []).map(String);
            for (const selId in colMap) populateSelectFromFilteredData(selId);
        }

        Object.keys(colMap).forEach(selId => {
            $('#' + selId).on('change', function () {
                onSelectChange(selId);
            });
        });

        Object.keys(colMap).forEach(populateSelectFromFilteredData);

        // === EVENT: FILTER DATA ===
        $('#btnFilterDataProject').on('click', function (e) {
            e.preventDefault();

            const filters = {
                bowheer: $('#filter_bowheer_up').val(),
                regional: $('#filter_regional_up').val(),
                city: $('#filter_city_up').val(),
                priority: $('#filter_priority_up').val(),
                status_invoice: activeStatus
            };

            $.ajax({
                url: '<?= base_url("BillingPayment/getFilteredBillingPaymentAjax") ?>',
                type: 'POST',
                data: filters,
                dataType: 'json',
                beforeSend: function () {
                    // $('#btnFilterDataProject i.loading').show();
                    showLoader();
                },
                success: function (response) {
                    if ($.fn.DataTable.isDataTable('#tabel_list_open_invoice')) {
                        $('#tabel_list_open_invoice').DataTable().clear().destroy();
                    }

                    // === HEADER ===
                    let theadHtml = '<tr>';
                    response.columns.forEach(col => theadHtml += `<th>${col}</th>`);
                    theadHtml += '</tr>';
                    $('#tabel_list_open_invoice thead').html(theadHtml);

                    // === FOOTER ===
                    let tfootHtml = '<tr>';
                    response.columns.forEach((col, index) => {
                        if (['Price', 'Outstanding Balance'].includes(col)) {
                            tfootHtml += `<th id="footer_${col.replace(/\s+/g, '_')}">0</th>`;
                        } else if (index === 0) {
                            tfootHtml += `<th style="text-align:right">Total:</th>`;
                        } else {
                            tfootHtml += `<th></th>`;
                        }
                    });
                    tfootHtml += '</tr>';
                    $('#tabel_list_open_invoice tfoot').html(tfootHtml);

                    const filteredData = response.data || [];
                    const outstandingSummary = response.summary || {};
                    latestBillingRows = {};

                    filteredData.forEach(row => {
                        latestBillingRows[row.id_billing] = row;
                    });

                    if (activeStatus === 'open') {
                        animateValue('dashboardTotalBilling', 0, parseFloat(outstandingSummary.total_all || 0), 600, true);
                        animateValue('dashboardBillingP1', 0, parseFloat(outstandingSummary.total_p1 || 0), 600, true);
                        animateValue('dashboardBillingP2', 0, parseFloat(outstandingSummary.total_p2 || 0), 600, true);
                        animateValue('dashboardBillingP3', 0, parseFloat(outstandingSummary.total_p3 || 0), 600, true);
                        animateValue('dashboardBillingBJT', 0, parseFloat(outstandingSummary.total_bjt || 0), 600, true);
                    }

                    // === BODY ===
                    if (!filteredData || filteredData.length === 0) {
                        $('#tabel_list_open_invoice tbody').html(
                            `<tr><td colspan="${response.columns.length}" class="text-center">No data available</td></tr>`
                        );
                    } else {
                        let tbodyHtml = '';
                        filteredData.forEach((row, i) => {
                            tbodyHtml += `<tr>`;
                            response.columns.forEach(col => {
                                switch (col) {
                                    case 'No':
                                        tbodyHtml += `<td>${i + 1}</td>`;
                                        break;
                                    case 'Bowheer':
                                        tbodyHtml += `<td>${row.nama_bowheer || '-'}</td>`;
                                        break;
                                    case 'Invoice':
                                        tbodyHtml += `<td>${row.no_invoice || '-'}</td>`;
                                        break;
                                    case 'Price':
                                    case 'Outstanding Balance':
                                        const displayPrice = activeStatus === 'partial'
                                            ? getOutstandingAmount(row)
                                            : parseAmount(row.invoice_price_nett || 0);
                                        tbodyHtml += `<td style="text-align: right;">${formatTitik(displayPrice, 2)}</td>`;
                                        break;
                                    case 'Regional':
                                        tbodyHtml += `<td>${row.regional_payment || '-'}</td>`;
                                        break;
                                    case 'Area':
                                        tbodyHtml += `<td>${row.area_payment || '-'}</td>`;
                                        break;
                                    case 'Date Invoice':
                                        tbodyHtml += `<td>${row.tgl_create_invoice || '-'}</td>`;
                                        break;
                                    case 'Date Submit':
                                        tbodyHtml += `<td>${row.tgl_submit_invoice || '-'}</td>`;
                                        break;
                                    case 'Due Date':
                                        tbodyHtml += `<td>${row.tgl_jatuh_tempo || '-'}</td>`;
                                        break;
                                    case 'Aging Today':
                                        tbodyHtml += `<td>${row.umur_invoice || '-'}</td>`;
                                        break;
                                    case 'Aging Due Date':
                                        tbodyHtml += `<td>${row.umur_due_date || '-'}</td>`;
                                        break;
                                    case 'Priority':
                                        tbodyHtml += `<td>${row.priority || '-'}</td>`;
                                        break;
                                    case 'PO Number':
                                        tbodyHtml += `<td>${row.po_number || '-'}</td>`;
                                        break;
                                    case 'Status Invoice':
                                        tbodyHtml += `<td>${row.status_invoice || '-'}</td>`;
                                        break;
                                    case 'Action':
                                        tbodyHtml += buildActionButtons(row);
                                        break;
                                    default:
                                        tbodyHtml += `<td>-</td>`;
                                }
                            });
                            tbodyHtml += `</tr>`;
                        });
                        $('#tabel_list_open_invoice tbody').html(tbodyHtml);
                    }

                    // === DATATABLE ===

                    let table = null;

                    if (response.data && response.data.length > 0) {
                        table = $('#tabel_list_open_invoice').DataTable({
                            responsive: true,
                            autoWidth: false,
                            pageLength: 10,
                            ordering: true,
                            footerCallback: function (row, data, start, end, display) {
                                const api = this.api();

                                const colIndex = name => response.columns.indexOf(name);

                                const parseValue = val => {
                                    if (!val) return 0;

                                    // kalau sudah number
                                    if (typeof val === 'number') return val;

                                    // kalau string format indonesia
                                    if (typeof val === 'string') {
                                        return parseFloat(
                                            val
                                                .replace(/\./g, '')   // hapus ribuan
                                                .replace(',', '.')    // ubah desimal
                                        ) || 0;
                                    }

                                    return 0;
                                };

                                let totalAll = 0;
                                let totalP1 = 0;
                                let totalP2 = 0;
                                let totalP3 = 0;

                                const priceIdx = colIndex('Outstanding Balance') > -1
                                    ? colIndex('Outstanding Balance')
                                    : colIndex('Price');
                                const priorityIdx = colIndex('Priority');

                                api.rows({ search: 'applied' }).every(function () {
                                    const row = this.data();

                                    const price = parseValue(row[priceIdx]);
                                    const priority = row[priorityIdx];

                                    totalAll += price;

                                    if (priority === 'P1') totalP1 += price;
                                    else if (priority === 'P2') totalP2 += price;
                                    else if (priority === 'P3') totalP3 += price;
                                });

                                if (priceIdx > -1) {
                                    $(api.column(priceIdx).footer()).html(formatTitik(totalAll, 2));
                                }

                                highlightCells();
                            }
                        });
                    }

                    // === Nomor Otomatis ===
                    if (table) {
                        table.on('order.dt search.dt', function () {
                            table.column(0, { search: 'applied', order: 'applied' })
                                .nodes().each(function (cell, i) {
                                    cell.innerHTML = i + 1;
                                });
                        }).draw();
                    }
                },
                error: function (xhr) {
                    console.error('Error:', xhr.responseText);
                },
                complete: function () {
                    // $('#btnFilterDataProject i.loading').hide();
                    hideLoader();
                }
            });
        });

        // === AUTO CLICK SAAT AWAL ===
        setTimeout(() => {
            $('#btnFilterDataProject').trigger('click');
        }, 500);

        // === RESET FILTER ===
        $('#reset_filter').on('click', function () {
            window.location.reload();
        });
    });

    function parseNumber(value) {
        if (!value) return 0;

        if (typeof value === 'number') return value;

        // hapus SEMUA selain angka
        value = value.toString().replace(/[^\d]/g, '');

        return parseInt(value) || 0;
    }

    function parseAmount(value) {
        if (value === null || value === undefined || value === '') return 0;

        if (typeof value === 'number') return value;

        let normalized = value.toString().trim().replace(/\s/g, '');

        const lastDot = normalized.lastIndexOf('.');
        const lastComma = normalized.lastIndexOf(',');

        if (lastDot > -1 && lastComma > -1) {
            if (lastDot > lastComma) {
                normalized = normalized.replace(/,/g, '');
            } else {
                normalized = normalized.replace(/\./g, '').replace(',', '.');
            }
        } else if (lastComma > -1) {
            normalized = normalized.replace(/\./g, '').replace(',', '.');
        } else {
            const parts = normalized.split('.');
            if (parts.length > 2) {
                const decimalPart = parts.pop();
                normalized = `${parts.join('')}.${decimalPart}`;
            }
        }

        normalized = normalized.replace(/[^\d.-]/g, '');
        return parseFloat(normalized) || 0;
    }

    function formatTitik(num, decimalPlaces = 0) {
        return Number(num || 0).toLocaleString('id-ID', {
            minimumFractionDigits: decimalPlaces,
            maximumFractionDigits: decimalPlaces
        });
    }

    function formatDateForInput(value) {
        if (!value) {
            return '';
        }

        const stringValue = value.toString().trim();
        const match = stringValue.match(/^(\d{4}-\d{2}-\d{2})/);

        if (match) {
            return match[1];
        }

        const parsedDate = new Date(stringValue);
        if (Number.isNaN(parsedDate.getTime())) {
            return '';
        }

        const month = String(parsedDate.getMonth() + 1).padStart(2, '0');
        const day = String(parsedDate.getDate()).padStart(2, '0');
        return `${parsedDate.getFullYear()}-${month}-${day}`;
    }

    function formatInputRupiah(value) {
        const num = parseNumber(value);
        if (!num) return '';
        return formatTitik(num, 0);
    }

    function getOutstandingAmount(row) {
        const nett = parseAmount(row && row.invoice_price_nett ? row.invoice_price_nett : 0);
        const payment = parseAmount(row && row.invoice_price_payment ? row.invoice_price_payment : 0);
        return Math.max(nett - payment, 0);
    }

    function formatRupiah(value) {
        let num = parseFloat(value.toString().replace(/[^\d]/g, '')) || 0;
        return 'Rp. ' + formatTitik(num);
    }

    // === ANIMASI ANGKA DASHBOARD ===
    function animateValue(id, start, end, duration, isRupiah = false) {
        const element = document.getElementById(id);
        if (!element) return;
        let startTimestamp = null;
        const step = (timestamp) => {
            if (!startTimestamp) startTimestamp = timestamp;
            const progress = Math.min((timestamp - startTimestamp) / duration, 1);
            const currentValue = Math.floor(progress * (end - start) + start);
            element.innerText = isRupiah
                ? formatRupiah(currentValue)
                : formatTitik(currentValue) + (id === 'dashboardPersentaseInvoice' ? '%' : '');
            if (progress < 1) window.requestAnimationFrame(step);
        };
        window.requestAnimationFrame(step);
    }


    function highlightCells() {
        $('#tabel_list_open_invoice tbody tr').each(function () {
            const cell = $(this).find('td:contains("%")').last();
            if (!cell.length) return;

            let persenText = cell.text().trim();
            persenText = persenText.replace(/<[^>]+>/g, '').replace(/[\u2191\u2193✅❌]/g, '');

            const persen = parseFloat(persenText.replace('%', '').replace(',', '.')) || 0;

            let icon = '';
            if (persen < 100) {
                icon = ' <i class="fas fa-arrow-down text-danger"></i>';
                cell.addClass('cell-red');
            } else if (persen === 100) {
                icon = ' <i class="fas fa-check-circle text-success"></i>';
                cell.addClass('cell-green-light');
            } else {
                icon = ' <i class="fas fa-arrow-up text-success"></i>';
                cell.addClass('cell-green-dark');
            }

            cell.html(`${persenText}${icon}`);
        });
    }

    $(document).ready(function () {
        function loadTargetInvoice() {
            const bowheer = $('#addfilter_bowheer').val();
            const area = $('#addfilter_area').val();
            const month = $('#addfilter_month').val();
            const week = $('#addfilter_week').val();

            // Pastikan semua filter sudah dipilih
            if (bowheer && area && month && week) {
                $.ajax({
                    url: "<?= base_url('BillingPayment/get_target_invoice') ?>",
                    type: "POST",
                    dataType: "json",
                    data: {
                        bowheer: $('#addfilter_bowheer').val(),
                        area: $('#addfilter_area').val(),
                        month: $('#addfilter_month').val(),
                        week: $('#addfilter_week').val()
                    },
                    success: function (res) {
                        console.log(res);
                        let qtyTarget = res.qty_target ? parseFloat(res.qty_target) : 0;
                        let qtyAchiev = res.qty_achiev_target ? parseFloat(res.qty_achiev_target) : 0;

                        // Format angka ke Rupiah dengan titik
                        const formatRupiah = (val) => "" + (parseFloat(val) || 0).toLocaleString('id-ID');

                        // Masukkan nilai ke field
                        $('[name="target_invoice"]').val(formatRupiah(qtyTarget));
                        $('[name="achiev_invoice"]').val(formatRupiah(qtyAchiev));

                        // === KONDISI: Jika qty_achiev_target tidak null dan tidak 0 ===
                        if (qtyAchiev && qtyAchiev !== 0) {
                            // Disable input achiev_invoice
                            $('[name="achiev_invoice"]').prop('disabled', true);

                            // Tampilkan field tambahan_invoice & total_invoice
                            $('[name="tambahan_invoice"]').closest('.form-group').slideDown();
                            $('[name="total_invoice"]').closest('.form-group').slideDown();

                            // Tampilkan nilai awal total_invoice = qtyAchiev + 0
                            $('[name="total_invoice"]').val(formatRupiah(qtyAchiev));

                            // Format input tambahan_invoice hanya angka & auto Rupiah

                        } else {
                            // Jika qty_achiev_target kosong, reset ke mode input baru
                            $('[name="achiev_invoice"]').prop('disabled', false);
                            $('[name="tambahan_invoice"]').closest('.form-group').slideUp();
                            $('[name="total_invoice"]').closest('.form-group').slideUp();
                            $('[name="tambahan_invoice"]').val('');
                            $('[name="total_invoice"]').val('');
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error("Error:", error);
                        console.log(xhr.responseText);
                    }
                });
            }
        }

        // Jalankan ketika salah satu dropdown berubah
        $('#addfilter_bowheer, #addfilter_area, #addfilter_month, #addfilter_week').on('change', function () {
            loadTargetInvoice();
        });
    });

    $(document).ready(function () {
        function getTodayDate() {
            const d = new Date();
            const month = String(d.getMonth() + 1).padStart(2, '0');
            const day = String(d.getDate()).padStart(2, '0');
            return `${d.getFullYear()}-${month}-${day}`;
        }

        function getBowheerSelectOptions(selectedId = '') {
            let options = '<option value="">Pilih Bowheer</option>';
            bowheerOptions.forEach(item => {
                const isSelected = String(item.id) === String(selectedId) ? 'selected' : '';
                options += `<option value="${item.id}" ${isSelected}>${item.text}</option>`;
            });
            return options;
        }

        function refreshManualInvoiceNumbers() {
            $('#table_manual_invoice tbody tr.manual-invoice-row').each(function (index) {
                $(this).find('.manual-row-no').text(index + 1);
            });

            if ($('#table_manual_invoice tbody tr.manual-invoice-row').length === 0) {
                $('#table_manual_invoice tbody').html(`
                    <tr id="emptyManualInvoiceRow">
                        <td colspan="13" class="text-center text-muted">Belum ada invoice ditambahkan</td>
                    </tr>
                `);
            }
        }

        function updateManualInvoiceTotals() {
            let totalEst = 0;
            let totalNett = 0;

            $('#table_manual_invoice tbody tr.manual-invoice-row').each(function () {
                totalEst += parseAmount($(this).find('.manual-invoice-est').val());
                totalNett += parseAmount($(this).find('.manual-invoice-nett').val());
            });

            $('#manualInvoiceEstTotal').text(formatTitik(totalEst, 2));
            $('#manualInvoiceNettTotal').text(formatTitik(totalNett, 2));
        }

        window.buildActionButtons = function (row) {
            const id = row.id_billing;
            const buttons = [];

            if (activeStatus === 'open') {
                buttons.push(`<button type="button" class="btn btn-warning edit-billing-item" data-id="${id}" title="Edit Invoice"><i class="fa fa-edit"></i></button>`);
                buttons.push(`<button type="button" class="btn btn-success payment-item" data-id="${id}" title="Add Payment"><i class="fas fa-dollar-sign"></i></button>`);
                buttons.push(`<button type="button" class="btn btn-danger hapus-item" data-id="${id}" title="Hapus Invoice"><i class="fa fa-trash"></i></button>`);
            } else if (activeStatus === 'partial') {
                buttons.push(`<button type="button" class="btn btn-info detail-partial-item" data-id="${id}" title="Detail Partial"><i class="fa fa-list-alt"></i></button>`);
                buttons.push(`<button type="button" class="btn btn-warning edit-partial-item" data-id="${id}" title="Edit Partial"><i class="fa fa-edit"></i></button>`);
                buttons.push(`<button type="button" class="btn btn-danger hapus-item" data-id="${id}" title="Hapus Invoice"><i class="fa fa-trash"></i></button>`);
            } else if (activeStatus === 'all') {
                buttons.push(`<button type="button" class="btn btn-warning edit-billing-item" data-id="${id}" title="Edit Invoice"><i class="fa fa-edit"></i></button>`);
                buttons.push(`<button type="button" class="btn btn-success payment-item" data-id="${id}" title="Payment"><i class="fas fa-dollar-sign"></i></button>`);
                buttons.push(`<button type="button" class="btn btn-danger hapus-item" data-id="${id}" title="Hapus Invoice"><i class="fa fa-trash"></i></button>`);
            } else if (activeStatus === 'reject') {
                buttons.push(`<button type="button" class="btn btn-warning edit-billing-item" data-id="${id}" title="Edit Invoice"><i class="fa fa-edit"></i></button>`);
                buttons.push(`<button type="button" class="btn btn-danger hapus-item" data-id="${id}" title="Hapus Invoice"><i class="fa fa-trash"></i></button>`);
            } else {
                buttons.push(`<button type="button" class="btn btn-warning edit-billing-item" data-id="${id}" title="Edit Invoice"><i class="fa fa-edit"></i></button>`);
                buttons.push(`<button type="button" class="btn btn-danger hapus-item" data-id="${id}" title="Hapus Invoice"><i class="fa fa-trash"></i></button>`);
            }

            return `<td class="text-center action-buttons"><div class="btn-group btn-group-sm" role="group">${buttons.join('')}</div></td>`;
        };

        window.reloadBillingPage = function () {
            window.location.reload();
        };

        window.populateEditBillingBowheer = function (selectedId = '') {
            const $select = $('#edit_billing_bowheer');
            $select.empty().append('<option value="">Pilih Bowheer</option>');

            bowheerOptions.forEach(item => {
                const selected = String(item.id) === String(selectedId) ? 'selected' : '';
                $select.append(`<option value="${item.id}" ${selected}>${item.text}</option>`);
            });
        };

        window.openBillingEditModal = function (row) {
            if (!row) {
                return;
            }

            populateEditBillingBowheer(row.id_bowheer || '');
            $('#edit_billing_id').val(row.id_billing || '');
            $('#edit_billing_no_invoice').val(row.no_invoice || '');
            $('#edit_billing_tgl_create').val(formatDateForInput(row.tgl_create_invoice));
            $('#edit_billing_tgl_submit').val(formatDateForInput(row.tgl_submit_invoice));
            $('#edit_billing_tgl_payment').val(formatDateForInput(row.tgl_payment_invoice));
            $('#edit_billing_po_number').val(row.po_number || '');
            $('#edit_billing_po_tgl').val(formatDateForInput(row.po_tgl));
            $('#edit_billing_invoice_est').val(row.invoice_price_est ? formatTitik(parseAmount(row.invoice_price_est), 2) : '');
            $('#edit_billing_invoice_nett').val(row.invoice_price_nett ? formatTitik(parseAmount(row.invoice_price_nett), 2) : '');
            $('#edit_billing_invoice_payment').val(row.invoice_price_payment ? formatTitik(parseAmount(row.invoice_price_payment), 2) : '');
            $('#edit_billing_status_invoice').val(row.status_invoice || 'open');
            $('#edit_billing_regional').val(row.regional_payment || '');
            $('#edit_billing_area').val(row.area_payment || '');
            $('#edit_billing_deskripsi').val(row.deskripsi_payment || '');

            syncEditBillingStatus();
            $('#modal-edit-billing').modal('show');
        };

        window.syncEditBillingStatus = function () {
            const status = $('#edit_billing_status_invoice').val();
            const invoiceNett = parseAmount($('#edit_billing_invoice_nett').val());
            let payment = parseAmount($('#edit_billing_invoice_payment').val());

            if (status === 'open') {
                $('#edit_billing_invoice_payment').val('');
                $('#edit_billing_tgl_payment').val('');
                return;
            }

            if (status === 'reject') {
                $('#edit_billing_invoice_payment').val('');
                $('#edit_billing_tgl_payment').val('');
                return;
            }

            if (status === 'paid') {
                payment = invoiceNett;
                $('#edit_billing_invoice_payment').val(payment ? formatTitik(payment, 2) : '');
                if (!$('#edit_billing_tgl_payment').val()) {
                    $('#edit_billing_tgl_payment').val(getToday());
                }
                return;
            }

            if (status === 'partial') {
                if (payment >= invoiceNett && invoiceNett > 0) {
                    payment = '';
                }

                $('#edit_billing_invoice_payment').val(payment ? formatTitik(payment, 2) : '');
                if (!$('#edit_billing_tgl_payment').val()) {
                    $('#edit_billing_tgl_payment').val(getToday());
                }
            }
        };

        window.openPartialEditModal = function (row) {
            if (!row) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Data tidak ditemukan',
                    text: 'Data partial payment tidak tersedia.'
                });
                return;
            }

            $('#edit_partial_id_billing').val(row.id_billing);
            $('#edit_partial_bowheer').val(row.nama_bowheer || '-');
            $('#edit_partial_no_invoice').val(row.no_invoice || '-');
            $('#edit_partial_invoice_price').val(formatTitik(parseAmount(row.invoice_price_nett || 0), 2));
            $('#edit_partial_payment_price').val(formatTitik(parseAmount(row.invoice_price_payment || 0), 2));
            $('#edit_partial_payment_date').val(formatDateForInput(row.tgl_payment_invoice) || getToday());
            $('#edit_partial_status_invoice').val(row.status_invoice || 'partial');

            updateEditPartialSummary();
            $('#modal-edit-partial-payment').modal('show');
        };

        window.openPartialDetailModal = function (row) {
            if (!row) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Data tidak ditemukan',
                    text: 'Data partial payment tidak tersedia.'
                });
                return;
            }

            const nett = parseAmount(row.invoice_price_nett || 0);
            const payment = parseAmount(row.invoice_price_payment || 0);
            const remaining = getOutstandingAmount(row);

            $('#detail_partial_bowheer').val(row.nama_bowheer || '-');
            $('#detail_partial_no_invoice').val(row.no_invoice || '-');
            $('#detail_partial_invoice_nett').val(formatTitik(nett, 2));
            $('#detail_partial_payment').val(formatTitik(payment, 2));
            $('#detail_partial_remaining').val(formatTitik(remaining, 2));
            $('#modal-detail-partial-payment').modal('show');
        };

        function addManualInvoiceRow(data = {}) {
            $('#emptyManualInvoiceRow').remove();

            const html = `
                <tr class="manual-invoice-row">
                    <td class="text-center manual-row-no"></td>
                    <td>
                        <select class="form-control manual-bowheer-select" name="id_bowheer[]">
                            ${getBowheerSelectOptions(data.id_bowheer || '')}
                        </select>
                    </td>
                    <td><input type="text" class="form-control" name="no_invoice[]" value="${data.no_invoice || ''}"></td>
                    <td><input type="date" class="form-control" name="tgl_create_invoice[]" value="${data.tgl_create_invoice || getTodayDate()}"></td>
                    <td><input type="date" class="form-control" name="tgl_submit_invoice[]" value="${data.tgl_submit_invoice || getTodayDate()}"></td>
                    <td><input type="text" class="form-control" name="po_number[]" value="${data.po_number || ''}"></td>
                    <td><input type="date" class="form-control" name="po_tgl[]" value="${data.po_tgl || ''}"></td>
                    <td><input type="text" class="form-control text-right manual-invoice-amount manual-invoice-est" name="invoice_price_est[]" value="${data.invoice_price_est ? formatTitik(parseAmount(data.invoice_price_est), 2) : ''}"></td>
                    <td><input type="text" class="form-control text-right manual-invoice-amount manual-invoice-nett" name="invoice_price_nett[]" value="${data.invoice_price_nett ? formatTitik(parseAmount(data.invoice_price_nett), 2) : ''}"></td>
                    <td><input type="text" class="form-control" name="regional_payment[]" value="${data.regional_payment || ''}"></td>
                    <td><input type="text" class="form-control" name="area_payment[]" value="${data.area_payment || ''}"></td>
                    <td><textarea class="form-control" name="deskripsi_payment[]" rows="2">${data.deskripsi_payment || ''}</textarea></td>
                    <td class="text-center">
                        <button type="button" class="btn btn-danger btn-sm btn-remove-manual-invoice">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;

            $('#table_manual_invoice tbody').append(html);
            refreshManualInvoiceNumbers();
            updateManualInvoiceTotals();
        }

        function resetManualInvoiceForm() {
            $('#formManualInvoiceBatch')[0].reset();
            $('#table_manual_invoice tbody').html(`
                <tr id="emptyManualInvoiceRow">
                    <td colspan="13" class="text-center text-muted">Belum ada invoice ditambahkan</td>
                </tr>
            `);
            updateManualInvoiceTotals();
        }

        function resetImportPreview() {
            $('#invoiceImportRowsJson').val('');
            $('#invoiceImportSummary').text('Belum ada file dipreview');
            $('#btnSaveImportedInvoice').prop('disabled', true);
            $('#table_invoice_import_preview tbody').html(`
                <tr id="emptyImportPreviewRow">
                    <td colspan="10" class="text-center text-muted">Preview import akan tampil di sini</td>
                </tr>
            `);
        }

        function renderImportPreview(rows, validRows) {
            if (!rows || rows.length === 0) {
                resetImportPreview();
                return;
            }

            let html = '';
            rows.forEach(row => {
                const badgeClass = row.status === 'valid' ? 'badge-success' : 'badge-danger';
                html += `
                    <tr>
                        <td>${row.row_number}</td>
                        <td>${row.id_bowheer || '-'}</td>
                        <td>${row.no_invoice || '-'}</td>
                        <td>${row.tgl_submit_invoice || '-'}</td>
                        <td>${row.po_number || '-'}</td>
                        <td class="text-right">${formatTitik(parseAmount(row.invoice_price_nett || 0), 2)}</td>
                        <td>${row.regional_payment || '-'}</td>
                        <td>${row.area_payment || '-'}</td>
                        <td class="text-center"><span class="badge ${badgeClass}">${row.status}</span></td>
                        <td>${row.message || '-'}</td>
                    </tr>
                `;
            });

            $('#table_invoice_import_preview tbody').html(html);
            $('#invoiceImportRowsJson').val(JSON.stringify(validRows || []));
            $('#invoiceImportSummary').text(`${validRows.length} data valid dari ${rows.length} baris`);
            $('#btnSaveImportedInvoice').prop('disabled', !validRows.length);
        }

        $('#btnAddManualInvoiceRow').on('click', function () {
            addManualInvoiceRow();
        });

        $(document).on('click', '.btn-remove-manual-invoice', function () {
            $(this).closest('tr').remove();
            refreshManualInvoiceNumbers();
            updateManualInvoiceTotals();
        });

        $(document).on('focus', '.manual-invoice-amount, .edit-billing-amount', function () {
            const value = parseAmount($(this).val());
            $(this).val(value ? value : '');
        });

        $(document).on('input', '.manual-invoice-amount', function () {
            updateManualInvoiceTotals();
        });

        $(document).on('blur', '.manual-invoice-amount, .edit-billing-amount', function () {
            const value = parseAmount($(this).val());
            $(this).val(value ? formatTitik(value, 2) : '');
            updateManualInvoiceTotals();
        });

        $('#edit_billing_status_invoice').on('change', function () {
            syncEditBillingStatus();
        });

        $('#edit_billing_invoice_nett, #edit_billing_invoice_payment').on('blur', function () {
            syncEditBillingStatus();
        });

        $('#modal-download-report').on('shown.bs.modal', function () {
            if ($('#table_manual_invoice tbody tr.manual-invoice-row').length === 0) {
                addManualInvoiceRow();
            }
        });

        $('#modal-download-report').on('hidden.bs.modal', function () {
            resetManualInvoiceForm();
            resetImportPreview();
            $('#invoiceExcelFile').val('');
            $('#tab-manual-invoice-link').tab('show');
        });

        $('#formManualInvoiceBatch').on('submit', function (e) {
            e.preventDefault();

            const rowCount = $('#table_manual_invoice tbody tr.manual-invoice-row').length;
            if (!rowCount) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Belum ada data',
                    text: 'Tambahkan minimal satu invoice.'
                });
                return;
            }

            $.ajax({
                url: '<?= base_url("BillingPayment/saveManualBatchInvoice") ?>',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                beforeSend: function () {
                    showLoader();
                },
                success: function (response) {
                    if (response.status) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: response.message
                        }).then(() => {
                            resetManualInvoiceForm();
                            $('#modal-download-report').modal('hide');
                            reloadBillingPage();
                        });
                    } else {
                        const errorText = response.errors && response.errors.length
                            ? response.errors.map(item => `Baris ${item.row}: ${item.message}`).join('\n')
                            : (response.message || 'Gagal menyimpan invoice manual');

                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: errorText
                        });
                    }
                },
                error: function () {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Terjadi kesalahan saat menyimpan invoice manual'
                    });
                },
                complete: function () {
                    hideLoader();
                }
            });
        });

        $('#invoiceDropzone').on('click', function () {
            $('#invoiceExcelFile').trigger('click');
        });

        $('#invoiceDropzone').on('dragover', function (e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).addClass('dragover');
        });

        $('#invoiceDropzone').on('dragleave', function (e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).removeClass('dragover');
        });

        $('#invoiceDropzone').on('drop', function (e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).removeClass('dragover');

            const files = e.originalEvent.dataTransfer.files;
            if (files && files.length) {
                $('#invoiceExcelFile')[0].files = files;
                $('#invoiceExcelFile').trigger('change');
            }
        });

        $('#invoiceExcelFile').on('change', function () {
            const file = this.files[0];
            if (!file) {
                return;
            }

            const formData = new FormData();
            formData.append('file_excel', file);

            $.ajax({
                url: '<?= base_url("BillingPayment/previewInvoiceImport") ?>',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                beforeSend: function () {
                    showLoader();
                    resetImportPreview();
                },
                success: function (response) {
                    if (response.status) {
                        renderImportPreview(response.rows || [], response.valid_rows || []);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal Preview',
                            text: response.message || 'File import tidak valid'
                        });
                    }
                },
                error: function () {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Preview',
                        text: 'Terjadi kesalahan saat membaca file import'
                    });
                },
                complete: function () {
                    hideLoader();
                }
            });
        });

        $('#btnSaveImportedInvoice').on('click', function () {
            const rowsJson = $('#invoiceImportRowsJson').val();

            if (!rowsJson) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Belum ada data',
                    text: 'Preview file Excel terlebih dahulu.'
                });
                return;
            }

            $.ajax({
                url: '<?= base_url("BillingPayment/saveImportedInvoices") ?>',
                type: 'POST',
                data: { rows_json: rowsJson },
                dataType: 'json',
                beforeSend: function () {
                    showLoader();
                },
                success: function (response) {
                    if (response.status) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Import Berhasil',
                            text: response.message
                        }).then(() => {
                            resetImportPreview();
                            $('#invoiceExcelFile').val('');
                            $('#modal-download-report').modal('hide');
                            reloadBillingPage();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Import Gagal',
                            text: response.message || 'Gagal menyimpan hasil import'
                        });
                    }
                },
                error: function () {
                    Swal.fire({
                        icon: 'error',
                        title: 'Import Gagal',
                        text: 'Terjadi kesalahan saat menyimpan hasil import'
                    });
                },
                complete: function () {
                    hideLoader();
                }
            });
        });

        $('#modal-download-billing-report').on('show.bs.modal', function () {
            $('#report_filter_bowheer').val($('#filter_bowheer_up').val()).trigger('change');
            $('#report_filter_regional').val($('#filter_regional_up').val()).trigger('change');
            $('#report_filter_city').val($('#filter_city_up').val()).trigger('change');
            $('#report_filter_priority').val($('#filter_priority_up').val()).trigger('change');
            $('#report_filter_status').val(activeStatus || 'open');
        });
    });

    $(document).ready(function () {
        function resetPaymentImportPreview() {
            $('#paymentImportRowsJson').val('');
            $('#paymentImportSummary').text('Belum ada file dipreview');
            $('#btnSaveImportedPayment').prop('disabled', true);
            $('#table_payment_import_preview tbody').html(`
                <tr id="emptyPaymentImportPreviewRow">
                    <td colspan="8" class="text-center text-muted">Preview import akan tampil di sini</td>
                </tr>
            `);
        }

        function renderPaymentImportPreview(rows, validRows) {
            if (!rows || rows.length === 0) {
                resetPaymentImportPreview();
                return;
            }

            let html = '';
            rows.forEach(row => {
                const badgeClass = row.status === 'sesuai' ? 'badge-success' : 'badge-danger';
                const invoicePrice = parseAmount(row.invoice_price_nett || 0);
                const paymentPrice = parseAmount(row.invoice_price_payment || 0);
                const selisih = invoicePrice - paymentPrice;
                html += `
                    <tr>
                        <td>${row.no_invoice || '-'}</td>
                        <td>${row.tgl_payment_invoice || '-'}</td>
                        <td class="text-right">${row.invoice_price_nett ? formatTitik(invoicePrice, 2) : '-'}</td>
                        <td class="text-right">${row.invoice_price_payment ? formatTitik(paymentPrice, 2) : '-'}</td>
                        <td class="text-right">${row.invoice_price_nett ? formatTitik(selisih, 2) : '-'}</td>
                        <td>${row.status_invoice || '-'}</td>
                        <td class="text-center"><span class="badge ${badgeClass}">${row.status}</span></td>
                        <td>${row.message || '-'}</td>
                    </tr>
                `;
            });

            $('#table_payment_import_preview tbody').html(html);
            $('#paymentImportRowsJson').val(JSON.stringify(validRows || []));
            $('#paymentImportSummary').text(`${validRows.length} data valid dari ${rows.length} baris`);
            $('#btnSaveImportedPayment').prop('disabled', !validRows.length);
        }

        $('#modal-payment').on('hidden.bs.modal', function () {
            resetBatchPaymentTable();
            resetPaymentImportPreview();
            $('#paymentExcelFile').val('');
            $('#tab-manual-payment-link').tab('show');
        });

        $('#formBatchPayment').on('submit', function (e) {
            e.preventDefault();

            const rowCount = $('#table_selected_invoice tbody tr.data-row').length;
            if (rowCount === 0) {
                alert('Pilih invoice terlebih dahulu');
                return;
            }

            let valid = true;

            $('#table_selected_invoice tbody tr.data-row').each(function () {
                const price = $(this).find('input[name="invoice_price_payment[]"]').val().trim();
                const date = $(this).find('input[name="tgl_payment_invoice[]"]').val().trim();
                const status = $(this).find('select[name="status_invoice[]"]').val();

                if (status === '') {
                    valid = false;
                    return false;
                }

                if (status === 'reject') {
                    return true;
                }

                if (price === '' || date === '') {
                    valid = false;
                    return false;
                }
            });

            if (!valid) {
                alert('Payment Price dan Date Payment wajib diisi semua, kecuali status reject');
                return;
            }

            const formData = $(this).serialize();

            $.ajax({
                url: '<?= base_url("BillingPayment/saveBatchPayment") ?>',
                type: 'POST',
                data: formData,
                dataType: 'json',
                beforeSend: function () {
                    showLoader();
                },
                success: function (response) {
                    if (response.status) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: response.message
                        }).then(() => {
                            resetBatchPaymentTable();
                            $('#modal-payment').modal('hide');
                            reloadBillingPage();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: response.message || 'Gagal menyimpan pembayaran'
                        });
                    }
                },
                error: function () {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Terjadi kesalahan saat menyimpan pembayaran'
                    });
                },
                complete: function () {
                    hideLoader();
                }
            });
        });
        $(document).on('click', '.btn-remove-row', function () {
            $(this).closest('tr').remove();
            reindexTableRows();
            updatePaymentSummary();
        });

        $(document).on('focus', '.payment-price', function () {
            const raw = parseAmount($(this).val());
            $(this).val(raw ? raw : '');
        });

        $(document).on('input', '.payment-price', function () {
            updatePaymentSummary();
        });

        $(document).on('blur', '.payment-price', function () {
            const raw = parseAmount($(this).val());
            $(this).val(raw ? formatTitik(raw, 2) : '');
            updatePaymentSummary();
        });

        $(document).on('change', '.payment-status', function () {
            const $row = $(this).closest('tr');
            const invoicePrice = parseAmount($row.find('.invoice-price-raw').val());
            const status = $(this).val();

            if (status === 'paid') {
                $row.find('.payment-price').val(formatTitik(invoicePrice, 2));
                $row.find('input[name="tgl_payment_invoice[]"]').val(getToday());
            } else if (status === 'reject') {
                $row.find('.payment-price').val('');
                $row.find('input[name="tgl_payment_invoice[]"]').val('');
            }

            updatePaymentSummary();
        });

        $('#select_invoice_payment').on('select2:select', function (e) {
            const item = e.params.data;
            addInvoiceRow(item);

            setTimeout(() => {
                $('#select_invoice_payment').val(null).trigger('change');
            }, 0);
        });

        $('#select_invoice_payment').on('select2:unselect', function (e) {
            const item = e.params.data;
            $('#row_invoice_' + item.id).remove();
            reindexTableRows();
        });

        $('#select_invoice_payment').select2({
            theme: 'bootstrap4',
            placeholder: 'Pilih nomor invoice',
            allowClear: true,
            width: '100%',
            ajax: {
                url: '<?= base_url("BillingPayment/getOpenInvoices") ?>',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term || ''
                    };
                },
                processResults: function (data) {
                    return {
                        results: data.results
                    };
                },
                cache: true
            }
        });

        $('#paymentDropzone').on('click', function () {
            $('#paymentExcelFile').trigger('click');
        });

        $('#paymentDropzone').on('dragover', function (e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).addClass('dragover');
        });

        $('#paymentDropzone').on('dragleave', function (e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).removeClass('dragover');
        });

        $('#paymentDropzone').on('drop', function (e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).removeClass('dragover');

            const files = e.originalEvent.dataTransfer.files;
            if (files && files.length) {
                $('#paymentExcelFile')[0].files = files;
                $('#paymentExcelFile').trigger('change');
            }
        });

        $('#paymentExcelFile').on('change', function () {
            const file = this.files[0];
            if (!file) {
                return;
            }

            const formData = new FormData();
            formData.append('file_excel', file);

            $.ajax({
                url: '<?= base_url("BillingPayment/previewPaymentImport") ?>',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                beforeSend: function () {
                    showLoader();
                    resetPaymentImportPreview();
                },
                success: function (response) {
                    if (response.status) {
                        renderPaymentImportPreview(response.rows || [], response.valid_rows || []);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal Preview',
                            text: response.message || 'File import tidak valid'
                        });
                    }
                },
                error: function () {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Preview',
                        text: 'Terjadi kesalahan saat membaca file import payment'
                    });
                },
                complete: function () {
                    hideLoader();
                }
            });
        });

        $('#btnSaveImportedPayment').on('click', function () {
            const rowsJson = $('#paymentImportRowsJson').val();

            if (!rowsJson) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Belum ada data',
                    text: 'Preview file Excel terlebih dahulu.'
                });
                return;
            }

            $.ajax({
                url: '<?= base_url("BillingPayment/saveImportedPayments") ?>',
                type: 'POST',
                data: { rows_json: rowsJson },
                dataType: 'json',
                beforeSend: function () {
                    showLoader();
                },
                success: function (response) {
                    if (response.status) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Import Berhasil',
                            text: response.message
                        }).then(() => {
                            resetPaymentImportPreview();
                            $('#paymentExcelFile').val('');
                            $('#modal-payment').modal('hide');
                            reloadBillingPage();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Import Gagal',
                            text: response.message || 'Gagal menyimpan hasil import payment'
                        });
                    }
                },
                error: function () {
                    Swal.fire({
                        icon: 'error',
                        title: 'Import Gagal',
                        text: 'Terjadi kesalahan saat menyimpan hasil import payment'
                    });
                },
                complete: function () {
                    hideLoader();
                }
            });
        });

        function formatNumberInput(value) {
            if (!value) return '';
            value = value.toString().replace(/[^\d]/g, '');
            return new Intl.NumberFormat('id-ID').format(value);
        }

        function reindexTableRows() {
            $('#table_selected_invoice tbody tr.data-row').each(function (index) {
                $(this).find('.row-no').text(index + 1);
            });

            if ($('#table_selected_invoice tbody tr.data-row').length === 0) {
                $('#table_selected_invoice tbody').html(`
            <tr id="emptyRow">
                <td colspan="9" class="text-center text-muted">Belum ada invoice dipilih</td>
            </tr>
        `);
            }
        }

        function updatePaymentSummary() {
            let totalInvoice = 0;
            let totalPayment = 0;

            $('#table_selected_invoice tbody tr.data-row').each(function () {
                const invoiceVal = parseAmount($(this).find('.invoice-price-raw').val());
                const paymentVal = parseAmount($(this).find('.payment-price').val());

                totalInvoice += invoiceVal;
                totalPayment += paymentVal;
            });

            $('#totalInvoicePrice').text(formatTitik(totalInvoice, 2));
            $('#totalPaymentPrice').text(formatTitik(totalPayment, 2));
        }

        function getToday() {
            const d = new Date();
            const month = String(d.getMonth() + 1).padStart(2, '0');
            const day = String(d.getDate()).padStart(2, '0');
            return `${d.getFullYear()}-${month}-${day}`;
        }

        function addInvoiceRow(item) {
            const rowId = 'row_invoice_' + item.id;

            if ($('#' + rowId).length > 0) {
                return;
            }

            $('#emptyRow').remove();

            const invoicePrice = parseAmount(item.invoice_price_nett);

            const html = `
        <tr class="data-row" id="${rowId}" data-id="${item.id}">
            <td class="text-center row-no"></td>
            <td>
                ${item.nama_bowheer || '-'}
                <input type="hidden" name="id_billing[]" value="${item.id}">
            </td>
            <td>${item.no_invoice || '-'}</td>
            <td>${item.po_number || '-'}</td>
            <td class="text-right">
                ${formatTitik(invoicePrice, 2)}
                <input type="hidden" class="invoice-price-raw" value="${invoicePrice}">
            </td>
            <td>
                <input type="text" class="form-control text-right payment-price" name="invoice_price_payment[]" value="${formatTitik(invoicePrice, 2)}" placeholder="Input payment price" autocomplete="off">
            </td>
            <td>
                <input type="date" class="form-control" name="tgl_payment_invoice[]" value="${getToday()}">
            </td>
            <td>
                <select class="form-control payment-status" name="status_invoice[]">
                    <option value="paid" selected>Paid</option>
                    <option value="partial">Partial</option>
                    <option value="reject">Reject</option>
                </select>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-danger btn-sm btn-remove-row">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
    `;

            $('#table_selected_invoice tbody').append(html);
            reindexTableRows();
            updatePaymentSummary();
        }

        function resetBatchPaymentTable() {
            $('#formBatchPayment')[0].reset();
            $('#select_invoice_payment').val(null).trigger('change');
            $('#table_selected_invoice tbody').html(`
                <tr id="emptyRow">
                    <td colspan="9" class="text-center text-muted">Belum ada invoice dipilih</td>
                </tr>
            `);
            updatePaymentSummary();
        }

        function openPaymentModalWithInvoice(row) {
            if (!row) {
                return;
            }

            resetBatchPaymentTable();

            addInvoiceRow({
                id: row.id_billing,
                no_invoice: row.no_invoice,
                po_number: row.po_number,
                nama_bowheer: row.nama_bowheer,
                invoice_price_nett: row.invoice_price_nett
            });

            $('#table_selected_invoice tbody tr.data-row').first().find('input[name="tgl_payment_invoice[]"]').val(getToday());
            $('#modal-payment').modal('show');
        }

        $(document).on('click', '.payment-item', function () {
            const id = $(this).data('id');
            const row = latestBillingRows[id];

            if (!row) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Data tidak ditemukan',
                    text: 'Invoice yang dipilih tidak tersedia.'
                });
                return;
            }

            if (row.status_invoice === 'open') {
                openPaymentModalWithInvoice(row);
                return;
            }

            if (row.status_invoice === 'partial') {
                openPartialEditModal(row);
                return;
            }

            Swal.fire({
                icon: 'info',
                title: 'Informasi',
                text: 'Invoice ini sudah paid. Gunakan menu edit jika ingin mengubah data.'
            });
        });

        $(document).on('click', '.edit-billing-item', function () {
            const id = $(this).data('id');
            const row = latestBillingRows[id];

            if (!row) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Data tidak ditemukan',
                    text: 'Invoice yang dipilih tidak tersedia.'
                });
                return;
            }

            openBillingEditModal(row);
        });

        $(document).on('click', '.hapus-item', function () {
            const id = $(this).data('id');
            const row = latestBillingRows[id];
            const invoiceNumber = row && row.no_invoice ? row.no_invoice : 'invoice ini';

            Swal.fire({
                icon: 'warning',
                title: 'Hapus invoice?',
                text: `Data ${invoiceNumber} akan dihapus permanen.`,
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d'
            }).then((result) => {
                if (!result.isConfirmed) {
                    return;
                }

                $.ajax({
                    url: '<?= base_url("BillingPayment/deleteBilling") ?>',
                    type: 'POST',
                    data: { id_billing: id },
                    dataType: 'json',
                    beforeSend: function () {
                        showLoader();
                    },
                    success: function (response) {
                        if (response.status) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: response.message
                            }).then(() => {
                                reloadBillingPage();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: response.message || 'Gagal menghapus invoice'
                            });
                        }
                    },
                    error: function () {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: 'Terjadi kesalahan saat menghapus invoice'
                        });
                    },
                    complete: function () {
                        hideLoader();
                    }
                });
            });
        });

        window.updateEditPartialSummary = function () {
            const invoiceValue = parseAmount($('#edit_partial_invoice_price').val());
            let paymentValue = parseAmount($('#edit_partial_payment_price').val());
            const selectedStatus = $('#edit_partial_status_invoice').val();

            if (paymentValue > invoiceValue) {
                paymentValue = invoiceValue;
                $('#edit_partial_payment_price').val(formatTitik(paymentValue, 2));
            }

            const remaining = Math.max(invoiceValue - paymentValue, 0);
            $('#edit_partial_remaining').val(formatTitik(remaining, 2));

            if (selectedStatus === 'reject') {
                return;
            }

            if (paymentValue >= invoiceValue && invoiceValue > 0) {
                $('#edit_partial_status_invoice').val('paid');
            } else {
                $('#edit_partial_status_invoice').val('partial');
            }
        };

        $(document).on('click', '.edit-partial-item', function () {
            const id = $(this).data('id');
            openPartialEditModal(latestBillingRows[id]);
        });

        $(document).on('click', '.detail-partial-item', function () {
            const id = $(this).data('id');
            openPartialDetailModal(latestBillingRows[id]);
        });

        $('#edit_partial_payment_price').on('focus', function () {
            const raw = parseAmount($(this).val());
            $(this).val(raw ? raw : '');
        });

        $('#edit_partial_payment_price').on('input', function () {
            updateEditPartialSummary();
        });

        $('#edit_partial_payment_price').on('blur', function () {
            const raw = parseAmount($(this).val());
            $(this).val(raw ? formatTitik(raw, 2) : '');
            updateEditPartialSummary();
        });

        $('#edit_partial_status_invoice').on('change', function () {
            const status = $(this).val();
            const invoiceValue = parseAmount($('#edit_partial_invoice_price').val());

            if (status === 'paid') {
                $('#edit_partial_payment_price').val(formatTitik(invoiceValue, 2));
            } else if (status === 'reject') {
                $('#edit_partial_payment_price').val('');
                $('#edit_partial_payment_date').val('');
            }

            updateEditPartialSummary();
        });

        $('#formEditPartialPayment').on('submit', function (e) {
            e.preventDefault();

            const invoiceValue = parseAmount($('#edit_partial_invoice_price').val());
            const paymentValue = parseAmount($('#edit_partial_payment_price').val());
            const paymentDate = $('#edit_partial_payment_date').val();

            if (!paymentValue || !paymentDate) {
                alert('Total pembayaran dan tanggal payment wajib diisi');
                return;
            }

            if (paymentValue > invoiceValue) {
                alert('Total pembayaran tidak boleh melebihi nilai invoice');
                return;
            }

            $.ajax({
                url: '<?= base_url("BillingPayment/updatePartialPayment") ?>',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                beforeSend: function () {
                    showLoader();
                },
                success: function (response) {
                    if (response.status) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: response.message
                        }).then(() => {
                            $('#modal-edit-partial-payment').modal('hide');
                            reloadBillingPage();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: response.message || 'Gagal memperbarui partial payment'
                        });
                    }
                },
                error: function () {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Terjadi kesalahan saat update partial payment'
                    });
                },
                complete: function () {
                    hideLoader();
                }
            });
        });

        $('#formEditBillingInvoice').on('submit', function (e) {
            e.preventDefault();

            $.ajax({
                url: '<?= base_url("BillingPayment/updateBillingInvoice") ?>',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                beforeSend: function () {
                    showLoader();
                },
                success: function (response) {
                    if (response.status) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: response.message
                        }).then(() => {
                            $('#modal-edit-billing').modal('hide');
                            reloadBillingPage();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: response.message || 'Gagal memperbarui invoice'
                        });
                    }
                },
                error: function () {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Terjadi kesalahan saat update invoice'
                    });
                },
                complete: function () {
                    hideLoader();
                }
            });
        });
    });

    $(document).ready(function () {

        // === Format rupiah mendukung angka negatif ===
        function formatRupiah(angka) {
            angka = angka.toString().replace(/[^0-9\-]/g, ""); // izinkan minus

            if (angka === "" || angka === "-") return angka; // biar bisa ketik "-" dulu

            let num = parseFloat(angka);
            if (isNaN(num)) num = 0;

            const formatted = (num < 0 ? "-" : "") + Math.abs(num).toLocaleString('id-ID');
            return formatted;
        }

        // === Hitung total otomatis ===
        function updateTotalInvoice() {
            let achiev = parseFloat($("[name='achiev_invoice']").val().replace(/[^0-9\-]/g, "")) || 0;
            let tambahan = parseFloat($("[name='tambahan_invoice']").val().replace(/[^0-9\-]/g, "")) || 0;
            let total = achiev + tambahan;

            const formattedTotal = (total < 0 ? "-" : "") + Math.abs(total).toLocaleString('id-ID');
            $("[name='total_invoice']").val(formattedTotal);
        }

        // === Event gabungan, tidak duplikat lagi ===
        $(document).on("input", "[name='achiev_invoice'], [name='tambahan_invoice']", function (e) {
            const caretPos = e.target.selectionStart; // simpan posisi kursor agar tidak loncat
            $(this).val(formatRupiah($(this).val()));
            e.target.setSelectionRange(caretPos, caretPos);
            updateTotalInvoice();
        });

        // === Fungsi ambil target invoice ===
        function loadTargetInvoice() {
            const bowheer = $('#addfilter_bowheer').val();
            const area = $('#addfilter_area').val();
            const month = $('#addfilter_month').val();
            const week = $('#addfilter_week').val();

            if (bowheer && area && month && week) {
                $.ajax({
                    url: "<?= base_url('BillingPayment/get_target_invoice') ?>",
                    type: "POST",
                    dataType: "json",
                    data: { bowheer, area, month, week },
                    success: function (res) {
                        console.log(res);

                        let qtyTarget = res.qty_target ? parseFloat(res.qty_target) : 0;
                        let qtyAchiev = res.qty_achiev_target ? parseFloat(res.qty_achiev_target) : 0;

                        $('[name="target_invoice"]').val(formatRupiah(qtyTarget));
                        $('[name="achiev_invoice"]').val(formatRupiah(qtyAchiev));

                        if (qtyAchiev && qtyAchiev !== 0) {
                            $('[name="achiev_invoice"]').prop('disabled', true);
                            $('[name="tambahan_invoice"]').closest('.form-group').slideDown();
                            $('[name="total_invoice"]').closest('.form-group').slideDown();
                            $('[name="total_invoice"]').val(formatRupiah(qtyAchiev));
                        } else {
                            $('[name="achiev_invoice"]').prop('disabled', false);
                            $('[name="tambahan_invoice"]').closest('.form-group').slideUp();
                            $('[name="total_invoice"]').closest('.form-group').slideUp();
                            $('[name="tambahan_invoice"]').val('');
                            $('[name="total_invoice"]').val('');
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error("Error:", error);
                        console.log(xhr.responseText);
                    }
                });
            }
        }

        // Jalankan ketika dropdown berubah
        $('#addfilter_bowheer, #addfilter_area, #addfilter_month, #addfilter_week').on('change', loadTargetInvoice);

        // === VALIDASI SAAT SIMPAN ===
        $("form").on("submit", function (e) {
            const currentForm = $(this);

            if (!currentForm.find('#addfilter_bowheer').length) {
                return true;
            }

            e.preventDefault();

            // 🔹 Validasi tetap dipertahankan (jangan dihapus)
            let bowheer = $("#addfilter_bowheer").val();
            const inputKotaBaru = $('#inputKotaBaru');
            const areaDropdown = $('#addfilter_area');
            let area = areaDropdown.val(); // ✅ Tambahkan ini
            let regional = $("#inputRegionalBaru").val();
            let pic = $("#inputPICBaru").val();
            let month = $("#addfilter_month").val();
            let week = $("#addfilter_week").val();
            let achiev = $("[name='achiev_invoice']").val().trim();
            let tambahanVisible = $("[name='tambahan_invoice']").closest('.form-group').is(":visible");
            let tambahan = $("[name='tambahan_invoice']").val().trim();

            // 🔹 Jika tambah kota baru aktif → pakai nilai input kota baru
            if (inputKotaBaru.is(':visible') && inputKotaBaru.val().trim() !== '') {
                area = inputKotaBaru.val().trim(); // ✅ overwrite nilai area
                areaDropdown.val(area); // agar ikut terkirim via serialize
            }

            // 🔹 Validasi dropdown dan input wajib
            if (!bowheer || !area || !month || !week) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Data belum lengkap!',
                    text: 'Pastikan semua dropdown sudah dipilih.'
                });
                return;
            }
            if (achiev === "" || achiev === "0") {
                Swal.fire({
                    icon: 'warning',
                    title: 'Input belum diisi!',
                    text: 'Nilai Realisasi Invoice harus diisi.'
                });
                return;
            }
            if (tambahanVisible && (tambahan === "" || tambahan === "0")) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Input belum diisi!',
                    text: 'Tambahan Invoice wajib diisi jika tampil.'
                });
                return;
            }

            // 🔹 Konfirmasi simpan
            Swal.fire({
                icon: 'question',
                title: 'Simpan Invoice?',
                text: 'Pastikan semua data sudah benar sebelum disimpan.',
                showCancelButton: true,
                confirmButtonText: 'Ya, Simpan!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = $("form").serialize();

                    $.ajax({
                        url: "<?= base_url('BillingPayment/addInvoice') ?>",
                        type: "POST",
                        data: formData,
                        dataType: "json",
                        success: function (res) {
                            console.log(res);

                            if (res.status === 'not_found') {
                                Swal.fire({
                                    icon: 'question',
                                    title: 'Area belum terdaftar',
                                    text: 'Project ini tidak memiliki area ini. Tambahkan area dan invoice?',
                                    showCancelButton: true,
                                    confirmButtonText: 'Ya, Tambahkan!',
                                    cancelButtonText: 'Batal'
                                }).then((r) => {
                                    if (r.isConfirmed) {
                                        $.ajax({
                                            url: "<?= base_url('BillingPayment/createNewTargetInvoice') ?>",
                                            type: "POST",
                                            dataType: "json",
                                            data: res,

                                            success: function (res2) {
                                                Swal.fire({
                                                    icon: res2.status ? 'success' : 'error',
                                                    title: res2.status ? 'Berhasil' : 'Gagal',
                                                    text: res2.message
                                                }).then(() => location.reload());
                                            }
                                        });
                                    }
                                });
                            } else if (res.status) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    text: 'Invoice berhasil disimpan.'
                                }).then(() => location.reload());
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal!',
                                    text: res.message || 'Terjadi kesalahan.'
                                });
                            }
                        },
                        error: function (xhr) {
                            console.error(xhr.responseText);
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: 'Invoice berhasil disimpan.'
                            }).then(() => location.reload());
                        }
                    });
                }
            });
        });

        // Reset form ketika modal dibuka
        $('#modal-lg-tambah-invoice').on('show.bs.modal', function () {
            $(this).find('form')[0].reset();
            $("[name='achiev_invoice'], [name='tambahan_invoice'], [name='total_invoice']").val("");
            $("[name='tambahan_invoice']").closest('.form-group').hide();
            $("[name='total_invoice']").closest('.form-group').hide();
        });

    });

    $(document).ready(function () {
        let isTambahKotaActive = false;

        // Toggle form tambah kota
        $('#btnTambahKota').on('click', function () {
            isTambahKotaActive = !isTambahKotaActive;

            if (isTambahKotaActive) {
                // Saat aktif → tampilkan input, disable dropdown
                $('#inputKotaBaruContainer').slideDown();
                $('#addfilter_area').prop('disabled', true);
                $('#addfilter_area').val('');
                $(this).text('× Batalkan Tambah Kota');

                $('#inputRegionalBaru').slideDown();

                $('#inputPICBaruContainer').slideDown();
            } else {
                // Saat nonaktif → sembunyikan input, enable dropdown
                $('#inputKotaBaruContainer').slideUp();
                $('#addfilter_area').prop('disabled', false);
                $('#inputKotaBaru').val('');
                $(this).text('+ Tambah Kota Baru');

                $('#inputRegionalBaru').slideUp();
                $('#inputRegionalBaru').val('');

                $('#inputPICBaruContainer').slideUp();
                $('#inputPICBaru').val('');
            }
        });

        // Pastikan saat submit form, area_target diambil sesuai yang aktif
        $('form').on('submit', function (e) {
            const isInputBaru = isTambahKotaActive;
            const areaDropdown = $('#addfilter_area').val();
            const areaBaru = $('#inputKotaBaru').val().trim();
            const regionalBaru = $('#inputRegionalBaru').val().trim();
            const picBaru = $('#inputPICBaru').val().trim();

            if (isInputBaru && areaBaru === '') {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Kota baru belum diisi!',
                    text: 'Silakan isi nama kota sebelum melanjutkan.'
                });
                return false;
            }

            if (isInputBaru && regionalBaru === '') {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Regional baru belum diisi!',
                    text: 'Silakan isi nama regional sebelum melanjutkan.'
                });
                return false;
            }

            if (isInputBaru && picBaru === '') {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'PIC baru belum diisi!',
                    text: 'Silakan isi nama PIC sebelum melanjutkan.'
                });
                return false;
            }

            // Jika kota baru aktif, gunakan nilainya sebagai area_target
            if (isInputBaru) {
                $('<input>').attr({
                    type: 'hidden',
                    name: 'addfilter_area',
                    value: areaBaru
                }).appendTo('form');
            } else if (!areaDropdown) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Area belum dipilih!',
                    text: 'Pilih area dari daftar atau tambahkan area baru.'
                });
                return false;
            }
        });
    });

    document.addEventListener("DOMContentLoaded", function () {
    let cardfilter = document.getElementById("cardfiltercollapse").closest(".card");
    cardfilter.classList.add("collapsed-card");
  });
</script>
<script>
    function showLoader() {
        $('#globalLoader').fadeIn(200);
    }

    function hideLoader() {
        $('#globalLoader').fadeOut(200);

        // bersihin semua overlay liar
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open');
    }
</script>



<style>
    .billing-filter-card {
        border: 0;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 18px 42px rgba(14, 41, 64, 0.08);
        background: linear-gradient(180deg, #ffffff 0%, #f6fbff 100%);
    }

    .billing-filter-card__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem 1.25rem;
        background:
            radial-gradient(circle at top right, rgba(255, 255, 255, 0.18), transparent 30%),
            linear-gradient(135deg, #103b5a, #1f6da1 55%, #53a9d8);
        color: #fff;
    }

    .billing-card-tools {
        margin-left: auto;
        display: flex;
        align-items: center;
        justify-content: flex-end;
    }

    .billing-filter-card .card-title {
        font-weight: 700;
    }

    .billing-filter-subtitle {
        color: rgba(255, 255, 255, 0.84);
        font-size: 0.92rem;
        max-width: 760px;
    }

    .billing-filter-card__body {
        padding: 1.25rem 1.25rem 1.35rem;
    }

    .billing-detail-card {
        border: 0;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 18px 42px rgba(14, 41, 64, 0.08);
        background: linear-gradient(180deg, #ffffff 0%, #f7fbff 100%);
    }

    .billing-detail-card__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem 1.25rem;
        background:
            radial-gradient(circle at top right, rgba(255, 255, 255, 0.16), transparent 30%),
            linear-gradient(135deg, #0f3a57 0%, #145f8f 55%, #3f91c3 100%);
        color: #fff;
    }

    .billing-detail-subtitle {
        color: rgba(255, 255, 255, 0.82);
        font-size: 0.9rem;
        max-width: 720px;
    }

    .billing-field-label {
        display: block;
        margin-bottom: 0.55rem;
        font-size: 0.83rem;
        font-weight: 800;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: #2d6287;
    }

    .billing-filter-card .form-group {
        margin-bottom: 1rem;
    }

    .billing-filter-card .select2-container {
        width: 100% !important;
    }

    .billing-filter-card .select2-container--bootstrap4 .select2-selection {
        min-height: 46px;
        border-radius: 14px;
        border: 1px solid #cfe0ee;
        box-shadow: none;
        background: #fff;
    }

    .billing-filter-card .select2-container--bootstrap4 .select2-selection--multiple {
        padding: 0.4rem 0.65rem 0.3rem;
    }

    .billing-filter-card .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__rendered {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        padding: 0;
    }

    .billing-filter-card .select2-container--bootstrap4 .select2-selection__choice {
        margin-top: 0;
        border: 1px solid #cfe0ee;
        border-radius: 999px;
        padding: 0.22rem 0.65rem;
        background: linear-gradient(135deg, #eff7fc 0%, #dfeef8 100%);
        color: #27587c;
        font-size: 0.82rem;
        font-weight: 700;
    }

    .billing-filter-card .select2-container--bootstrap4 .select2-selection__choice__remove {
        color: #5f86a4;
        margin-right: 6px;
    }

    .billing-filter-card .select2-container--bootstrap4 .select2-selection__placeholder,
    .billing-filter-card .select2-container--bootstrap4 .select2-search__field::placeholder {
        color: #8aa0b4;
    }

    .billing-filter-card .select2-container--bootstrap4.select2-container--focus .select2-selection,
    .billing-filter-card .select2-container--bootstrap4.select2-container--open .select2-selection {
        border-color: #55a7d5;
        box-shadow: 0 0 0 0.18rem rgba(85, 167, 213, 0.18);
    }

    .billing-filter-card .select2-container--bootstrap4 .select2-dropdown {
        border-color: #cfe0ee;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 18px 32px rgba(16, 59, 90, 0.14);
    }

    .billing-filter-card .select2-container--bootstrap4 .select2-search__field {
        border-radius: 10px;
        border-color: #cfe0ee;
        min-height: 38px;
    }

    .billing-filter-actions {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-end;
        gap: 12px;
        padding-top: 0.25rem;
    }

    .budget-btn {
        border: 0;
        border-radius: 12px;
        padding: 0.68rem 1.15rem;
        font-weight: 700;
        letter-spacing: 0.01em;
        transition: all 0.2s ease;
        box-shadow: 0 12px 22px rgba(16, 59, 90, 0.12);
    }

    .budget-btn:hover,
    .budget-btn:focus {
        transform: translateY(-1px);
        box-shadow: 0 16px 28px rgba(16, 59, 90, 0.16);
    }

    .budget-btn--primary {
        background: linear-gradient(135deg, #103b5a 0%, #1f6da1 100%);
        color: #fff;
    }

    .budget-btn--success {
        background: linear-gradient(135deg, #0f8b72 0%, #24b18f 100%);
        color: #fff;
    }

    .budget-btn--ghost {
        background: #fff;
        color: #315d7f;
        border: 1px solid #d7e6f2;
        box-shadow: 0 10px 22px rgba(112, 141, 165, 0.12);
    }

    .billing-filter-btn {
        min-width: 158px;
    }

    .billing-filter-collapse {
        color: #fff;
    }

    .billing-filter-collapse:hover,
    .billing-filter-collapse:focus {
        color: #fff;
        background: rgba(255, 255, 255, 0.12);
        border-radius: 10px;
    }

    .billing-report-modal {
        border: 0;
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 30px 50px rgba(8, 35, 55, 0.22);
    }

    .billing-report-modal__header {
        border-bottom: 0;
        padding: 1.4rem 1.5rem 1.1rem;
        background:
            radial-gradient(circle at top right, rgba(255, 255, 255, 0.22), transparent 30%),
            linear-gradient(135deg, #103b5a 0%, #1f6da1 55%, #53a9d8 100%);
        color: #fff;
    }

    .billing-report-modal__eyebrow {
        display: inline-block;
        margin-bottom: 6px;
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: rgba(255, 255, 255, 0.76);
    }

    .billing-report-modal__subtitle {
        max-width: 88%;
        color: rgba(255, 255, 255, 0.84);
        font-size: 0.92rem;
    }

    .billing-report-modal .modal-body {
        padding: 1.5rem;
        background: linear-gradient(180deg, #fbfdff 0%, #f2f8fc 100%);
    }

    .billing-report-modal__footer {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
        border-top: 0;
        padding: 0 1.5rem 1.5rem;
        background: transparent;
    }

    .billing-report-section {
        margin-bottom: 0;
        padding: 1rem 1rem 0.9rem;
        border: 1px solid #dbe9f4;
        border-radius: 18px;
        background: rgba(255, 255, 255, 0.92);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.7);
    }

    .billing-report-section__title {
        margin-bottom: 0.9rem;
        font-size: 0.86rem;
        font-weight: 800;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: #2d6287;
    }

    .billing-report-modal .form-group {
        margin-bottom: 1rem;
    }

    .billing-report-modal .select2-container {
        width: 100% !important;
    }

    .billing-report-modal .select2-container--bootstrap4 .select2-selection,
    .billing-report-input {
        min-height: 44px;
        border-radius: 12px;
        border: 1px solid #cfe0ee;
        box-shadow: none;
        background: #fff;
    }

    .billing-report-modal .select2-container--bootstrap4 .select2-selection--multiple {
        padding: 0.35rem 0.6rem 0.28rem;
    }

    .billing-report-modal .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__rendered {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        padding: 0;
    }

    .billing-report-modal .select2-container--bootstrap4 .select2-selection__choice {
        margin-top: 0;
        border: 1px solid #cfe0ee;
        border-radius: 999px;
        padding: 0.22rem 0.65rem;
        background: linear-gradient(135deg, #eff7fc 0%, #dfeef8 100%);
        color: #27587c;
        font-size: 0.82rem;
        font-weight: 700;
    }

    .billing-report-modal .select2-container--bootstrap4 .select2-selection__choice__remove {
        color: #5f86a4;
        margin-right: 6px;
    }

    .billing-report-modal .select2-container--bootstrap4.select2-container--focus .select2-selection,
    .billing-report-modal .select2-container--bootstrap4.select2-container--open .select2-selection,
    .billing-report-input:focus {
        border-color: #55a7d5;
        box-shadow: 0 0 0 0.18rem rgba(85, 167, 213, 0.18);
    }

    .billing-report-modal .select2-container--bootstrap4 .select2-dropdown {
        border-color: #cfe0ee;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 18px 32px rgba(16, 59, 90, 0.14);
    }

    .billing-report-modal .select2-container--bootstrap4 .select2-search__field {
        border-radius: 10px;
        border-color: #cfe0ee;
        min-height: 38px;
    }

    .billing-detail-toolbar {
        display: inline-flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 12px;
        padding: 0.35rem 0.75rem;
        border-radius: 18px;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.92), rgba(242, 248, 252, 0.92));
        box-shadow: 0 18px 40px rgba(14, 41, 64, 0.08);
    }

    .billing-detail-toolbar__btn {
        min-width: 180px;
    }

    .billing-workbench-card {
        border: 0;
        border-radius: 22px;
        overflow: hidden;
        box-shadow: 0 20px 46px rgba(14, 41, 64, 0.08);
        background: linear-gradient(180deg, #ffffff 0%, #f7fbff 100%);
    }

    .billing-workbench-card__header {
        padding: 1.15rem 1.35rem;
        background:
            radial-gradient(circle at top right, rgba(255, 255, 255, 0.16), transparent 32%),
            linear-gradient(135deg, #103b5a 0%, #165f90 55%, #58acd8 100%);
        color: #fff;
        border-bottom: 0;
    }

    .billing-workbench-subtitle {
        color: rgba(255, 255, 255, 0.84);
        font-size: 0.92rem;
        max-width: 820px;
    }

    .billing-workbench-tabs-wrap {
        padding: 1rem 1.35rem 0;
    }

    #invoice-status-tab {
        gap: 10px;
        flex-wrap: wrap;
    }

    #invoice-status-tab .nav-link {
        border: 1px solid #d4e5f1;
        border-radius: 999px;
        padding: 0.62rem 1rem;
        background: linear-gradient(180deg, #ffffff 0%, #f4f9fd 100%);
        color: #3b6b8e;
        font-weight: 700;
        transition: all 0.2s ease;
    }

    #invoice-status-tab .nav-link:hover,
    #invoice-status-tab .nav-link:focus {
        transform: translateY(-1px);
        box-shadow: 0 12px 22px rgba(16, 59, 90, 0.1);
    }

    #invoice-status-tab .nav-link.active {
        border-color: transparent;
        background: linear-gradient(135deg, #103b5a 0%, #1f6da1 100%);
        color: #fff;
        box-shadow: 0 14px 26px rgba(16, 59, 90, 0.18);
    }

    .billing-workbench-card__body {
        padding: 1.15rem 1.35rem 1.35rem;
    }

    .billing-workbench-table-shell {
        border-radius: 18px;
        border: 1px solid #d8e7f2;
        background: #fff;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.8);
    }

    .js-billing-detail-table {
        margin-bottom: 0;
    }

    .js-billing-detail-table thead th {
        border-top: 0;
        background: linear-gradient(180deg, #eef6fb 0%, #dcecf8 100%);
        color: #1f5e8a;
        font-size: 0.8rem;
        font-weight: 800;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .js-billing-detail-table tbody td {
        vertical-align: middle;
    }

    .js-billing-detail-table tbody tr:hover {
        background: rgba(219, 236, 247, 0.26);
    }

    .js-billing-detail-table tfoot th {
        background: #f7fbff;
        color: #315d7f;
        font-weight: 800;
    }

    .billing-workflow-modal {
        border: 0;
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 30px 50px rgba(8, 35, 55, 0.22);
    }

    .billing-workflow-modal__header {
        border-bottom: 0;
        padding: 1.35rem 1.5rem 1.1rem;
        background:
            radial-gradient(circle at top right, rgba(255, 255, 255, 0.22), transparent 30%),
            linear-gradient(135deg, #103b5a 0%, #1f6da1 55%, #53a9d8 100%);
        color: #fff;
    }

    .billing-workflow-modal__eyebrow {
        display: inline-block;
        margin-bottom: 6px;
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: rgba(255, 255, 255, 0.76);
    }

    .billing-workflow-modal__subtitle {
        max-width: 88%;
        color: rgba(255, 255, 255, 0.84);
        font-size: 0.92rem;
    }

    .billing-workflow-modal__body {
        padding: 1.5rem;
        background: linear-gradient(180deg, #fbfdff 0%, #f2f8fc 100%);
    }

    .billing-workflow-modal__footer {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
        border-top: 0;
        padding: 0 1.5rem 1.5rem;
        background: transparent;
    }

    .billing-workflow-modal .form-group label {
        font-size: 0.82rem;
        font-weight: 800;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        color: #2d6287;
    }

    .billing-workflow-modal .form-control,
    .billing-workflow-modal .select2-container--bootstrap4 .select2-selection {
        min-height: 44px;
        border-radius: 12px;
        border: 1px solid #cfe0ee;
        box-shadow: none;
        background: #fff;
    }

    .billing-workflow-modal .form-control:focus,
    .billing-workflow-modal .select2-container--bootstrap4.select2-container--focus .select2-selection,
    .billing-workflow-modal .select2-container--bootstrap4.select2-container--open .select2-selection {
        border-color: #55a7d5;
        box-shadow: 0 0 0 0.18rem rgba(85, 167, 213, 0.18);
    }

    .billing-workflow-modal .select2-container {
        width: 100% !important;
    }

    .billing-workflow-modal .select2-container--bootstrap4 .select2-selection--multiple {
        padding: 0.35rem 0.6rem 0.28rem;
    }

    .billing-workflow-modal .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__rendered {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        padding: 0;
    }

    .billing-workflow-modal .select2-container--bootstrap4 .select2-selection__choice {
        margin-top: 0;
        border: 1px solid #cfe0ee;
        border-radius: 999px;
        padding: 0.22rem 0.65rem;
        background: linear-gradient(135deg, #eff7fc 0%, #dfeef8 100%);
        color: #27587c;
        font-size: 0.82rem;
        font-weight: 700;
    }

    .billing-workflow-modal .select2-container--bootstrap4 .select2-dropdown {
        border-color: #cfe0ee;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 18px 32px rgba(16, 59, 90, 0.14);
    }

    .billing-workflow-modal .table {
        background: #fff;
    }

    .billing-workflow-modal .table thead th {
        border-top: 0;
        background: linear-gradient(180deg, #eef6fb 0%, #dcecf8 100%);
        color: #1f5e8a;
        font-size: 0.8rem;
        font-weight: 800;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .billing-workflow-modal .table tfoot th {
        background: #f7fbff;
        color: #315d7f;
        font-weight: 800;
    }

    .billing-workflow-modal .btn-primary {
        border: 0;
        background: linear-gradient(135deg, #103b5a 0%, #1f6da1 100%);
        box-shadow: 0 12px 22px rgba(16, 59, 90, 0.12);
    }

    .billing-workflow-modal .btn-success {
        border: 0;
        background: linear-gradient(135deg, #0f8b72 0%, #24b18f 100%);
        box-shadow: 0 12px 22px rgba(15, 139, 114, 0.14);
    }

    .billing-workflow-modal .btn-outline-info,
    .billing-workflow-modal .btn-outline-success,
    .billing-workflow-modal .btn-outline-primary {
        border-radius: 12px;
        font-weight: 700;
    }

    #invoiceUploadTab {
        border-bottom: 0;
        gap: 10px;
    }

    #invoiceUploadTab .nav-link {
        border: 1px solid #d4e5f1;
        border-radius: 14px;
        padding: 0.7rem 1rem;
        font-weight: 700;
        color: #3b6b8e;
        background: linear-gradient(180deg, #ffffff 0%, #f4f9fd 100%);
    }

    #invoiceUploadTab .nav-link.active {
        color: #fff;
        border-color: transparent;
        background: linear-gradient(135deg, #103b5a 0%, #1f6da1 100%);
        box-shadow: 0 14px 26px rgba(16, 59, 90, 0.18);
    }

    .billing-summary-row {
        margin-bottom: 0.5rem;
    }

    .billing-summary-row--hero {
        margin-bottom: 0.9rem;
    }

    .billing-summary-row--detail {
        margin-bottom: 0.6rem;
    }

    .billing-summary-col {
        min-width: 0;
    }

    .billing-summary-col--hero {
        display: flex;
        justify-content: center;
    }

    .premium-summary-card {
        position: relative;
        overflow: hidden;
        min-height: 148px;
        border: 1px solid rgba(15, 23, 42, 0.08);
        border-radius: 22px;
        padding: 0.35rem 0.4rem;
        background:
            radial-gradient(circle at top right, rgba(255, 255, 255, 0.92), rgba(255, 255, 255, 0) 35%),
            linear-gradient(145deg, #ffffff 0%, #f5f8ff 100%);
        box-shadow: 0 16px 35px rgba(15, 23, 42, 0.12);
        transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
    }

    .premium-summary-card--hero {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 150px;
        border-radius: 22px;
        padding: 1rem 1.15rem 1.1rem;
        background:
            radial-gradient(circle at top right, rgba(255, 255, 255, 0.94), rgba(255, 255, 255, 0) 38%),
            linear-gradient(145deg, #ffffff 0%, #eef5ff 100%);
        box-shadow: 0 18px 38px rgba(15, 23, 42, 0.13);
    }

    .premium-summary-card--hero .info-box-content {
        width: 100%;
        padding: 0;
        align-items: center;
        text-align: center;
    }

    .premium-summary-card::before {
        content: "";
        position: absolute;
        inset: 0 auto auto 0;
        width: 100%;
        height: 5px;
        background: var(--summary-accent, #1d4ed8);
    }

    .premium-summary-card::after {
        content: "";
        position: absolute;
        right: -35px;
        bottom: -40px;
        width: 130px;
        height: 130px;
        border-radius: 50%;
        background: var(--summary-soft, rgba(29, 78, 216, 0.12));
        pointer-events: none;
    }

    .premium-summary-card:hover {
        transform: translateY(-6px);
        border-color: var(--summary-border, rgba(29, 78, 216, 0.28));
        box-shadow: 0 22px 44px rgba(15, 23, 42, 0.16);
    }

    .premium-summary-card .info-box-content {
        display: flex;
        flex-direction: column;
        justify-content: center;
        gap: 0.22rem;
        min-width: 0;
        padding: 0.2rem 0;
    }

    .premium-summary-icon {
        width: 62px;
        min-width: 62px;
        height: 62px;
        border-radius: 18px;
        margin: 0.8rem 1rem;
        background: linear-gradient(145deg, var(--summary-light, #60a5fa), var(--summary-accent, #1d4ed8));
        color: #ffffff;
        box-shadow: 0 14px 28px var(--summary-shadow, rgba(29, 78, 216, 0.24));
    }

    .premium-summary-icon--hero {
        width: 56px;
        min-width: 56px;
        height: 56px;
        border-radius: 17px;
        margin: 0 0 0.85rem;
        font-size: 1.1rem;
    }

    .premium-summary-card .info-box-text {
        font-size: 1.04rem;
        font-weight: 800;
        letter-spacing: 0.14em;
        color: #475569;
        text-transform: uppercase;
        white-space: normal;
    }

    .premium-summary-card .summary-caption {
        font-size: 0.78rem;
        font-weight: 600;
        color: #94a3b8;
    }

    .premium-summary-card .info-box-number {
        margin-top: 0.3rem;
        font-size: 1.45rem;
        font-weight: 800;
        line-height: 1.15;
        color: #0f172a;
        white-space: normal;
        word-break: break-word;
    }

    .premium-summary-card--hero .info-box-text {
        font-size: 1.32rem;
        letter-spacing: 0.22em;
    }

    .premium-summary-card--hero .summary-caption {
        margin-top: 0.2rem;
        font-size: 0.74rem;
        max-width: 88%;
    }

    .premium-summary-card--hero .info-box-number {
        margin-top: 0.58rem;
        font-size: 2rem;
        line-height: 1.04;
    }

    .premium-total {
        --summary-accent: #1d4ed8;
        --summary-light: #60a5fa;
        --summary-soft: rgba(29, 78, 216, 0.12);
        --summary-border: rgba(29, 78, 216, 0.28);
        --summary-shadow: rgba(29, 78, 216, 0.24);
    }

    .premium-p1 {
        --summary-accent: #16a34a;
        --summary-light: #4ade80;
        --summary-soft: rgba(22, 163, 74, 0.12);
        --summary-border: rgba(22, 163, 74, 0.28);
        --summary-shadow: rgba(22, 163, 74, 0.24);
    }

    .premium-p2 {
        --summary-accent: #dc2626;
        --summary-light: #f87171;
        --summary-soft: rgba(220, 38, 38, 0.12);
        --summary-border: rgba(220, 38, 38, 0.28);
        --summary-shadow: rgba(220, 38, 38, 0.24);
    }

    .premium-p3 {
        --summary-accent: #f59e0b;
        --summary-light: #fbbf24;
        --summary-soft: rgba(245, 158, 11, 0.14);
        --summary-border: rgba(245, 158, 11, 0.3);
        --summary-shadow: rgba(245, 158, 11, 0.24);
    }

    .premium-bjt {
        --summary-accent: #0f766e;
        --summary-light: #2dd4bf;
        --summary-soft: rgba(15, 118, 110, 0.12);
        --summary-border: rgba(15, 118, 110, 0.28);
        --summary-shadow: rgba(15, 118, 110, 0.24);
    }

    #tabel_list_open_invoice tfoot th {
        text-align: right;
        background-color: #f8f9fa;
        font-weight: bold;
    }

    .glow-red {
        border: 1px solid rgba(220, 38, 38, 0.35);
        box-shadow: 0 10px 24px rgba(220, 38, 38, 0.12);
        transition: box-shadow 0.3s ease-in-out;
        border-radius: 16px;
    }

    .glow-red:hover {
        box-shadow: 0 16px 32px rgba(220, 38, 38, 0.2);
    }

    .glow-green {
        border: 1px solid rgba(22, 163, 74, 0.35);
        box-shadow: 0 10px 24px rgba(22, 163, 74, 0.12);
        transition: box-shadow 0.3s ease-in-out;
        border-radius: 16px;
    }

    .glow-green:hover {
        box-shadow: 0 16px 32px rgba(22, 163, 74, 0.2);
    }


    /* upgrade warna table */

    .cell-red {
        font-color: #ffb3b3 !important;
        color: #eb0000ff !important;
        font-weight: bold;
    }

    /* 🟢 Hijau muda untuk = 100% */
    .cell-green-light {
        color: #b3ffb3 !important;
        font-weight: bold;
    }

    /* 🟩 Hijau tua untuk > 100% */
    .cell-green-dark {
        color: #33cc33 !important;
        font-weight: bold;
        font-weight: bold;
    }

    .btn-gradient-success {
        background: linear-gradient(45deg, #327747, #00ff0d);
        border: none;
        color: #fff;
        border-radius: 50px;
        transition: all 0.3s ease-in-out;
        padding: 12px 25px;
        letter-spacing: 1px;
    }

    /* Gradien biru menarik */
    .btn-gradient-primary {
        background: linear-gradient(45deg, #007bff, #00c6ff);
        border: none;
        color: #fff;
        border-radius: 50px;
        transition: all 0.3s ease-in-out;
        padding: 12px 25px;
        letter-spacing: 1px;
    }

    /* Efek hover */
    .btn-gradient-primary:hover {
        background: linear-gradient(45deg, #0056b3, #0099cc);
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(0, 123, 255, 0.3);
    }

    /* Efek glowing lembut */
    .btn-gradient-primary:focus,
    .btn-gradient-primary:active {
        box-shadow: 0 0 10px rgba(0, 123, 255, 0.5);
        outline: none;
    }

    /* Animasi berdenyut (pulse effect) */
    .pulse {
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% {
            box-shadow: 0 0 0 0 rgba(0, 123, 255, 0.4);
        }

        70% {
            box-shadow: 0 0 0 15px rgba(0, 123, 255, 0);
        }

        100% {
            box-shadow: 0 0 0 0 rgba(0, 123, 255, 0);
        }
    }

    @media (max-width: 767.98px) {
        .premium-summary-card--hero .info-box-number {
            font-size: 1.62rem;
        }

        .premium-summary-card--hero .summary-caption {
            max-width: 100%;
        }

        .billing-filter-card__header {
            align-items: flex-start;
        }

        .billing-filter-actions {
            justify-content: stretch;
        }

        .billing-filter-btn {
            width: 100%;
            min-width: 0;
        }

        .billing-detail-toolbar {
            width: 100%;
        }

        .billing-detail-toolbar__btn {
            width: 100%;
            min-width: 0;
        }

        .billing-workbench-tabs-wrap,
        .billing-workbench-card__body,
        .billing-workflow-modal__body,
        .billing-report-modal .modal-body {
            padding-left: 1rem;
            padding-right: 1rem;
        }

        .billing-workflow-modal__footer,
        .billing-report-modal__footer {
            flex-direction: column;
            padding-left: 1rem;
            padding-right: 1rem;
        }

        .billing-workflow-modal__footer .btn,
        .billing-report-modal__footer .btn {
            width: 100%;
        }
    }

    /* ======== TAMBAHAN STYLE UNTUK MODAL TAMBAH INVOICE ======== */
    #modal-lg-tambah-invoice .modal-content {
        border-radius: 15px;
        border: none;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.25);
        animation: fadeInUp 0.3s ease-out;
    }

    #modal-lg-tambah-invoice .modal-header {
        background: linear-gradient(135deg, #007bff, #6610f2);
        color: white;
        border-top-left-radius: 15px;
        border-top-right-radius: 15px;
        padding: 1rem 1.5rem;
    }

    #modal-lg-tambah-invoice .modal-header h4 {
        font-weight: bold;
        letter-spacing: 0.5px;
    }

    #modal-lg-tambah-invoice .modal-body {
        background-color: #f9fafc;
        padding: 1.5rem;
        border-radius: 0 0 15px 15px;
    }

    #modal-lg-tambah-invoice .form-group label {
        font-weight: 600;
        color: #495057;
    }

    #modal-lg-tambah-invoice .form-control {
        border-radius: 10px;
        border: 1px solid #ced4da;
        transition: all 0.2s ease-in-out;
    }

    #modal-lg-tambah-invoice .form-control:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }

    #modal-lg-tambah-invoice .modal-footer {
        background: #f1f3f6;
        border-top: 1px solid #dee2e6;
        border-bottom-left-radius: 15px;
        border-bottom-right-radius: 15px;
    }

    #modal-lg-tambah-invoice .btn-primary {
        background: linear-gradient(135deg, #007bff, #6610f2);
        border: none;
        border-radius: 10px;
        padding: 8px 20px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    #modal-lg-tambah-invoice .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 123, 255, 0.4);
    }

    #modal-lg-tambah-invoice .btn-danger {
        border-radius: 10px;
        font-weight: 600;
        padding: 8px 20px;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Tombol pemicu modal (judul TAMBAH INVOICE) */
    .text-primary.font-weight-bold {
        background: linear-gradient(90deg, #007bff, #6610f2);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        transition: all 0.3s ease;
    }

    .text-primary.font-weight-bold:hover {
        transform: scale(1.05);
        text-shadow: 0 0 10px rgba(102, 16, 242, 0.4);
    }

    #btnTambahKota {
        font-size: 0.9rem;
        transition: all 0.3s ease;
    }

    #btnTambahKota:hover {
        text-decoration: underline;
        transform: scale(1.05);
    }

    #globalLoader {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100vh;
        background: rgba(0, 0, 0, 0.5);
        /* gelap transparan */
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    }

    .loader-content {
        text-align: center;
        align-items: center;
        color: #fff;
    }

    .spinner {
        width: 50px;
        height: 50px;
        border: 5px solid #ddd;
        border-top: 5px solid #3498db;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin: 0 auto 10px;
    }

    @keyframes spin {
        100% {
            transform: rotate(360deg);
        }
    }

    .fade-in {
        opacity: 0;
        transform: translateY(-10px);
        animation: fadeIn 0.3s ease-in-out forwards;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .active-tab {
        position: relative;
    }

    .active-tab::after {
        content: "";
        position: absolute;
        left: 0;
        bottom: -5px;
        width: 100%;
        height: 4px;
        background: rgb(12, 127, 180);
        /* Warna hijau neon */
        box-shadow: 0 0 10px rgb(12, 127, 180), 0 0 20px rgb(12, 127, 180);
        border-radius: 2px;
    }

    .modal-xxl {
        max-width: 75% !important;
    }

    .modal-body {
        max-height: 70vh;
        overflow-y: auto;
    }

    .invoice-dropzone {
        border: 2px dashed #17a2b8;
        border-radius: 12px;
        padding: 36px 24px;
        background: #f7fcfd;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .invoice-dropzone.dragover {
        border-color: #007bff;
        background: #eef6ff;
        transform: scale(1.01);
    }

    #table_manual_invoice .form-control,
    #table_invoice_import_preview .form-control {
        min-width: 120px;
    }
</style>
