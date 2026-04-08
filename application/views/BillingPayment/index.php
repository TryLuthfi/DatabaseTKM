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
foreach ($getAllData as $row) {
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
                    <h1 class="m-0 text-dark" style="text-align: center;">MONITORING JATUH TEMPO PEMBAYARAN</h1>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>

    <div class="container-fluid">

        <div class="row">
            <div class="col-md-12">
                <!-- DIRECT CHAT DANGER -->
                <div class="card card-primary direct-chat direct-chat-primary shadow-lg">
                    <div class="card-header">
                        <h3 class="card-title">FILTER DATA</h3>

                        <div class="card-tools">
                            <button id="cardfiltercollapse" type="button" class="btn btn-tool"
                                data-card-widget="collapse">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>

                    <div class="card-body" style="margin-top:10px;">
                        <div class="container-fluid">
                            <!-- Info boxes -->
                            <div class="row">
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label
                                            style="display: flex; justify-content: center; align-items: center;">PROJECT
                                            / BOWHEER
                                        </label>
                                        <select id="filter_bowheer_up" class="select2" multiple="multiple"
                                            data-placeholder="Pilih bowheer" style="width: 100%;">
                                            <?php foreach ($unique_bowheer as $bowheer): ?>
                                                <option value="<?= $bowheer ?>"><?= $bowheer ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label
                                            style="display: flex; justify-content: center; align-items: center;">REGIONAL
                                        </label>
                                        <select id="filter_regional_up" class="select2" multiple="multiple"
                                            data-placeholder="Pilih bowheer" style="width: 100%;">
                                            <?php foreach ($unique_regional as $regional): ?>
                                                <option value="<?= $regional ?>"><?= $regional ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label style="display: flex; justify-content: center; align-items: center;">KOTA
                                        </label>
                                        <select id="filter_city_up" class="select2" multiple="multiple"
                                            data-placeholder="Pilih bowheer" style="width: 100%;">
                                            <?php foreach ($unique_city as $city): ?>
                                                <option value="<?= $city ?>"><?= $city ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label
                                            style="display: flex; justify-content: center; align-items: center;">PRIORITY</label>
                                        <select id="filter_priority_up" class="select2" multiple="multiple"
                                            data-placeholder="Pilih Prioritas" style="width: 100%;">
                                            <?php foreach ($unique_priority as $priority): ?>
                                                <option value="<?= $priority ?>"><?= $priority ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="modal-footer col-sm-12">
                                    <button type="button" id="reset_filter" class="btn btn-danger"
                                        data-dismiss="modal">Hapus</button>
                                    <button id="btnFilterDataProject" class="btn btn-primary"><i
                                            class="fa fa-spinner fa-spin loading" style="display:none"></i> Cari
                                    </button>
                                    <button type="button" class="btn btn-success"
                                        data-target="#modal-download-billing-report" data-toggle="modal">
                                        Download Report &nbsp; <i class="fas fa-print"></i>
                                    </button>
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
            <div class="row">
                <!-- /.col -->
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box mb-3">
                        <span class="info-box-icon bg-grey elevation-1"><i
                                class="fas fa-file-invoice-dollar"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">TOTAL BILLING</span>
                            <span class="info-box-number" id="dashboardTotalBilling">Rp. 0
                            </span>
                        </div>
                        <!-- /.info-box-content -->
                    </div>
                    </a>
                    <!-- /.info-box -->
                </div>

                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box mb-3 glow-green">
                        <span class="info-box-icon bg-grey elevation-1"><i
                                class="fas fa-file-invoice-dollar"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">BILLING ( P1 )</span>
                            <h4 class="info-box-number" style="color: #33cc33;" id="dashboardBillingP1">
                                Rp. 0
                            </h4>
                        </div>
                        <!-- /.info-box-content -->
                    </div>
                    </a>
                    <!-- /.info-box -->
                </div>

                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box mb-3 glow-red">
                        <span class="info-box-icon bg-grey elevation-1"><i class="fas fa-money-check-alt"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">BILLING ( P2 )</span>
                            <h4 class="info-box-number" style="color: #ce0808ff;" id="dashboardBillingP2">
                                Rp. 0
                            </h4>
                        </div>
                        <!-- /.info-box-content -->
                    </div>
                    </a>
                    <!-- /.info-box -->
                </div>

                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box mb-3">
                        <span class="info-box-icon bg-grey elevation-1"><i class="fas fa-money-check-alt"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">BILLING ( P3 )</span>
                            <span class="info-box-number" id="dashboardBillingP3">
                                0%
                            </span>
                        </div>
                        <!-- /.info-box-content -->
                    </div>
                    </a>
                    <!-- /.info-box -->
                </div>
            </div>
    </section>

    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1 class="m-0 text-dark" style="text-align: center;">RINCIAN PRIORITAS</h1>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Info boxes -->
            <div class="row">

                <div class="col-12">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">RINCIAN</h3>

                            <div class="card-tools">
                                <button id="cardfiltercollapse" type="button" class="btn btn-tool"
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
                                        <th colspan="3"
                                            style="text-align:center; background-color: aqua; color: #000000;">RINCIAN
                                            TAGIHAN</th>
                                        <th rowspan="2"
                                            style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                            GRAND TOTAL</th>
                                    </tr>
                                    <tr>
                                        <th style="color: #000000;">TAGIHAN ( P1 )<br>>= 75 HK</th>
                                        <th style="color: #000000;">TAGIHAN ( P2 )<br>>= 45 HK</th>
                                        <th style="color: #000000;">TAGIHAN ( P3 )<br>
                                            < 45 HK</th>
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
                <div class="d-flex gap-3">

                    <button type="button" class="btn btn-gradient-primary btn-lg shadow pulse mr-2" data-toggle="modal"
                        data-target="#modal-download-report">
                        <i class="fas fa-plus-circle mr-2"></i>
                        <strong>ADD INVOICE</strong>
                    </button>

                    <button type="button" class="btn btn-gradient-success btn-lg shadow pulse mr-2" data-toggle="modal"
                        data-target="#modal-payment">
                        <i class="fas fa-money-bill mr-2"></i>
                        <strong>ADD PAYMENT</strong>
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
                    <div class="card">
                        <!-- /.card-header -->
                        <div class="card-header p-0 pt-1">
                            <ul class="nav nav-tabs" id="invoice-status-tab" role="tablist">
                                <li class="pt-2 px-3">
                                    <h3 class="card-title">DETAIL</h3>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link active tab-status-invoice" data-status="open"
                                        href="javascript:void(0)">OPEN INVOICE</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link tab-status-invoice" data-status="partial"
                                        href="javascript:void(0)">PARTIAL PAYMENT</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link tab-status-invoice" data-status="paid"
                                        href="javascript:void(0)">PAID PAYMENT</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link tab-status-invoice" data-status="all"
                                        href="javascript:void(0)">ALL INVOICE</a>
                                </li>
                            </ul>
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="tabel_list_open_invoice" class="table table-bordered table-hover">
                                    <thead class="bg-info">
                                        <tr>
                                            <th>No</th>
                                            <th>Bowheer</th>
                                            <th>Invoice</th>
                                            <th>Price</th>
                                            <th>Regional</th>
                                            <th>Area</th>
                                            <th>Date Submit</th>
                                            <th>Due Date</th>
                                            <th>Agging</th>
                                            <th>Priority</th>
                                            <th>PO Number</th>
                                            <th>Status Invoice</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="8" style="text-align:right">Total:</th>
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
                        <div class="row">
                            <!-- ISI -->
                        </div>
                    </div>
    </section>

    <!-- MODAL TAMBAH INVOICE -->
    <div class="modal fade" id="modal-download-report" data-backdrop="static" tabindex="-1" role="dialog"
        aria-labelledby="exampleModalLongTitle" aria-hidden="true">
        <div class="modal-dialog modal-xxl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">TAMBAH INVOICE</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
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
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-download-billing-report" data-backdrop="static" tabindex="-1" role="dialog"
        aria-labelledby="modalBillingReportLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form id="formDownloadBillingReport" method="GET" target="_blank"
                    action="<?= base_url('BillingPayment/downloadBillingReport') ?>">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalBillingReportLabel">Download Billing Report</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Bowheer</label>
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
                                    <label>Regional</label>
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
                                    <label>Area</label>
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
                                    <label>Priority</label>
                                    <select name="priority[]" id="report_filter_priority" class="select2"
                                        multiple="multiple" data-placeholder="Pilih priority" style="width: 100%;">
                                        <?php foreach ($unique_priority as $priority): ?>
                                            <option value="<?= $priority ?>"><?= $priority ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Status Invoice</label>
                                    <select name="status_invoice" id="report_filter_status" class="form-control">
                                        <option value="open">Open</option>
                                        <option value="partial">Partial</option>
                                        <option value="paid">Paid</option>
                                        <option value="all">Semua Status</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Download Excel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-payment" tabindex="-1" role="dialog" aria-labelledby="modalPaymentLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xxl" role="document">
            <div class="modal-content">
                <form id="formBatchPayment">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalPaymentLabel">Tambah Pencairan Invoice</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span>&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
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
                                        <td colspan="8" class="text-center text-muted">Belum ada invoice dipilih</td>
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
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-success">Simpan Pembayaran</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-edit-partial-payment" tabindex="-1" role="dialog"
        aria-labelledby="modalEditPartialPaymentLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <form id="formEditPartialPayment">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalEditPartialPaymentLabel">Edit Partial Payment</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
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
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary">Update Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-detail-partial-payment" tabindex="-1" role="dialog"
        aria-labelledby="modalDetailPartialPaymentLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalDetailPartialPaymentLabel">Detail Partial Payment</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
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
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-edit-billing" tabindex="-1" role="dialog"
        aria-labelledby="modalEditBillingLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <form id="formEditBillingInvoice">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalEditBillingLabel">Edit Invoice</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
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
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Invoice</button>
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
            let totalAll = 0;

            data.each(function (row) {
                totalP1 += parseValue(row[2]);
                totalP2 += parseValue(row[3]);
                totalP3 += parseValue(row[4]);
                totalAll += parseValue(row[5]);
            });

            // isi footer sesuai kolom
            $(table.column(2).footer()).text(formatTitik(totalP1));
            $(table.column(3).footer()).text(formatTitik(totalP2));
            $(table.column(4).footer()).text(formatTitik(totalP3));
            $(table.column(5).footer()).text(formatTitik(totalAll));
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
                        if (['Price'].includes(col)) {
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
                                    case 'Date Submit':
                                        tbodyHtml += `<td>${row.tgl_submit_invoice || '-'}</td>`;
                                        break;
                                    case 'Due Date':
                                        tbodyHtml += `<td>${row.tgl_jatuh_tempo || '-'}</td>`;
                                        break;
                                    case 'Aging':
                                        tbodyHtml += `<td>${row.umur_invoice || '-'}</td>`;
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

                                const priceIdx = colIndex('Price');
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
            $('#filter_bowheer_up, #filter_regional_up, #filter_city_up, #filter_priority_up').val(null).trigger('change');
            setTimeout(() => $('#btnFilterDataProject').trigger('click'), 300);
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
        return '' + formatTitik(num);
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

                if (price === '' || date === '' || status === '') {
                    valid = false;
                    return false;
                }
            });

            if (!valid) {
                alert('Payment Price dan Date Payment wajib diisi semua');
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
                <td colspan="8" class="text-center text-muted">Belum ada invoice dipilih</td>
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

            if (paymentValue > invoiceValue) {
                paymentValue = invoiceValue;
                $('#edit_partial_payment_price').val(formatTitik(paymentValue, 2));
            }

            const remaining = Math.max(invoiceValue - paymentValue, 0);
            $('#edit_partial_remaining').val(formatTitik(remaining, 2));

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
    #tabel_list_open_invoice tfoot th {
        text-align: right;
        background-color: #f8f9fa;
        font-weight: bold;
    }

    /* upgrade warna header merah */

    .glow-red {
        border: 1px solid rgba(255, 0, 0, 0.7);
        box-shadow: 0 0 15px rgba(255, 0, 0, 0.6);
        transition: box-shadow 0.3s ease-in-out;
        border-radius: 10px;
    }

    .glow-red:hover {
        box-shadow: 0 0 25px rgba(255, 0, 0, 0.9);
    }

    /* upgrade warna header hijau */
    .glow-green {
        border: 1px solid rgba(0, 255, 0, 0.7);
        box-shadow: 0 0 15px rgba(0, 255, 0, 0.6);
        transition: box-shadow 0.3s ease-in-out;
        border-radius: 10px;
    }

    .glow-green:hover {
        box-shadow: 0 0 25px rgba(0, 255, 0, 0.9);
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
