<?php
$flashSuccess = $this->session->flashdata('success');
$flashError = $this->session->flashdata('error');
$canTambah = isset($this->myrepAccess) ? $this->myrepAccess->hasPermission('DRM_MyRep', 'TAMBAH') : true;
$canEdit = isset($this->myrepAccess) ? $this->myrepAccess->hasPermission('DRM_MyRep', 'EDIT') : true;
$canHapus = isset($this->myrepAccess) ? $this->myrepAccess->hasPermission('DRM_MyRep', 'HAPUS') : true;
$canApprovalAction = isset($this->myrepAccess) ? $this->myrepAccess->hasPermission('DRM_MyRep', 'APPROVAL') : true;

if (!function_exists('drmDetailBadgeClass')) {
    function drmDetailBadgeClass($status)
    {
        switch (strtoupper(trim((string) $status))) {
            case 'APPROVED':
            case 'DONE':
                return 'success';
            case 'REJECTED':
                return 'danger';
            case 'UPLOADED':
            case 'ON REVIEW':
            case 'WAITING HO':
                return 'warning';
            default:
                return 'secondary';
        }
    }
}

if (!function_exists('drmDocumentLabel')) {
    function drmDocumentLabel($row)
    {
        if ((int) ($row['is_document_not_required'] ?? 0) === 1) {
            return 'Tidak Dibutuhkan';
        }

        $status = strtoupper(trim((string) ($row['status_file'] ?? '')));
        if ($status === 'UPLOADED') {
            return 'ON REVIEW';
        }

        return $status !== '' ? $status : 'BELUM UPLOAD';
    }
}

if (!function_exists('drmScopeText')) {
    function drmScopeText($scopeKey)
    {
        return strtoupper(trim((string) $scopeKey)) === 'SUBFEEDER' ? 'Subfeeder' : 'Cluster';
    }
}
?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">Detail DRM MyRep</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="<?= base_url('DRM_MyRep') ?>" class="btn btn-outline-secondary">Kembali</a>
                    <?php if ($canHapus): ?>
                        <form method="post" action="<?= base_url('DRM_MyRep/deleteCluster') ?>" class="d-inline" onsubmit="return confirm('Hapus cluster ini beserta DRM dan seluruh flow MyRep terkait?');">
                            <input type="hidden" name="cluster_id" value="<?= (int) ($cluster['id_myrep_cluster'] ?? 0) ?>">
                            <button type="submit" class="btn btn-outline-danger">Hapus Cluster</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <?php if (!empty($flashSuccess)): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?= $flashSuccess ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
            <?php endif; ?>
            <?php if (!empty($flashError)): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?= $flashError ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
            <?php endif; ?>

            <style>
                .drm-header-card .card-header {
                    background: linear-gradient(135deg, #f8fbff, #eef6ff);
                    border-bottom: 1px solid #dbeafe;
                }

                .drm-edit-btn {
                    display: inline-flex;
                    align-items: center;
                    gap: .45rem;
                    padding: .55rem .95rem;
                    border-radius: 999px;
                    border: 1px solid rgba(255, 255, 255, 0.65);
                    background: linear-gradient(135deg, #1d4ed8, #2563eb);
                    color: #fff;
                    font-weight: 700;
                    box-shadow: 0 10px 24px rgba(37, 99, 235, 0.22);
                }

                .drm-edit-btn:hover,
                .drm-edit-btn:focus {
                    color: #fff;
                    background: linear-gradient(135deg, #1e40af, #1d4ed8);
                }

                .drm-info-grid strong {
                    display: block;
                    margin-bottom: .2rem;
                    color: #334155;
                }

                .drm-info-grid > div {
                    margin-bottom: 1rem;
                }

                .drm-scope-tabs .nav-link {
                    border-radius: 12px 12px 0 0;
                    font-weight: 700;
                }

                .drm-scope-tabs .nav-link.active {
                    background: #2563eb;
                    color: #fff;
                }

                .drm-doc-card .card-header,
                .drm-boq-card .card-header {
                    background: #eff6ff;
                    color: #1e3a8a;
                }

                .drm-doc-card .table thead th,
                .drm-boq-card .table thead th {
                    white-space: nowrap;
                }

                .drm-dropzone {
                    position: relative;
                    background: linear-gradient(135deg, #f8fbff, #eff6ff);
                    border: 2px dashed #93c5fd;
                    border-radius: 16px;
                    padding: 1rem;
                    transition: all .2s ease;
                    cursor: pointer;
                }

                .drm-dropzone.dragover {
                    border-color: #2563eb;
                    background: linear-gradient(135deg, #dbeafe, #eff6ff);
                }

                .drm-dropzone input[type="file"] {
                    position: absolute;
                    inset: 0;
                    opacity: 0;
                    cursor: pointer;
                }

                .drm-dropzone-content {
                    pointer-events: none;
                    text-align: center;
                }

                .drm-dropzone-icon {
                    font-size: 1.8rem;
                    color: #2563eb;
                    margin-bottom: .5rem;
                }

                .drm-dropzone-title {
                    font-weight: 700;
                    color: #1d4ed8;
                    margin-bottom: .25rem;
                }

                .drm-dropzone-text {
                    color: #64748b;
                    font-size: .9rem;
                    margin-bottom: .35rem;
                }

                .drm-dropzone-file {
                    color: #0f766e;
                    font-weight: 600;
                    font-size: .88rem;
                }

                .drm-modal .modal-content {
                    border: 0;
                    border-radius: 18px;
                    overflow: hidden;
                    box-shadow: 0 18px 45px rgba(0, 0, 0, 0.18);
                }

                .drm-modal .modal-body {
                    background: #f6f8fb;
                    padding: 1.25rem;
                }

                .drm-modal .modal-footer {
                    border-top: 0;
                    background: #eef2f7;
                }

                .drm-form-box {
                    background: #fff;
                    border: 1px solid #e5edf6;
                    border-radius: 14px;
                    padding: 1rem 1.1rem;
                    margin-bottom: 1rem;
                }

                .drm-form-box:last-child {
                    margin-bottom: 0;
                }

                .drm-form-box__title {
                    font-weight: 700;
                    color: #1f2937;
                    margin-bottom: .85rem;
                }
                .boq-zero-cell {
                    background-color: #fdecec !important;
                    color: #9f1239;
                    font-weight: 600;
                }
                .doc-history-list {
                    list-style: none;
                    margin: 0;
                    padding: 0;
                }

                .doc-history-item {
                    border-left: 3px solid #d8e3ee;
                    padding-left: 1rem;
                    margin-bottom: 1rem;
                }

                .doc-history-item:last-child {
                    margin-bottom: 0;
                }

                .doc-history-title {
                    font-weight: 700;
                    color: #1f2937;
                }

                .doc-history-meta {
                    color: #6b7280;
                    font-size: .86rem;
                    margin-bottom: .2rem;
                }

                .drm-bulk-summary {
                    display: flex;
                    align-items: flex-start;
                    justify-content: space-between;
                    gap: 1rem;
                    padding: 1rem 1.1rem;
                    border: 1px solid #dbeafe;
                    border-radius: 18px;
                    background: linear-gradient(135deg, #f8fbff, #eef6ff);
                    margin-bottom: 1rem;
                }

                .drm-bulk-summary__title {
                    font-size: 1rem;
                    font-weight: 800;
                    color: #0f172a;
                    margin-bottom: .2rem;
                }

                .drm-bulk-summary__text {
                    margin: 0;
                    color: #64748b;
                    font-size: .92rem;
                }

                .drm-bulk-summary__badge {
                    flex-shrink: 0;
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    min-width: 52px;
                    min-height: 52px;
                    padding: .4rem .85rem;
                    border-radius: 16px;
                    background: #1d4ed8;
                    color: #fff;
                    font-size: 1.05rem;
                    font-weight: 800;
                    box-shadow: 0 12px 24px rgba(29, 78, 216, 0.22);
                }

                .drm-bulk-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(340px, 1fr));
                    gap: 1rem;
                }

                .drm-bulk-card {
                    border: 1px solid #dbe4f0;
                    border-radius: 20px;
                    background: #fff;
                    box-shadow: 0 16px 34px rgba(15, 23, 42, 0.07);
                    overflow: hidden;
                }

                .drm-bulk-card__header {
                    display: flex;
                    align-items: flex-start;
                    justify-content: space-between;
                    gap: .9rem;
                    padding: 1rem 1.1rem .85rem;
                    background: linear-gradient(135deg, #fbfdff, #f4f8fc);
                    border-bottom: 1px solid #e5edf6;
                }

                .drm-bulk-card__eyebrow {
                    font-size: .72rem;
                    font-weight: 800;
                    letter-spacing: .08em;
                    text-transform: uppercase;
                    color: #64748b;
                }

                .drm-bulk-card__title {
                    margin: .2rem 0 0;
                    font-size: 1rem;
                    font-weight: 700;
                    color: #0f172a;
                }

                .drm-bulk-card__body {
                    padding: 1rem 1.1rem 1.1rem;
                }
            </style>

            <div class="card card-primary shadow-sm drm-header-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">Header DRM</h3>
                    <?php if ($canEdit): ?>
                        <button type="button" class="btn btn-sm drm-edit-btn" data-toggle="modal" data-target="#modal-drm-edit">
                            <i class="fas fa-pen"></i>
                            Edit DRM
                        </button>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <div class="row drm-info-grid">
                        <div class="col-md-4"><strong>Cluster</strong><div><?= htmlspecialchars((string) ($cluster['cluster_name'] ?? '-')) ?></div></div>
                        <div class="col-md-2"><strong>Kota</strong><div><?= htmlspecialchars((string) ($cluster['city_name'] ?? '-')) ?></div></div>
                        <div class="col-md-2"><strong>Regional</strong><div><?= htmlspecialchars((string) ($cluster['regional_name'] ?? '-')) ?></div></div>
                        <div class="col-md-2"><strong>Province</strong><div><?= !empty($cluster['province_name']) ? htmlspecialchars((string) $cluster['province_name']) : '-' ?></div></div>
                        <div class="col-md-2"><strong>Status Flow</strong><div><?= !empty($cluster['status_current']) ? htmlspecialchars((string) $cluster['status_current']) : '-' ?></div></div>
                    </div>
                    <hr>
                    <div class="row drm-info-grid">
                        <div class="col-md-2"><strong>Cluster Code</strong><div><?= !empty($cluster['cluster_code']) ? htmlspecialchars((string) $cluster['cluster_code']) : '-' ?></div></div>
                        <div class="col-md-2"><strong>Team</strong><div><?= !empty($cluster['team_name']) ? htmlspecialchars((string) $cluster['team_name']) : '-' ?></div></div>
                        <div class="col-md-2"><strong>Chief</strong><div><?= !empty($cluster['chief']) ? htmlspecialchars((string) $cluster['chief']) : '-' ?></div></div>
                        <div class="col-md-2"><strong>RPM</strong><div><?= !empty($cluster['rpm']) ? htmlspecialchars((string) $cluster['rpm']) : '-' ?></div></div>
                        <div class="col-md-2"><strong>SM</strong><div><?= !empty($cluster['sm']) ? htmlspecialchars((string) $cluster['sm']) : '-' ?></div></div>
                        <div class="col-md-2"><strong>SPV</strong><div><?= !empty($cluster['spv']) ? htmlspecialchars((string) $cluster['spv']) : '-' ?></div></div>
                    </div>
                    <hr>
                    <div class="row drm-info-grid">
                        <div class="col-md-3"><strong>PIC Project</strong><div><?= !empty($cluster['pic_project']) ? htmlspecialchars((string) $cluster['pic_project']) : '-' ?></div></div>
                        <div class="col-md-3"><strong>NTP Name</strong><div><?= !empty($cluster['ntp_name']) ? htmlspecialchars((string) $cluster['ntp_name']) : '-' ?></div></div>
                        <div class="col-md-3"><strong>NTP Date</strong><div><?= !empty($cluster['ntp_date']) ? htmlspecialchars((string) $cluster['ntp_date']) : '-' ?></div></div>
                        <div class="col-md-3"><strong>NTP Year</strong><div><?= !empty($cluster['ntp_year']) ? htmlspecialchars((string) $cluster['ntp_year']) : '-' ?></div></div>
                    </div>
                    <hr>
                    <div class="row drm-info-grid">
                        <div class="col-md-2"><strong>HP Plan</strong><div><?= number_format((float) ($cluster['hp_plan'] ?? 0), 0, ',', '.') ?></div></div>
                        <div class="col-md-2"><strong>HP Donasi</strong><div><?= number_format((float) ($cluster['hp_donasi'] ?? 0), 0, ',', '.') ?></div></div>
                        <div class="col-md-2"><strong>HP DRM</strong><div><?= !is_null($cluster['homepass_drm'] ?? null) ? number_format((float) $cluster['homepass_drm'], 0, ',', '.') : '-' ?></div></div>
                        <div class="col-md-3"><strong>Tanggal DRM</strong><div><?= !empty($cluster['drm_date']) ? htmlspecialchars((string) $cluster['drm_date']) : '-' ?></div></div>
                        <div class="col-md-4">
                            <strong>Screenshoot Astri</strong>
                            <div>
                                <?php if (!empty($cluster['screenshot_astri_path'])): ?>
                                    <a href="<?= base_url((string) $cluster['screenshot_astri_path']) ?>" target="_blank">
                                        <img src="<?= base_url((string) $cluster['screenshot_astri_path']) ?>" alt="Screenshoot Astri" style="max-width: 220px; max-height: 140px; border-radius: 8px; border: 1px solid #dbeafe; margin-top: 6px;">
                                    </a>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-3"><strong>Status DRM</strong><div><?= !empty($cluster['display_status_drm']) ? htmlspecialchars((string) $cluster['display_status_drm']) : (!empty($cluster['status_drm']) ? htmlspecialchars((string) $cluster['status_drm']) : 'WAITING INPUT') ?></div></div>
                    </div>
                    <hr>
                    <div class="row drm-info-grid">
                        <div class="col-md-4"><strong>Released At</strong><div><?= !empty($cluster['released_at']) ? htmlspecialchars((string) $cluster['released_at']) : '-' ?></div></div>
                        <div class="col-md-4"><strong>Remark DRM</strong><div><?= !empty($cluster['remark_drm']) ? nl2br(htmlspecialchars((string) $cluster['remark_drm'])) : '-' ?></div></div>
                        <div class="col-md-4"><strong>Outstanding Progress</strong><div><?= !empty($cluster['outstanding_progress']) ? nl2br(htmlspecialchars((string) $cluster['outstanding_progress'])) : '-' ?></div></div>
                    </div>
                </div>
            </div>

            <?php
            $boqClusterTotal = 0;
            $boqSubfeederTotal = 0;
            $boqClusterItems = (array) (($drmScopes['CLUSTER']['boqItems'] ?? []));
            $boqSubfeederItems = (array) (($drmScopes['SUBFEEDER']['boqItems'] ?? []));
            foreach ($boqClusterItems as $clusterBoqItem) {
                $boqClusterTotal += (float) ($clusterBoqItem['qty_boq'] ?? 0);
            }
            foreach ($boqSubfeederItems as $subfeederBoqItem) {
                $boqSubfeederTotal += (float) ($subfeederBoqItem['qty_boq'] ?? 0);
            }
            $boqTotal = $boqClusterTotal + $boqSubfeederTotal;

            $boqCombinedRowsMap = [];
            foreach ($boqClusterItems as $clusterBoqItem) {
                $boqItemId = (int) ($clusterBoqItem['id_boq_item'] ?? 0);
                if ($boqItemId <= 0) {
                    continue;
                }
                if (!isset($boqCombinedRowsMap[$boqItemId])) {
                    $boqCombinedRowsMap[$boqItemId] = [
                        'id_boq_item' => $boqItemId,
                        'item_name' => (string) ($clusterBoqItem['item_name'] ?? '-'),
                        'item_type' => (string) ($clusterBoqItem['item_type'] ?? '-'),
                        'sort_no' => (int) ($clusterBoqItem['sort_no'] ?? 0),
                        'qty_cluster' => 0,
                        'qty_subfeeder' => 0,
                    ];
                }
                $boqCombinedRowsMap[$boqItemId]['qty_cluster'] = (float) ($clusterBoqItem['qty_boq'] ?? 0);
            }
            foreach ($boqSubfeederItems as $subfeederBoqItem) {
                $boqItemId = (int) ($subfeederBoqItem['id_boq_item'] ?? 0);
                if ($boqItemId <= 0) {
                    continue;
                }
                if (!isset($boqCombinedRowsMap[$boqItemId])) {
                    $boqCombinedRowsMap[$boqItemId] = [
                        'id_boq_item' => $boqItemId,
                        'item_name' => (string) ($subfeederBoqItem['item_name'] ?? '-'),
                        'item_type' => (string) ($subfeederBoqItem['item_type'] ?? '-'),
                        'sort_no' => (int) ($subfeederBoqItem['sort_no'] ?? 0),
                        'qty_cluster' => 0,
                        'qty_subfeeder' => 0,
                    ];
                }
                $boqCombinedRowsMap[$boqItemId]['qty_subfeeder'] = (float) ($subfeederBoqItem['qty_boq'] ?? 0);
            }

            $boqCombinedRows = array_values(array_filter($boqCombinedRowsMap, static function ($row) {
                $qtyCluster = (float) ($row['qty_cluster'] ?? 0);
                $qtySubfeeder = (float) ($row['qty_subfeeder'] ?? 0);
                return ($qtyCluster + $qtySubfeeder) > 0;
            }));
            usort($boqCombinedRows, static function ($a, $b) {
                $sortA = (int) ($a['sort_no'] ?? 0);
                $sortB = (int) ($b['sort_no'] ?? 0);
                if ($sortA === $sortB) {
                    return (int) ($a['id_boq_item'] ?? 0) <=> (int) ($b['id_boq_item'] ?? 0);
                }
                return $sortA <=> $sortB;
            });
            ?>

            <div class="card card-outline card-info shadow-sm">
                <div class="card-header">
                    <h3 class="card-title mb-0">Ringkasan BOQ</h3>
                </div>
                <div class="card-body">
                    <?php if (!empty($boqCombinedRows)): ?>
                        <div class="drm-form-box mb-0">
                            <div class="drm-form-box__title">Baseline BOQ Implementasi Cluster</div>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Nama Item</th>
                                            <th>Jenis</th>
                                            <th>BOQ Cluster</th>
                                            <th>BOQ Subfeeder</th>
                                            <th>Total BOQ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($boqCombinedRows as $index => $item): ?>
                                            <?php
                                            $qtyCluster = (float) ($item['qty_cluster'] ?? 0);
                                            $qtySubfeeder = (float) ($item['qty_subfeeder'] ?? 0);
                                            $qtyItemTotal = $qtyCluster + $qtySubfeeder;
                                            ?>
                                            <tr>
                                                <td><?= $index + 1 ?></td>
                                                <td><?= htmlspecialchars((string) ($item['item_name'] ?? '-')) ?></td>
                                                <td><?= htmlspecialchars((string) ($item['item_type'] ?? '-')) ?></td>
                                                <td class="<?= abs($qtyCluster) < 0.00001 ? 'boq-zero-cell' : '' ?>">
                                                    <?= abs($qtyCluster) < 0.00001 ? '-' : number_format($qtyCluster, 0, ',', '.') ?>
                                                </td>
                                                <td class="<?= abs($qtySubfeeder) < 0.00001 ? 'boq-zero-cell' : '' ?>">
                                                    <?= abs($qtySubfeeder) < 0.00001 ? '-' : number_format($qtySubfeeder, 0, ',', '.') ?>
                                                </td>
                                                <td class="<?= abs($qtyItemTotal) < 0.00001 ? 'boq-zero-cell' : '' ?>">
                                                    <?= abs($qtyItemTotal) < 0.00001 ? '-' : number_format($qtyItemTotal, 0, ',', '.') ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <tr class="table-secondary font-weight-bold">
                                            <td colspan="3" class="text-right">TOTAL</td>
                                            <td class="<?= abs($boqClusterTotal) < 0.00001 ? 'boq-zero-cell' : '' ?>">
                                                <?= abs($boqClusterTotal) < 0.00001 ? '-' : number_format($boqClusterTotal, 0, ',', '.') ?>
                                            </td>
                                            <td class="<?= abs($boqSubfeederTotal) < 0.00001 ? 'boq-zero-cell' : '' ?>">
                                                <?= abs($boqSubfeederTotal) < 0.00001 ? '-' : number_format($boqSubfeederTotal, 0, ',', '.') ?>
                                            </td>
                                            <td class="<?= abs($boqTotal) < 0.00001 ? 'boq-zero-cell' : '' ?>">
                                                <?= abs($boqTotal) < 0.00001 ? '-' : number_format($boqTotal, 0, ',', '.') ?>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card card-outline card-primary shadow-sm">
                <div class="card-header p-0">
                    <ul class="nav nav-tabs drm-scope-tabs px-3 pt-2 border-bottom-0" role="tablist">
                        <?php $tabIndex = 0; ?>
                        <?php foreach ($drmScopes as $scopeKey => $scope): ?>
                            <li class="nav-item">
                                <a class="nav-link <?= $tabIndex === 0 ? 'active' : '' ?>" data-toggle="tab" href="#tab-drm-<?= strtolower($scopeKey) ?>" role="tab">
                                    <?= htmlspecialchars((string) ($scope['label'] ?? drmScopeText($scopeKey))) ?>
                                </a>
                            </li>
                            <?php $tabIndex++; ?>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <?php $tabIndex = 0; ?>
                        <?php foreach ($drmScopes as $scopeKey => $scope): ?>
                            <?php
                            $scopeLabel = (string) ($scope['label'] ?? drmScopeText($scopeKey));
                            $documentRows = (array) ($scope['documentRows'] ?? []);
                            $boqHeader = (array) ($scope['boqHeader'] ?? []);
                            $boqItems = (array) ($scope['boqItems'] ?? []);
                            $boqBaselineItems = (array) ($scope['boqBaselineItems'] ?? []);
                            $apdBoqFile = (array) ($scope['apdBoqFile'] ?? []);
                            $scopeReady = !empty($scope['isReady']);
                            $boqReviewStatus = strtoupper(trim((string) ($boqHeader['review_status'] ?? 'DRAFT')));
                            $isBoqLocked = $boqReviewStatus === 'APPROVED';
                            $hasApdBoqFile = !empty($apdBoqFile['id_doc_file']);
                            $bulkUploadableRows = [];
                            $reviewableRows = [];
                            $downloadableRows = [];
                            foreach ($documentRows as $docRow) {
                                $docNameUpper = strtoupper(trim((string) ($docRow['doc_name'] ?? '')));
                                $docStatus = drmDocumentLabel($docRow);
                                $docRawStatus = strtoupper(trim((string) ($docRow['status_file'] ?? '')));
                                $isApdBoqDoc = $docNameUpper === 'APD BOQ';

                                if (!$isApdBoqDoc && (in_array($docStatus, ['BELUM UPLOAD'], true) || $docRawStatus === 'REJECTED')) {
                                    $bulkUploadableRows[] = $docRow;
                                }

                                if (!$isApdBoqDoc && !empty($docRow['id_doc_file']) && in_array($docRawStatus, ['UPLOADED', 'REJECTED'], true)) {
                                    $reviewableRows[] = $docRow;
                                }

                                if (!empty($docRow['file_path'])) {
                                    $downloadableRows[] = $docRow;
                                }
                            }
                            ?>
                            <div class="tab-pane fade <?= $tabIndex === 0 ? 'show active' : '' ?>" id="tab-drm-<?= strtolower($scopeKey) ?>" role="tabpanel">
                                <?php if (!$scopeReady): ?>
                                    <div class="alert alert-warning mb-0">
                                        Struktur <?= htmlspecialchars($scopeLabel) ?> belum siap. Jalankan patch database DRM subfeeder dulu.
                                    </div>
                                <?php else: ?>
                                    <div class="card card-outline card-primary shadow-sm drm-doc-card">
                                        <div class="card-header">
                                            <h3 class="card-title mb-0">Dokumen <?= htmlspecialchars($scopeLabel) ?></h3>
                                        </div>
                                        <div class="card-body">
                                            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3" style="gap:.5rem;">
                                                <div class="small text-muted">
                                                    Bulk upload & approve all tidak mencakup <strong>APD BOQ</strong> dan <strong>Manual BOQ</strong>.
                                                </div>
                                                <div class="d-flex flex-wrap" style="gap:.45rem;">
                                                    <?php if (!empty($downloadableRows)): ?>
                                                        <a href="<?= base_url('DRM_MyRep/downloadDocumentBundle/' . (int) $cluster['id_myrep_cluster'] . '/' . urlencode((string) $scopeKey)) ?>" class="btn btn-sm btn-outline-dark">
                                                            Download RAR
                                                        </a>
                                                    <?php endif; ?>
                                                    <?php if ($canTambah && !empty($bulkUploadableRows)): ?>
                                                        <button type="button" class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="#modal-drm-bulk-upload-<?= strtolower($scopeKey) ?>">
                                                            Bulk Upload
                                                        </button>
                                                    <?php endif; ?>
                                                    <?php if ($canApprove && $canApprovalAction && !empty($reviewableRows)): ?>
                                                        <form method="post" action="<?= base_url('DRM_MyRep/approveAllDocuments') ?>" class="d-inline">
                                                            <input type="hidden" name="cluster_id" value="<?= (int) ($cluster['id_myrep_cluster'] ?? 0) ?>">
                                                            <input type="hidden" name="scope_type" value="<?= htmlspecialchars((string) $scopeKey) ?>">
                                                            <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Approve semua dokumen upload/reject untuk scope ini? APD BOQ dan Manual BOQ tidak ikut.');">
                                                                Approve All
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="table-responsive">
                                                <table class="table table-bordered table-hover">
                                                    <thead>
                                                        <tr>
                                                            <th>Dokumen</th>
                                                            <th>Catatan</th>
                                                            <th>Status</th>
                                                            <th>File</th>
                                                            <th>Upload / Update</th>
                                                            <th>Remarks</th>
                                                            <?php if ($canApprove && $canApprovalAction): ?><th>Review</th><?php endif; ?>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($documentRows as $row): ?>
                                                            <?php
                                                            $docStatus = drmDocumentLabel($row);
                                                            $docRawStatus = strtoupper(trim((string) ($row['status_file'] ?? '')));
                                                            $docCanUpload = $docStatus === 'BELUM UPLOAD' || $docRawStatus === 'REJECTED';
                                                            ?>
                                                            <tr>
                                                                <td><strong><?= htmlspecialchars((string) ($row['doc_name'] ?? '-')) ?></strong></td>
                                                                <td><?= !empty($row['doc_requirement_note']) ? htmlspecialchars((string) $row['doc_requirement_note']) : '-' ?></td>
                                                                <td><span class="badge badge-<?= drmDetailBadgeClass($docStatus) ?>"><?= htmlspecialchars($docStatus) ?></span></td>
                                                                <td>
                                                                    <?php if (!empty($row['file_name'])): ?>
                                                                        <div><?= htmlspecialchars((string) $row['file_name']) ?></div>
                                                                        <a href="<?= base_url('DRM_MyRep/previewDocument/' . (int) $row['id_doc_file']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary mt-1">Preview</a>
                                                                        <button
                                                                            type="button"
                                                                            class="btn btn-sm btn-outline-dark mt-1 js-doc-history"
                                                                            data-toggle="modal"
                                                                            data-target="#modal-doc-history"
                                                                            data-doc-name="<?= htmlspecialchars((string) ($row['doc_name'] ?? ''), ENT_QUOTES) ?>"
                                                                            data-history='<?= htmlspecialchars(json_encode(!empty($row['id_doc_file']) ? $this->MDRM_MyRep->getDrmFileLogs((int) $row['id_doc_file']) : []), ENT_QUOTES) ?>'>
                                                                            History
                                                                        </button>
                                                                    <?php else: ?>
                                                                        <span class="text-muted">Belum ada file</span>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td style="min-width:320px;">
                                                                    <?php if (($row['doc_name'] ?? '') === 'APD BOQ' && $boqReady): ?>
                                                                        <?php if ($canTambah && !$isBoqLocked): ?>
                                                                            <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#modal-apd-boq-package-<?= strtolower($scopeKey) ?>">Kelola APD BOQ & BOQ Manual</button>
                                                                        <?php else: ?>
                                                                            <span class="text-success small font-weight-bold">BOQ sudah approved</span>
                                                                        <?php endif; ?>
                                                                        <div class="small text-muted mt-2">
                                                                            Status BOQ:
                                                                            <span class="badge badge-<?= drmDetailBadgeClass($boqReviewStatus) ?>"><?= htmlspecialchars($boqReviewStatus !== '' ? $boqReviewStatus : 'DRAFT') ?></span>
                                                                        </div>
                                                                        <?php if ($canApprove && $canApprovalAction && !empty($boqHeader['id_drm_boq']) && in_array($boqReviewStatus, ['WAITING HO', 'REJECTED'], true)): ?>
                                                                            <button type="button" class="btn btn-sm btn-outline-success mt-2" data-toggle="modal" data-target="#modal-boq-review-<?= strtolower($scopeKey) ?>">Review BOQ</button>
                                                                        <?php endif; ?>
                                                                        <?php if (!empty($boqHeader['ho_review_remark'])): ?>
                                                                            <div class="small text-info mt-1">Catatan HO: <?= htmlspecialchars((string) $boqHeader['ho_review_remark']) ?></div>
                                                                        <?php endif; ?>
                                                                    <?php elseif ($canTambah && $docCanUpload): ?>
                                                                        <button
                                                                            type="button"
                                                                            class="btn btn-sm btn-primary js-open-drm-upload-modal"
                                                                            data-toggle="modal"
                                                                            data-target="#modal-drm-upload"
                                                                            data-scope-type="<?= htmlspecialchars($scopeKey, ENT_QUOTES) ?>"
                                                                            data-doc-item-id="<?= (int) $row['id_doc_item'] ?>"
                                                                            data-doc-name="<?= htmlspecialchars((string) ($row['doc_name'] ?? ''), ENT_QUOTES) ?>"
                                                                            data-file-name="<?= htmlspecialchars((string) ($row['file_name'] ?? ''), ENT_QUOTES) ?>"
                                                                            data-remark="<?= htmlspecialchars((string) ($row['remark'] ?? ''), ENT_QUOTES) ?>">
                                                                            Upload
                                                                        </button>
                                                                    <?php else: ?>
                                                                        <span class="text-muted small">Upload tidak tersedia</span>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td style="min-width:220px;">
                                                                    <?= !empty($row['remark']) ? nl2br(htmlspecialchars((string) $row['remark'])) : '-' ?>
                                                                </td>
                                                                <?php if ($canApprove && $canApprovalAction): ?>
                                                                    <td style="min-width:220px;">
                                                                        <?php if (($row['doc_name'] ?? '') === 'APD BOQ'): ?>
                                                                            <span class="text-info small font-weight-bold">Review mengikuti approval BOQ</span>
                                                                        <?php elseif (!empty($row['id_doc_file']) && $docRawStatus === 'UPLOADED'): ?>
                                                                            <div class="d-flex flex-wrap" style="gap:.35rem;">
                                                                                <form method="post" action="<?= base_url('DRM_MyRep/approveDocument') ?>" class="d-inline">
                                                                                    <input type="hidden" name="cluster_id" value="<?= (int) ($cluster['id_myrep_cluster'] ?? 0) ?>">
                                                                                    <input type="hidden" name="id_doc_file" value="<?= (int) ($row['id_doc_file'] ?? 0) ?>">
                                                                                    <input type="hidden" name="scope_type" value="<?= htmlspecialchars((string) $scopeKey) ?>">
                                                                                    <input type="hidden" name="remark" value="<?= htmlspecialchars((string) ($row['remark'] ?? ''), ENT_QUOTES) ?>">
                                                                                    <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                                                                </form>
                                                                                <form method="post" action="<?= base_url('DRM_MyRep/rejectDocument') ?>" class="d-inline" onsubmit="return confirm('Reject dokumen ini?');">
                                                                                    <input type="hidden" name="cluster_id" value="<?= (int) ($cluster['id_myrep_cluster'] ?? 0) ?>">
                                                                                    <input type="hidden" name="id_doc_file" value="<?= (int) ($row['id_doc_file'] ?? 0) ?>">
                                                                                    <input type="hidden" name="scope_type" value="<?= htmlspecialchars((string) $scopeKey) ?>">
                                                                                    <input type="hidden" name="remark" value="<?= htmlspecialchars((string) ($row['remark'] ?? ''), ENT_QUOTES) ?>">
                                                                                    <button type="submit" class="btn btn-sm btn-danger">Reject</button>
                                                                                </form>
                                                                            </div>
                                                                        <?php elseif ($docRawStatus === 'APPROVED'): ?>
                                                                            <span class="text-success small font-weight-bold">Sudah approved</span>
                                                                        <?php elseif ($docRawStatus === 'REJECTED'): ?>
                                                                            <span class="text-danger small font-weight-bold">Sudah rejected</span>
                                                                        <?php else: ?>
                                                                            <span class="text-muted small">Belum ada review langsung</span>
                                                                        <?php endif; ?>
                                                                    </td>
                                                                <?php endif; ?>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                        <?php if (empty($documentRows)): ?>
                                                            <tr><td colspan="<?= ($canApprove && $canApprovalAction) ? '7' : '6' ?>" class="text-center text-muted">Belum ada dokumen <?= htmlspecialchars($scopeLabel) ?>.</td></tr>
                                                        <?php endif; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <?php $tabIndex++; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php if ($canEdit): ?>
<div class="modal fade" id="modal-drm-edit" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content drm-modal">
            <form method="post" action="<?= base_url(!empty($cluster['id_drm']) ? 'DRM_MyRep/updateDrm' : 'DRM_MyRep/saveDrm') ?>">
                <input type="hidden" name="cluster_id" value="<?= (int) $cluster['id_myrep_cluster'] ?>">
                <input type="hidden" name="id_drm" value="<?= (int) ($cluster['id_drm'] ?? 0) ?>">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><?= !empty($cluster['id_drm']) ? 'Edit Header DRM' : 'Lengkapi Header DRM' ?></h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="drm-form-box">
                        <div class="drm-form-box__title">Informasi Cluster</div>
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group mb-md-0">
                                    <label>Nama Cluster</label>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars((string) ($cluster['cluster_name'] ?? '-')) ?>" readonly>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-0">
                                    <label>Kota</label>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars((string) ($cluster['city_name'] ?? '-')) ?>" readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="drm-form-box">
                        <div class="drm-form-box__title">Header DRM</div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Tanggal DRM</label>
                                    <input type="date" name="drm_date" class="form-control" value="<?= htmlspecialchars((string) ($cluster['drm_date'] ?? '')) ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>HP DRM</label>
                                    <input type="number" min="0" step="1" name="homepass_drm" class="form-control" value="<?= htmlspecialchars((string) ($cluster['homepass_drm'] ?? '')) ?>" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Nama OLT</label>
                                    <input type="text" name="nama_olt" class="form-control" value="<?= htmlspecialchars((string) ($cluster['nama_olt'] ?? '')) ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-0">
                                    <label>Status DRM</label>
                                    <select name="status_drm" class="form-control">
                                        <?php
                                        $statusOptions = ['WAITING DOC', 'WAITING APPROVE', 'COMPLETE', 'REJECTED'];
                                        $currentStatusDrm = strtoupper(trim((string) ($cluster['status_drm'] ?? 'WAITING DOC')));
                                        foreach ($statusOptions as $statusOption):
                                        ?>
                                            <option value="<?= htmlspecialchars($statusOption) ?>" <?= $currentStatusDrm === $statusOption ? 'selected' : '' ?>><?= htmlspecialchars($statusOption) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-group mb-0">
                                    <label>Remark DRM</label>
                                    <textarea name="remark_drm" rows="3" class="form-control"><?= htmlspecialchars((string) ($cluster['remark_drm'] ?? '')) ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary btn-sm"><?= !empty($cluster['id_drm']) ? 'Update DRM' : 'Simpan DRM' ?></button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($canTambah): ?>
<div class="modal fade" id="modal-drm-upload" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content drm-modal">
            <form method="post" action="<?= base_url('DRM_MyRep/uploadDocument') ?>" enctype="multipart/form-data" id="form-drm-upload">
                <input type="hidden" name="cluster_id" value="<?= (int) $cluster['id_myrep_cluster'] ?>">
                <input type="hidden" name="scope_type" id="drm_upload_scope_type" value="CLUSTER">
                <input type="hidden" name="id_doc_item" id="drm_upload_doc_item_id">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Upload Dokumen DRM</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2"><strong>Scope:</strong> <span id="drm_upload_scope_label">Cluster</span></div>
                    <div class="mb-3"><strong>Dokumen:</strong> <span id="drm_upload_doc_name">-</span></div>
                    <div class="mb-3"><strong>File Saat Ini:</strong> <span id="drm_upload_current_file">-</span></div>
                    <div class="drm-dropzone js-dropzone">
                        <input type="file" name="file" class="js-dropzone-input">
                        <div class="drm-dropzone-content">
                            <div class="drm-dropzone-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                            <div class="drm-dropzone-title">Drag & drop dokumen di sini</div>
                            <div class="drm-dropzone-text">Atau klik area ini untuk memilih file</div>
                            <div class="drm-dropzone-file js-dropzone-label">Belum ada file dipilih</div>
                        </div>
                    </div>
                    <div class="form-group mt-3">
                        <label>Remark Upload</label>
                        <textarea name="remark" id="drm_upload_remark" class="form-control" rows="3" placeholder="Catatan upload dokumen"></textarea>
                    </div>
                    <div class="form-group form-check mb-0">
                        <input type="checkbox" class="form-check-input" id="drm_upload_not_required" name="is_document_not_required" value="1">
                        <label class="form-check-label" for="drm_upload_not_required">Tidak dibutuhkan</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary btn-sm">Simpan Dokumen</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="modal fade" id="modal-doc-history" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content drm-modal">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title">History Dokumen</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="mb-3"><strong>Dokumen:</strong> <span id="history_doc_label">-</span></div>
                <ul class="doc-history-list" id="history_doc_items">
                    <li class="text-muted">Belum ada history.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php foreach ($drmScopes as $scopeKey => $scope): ?>
    <?php
    $scopeReady = !empty($scope['isReady']);
    if (!$scopeReady || !$boqReady) {
        continue;
    }
    $scopeLabel = (string) ($scope['label'] ?? drmScopeText($scopeKey));
    $boqHeader = (array) ($scope['boqHeader'] ?? []);
    $boqItems = (array) ($scope['boqItems'] ?? []);
    $boqReviewStatus = strtoupper(trim((string) ($boqHeader['review_status'] ?? 'DRAFT')));
    $isBoqLocked = $boqReviewStatus === 'APPROVED';
    $apdBoqFile = (array) ($scope['apdBoqFile'] ?? []);
    $hasApdBoqFile = !empty($apdBoqFile['id_doc_file']);
    ?>
    <?php if ($canTambah): ?>
    <div class="modal fade" id="modal-apd-boq-package-<?= strtolower($scopeKey) ?>" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content drm-modal">
                <form method="post" action="<?= base_url('DRM_MyRep/saveApdBoqPackage') ?>" enctype="multipart/form-data" class="js-apd-boq-form" data-existing-file="<?= $hasApdBoqFile ? '1' : '0' ?>" data-preview-url="<?= base_url('DRM_MyRep/previewApdBoqParse') ?>">
                    <input type="hidden" name="cluster_id" value="<?= (int) $cluster['id_myrep_cluster'] ?>">
                    <input type="hidden" name="scope_type" value="<?= htmlspecialchars($scopeKey) ?>">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">APD BOQ dan Manual BOQ - <?= htmlspecialchars($scopeLabel) ?></h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="drm-form-box">
                            <div class="drm-form-box__title">Informasi <?= htmlspecialchars($scopeLabel) ?></div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-md-0">
                                        <label>Nama Cluster</label>
                                        <input type="text" class="form-control" value="<?= htmlspecialchars((string) ($cluster['cluster_name'] ?? '-')) ?>" readonly>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-md-0">
                                        <label>Kota</label>
                                        <input type="text" class="form-control" value="<?= htmlspecialchars((string) ($cluster['city_name'] ?? '-')) ?>" readonly>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-0">
                                        <label>Status BOQ</label>
                                        <input type="text" class="form-control" value="<?= htmlspecialchars($boqReviewStatus !== '' ? $boqReviewStatus : 'DRAFT') ?>" readonly>
                                    </div>
                                </div>
                            </div>
                            <?php if (!empty($boqHeader['ho_review_remark'])): ?>
                                <div class="small text-info mt-3">Catatan HO: <?= htmlspecialchars((string) $boqHeader['ho_review_remark']) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="drm-form-box js-manual-boq-section" style="display:none;">
                            <div class="drm-form-box__title">Input Jumlah Item Manual</div>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Item di Excel</th>
                                            <th>Nama Item</th>
                                            <th>Jenis Item</th>
                                            <th>Satuan</th>
                                            <th>Qty BOQ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($boqItems as $index => $item): ?>
                                            <?php $qtyValue = (float) ($item['qty_boq'] ?? 0); ?>
                                            <tr class="<?= $qtyValue > 0 ? 'table-success' : 'table-light' ?>">
                                                <td><?= $index + 1 ?></td>
                                                <td><?= htmlspecialchars((string) ($item['excel_item_name'] ?? '-')) ?></td>
                                                <td><?= htmlspecialchars((string) ($item['item_name'] ?? '-')) ?></td>
                                                <td><?= htmlspecialchars((string) ($item['item_type'] ?? '-')) ?></td>
                                                <td><?= htmlspecialchars((string) ($item['item_satuan'] ?? '-')) ?></td>
                                                <td>
                                                    <input type="number" step="any" min="0" name="boq_qty[<?= (int) $item['id_boq_item'] ?>]" class="form-control form-control-sm js-modal-boq-qty" value="<?= rtrim(rtrim(number_format($qtyValue, 3, '.', ''), '0'), '.') ?>" <?= $isBoqLocked ? 'readonly' : '' ?>>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="drm-form-box">
                            <div class="drm-form-box__title">Upload File APD BOQ</div>
                            <div class="mb-2">
                                <?php if ($hasApdBoqFile): ?>
                                    <div class="small text-muted">File saat ini: <a href="<?= base_url('DRM_MyRep/previewDocument/' . (int) $apdBoqFile['id_doc_file']) ?>" target="_blank"><?= htmlspecialchars((string) ($apdBoqFile['file_name'] ?? '-')) ?></a></div>
                                <?php else: ?>
                                    <div class="small text-muted">Belum ada file APD BOQ.</div>
                                <?php endif; ?>
                            </div>
                            <div class="drm-dropzone js-dropzone">
                                <input type="file" name="apd_boq_file" class="js-dropzone-input">
                                <div class="drm-dropzone-content">
                                    <div class="drm-dropzone-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                                    <div class="drm-dropzone-title">Drag & drop file APD BOQ di sini</div>
                                    <div class="drm-dropzone-text">Atau klik area ini untuk memilih file</div>
                                    <div class="drm-dropzone-file js-dropzone-label">Belum ada file baru dipilih</div>
                                </div>
                            </div>
                            <div class="form-group mt-3 mb-0">
                                <label>Remark Upload File</label>
                                <textarea name="apd_boq_remark" rows="2" class="form-control" placeholder="Catatan upload APD BOQ jika diperlukan"></textarea>
                            </div>
                            <div class="mt-3 js-apd-boq-alert" style="display:none;"></div>
                            <div class="mt-3 p-2 border rounded bg-light js-apd-boq-preview" style="display:none;">
                                <div class="js-apd-boq-loading" style="display:none;">
                                    <div class="small text-primary mb-2">Sedang parsing APD BOQ, mohon tunggu...</div>
                                    <div class="progress" style="height: 10px;">
                                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: 100%"></div>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered mb-0">
                                        <thead>
                                            <tr>
                                                <th>ID Item</th>
                                                <th>Item di Excel</th>
                                                <th>Nama Item</th>
                                                <th>Jenis</th>
                                                <th>Satuan</th>
                                                <th>Qty Hasil Parse</th>
                                            </tr>
                                        </thead>
                                        <tbody class="js-apd-boq-preview-body">
                                            <tr><td colspan="6" class="text-muted text-center">Belum ada hasil parsing.</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-between">
                        <div class="text-muted small">Upload APD BOQ dulu, lalu cek hasil parsing sebelum simpan.</div>
                        <div>
                            <?php if (!$isBoqLocked): ?>
                                <button type="submit" class="btn btn-outline-primary">Simpan Draft</button>
                                <button type="submit" class="btn btn-primary" name="submit_to_ho" value="1">Submit ke HO</button>
                            <?php else: ?>
                                <span class="text-muted small">BOQ sudah approved, upload dan edit dinonaktifkan.</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($canApprove && $canApprovalAction && !empty($boqHeader['id_drm_boq']) && in_array($boqReviewStatus, ['WAITING HO', 'REJECTED'], true)): ?>
        <div class="modal fade" id="modal-boq-review-<?= strtolower($scopeKey) ?>" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content drm-modal">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title">Review BOQ DRM - <?= htmlspecialchars($scopeLabel) ?></h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="drm-form-box">
                            <div class="drm-form-box__title">Informasi Review</div>
                            <div class="small text-muted">Approve BOQ akan sekaligus meng-approve dokumen <?= htmlspecialchars($scopeLabel) ?> yang sudah berstatus upload.</div>
                        </div>
                        <div class="drm-form-box">
                            <div class="drm-form-box__title">Detail Item BOQ</div>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Item di Excel</th>
                                            <th>Nama Item</th>
                                            <th>Jenis</th>
                                            <th>Satuan</th>
                                            <th>Qty BOQ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($boqItems as $index => $item): ?>
                                            <?php $qtyValue = (float) ($item['qty_boq'] ?? 0); ?>
                                            <tr class="<?= $qtyValue > 0 ? 'table-success' : 'table-light' ?>">
                                                <td><?= $index + 1 ?></td>
                                                <td><?= htmlspecialchars((string) ($item['excel_item_name'] ?? '-')) ?></td>
                                                <td><?= htmlspecialchars((string) ($item['item_name'] ?? '-')) ?></td>
                                                <td><?= htmlspecialchars((string) ($item['item_type'] ?? '-')) ?></td>
                                                <td><?= htmlspecialchars((string) ($item['item_satuan'] ?? '-')) ?></td>
                                                <td><?= htmlspecialchars(rtrim(rtrim(number_format($qtyValue, 3, '.', ''), '0'), '.')) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($boqItems)): ?>
                                            <tr>
                                                <td colspan="6" class="text-center text-muted">Belum ada item BOQ.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <form method="post" action="<?= base_url('DRM_MyRep/approveBoq') ?>">
                                    <input type="hidden" name="cluster_id" value="<?= (int) $cluster['id_myrep_cluster'] ?>">
                                    <input type="hidden" name="scope_type" value="<?= htmlspecialchars($scopeKey) ?>">
                                    <div class="drm-form-box mb-0">
                                        <div class="drm-form-box__title">Approve BOQ</div>
                                        <div class="form-group">
                                            <label>Remark Approve BOQ</label>
                                            <textarea name="remark" rows="3" class="form-control" placeholder="Catatan approval jika diperlukan"></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-success">Approve BOQ dan Dokumen</button>
                                    </div>
                                </form>
                            </div>
                            <div class="col-md-6">
                                <form method="post" action="<?= base_url('DRM_MyRep/rejectBoq') ?>">
                                    <input type="hidden" name="cluster_id" value="<?= (int) $cluster['id_myrep_cluster'] ?>">
                                    <input type="hidden" name="scope_type" value="<?= htmlspecialchars($scopeKey) ?>">
                                    <div class="drm-form-box mb-0">
                                        <div class="drm-form-box__title">Reject BOQ</div>
                                        <div class="form-group">
                                            <label>Alasan Reject BOQ</label>
                                            <textarea name="remark" rows="3" class="form-control" required placeholder="Wajib diisi jika BOQ manual tidak sesuai file APD BOQ"></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-danger">Reject BOQ</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
<?php endforeach; ?>

<?php foreach ($drmScopes as $scopeKey => $scope): ?>
    <?php
    $scopeReady = !empty($scope['isReady']);
    if (!$scopeReady) {
        continue;
    }

    $scopeLabel = (string) ($scope['label'] ?? drmScopeText($scopeKey));
    $bulkRows = [];
    foreach ((array) ($scope['documentRows'] ?? []) as $docRow) {
        $docNameUpper = strtoupper(trim((string) ($docRow['doc_name'] ?? '')));
        $docStatus = drmDocumentLabel($docRow);
        $docRawStatus = strtoupper(trim((string) ($docRow['status_file'] ?? '')));
        if ($docNameUpper === 'APD BOQ') {
            continue;
        }
        if (in_array($docStatus, ['BELUM UPLOAD'], true) || $docRawStatus === 'REJECTED') {
            $bulkRows[] = $docRow;
        }
    }
    ?>
    <?php if ($canTambah && !empty($bulkRows)): ?>
        <div class="modal fade" id="modal-drm-bulk-upload-<?= strtolower($scopeKey) ?>" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content drm-modal">
                    <form method="post" action="<?= base_url('DRM_MyRep/uploadBulkDocuments') ?>" enctype="multipart/form-data">
                        <input type="hidden" name="cluster_id" value="<?= (int) ($cluster['id_myrep_cluster'] ?? 0) ?>">
                        <input type="hidden" name="scope_type" value="<?= htmlspecialchars((string) $scopeKey) ?>">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title">Bulk Upload Dokumen DRM - <?= htmlspecialchars($scopeLabel) ?></h5>
                            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <div class="modal-body">
                            <div class="drm-bulk-summary">
                                <div>
                                    <div class="drm-bulk-summary__title">Upload beberapa dokumen DRM sekaligus</div>
                                    <p class="drm-bulk-summary__text">Pilih file per kartu dokumen, atau centang <strong>Tidak dibutuhkan</strong> jika item memang tidak wajib. APD BOQ dan Manual BOQ tidak termasuk di bulk upload.</p>
                                </div>
                                <div class="drm-bulk-summary__badge"><?= count($bulkRows) ?></div>
                            </div>
                            <div class="drm-bulk-grid">
                                <?php foreach ($bulkRows as $index => $row): ?>
                                    <div class="drm-bulk-card">
                                        <input type="hidden" name="bulk_doc_item_ids[]" value="<?= (int) ($row['id_doc_item'] ?? 0) ?>">
                                        <div class="drm-bulk-card__header">
                                            <div>
                                                <div class="drm-bulk-card__eyebrow">Dokumen <?= $index + 1 ?></div>
                                                <h6 class="drm-bulk-card__title"><?= htmlspecialchars((string) ($row['doc_name'] ?? '-')) ?></h6>
                                            </div>
                                            <span class="badge badge-<?= drmDetailBadgeClass(drmDocumentLabel($row)) ?>"><?= htmlspecialchars(drmDocumentLabel($row)) ?></span>
                                        </div>
                                        <div class="drm-bulk-card__body">
                                            <div class="small text-muted mb-2">
                                                <?= !empty($row['doc_requirement_note']) ? htmlspecialchars((string) $row['doc_requirement_note']) : 'Tidak ada catatan khusus.' ?>
                                            </div>
                                            <div class="small mb-2">
                                                <strong>File saat ini:</strong>
                                                <?= !empty($row['file_name']) ? htmlspecialchars((string) $row['file_name']) : 'Belum ada file aktif.' ?>
                                            </div>
                                            <div class="drm-dropzone js-dropzone">
                                                <input type="file" name="bulk_file_<?= (int) ($row['id_doc_item'] ?? 0) ?>" class="js-dropzone-input">
                                                <div class="drm-dropzone-content">
                                                    <div class="drm-dropzone-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                                                    <div class="drm-dropzone-title">Pilih file untuk <?= htmlspecialchars((string) ($row['doc_name'] ?? 'dokumen')) ?></div>
                                                    <div class="drm-dropzone-text">Drag & drop file di sini atau klik area ini</div>
                                                    <div class="drm-dropzone-file js-dropzone-label">Belum ada file dipilih</div>
                                                </div>
                                            </div>
                                            <div class="form-group mt-3 mb-2">
                                                <label class="mb-1 font-weight-bold">Remark Upload</label>
                                                <textarea name="bulk_remark_<?= (int) ($row['id_doc_item'] ?? 0) ?>" class="form-control" rows="2" placeholder="Remark upload jika diperlukan"><?= htmlspecialchars((string) ($row['remark'] ?? '')) ?></textarea>
                                            </div>
                                            <div class="form-group form-check mb-0">
                                                <input type="checkbox" class="form-check-input" id="bulk_not_required_<?= (int) ($row['id_doc_item'] ?? 0) ?>" name="bulk_not_required_<?= (int) ($row['id_doc_item'] ?? 0) ?>" value="1">
                                                <label class="form-check-label" for="bulk_not_required_<?= (int) ($row['id_doc_item'] ?? 0) ?>">Tidak dibutuhkan</label>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary btn-sm" data-dismiss="modal">Tutup</button>
                            <button type="submit" class="btn btn-primary btn-sm">Simpan Bulk Upload</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>
<?php endforeach; ?>

<script>
    (function () {
        function bindDropzones() {
            var dropzones = document.querySelectorAll('.js-dropzone');
            Array.prototype.forEach.call(dropzones, function (dropzone) {
                if (dropzone.dataset.bound === '1') {
                    return;
                }

                var input = dropzone.querySelector('.js-dropzone-input');
                var label = dropzone.querySelector('.js-dropzone-label');
                if (!input || !label) {
                    return;
                }

                dropzone.dataset.bound = '1';

                ['dragenter', 'dragover'].forEach(function (eventName) {
                    dropzone.addEventListener(eventName, function (event) {
                        event.preventDefault();
                        event.stopPropagation();
                        dropzone.classList.add('dragover');
                    });
                });

                ['dragleave', 'drop'].forEach(function (eventName) {
                    dropzone.addEventListener(eventName, function (event) {
                        event.preventDefault();
                        event.stopPropagation();
                        dropzone.classList.remove('dragover');
                    });
                });

                dropzone.addEventListener('drop', function (event) {
                    if (event.dataTransfer.files && event.dataTransfer.files.length > 0) {
                        input.files = event.dataTransfer.files;
                        label.textContent = event.dataTransfer.files[0].name;
                        input.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                });

                input.addEventListener('change', function () {
                    label.textContent = input.files && input.files.length > 0
                        ? input.files[0].name
                        : 'Belum ada file dipilih';
                });
            });
        }

        function getQtyInputMap(form) {
            var map = {};
            var qtyInputs = form.querySelectorAll('.js-modal-boq-qty');
            Array.prototype.forEach.call(qtyInputs, function (input) {
                var name = input.getAttribute('name') || '';
                var match = name.match(/^boq_qty\[(\d+)\]$/);
                if (match) {
                    map[match[1]] = input;
                }
            });
            return map;
        }

        function renderApdBoqPreview(form, payload) {
            var wrapper = form.querySelector('.js-apd-boq-preview');
            var manualSection = form.querySelector('.js-manual-boq-section');
            var loadingEl = form.querySelector('.js-apd-boq-loading');
            var bodyEl = form.querySelector('.js-apd-boq-preview-body');
            if (!wrapper || !bodyEl) {
                return;
            }

            wrapper.style.display = '';
            if (loadingEl) {
                loadingEl.style.display = 'none';
            }

            var items = Array.isArray(payload.items) ? payload.items : [];
            if (!items.length) {
                if (manualSection) {
                    manualSection.style.display = 'none';
                }
                bodyEl.innerHTML = '<tr><td colspan="6" class="text-muted text-center">Tidak ada item yang berhasil diparsing.</td></tr>';
                return;
            }
            if (manualSection) {
                manualSection.style.display = '';
            }

            var itemInfoMap = {};
            var qtyInputs = form.querySelectorAll('.js-modal-boq-qty');
            Array.prototype.forEach.call(qtyInputs, function (input) {
                var name = input.getAttribute('name') || '';
                var match = name.match(/^boq_qty\[(\d+)\]$/);
                if (!match) {
                    return;
                }
                var id = match[1];
                var row = input.closest('tr');
                if (!row) {
                    return;
                }
                var cells = row.querySelectorAll('td');
                itemInfoMap[id] = {
                    excelName: cells[1] ? (cells[1].textContent || '').trim() : '-',
                    itemName: cells[2] ? (cells[2].textContent || '').trim() : '-',
                    itemType: cells[3] ? (cells[3].textContent || '').trim() : '-',
                    itemSatuan: cells[4] ? (cells[4].textContent || '').trim() : '-'
                };
            });

            var rows = '';
            items.forEach(function (item) {
                var id = String(item.id_boq_item || '-');
                var info = itemInfoMap[id] || { excelName: '-', itemName: '-', itemType: '-', itemSatuan: '-' };
                rows += '<tr>' +
                    '<td>' + id + '</td>' +
                    '<td>' + info.excelName + '</td>' +
                    '<td>' + info.itemName + '</td>' +
                    '<td>' + info.itemType + '</td>' +
                    '<td>' + info.itemSatuan + '</td>' +
                    '<td>' + (item.qty_boq || 0) + '</td>' +
                '</tr>';
            });
            bodyEl.innerHTML = rows;
        }

        function renderApdBoqNotice(form, type, message) {
            var alertHost = form.querySelector('.js-apd-boq-alert');
            if (!alertHost) {
                return;
            }
            if (!message) {
                alertHost.style.display = 'none';
                alertHost.innerHTML = '';
                return;
            }
            var alertClass = type === 'success' ? 'alert-success' : (type === 'warning' ? 'alert-warning' : 'alert-danger');
            alertHost.style.display = '';
            alertHost.innerHTML = '<div class="alert ' + alertClass + ' mb-0 py-2 px-3 small">' + message + '</div>';
        }

        function fillManualQtyFromParsed(form, items) {
            var map = getQtyInputMap(form);
            Object.keys(map).forEach(function (id) {
                map[id].value = '0';
            });

            items.forEach(function (item) {
                var id = String(item.id_boq_item || '');
                var input = map[id];
                if (!input) {
                    return;
                }
                var qty = parseFloat(item.qty_boq || 0);
                input.value = qty > 0 ? String(qty) : '';
            });
            refreshManualBoqRowColors(form);
        }

        function refreshManualBoqRowColors(form) {
            var qtyInputs = form.querySelectorAll('.js-modal-boq-qty');
            Array.prototype.forEach.call(qtyInputs, function (input) {
                var row = input.closest('tr');
                if (!row) {
                    return;
                }
                var qty = parseFloat(input.value || '0');
                row.classList.remove('table-success', 'table-light');
                if (qty > 0) {
                    row.classList.add('table-success');
                } else {
                    row.classList.add('table-light');
                }
            });
        }

        function bindApdBoqPreview() {
            var forms = document.querySelectorAll('.js-apd-boq-form');
            Array.prototype.forEach.call(forms, function (form) {
                if (form.dataset.previewBound === '1') {
                    return;
                }
                form.dataset.previewBound = '1';

                var fileInput = form.querySelector('input[name="apd_boq_file"]');
                if (!fileInput) {
                    return;
                }
                refreshManualBoqRowColors(form);

                var qtyInputs = form.querySelectorAll('.js-modal-boq-qty');
                Array.prototype.forEach.call(qtyInputs, function (qtyInput) {
                    qtyInput.addEventListener('input', function () {
                        refreshManualBoqRowColors(form);
                    });
                    qtyInput.addEventListener('change', function () {
                        refreshManualBoqRowColors(form);
                    });
                });

                fileInput.addEventListener('change', function () {
                    var file = fileInput.files && fileInput.files.length > 0 ? fileInput.files[0] : null;
                    if (!file) {
                        console.log('[APD BOQ][Preview] Tidak ada file terpilih.');
                        return;
                    }
                    console.log('[APD BOQ][Preview] File dipilih:', {
                        name: file.name,
                        size: file.size,
                        type: file.type
                    });

                    var previewUrl = form.getAttribute('data-preview-url') || '';
                    if (previewUrl === '') {
                        console.warn('[APD BOQ][Preview] URL preview kosong.');
                        return;
                    }
                    console.log('[APD BOQ][Preview] Mulai request parsing ke:', previewUrl);

                    renderApdBoqPreview(form, {
                        status: false,
                        message: 'Memproses parsing file APD BOQ...',
                        items: [],
                        warnings: []
                    });
                    renderApdBoqNotice(form, '', '');
                    var loadingEl = form.querySelector('.js-apd-boq-loading');
                    if (loadingEl) {
                        loadingEl.style.display = '';
                    }

                    var fd = new FormData();
                    fd.append('apd_boq_file', file);
                    fd.append('scope_type', form.querySelector('input[name="scope_type"]') ? form.querySelector('input[name="scope_type"]').value : 'CLUSTER');

                    fetch(previewUrl, {
                        method: 'POST',
                        body: fd,
                        credentials: 'same-origin'
                    })
                    .then(function (response) {
                        console.log('[APD BOQ][Preview] HTTP status:', response.status);
                        return response.text().then(function (text) {
                            var payload = null;
                            try {
                                payload = JSON.parse(text);
                            } catch (e) {
                                var firstBrace = text.indexOf('{');
                                var lastBrace = text.lastIndexOf('}');
                                if (firstBrace !== -1 && lastBrace !== -1 && lastBrace > firstBrace) {
                                    var candidate = text.substring(firstBrace, lastBrace + 1);
                                    try {
                                        payload = JSON.parse(candidate);
                                    } catch (e2) {
                                        console.error('[APD BOQ][Preview] Response bukan JSON:', text);
                                        throw new Error('Response preview bukan JSON valid.');
                                    }
                                } else {
                                    console.error('[APD BOQ][Preview] Response bukan JSON:', text);
                                    throw new Error('Response preview bukan JSON valid.');
                                }
                            }
                            return payload;
                        });
                    })
                    .then(function (payload) {
                        console.log('[APD BOQ][Preview] Response payload:', payload);
                        renderApdBoqPreview(form, payload || {});
                        if (payload && Array.isArray(payload.items) && payload.items.length > 0) {
                            console.log('[APD BOQ][Preview] Parsing berhasil. Item terpetakan:', payload.items.length, '| warning:', (payload.warnings || []).length);
                            fillManualQtyFromParsed(form, payload.items);
                            console.log('[APD BOQ][Preview] Auto-fill qty manual selesai.');
                            renderApdBoqNotice(form, 'success', 'Parsing APD BOQ berhasil. ' + payload.items.length + ' item terpetakan.');
                        } else {
                            console.warn('[APD BOQ][Preview] Parsing tidak menghasilkan item terpetakan.');
                            renderApdBoqNotice(form, 'warning', 'Parsing APD BOQ selesai, tetapi tidak ada item yang berhasil terpetakan.');
                        }
                    })
                    .catch(function (error) {
                        console.error('[APD BOQ][Preview] Gagal request preview parsing:', error);
                        renderApdBoqPreview(form, {
                            status: false,
                            message: 'Gagal preview parsing file APD BOQ.',
                            items: [],
                            warnings: []
                        });
                        var loadingEl = form.querySelector('.js-apd-boq-loading');
                        if (loadingEl) {
                            loadingEl.style.display = 'none';
                        }
                        renderApdBoqNotice(form, 'error', 'Parsing APD BOQ gagal karena error sistem. Coba ulangi.');
                    });
                });
            });
        }

        function renderHistory(history) {
            if (!history.length) {
                return '<li class="text-muted">Belum ada history.</li>';
            }

            var html = '';
            history.forEach(function (entry) {
                html += '<li class="doc-history-item">' +
                    '<div class="doc-history-title">' + (entry.action_type || '-') + '</div>' +
                    '<div class="doc-history-meta">' + (entry.action_at || '-') + ' | ' + (entry.nama_user || 'System') + '</div>' +
                    '<div><strong>File:</strong> ' + (entry.file_name || '-') + '</div>' +
                    '<div><strong>Remark:</strong> ' + (entry.remark || '-') + '</div>' +
                '</li>';
            });
            return html;
        }

        bindDropzones();
        bindApdBoqPreview();

        document.addEventListener('click', function (event) {
            var uploadButton = event.target.closest('.js-open-drm-upload-modal');
            if (uploadButton) {
                var currentLabel = document.querySelector('#modal-drm-upload .js-dropzone-label');
                var currentInput = document.querySelector('#modal-drm-upload .js-dropzone-input');
                document.getElementById('drm_upload_scope_type').value = uploadButton.getAttribute('data-scope-type') || 'CLUSTER';
                document.getElementById('drm_upload_scope_label').textContent = (uploadButton.getAttribute('data-scope-type') || 'CLUSTER') === 'SUBFEEDER' ? 'Subfeeder' : 'Cluster';
                document.getElementById('drm_upload_doc_item_id').value = uploadButton.getAttribute('data-doc-item-id') || '';
                document.getElementById('drm_upload_doc_name').textContent = uploadButton.getAttribute('data-doc-name') || '-';
                document.getElementById('drm_upload_current_file').textContent = uploadButton.getAttribute('data-file-name') || '-';
                document.getElementById('drm_upload_remark').value = uploadButton.getAttribute('data-remark') || '';
                document.getElementById('drm_upload_not_required').checked = false;
                if (currentInput) {
                    currentInput.value = '';
                }
                if (currentLabel) {
                    currentLabel.textContent = 'Belum ada file dipilih';
                }
                return;
            }

            var historyButton = event.target.closest('.js-doc-history');
            if (historyButton) {
                var history = [];
                try {
                    history = historyButton.getAttribute('data-history')
                        ? JSON.parse(historyButton.getAttribute('data-history'))
                        : [];
                } catch (e) {
                    history = [];
                }

                document.getElementById('history_doc_label').textContent = historyButton.getAttribute('data-doc-name') || '-';
                document.getElementById('history_doc_items').innerHTML = renderHistory(history);
            }
        });

        document.addEventListener('submit', function (event) {
            if (event.target.id === 'form-drm-upload') {
                var uploadCheckbox = document.getElementById('drm_upload_not_required');
                var uploadInput = event.target.querySelector('.js-dropzone-input');
                var noDocument = uploadCheckbox && uploadCheckbox.checked;
                var hasFile = uploadInput && uploadInput.files && uploadInput.files.length > 0;

                if (!noDocument && !hasFile) {
                    event.preventDefault();
                    alert('File dokumen DRM wajib dipilih atau centang "Tidak dibutuhkan".');
                }
                return;
            }

            if (!event.target.classList.contains('js-apd-boq-form')) {
                return;
            }

            var hasExistingFile = event.target.getAttribute('data-existing-file') === '1';
            var uploadInput = event.target.querySelector('input[name="apd_boq_file"]');
            var hasNewFile = uploadInput && uploadInput.files && uploadInput.files.length > 0;
            var manualSection = event.target.querySelector('.js-manual-boq-section');
            var hasQty = false;
            var qtyInputs = event.target.querySelectorAll('.js-modal-boq-qty');

            Array.prototype.forEach.call(qtyInputs, function (input) {
                if (parseFloat(input.value || '0') > 0) {
                    hasQty = true;
                }
            });

            var manualVisible = manualSection && manualSection.style.display !== 'none';
            if (!manualVisible || !hasQty || (!hasExistingFile && !hasNewFile)) {
                event.preventDefault();
                alert('Upload APD BOQ dan pastikan parsing berhasil dulu sebelum disimpan.');
            }
        });
    })();
</script>
