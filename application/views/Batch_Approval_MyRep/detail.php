<?php
$flashSuccess = $this->session->flashdata('success');
$flashError = $this->session->flashdata('error');
$canTambah = isset($this->myrepAccess) ? $this->myrepAccess->hasPermission('Batch_Approval_MyRep', 'TAMBAH') : true;
$canEdit = isset($canEditBatchApproval) ? (bool) $canEditBatchApproval : (isset($this->myrepAccess) ? $this->myrepAccess->hasPermission('Batch_Approval_MyRep', 'EDIT') : true);
$canHapus = isset($this->myrepAccess) ? $this->myrepAccess->hasPermission('Batch_Approval_MyRep', 'HAPUS') : true;
$canApprovalAction = isset($this->myrepAccess) ? $this->myrepAccess->hasPermission('Batch_Approval_MyRep', 'APPROVAL') : true;
$canDonationUpload = (string) $this->session->userdata('nama_level') === 'Super Admin'
    || (isset($this->myrepAccess)
        && method_exists($this->myrepAccess, 'getCurrentRoleKeys')
        && (in_array('ADMIN_AREA', (array) $this->myrepAccess->getCurrentRoleKeys(), true)
            || in_array('SITAC_HO', (array) $this->myrepAccess->getCurrentRoleKeys(), true)));
$canReplaceDonationFile = !empty($canReplaceDonationFile);
$canFinanceApprovalAction = !empty($canFinanceApprovalAction);

if (!function_exists('batchDetailBadgeClass')) {
    function batchDetailBadgeClass($status)
    {
        switch (strtoupper(trim((string) $status))) {
            case 'APPROVED':
            case 'LINKED DOKUMENT':
            case 'RELEASED':
            case 'DONE BATCH APPROVAL':
            case 'BATCH_APPROVED':
            case 'PRE_ZEYN_DOC_APPROVED':
            case 'PRE_ZEYN_FINANCE_APPROVED':
            case 'POST_ZEYN_DOC_APPROVED':
            case 'ASTRI_APPROVED':
            case 'PO_DONASI':
            case 'INVOICE':
                return 'success';
            case 'REJECTED':
                return 'danger';
            case 'HOLD':
                return 'warning';
            case 'WAITING HO':
            case 'WAITING MYREP':
            case 'WAITING_BATCH_APPROVAL':
            case 'WAITING_ASTRI_SUBMISSION':
                return 'info';
            case 'WAITING FINANCE':
            case 'ON REVIEW':
            case 'UPLOADED':
            case 'WAITING_PRE_ZEYN_DOC':
            case 'PRE_ZEYN_DOC_ON_REVIEW':
            case 'PRE_ZEYN_FINANCE_ON_REVIEW':
            case 'WAITING_FINANCE_RELEASE':
            case 'WAITING_POST_ZEYN_DOC':
            case 'POST_ZEYN_DOC_ON_REVIEW':
            case 'POST_ZEYN_FINANCE_ON_REVIEW':
            case 'ASTRI_ON_REVIEW':
                return 'warning';
            default:
                return 'secondary';
        }
    }
}

if (!function_exists('batchDetailStatusLabel')) {
    function batchDetailStatusLabel($status)
    {
        $status = strtoupper(trim((string) $status));
        $labels = [
            'DRAFT' => 'Draft',
            'WAITING HO' => 'Menunggu Review HO',
            'WAITING MYREP' => 'Menunggu Review EMR',
            'WAITING FINANCE' => 'Menunggu Finance',
            'WAITING_BATCH_APPROVAL' => 'Menunggu Nomor Batch Approval',
            'BATCH_APPROVED' => 'Batch Approval Disetujui',
            'HOLD' => 'Ditahan',
            'WAITING_PRE_ZEYN_DOC' => 'NY Dokumen Tahap 1',
            'PRE_ZEYN_DOC_ON_REVIEW' => 'On Review Dokumen Tahap 1',
            'PRE_ZEYN_DOC_APPROVED' => 'Approved Dokumen Tahap 1',
            'PRE_ZEYN_FINANCE_ON_REVIEW' => 'On Review Finance Dokumen Tahap 1',
            'PRE_ZEYN_FINANCE_APPROVED' => 'Approved Finance Dokumen Tahap 1',
            'WAITING_FINANCE_RELEASE' => 'Menunggu Pembayaran Finance',
            'RELEASED' => 'Donasi Dibayarkan',
            'WAITING_POST_ZEYN_DOC' => 'NY Dokumen Tahap 2',
            'POST_ZEYN_DOC_ON_REVIEW' => 'On Review Dokumen Tahap 2',
            'POST_ZEYN_DOC_APPROVED' => 'Approved Dokumen Tahap 2',
            'POST_ZEYN_FINANCE_ON_REVIEW' => 'On Review Finance Dokumen Tahap 2',
            'WAITING_ASTRI_SUBMISSION' => 'Menunggu Submit Astri',
            'ASTRI_ON_REVIEW' => 'On Review Astri',
            'ASTRI_APPROVED' => 'Approved Astri',
            'PO_DONASI' => 'PO Donasi',
            'INVOICE' => 'Invoice',
            'DONE BATCH APPROVAL' => 'Batch Approval Selesai',
            'WAITING DOC' => 'Menunggu Dokumen Post Donasi',
            'COMPLETED' => 'Done',
            'REJECTED' => 'Ditolak',
            'WAITING INPUT' => 'Menunggu Pengajuan',
        ];

        return $labels[$status] ?? ($status !== '' ? ucwords(strtolower(str_replace('_', ' ', $status))) : 'Draft');
    }
}

if (!function_exists('batchDetailDateText')) {
    function batchDetailDateText($value, $format = 'd/m/Y')
    {
        $value = trim((string) $value);
        if ($value === '' || $value === '0000-00-00' || $value === '0000-00-00 00:00:00') {
            return '-';
        }

        $timestamp = strtotime($value);
        return $timestamp ? date($format, $timestamp) : '-';
    }
}

if (!function_exists('batchDetailAgingText')) {
    function batchDetailAgingText($value)
    {
        $value = trim((string) $value);
        if ($value === '' || $value === '0000-00-00' || $value === '0000-00-00 00:00:00') {
            return '-';
        }

        $timestamp = strtotime($value);
        if (!$timestamp) {
            return '-';
        }

        $start = new DateTime(date('Y-m-d', $timestamp));
        $today = new DateTime(date('Y-m-d'));
        return $start->diff($today)->days . ' hari';
    }
}

if (!function_exists('batchDetailAddCalendarDays')) {
    function batchDetailAddCalendarDays($value, $days)
    {
        $value = trim((string) $value);
        if ($value === '' || $value === '0000-00-00' || $value === '0000-00-00 00:00:00') {
            return '';
        }

        $timestamp = strtotime($value);
        if (!$timestamp) {
            return '';
        }

        $date = new DateTime(date('Y-m-d', $timestamp));
        $date->modify('+' . max(0, (int) $days) . ' day');
        return $date->format('Y-m-d');
    }
}

if (!function_exists('batchDetailLatestDateFromRows')) {
    function batchDetailLatestDateFromRows(array $rows, $dateField, $requiredOnly = true, $statusField = '', $statusValue = '')
    {
        $latest = '';
        foreach ($rows as $row) {
            if ($requiredOnly && (int) ($row['is_required'] ?? 1) !== 1) {
                continue;
            }
            if ($statusField !== '') {
                $currentStatus = strtoupper(trim((string) ($row[$statusField] ?? '')));
                if ($currentStatus !== strtoupper(trim((string) $statusValue))) {
                    continue;
                }
            }

            $value = trim((string) ($row[$dateField] ?? ''));
            if ($value === '' || $value === '0000-00-00' || $value === '0000-00-00 00:00:00') {
                continue;
            }
            if ($latest === '' || strtotime($value) > strtotime($latest)) {
                $latest = $value;
            }
        }

        return $latest;
    }
}

if (!function_exists('batchDetailSlaStatus')) {
    function batchDetailSlaStatus($targetDate, $actualDate)
    {
        $targetTimestamp = strtotime((string) $targetDate);
        $actualTimestamp = strtotime((string) $actualDate);
        if (!$actualTimestamp) {
            return ['label' => 'Pending', 'class' => 'secondary', 'icon' => 'clock'];
        }
        if (!$targetTimestamp || $actualTimestamp <= $targetTimestamp) {
            return ['label' => 'Tepat Waktu', 'class' => 'success', 'icon' => 'check-circle'];
        }

        return ['label' => 'Terlambat', 'class' => 'danger', 'icon' => 'exclamation-triangle'];
    }
}

if (!function_exists('batchDetailSlaRemainingDays')) {
    function batchDetailSlaRemainingDays($targetDate, $actualDate)
    {
        $targetTimestamp = strtotime((string) $targetDate);
        if (!$targetTimestamp) {
            return '-';
        }

        $endTimestamp = strtotime((string) $actualDate);
        if (!$endTimestamp) {
            $endTimestamp = strtotime(date('Y-m-d'));
        }

        $target = new DateTime(date('Y-m-d', $targetTimestamp));
        $end = new DateTime(date('Y-m-d', $endTimestamp));
        $diff = (int) $end->diff($target)->days;

        if ($end <= $target) {
            return $diff . ' hari';
        }

        return '-' . $diff . ' hari';
    }
}

if (!function_exists('batchDetailSlaPicMeta')) {
    function batchDetailSlaPicMeta($pic)
    {
        $pic = strtoupper(trim((string) $pic));
        $map = [
            'ASTRI' => ['label' => 'Astri', 'class' => 'astri', 'icon' => 'user-astronaut'],
            'ZEYN' => ['label' => 'Zeyn', 'class' => 'zeyn', 'icon' => 'user-check'],
            'SITAC HO TEAM' => ['label' => 'SITAC HO Team', 'class' => 'sitac', 'icon' => 'users'],
            'FINANCE TEAM' => ['label' => 'Finance Team', 'class' => 'finance', 'icon' => 'user-tie'],
        ];

        return $map[$pic] ?? ['label' => ucwords(strtolower($pic)), 'class' => 'default', 'icon' => 'user'];
    }
}

if (!function_exists('batchDetailDocumentLabel')) {
    function batchDetailDocumentLabel($row)
    {
        if ((int) ($row['is_document_not_required'] ?? $row['batch_doc_not_required'] ?? 0) === 1) {
            return 'TIDAK BUTUH DOKUMENT';
        }

        if (empty($row['id_doc_file']) && !empty($row['linked_source_file_id'])) {
            return 'LINKED DOKUMENT';
        }

        $status = strtoupper(trim((string) ($row['status_file'] ?? $row['batch_doc_status'] ?? '')));
        if ($status === 'UPLOADED') {
            return 'ON REVIEW';
        }

        return $status !== '' ? $status : 'BELUM UPLOAD';
    }
}

if (!function_exists('batchDetailFinanceDocumentLabel')) {
    function batchDetailFinanceDocumentLabel($row)
    {
        if ((int) ($row['is_required'] ?? 1) !== 1) {
            return 'TIDAK PERLU';
        }

        $sitacStatus = strtoupper(trim((string) ($row['status_file'] ?? '')));
        if ($sitacStatus !== 'APPROVED') {
            return 'MENUNGGU SITAC';
        }

        $financeStatus = strtoupper(trim((string) ($row['finance_status'] ?? 'NY')));
        if ($financeStatus === 'APPROVED') {
            return 'APPROVED';
        }
        if ($financeStatus === 'REJECTED') {
            return 'REJECTED';
        }

        return 'ON REVIEW';
    }
}

if (!function_exists('batchDetailIsImageDonationDoc')) {
    function batchDetailIsImageDonationDoc($docName)
    {
        return strpos(strtoupper(trim((string) $docName)), 'SCREENSHOT') !== false;
    }
}

if (!function_exists('batchDetailShortFileName')) {
    function batchDetailShortFileName($fileName, $maxWords = 10)
    {
        $fileName = trim((string) $fileName);
        if ($fileName === '') {
            return '-';
        }

        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        $baseName = $extension !== '' ? substr($fileName, 0, -1 * (strlen($extension) + 1)) : $fileName;
        $tokens = preg_split('/[\s_\-]+/', $baseName, -1, PREG_SPLIT_NO_EMPTY);
        if (!$tokens || count($tokens) <= (int) $maxWords) {
            return $fileName;
        }

        $shortName = implode(' ', array_slice($tokens, 0, (int) $maxWords));
        return $shortName . ' ....' . ($extension !== '' ? '.' . $extension : '');
    }
}

if (!function_exists('batchDetailStageMeta')) {
    function batchDetailStageMeta($status)
    {
        $status = strtoupper(trim((string) $status));
        switch ($status) {
            case 'WAITING_BATCH_APPROVAL':
                return ['percent' => 10, 'class' => 'bg-info', 'label' => 'Menunggu batch approval Astri'];
            case 'BATCH_APPROVED':
            case 'WAITING_PRE_ZEYN_DOC':
            case 'PRE_ZEYN_DOC_ON_REVIEW':
                return ['percent' => 25, 'class' => 'bg-warning', 'label' => 'Menunggu dokumen pra-finance Zeyn'];
            case 'PRE_ZEYN_DOC_APPROVED':
                return ['percent' => 40, 'class' => 'bg-success', 'label' => 'Dokumen pra-finance approved'];
            case 'PRE_ZEYN_FINANCE_ON_REVIEW':
                return ['percent' => 45, 'class' => 'bg-warning', 'label' => 'Dokumen pra-finance review Finance'];
            case 'PRE_ZEYN_FINANCE_APPROVED':
                return ['percent' => 50, 'class' => 'bg-success', 'label' => 'Dokumen pra-finance approved Finance'];
            case 'WAITING_FINANCE_RELEASE':
                return ['percent' => 55, 'class' => 'bg-warning', 'label' => 'Menunggu pembayaran donasi'];
            case 'RELEASED':
            case 'WAITING_POST_ZEYN_DOC':
            case 'POST_ZEYN_DOC_ON_REVIEW':
                return ['percent' => 70, 'class' => 'bg-warning', 'label' => 'Menunggu dokumen setelah pembayaran'];
            case 'POST_ZEYN_DOC_APPROVED':
                return ['percent' => 80, 'class' => 'bg-success', 'label' => 'Dokumen setelah pembayaran approved'];
            case 'POST_ZEYN_FINANCE_ON_REVIEW':
                return ['percent' => 82, 'class' => 'bg-warning', 'label' => 'Dokumen setelah pembayaran review Finance'];
            case 'WAITING_ASTRI_SUBMISSION':
                return ['percent' => 85, 'class' => 'bg-info', 'label' => 'Menunggu submit final Astri'];
            case 'ASTRI_ON_REVIEW':
                return ['percent' => 90, 'class' => 'bg-warning', 'label' => 'Final Astri on review'];
            case 'ASTRI_APPROVED':
                return ['percent' => 95, 'class' => 'bg-success', 'label' => 'Final Astri approved'];
            case 'PO_DONASI':
                return ['percent' => 98, 'class' => 'bg-success', 'label' => 'PO Donasi dibuat'];
            case 'INVOICE':
                return ['percent' => 100, 'class' => 'bg-success', 'label' => 'Invoice Donasi selesai'];
            case 'HOLD':
                return ['percent' => 10, 'class' => 'bg-warning', 'label' => 'Pengajuan donasi hold'];
            case 'WAITING HO':
                return ['percent' => 25, 'class' => 'bg-info', 'label' => 'Menunggu review HO'];
            case 'WAITING MYREP':
                return ['percent' => 50, 'class' => 'bg-primary', 'label' => 'Menunggu approval EMR'];
            case 'WAITING FINANCE':
                return ['percent' => 75, 'class' => 'bg-warning', 'label' => 'Menunggu release finance'];
            case 'WAITING DOC':
                return ['percent' => 90, 'class' => 'bg-warning', 'label' => 'Menunggu upload 12 dokumen post donasi'];
            case 'COMPLETED':
                return ['percent' => 100, 'class' => 'bg-success', 'label' => 'Dokumen post donasi lengkap'];
            case 'DONE BATCH APPROVAL':
                return ['percent' => 100, 'class' => 'bg-success', 'label' => 'Batch approval selesai'];
            case 'REJECTED':
                return ['percent' => 100, 'class' => 'bg-danger', 'label' => 'Batch approval ditolak'];
            default:
                return ['percent' => 10, 'class' => 'bg-secondary', 'label' => 'Draft'];
        }
    }
}

$displayStageStatus = (string) ($cluster['display_staging_status'] ?? $cluster['staging_status'] ?? 'DRAFT');
$stageMeta = batchDetailStageMeta($displayStageStatus);
$batchDocumentStatus = batchDetailDocumentLabel($batchDocument);
$batchDocumentRawStatus = strtoupper(trim((string) ($batchDocument['status_file'] ?? '')));
$batchDocumentCanUpload = $canDonationUpload && in_array($batchDocumentStatus, ['BELUM UPLOAD', 'REJECTED'], true);
$batchDocumentCanReview = $canApprove && $canApprovalAction && !empty($batchDocument['id_doc_file']) && $batchDocumentRawStatus === 'UPLOADED';
$transferProofPath = (string) ($cluster['transfer_proof_file_path'] ?? '');
$transferProofExtension = strtolower(pathinfo($transferProofPath, PATHINFO_EXTENSION));
$isTransferProofImage = $transferProofPath !== '' && in_array($transferProofExtension, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true);
$initialPics = !empty($batchPics) ? $batchPics : [[
    'pic_no' => 1,
    'pic_name' => $cluster['recipient_name'] ?? '',
    'pic_phone' => $cluster['recipient_phone'] ?? '',
    'pic_position' => $cluster['recipient_position'] ?? '',
    'pic_period' => $cluster['recipient_period'] ?? '',
    'is_primary' => 1,
]];
$statusOptions = [
    'DRAFT' => batchDetailStatusLabel('DRAFT'),
    'WAITING_BATCH_APPROVAL' => batchDetailStatusLabel('WAITING_BATCH_APPROVAL'),
    'BATCH_APPROVED' => batchDetailStatusLabel('BATCH_APPROVED'),
    'HOLD' => batchDetailStatusLabel('HOLD'),
    'WAITING_PRE_ZEYN_DOC' => batchDetailStatusLabel('WAITING_PRE_ZEYN_DOC'),
    'PRE_ZEYN_DOC_ON_REVIEW' => batchDetailStatusLabel('PRE_ZEYN_DOC_ON_REVIEW'),
    'PRE_ZEYN_DOC_APPROVED' => batchDetailStatusLabel('PRE_ZEYN_DOC_APPROVED'),
    'PRE_ZEYN_FINANCE_ON_REVIEW' => batchDetailStatusLabel('PRE_ZEYN_FINANCE_ON_REVIEW'),
    'PRE_ZEYN_FINANCE_APPROVED' => batchDetailStatusLabel('PRE_ZEYN_FINANCE_APPROVED'),
    'WAITING_FINANCE_RELEASE' => batchDetailStatusLabel('WAITING_FINANCE_RELEASE'),
    'RELEASED' => batchDetailStatusLabel('RELEASED'),
    'WAITING_POST_ZEYN_DOC' => batchDetailStatusLabel('WAITING_POST_ZEYN_DOC'),
    'POST_ZEYN_DOC_ON_REVIEW' => batchDetailStatusLabel('POST_ZEYN_DOC_ON_REVIEW'),
    'POST_ZEYN_DOC_APPROVED' => batchDetailStatusLabel('POST_ZEYN_DOC_APPROVED'),
    'POST_ZEYN_FINANCE_ON_REVIEW' => batchDetailStatusLabel('POST_ZEYN_FINANCE_ON_REVIEW'),
    'WAITING_ASTRI_SUBMISSION' => batchDetailStatusLabel('WAITING_ASTRI_SUBMISSION'),
    'ASTRI_ON_REVIEW' => batchDetailStatusLabel('ASTRI_ON_REVIEW'),
    'ASTRI_APPROVED' => batchDetailStatusLabel('ASTRI_APPROVED'),
    'PO_DONASI' => batchDetailStatusLabel('PO_DONASI'),
    'INVOICE' => batchDetailStatusLabel('INVOICE'),
    'REJECTED' => batchDetailStatusLabel('REJECTED'),
];
$currentStage = strtoupper(trim((string) ($cluster['staging_status'] ?? 'DRAFT')));
$stageButtonTarget = '';
$stageButtonLabel = '';
$releaseNominal = !is_null($cluster['nominal_release_finance'] ?? null) ? (float) $cluster['nominal_release_finance'] : null;
$approvalNominal = !is_null($cluster['nominal_nego_emr'] ?? null) ? (float) $cluster['nominal_nego_emr'] : null;
$releaseVariance = $releaseNominal !== null && $approvalNominal !== null ? $releaseNominal - $approvalNominal : null;
$slaStartDate = trim((string) ($cluster['submission_date'] ?? $cluster['astri_initial_submitted_at'] ?? $cluster['created_at'] ?? ''));
$slaRows = [];
$slaCumulativeDays = 0;
$slaDefinitions = [
    ['Pengajuan Donasi Astri & Zeyn', 'Astri', 1, $cluster['submission_date'] ?? $cluster['astri_initial_submitted_at'] ?? ''],
    ['Release Batch Approval', 'Zeyn', 1, $cluster['astri_batch_approved_at'] ?? ''],
    ['Upload Dokumen Tahap 1 Pra-Finance Zeyn', 'Zeyn', 2, batchDetailLatestDateFromRows((array) ($preZeynDocumentRows ?? []), 'uploaded_at', true)],
    ['Approve SITAC HO Tahap 1', 'SITAC HO Team', 1, batchDetailLatestDateFromRows((array) ($preZeynDocumentRows ?? []), 'approved_at', true, 'status_file', 'APPROVED')],
    ['Approve Finance Tahap 1', 'Finance Team', 1, batchDetailLatestDateFromRows((array) ($preZeynDocumentRows ?? []), 'finance_approved_at', true, 'finance_status', 'APPROVED')],
    ['Pembayaran Donasi Finance', 'Finance Team', 2, $cluster['released_at'] ?? ''],
    ['Upload Dokumen Tahap 2 Setelah Pembayaran', 'Zeyn', 1, batchDetailLatestDateFromRows((array) ($postZeynDocumentRows ?? []), 'uploaded_at', true)],
    ['Approve SITAC HO Tahap 2', 'SITAC HO Team', 1, batchDetailLatestDateFromRows((array) ($postZeynDocumentRows ?? []), 'approved_at', true, 'status_file', 'APPROVED')],
    ['Approve Finance Tahap 2', 'Finance Team', 1, batchDetailLatestDateFromRows((array) ($postZeynDocumentRows ?? []), 'finance_approved_at', true, 'finance_status', 'APPROVED')],
    ['Submit Final Astri', 'Astri', 1, batchDetailLatestDateFromRows((array) ($postZeynDocumentRows ?? []), 'astri_submitted_date', true)],
    ['Approved Astri', 'Astri', 3, batchDetailLatestDateFromRows((array) ($postZeynDocumentRows ?? []), 'astri_approved_date', true, 'astri_status', 'APPROVED')],
    ['PO Donasi', 'Zeyn', 3, $cluster['po_donasi_date'] ?? ''],
    ['Invoice Donasi', 'Zeyn', 1, $cluster['invoice_donasi_date'] ?? ''],
];
foreach ($slaDefinitions as $slaDefinition) {
    $slaCumulativeDays += (int) $slaDefinition[2];
    $targetDate = batchDetailAddCalendarDays($slaStartDate, $slaCumulativeDays);
    $actualDate = trim((string) $slaDefinition[3]);
    $picMeta = batchDetailSlaPicMeta($slaDefinition[1]);
    $slaRows[] = [
        'name' => $slaDefinition[0],
        'pic' => $picMeta,
        'days' => (int) $slaDefinition[2],
        'target_date' => $targetDate,
        'actual_date' => $actualDate,
        'remaining_days' => batchDetailSlaRemainingDays($targetDate, $actualDate),
        'status' => batchDetailSlaStatus($targetDate, $actualDate),
    ];
}
$postDonasiUploadableRows = [];
$postDonasiReviewableRows = [];
$postDonasiDownloadableRows = [];
foreach ((array) $postDonasiRows as $postDocRow) {
    $postDocStatus = batchDetailDocumentLabel($postDocRow);
    $postDocRawStatus = strtoupper(trim((string) ($postDocRow['status_file'] ?? '')));
    $postDocCanUpload = in_array($postDocStatus, ['BELUM UPLOAD', 'LINKED DOKUMENT'], true) || $postDocRawStatus === 'REJECTED';
    if ($postDocCanUpload) {
        $postDonasiUploadableRows[] = $postDocRow;
    }
    if ($canApprovalAction && (
        (!empty($postDocRow['id_doc_file']) && in_array($postDocRawStatus, ['UPLOADED', 'REJECTED'], true))
        || ($postDocStatus === 'LINKED DOKUMENT' && !empty($postDocRow['linked_source_file_id']))
    )) {
        $postDonasiReviewableRows[] = $postDocRow;
    }
    if (!empty($postDocRow['file_path'])) {
        $postDonasiDownloadableRows[] = $postDocRow;
    }
}
if ($canApprove && $canApprovalAction) {
    if ($currentStage === 'WAITING HO') {
        $stageButtonTarget = '#modal-stage-to-myrep';
        $stageButtonLabel = 'Edit Staging';
    } elseif ($currentStage === 'WAITING MYREP') {
        $stageButtonTarget = '#modal-stage-to-finance';
        $stageButtonLabel = 'Edit Staging';
    } elseif ($currentStage === 'WAITING FINANCE') {
        $stageButtonTarget = '#modal-stage-to-released';
        $stageButtonLabel = 'Edit Staging';
    }
}
?>

<style>
    .batch-info-card .card-header,
    .batch-doc-card .card-header,
    .batch-post-card .card-header {
        background: linear-gradient(135deg, #f8fbff, #eef6ff);
        border-bottom: 1px solid #dbeafe;
    }

    .donation-upload-panel {
        border: 1px solid #dbe3ed;
        border-top: 2px solid #0d6efd;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 10px 26px rgba(15, 23, 42, 0.06);
    }

    .donation-upload-panel .card-header {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        background: #fff;
        border-bottom: 1px solid #e5e7eb;
        padding: .65rem 1rem;
        width: 100%;
    }

    .donation-upload-title {
        color: #0f172a;
        font-size: .95rem;
        font-weight: 700;
        letter-spacing: 0;
        margin: 0;
    }

    .batch-section-header {
        display: flex !important;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
    }

    .batch-disbursement-card .card-header {
        background: #fff;
        border-bottom: 1px solid #e5e7eb;
    }

    .batch-disbursement-metric,
    .batch-disbursement-proof {
        min-height: 82px;
        padding: .9rem 1rem;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        background: #f8fafc;
    }

    .batch-disbursement-label {
        display: block;
        color: #64748b;
        font-size: .78rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0;
    }

    .batch-disbursement-metric strong {
        display: block;
        margin-top: .35rem;
        color: #0f172a;
        font-size: 1.05rem;
        line-height: 1.25;
        overflow-wrap: anywhere;
    }

    .batch-disbursement-proof__preview {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        min-height: 120px;
        max-height: 180px;
        padding: .6rem;
        border-radius: 8px;
        background: #111827;
        overflow: hidden;
    }

    .batch-disbursement-proof__preview img {
        max-width: 100%;
        max-height: 160px;
        object-fit: contain;
    }

    .batch-disbursement-empty {
        display: flex;
        align-items: center;
        min-height: 74px;
        padding: .75rem;
        border: 1px dashed #cbd5e1;
        border-radius: 8px;
        color: #64748b;
        background: #fff;
        font-weight: 700;
    }

    .batch-sla-card {
        border: 1px solid #d6e6f0;
        border-radius: 10px;
        overflow: hidden;
    }

    .batch-sla-card__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        min-height: 42px;
        padding: .55rem .85rem;
        background: linear-gradient(90deg, #93d6c5, #2b8fc3);
        color: #fff;
        border-bottom: 0;
    }

    .batch-sla-card__header .card-title {
        color: #082f49;
        font-size: .98rem;
        font-weight: 900;
    }

    .batch-sla-total-badge {
        display: inline-flex;
        align-items: center;
        min-height: 22px;
        padding: .15rem .55rem;
        border-radius: 4px;
        background: rgba(15, 118, 145, .88);
        color: #fff;
        font-size: .72rem;
        font-weight: 800;
    }

    .batch-sla-card__body {
        padding: .75rem .85rem .9rem;
        background: #fff;
    }

    .batch-sla-table {
        border: 1px solid #d7e0e8;
        font-size: .78rem;
    }

    .batch-sla-table thead th {
        background: #f5f7fa;
        border-color: #d7e0e8;
        color: #111827;
        font-weight: 800;
        line-height: 1.15;
        vertical-align: middle;
        white-space: nowrap;
    }

    .batch-sla-table tbody td {
        border-color: #d7e0e8;
        color: #111827;
        line-height: 1.2;
        padding: .35rem .45rem;
        vertical-align: middle;
        white-space: nowrap;
    }

    .batch-sla-table tbody tr:nth-child(odd) {
        background: #fbfdff;
    }

    .batch-sla-table tbody tr:hover {
        background: #eef9fb;
    }

    .batch-sla-no {
        color: #334155;
        font-weight: 800;
    }

    .batch-sla-process {
        min-width: 250px;
        white-space: normal !important;
        font-weight: 700;
    }

    .batch-sla-pic {
        display: inline-flex;
        align-items: center;
        gap: .25rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .batch-sla-pic i {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 17px;
        height: 17px;
        border-radius: 50%;
        color: #fff;
        font-size: .62rem;
    }

    .batch-sla-pic--astri i {
        background: #0f766e;
    }

    .batch-sla-pic--zeyn i {
        background: #65a30d;
    }

    .batch-sla-pic--sitac i {
        background: #2563eb;
    }

    .batch-sla-pic--finance i {
        background: #475569;
    }

    .batch-sla-pic--default i {
        background: #64748b;
    }

    .batch-sla-status {
        display: inline-flex;
        align-items: center;
        gap: .25rem;
        min-height: 20px;
        padding: .12rem .45rem;
        border-radius: 999px;
        font-size: .72rem;
        font-weight: 800;
        white-space: nowrap;
    }

    .batch-sla-status--success {
        background: #dcfce7;
        color: #15803d;
    }

    .batch-sla-status--danger {
        background: #fee2e2;
        color: #b91c1c;
    }

    .batch-sla-status--warning {
        background: #fef3c7;
        color: #a16207;
    }

    .batch-sla-status--secondary {
        background: #e5e7eb;
        color: #4b5563;
    }

    .donation-upload-actions {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: .35rem;
        justify-content: flex-end;
        margin-left: auto;
    }

    .donation-count-badge {
        display: inline-flex;
        align-items: center;
        min-height: 18px;
        padding: .1rem .4rem;
        border-radius: 4px;
        background: #64748b;
        color: #fff;
        font-size: .72rem;
        font-weight: 800;
    }

    .donation-group-progress {
        margin-bottom: .9rem;
    }

    .donation-group-progress .progress {
        height: 10px;
        border-radius: 999px;
        background: #e9edf3;
    }

    .donation-doc-meta {
        display: grid;
        grid-template-columns: repeat(6, minmax(120px, 1fr));
        gap: .85rem 1.25rem;
        margin-bottom: .9rem;
    }

    .donation-doc-meta-label {
        color: #0f172a;
        font-size: .88rem;
        font-weight: 800;
        line-height: 1.1;
    }

    .donation-doc-meta-value {
        color: #0f172a;
        font-size: .9rem;
        line-height: 1.2;
        margin-top: .15rem;
    }

    .donation-doc-table {
        font-size: .86rem;
        margin-bottom: 0;
    }

    .donation-doc-table thead th {
        background: #202529;
        border-color: #343a40;
        color: #fff;
        font-weight: 800;
        white-space: nowrap;
        vertical-align: middle;
    }

    .donation-doc-table tbody td {
        vertical-align: top;
    }

    .donation-doc-table .btn {
        font-weight: 700;
    }

    .donation-action-stack {
        display: flex;
        flex-wrap: wrap;
        gap: .35rem;
        position: relative;
        z-index: 3;
    }

    .js-open-donation-upload-modal {
        position: relative;
        z-index: 4;
        pointer-events: auto !important;
        cursor: pointer !important;
    }

    .donation-action-form {
        min-width: 260px;
        max-width: 320px;
    }

    .donation-doc-modal .modal-header {
        background: linear-gradient(135deg, #198754, #34c38f);
        color: #fff;
        border-bottom: 0;
    }

    .donation-doc-modal .modal-body {
        background: #f6f8fb;
        padding: 1.25rem;
    }

    .donation-doc-modal .modal-footer {
        background: #eef2f7;
        border-top: 0;
    }

    .donation-doc-modal-panel {
        background: #fff;
        border: 1px solid #e7ecf3;
        border-radius: 14px;
        padding: 1rem 1.1rem;
        margin-bottom: 1rem;
    }

    .donation-doc-modal-panel:last-child {
        margin-bottom: 0;
    }

    .donation-doc-highlight {
        background: linear-gradient(135deg, #ecfdf5, #f0fdf4);
        border: 1px dashed #61c093;
        border-radius: 14px;
        padding: 1rem;
        margin-bottom: 1rem;
    }

    .donation-doc-highlight-title {
        color: #116149;
        font-size: 1rem;
        font-weight: 800;
        margin-bottom: .25rem;
        word-break: break-word;
    }

    .donation-doc-highlight-note {
        color: #4b5563;
        font-size: .9rem;
        margin-bottom: 0;
    }

    .donation-bulk-table thead th {
        background: #eaf4f7;
        border-bottom: 0;
        color: #0f4c5c;
    }

    .donation-bulk-table td,
    .donation-bulk-table th {
        vertical-align: middle;
    }

    .donation-photo-thumb {
        width: 100%;
        height: 100%;
        object-fit: contain;
        border-radius: 4px;
        cursor: zoom-in;
    }

    .donation-photo-cell {
        display: flex;
        align-items: center;
        justify-content: center;
        width: min(100%, 360px);
        height: 78px;
        max-width: 100%;
        padding: 6px;
        border-radius: 6px;
        background: #111827;
        box-shadow: 0 3px 10px rgba(15, 23, 42, .18);
    }

    .donation-file-link {
        display: inline;
        max-width: 100%;
        color: #007bff;
        font-size: .85rem;
        font-weight: 700;
        line-height: 1.2;
    }

    .donation-file-link:hover {
        color: #0056b3;
        text-decoration: underline;
    }

    .donation-file-actions {
        display: flex;
        flex-wrap: wrap;
        gap: .25rem;
        margin-top: .35rem;
    }

    .donation-file-photo-cell {
        padding: 8px 12px !important;
        vertical-align: middle !important;
        min-width: 320px;
        max-width: 420px;
    }

    .donation-photo-lightbox {
        position: fixed;
        inset: 0;
        z-index: 2060;
        display: none;
        align-items: center;
        justify-content: center;
        background: rgba(15, 23, 42, .82);
        padding: 1.5rem;
    }

    .donation-photo-lightbox.is-open {
        display: flex;
    }

    .donation-photo-lightbox__dialog {
        width: min(96vw, 1100px);
        max-height: 92vh;
        background: #0f172a;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 24px 80px rgba(0, 0, 0, .45);
    }

    .donation-photo-lightbox__head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: .65rem .8rem;
        color: #fff;
        border-bottom: 1px solid rgba(255, 255, 255, .14);
    }

    .donation-photo-lightbox__title {
        font-weight: 800;
    }

    .donation-photo-lightbox__toolbar {
        display: flex;
        align-items: center;
        gap: .35rem;
    }

    .donation-photo-lightbox__action,
    .donation-photo-lightbox__close {
        width: 32px;
        height: 32px;
        border: 1px solid rgba(255, 255, 255, .28);
        border-radius: 6px;
        background: rgba(255, 255, 255, .08);
        color: #fff;
        font-weight: 800;
    }

    .donation-photo-lightbox__stage {
        height: min(72vh, 680px);
        overflow: auto;
        display: flex;
        align-items: flex-start;
        justify-content: flex-start;
        padding: 1rem;
        cursor: grab;
    }

    .donation-photo-lightbox__stage.is-dragging {
        cursor: grabbing;
    }

    .donation-photo-lightbox__image {
        display: block;
        max-width: none;
        height: auto;
        margin: 0 auto;
        user-select: none;
        -webkit-user-drag: none;
    }

    @media (max-width: 991.98px) {
        .donation-doc-meta {
            grid-template-columns: repeat(2, minmax(130px, 1fr));
        }
    }

    @media (max-width: 575.98px) {
        .donation-upload-panel .card-header {
            align-items: flex-start !important;
            flex-direction: column;
            gap: .5rem;
        }

        .donation-doc-meta {
            grid-template-columns: 1fr;
        }
    }

    .batch-progress-wrap {
        background: #f6f8fb;
        border: 1px solid #e7ecf3;
        border-radius: 16px;
        padding: 1rem 1.1rem;
        margin-bottom: 1.25rem;
    }

    .batch-progress-meta {
        display: flex;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: .6rem;
        font-weight: 700;
        color: #1f2937;
    }

    .batch-progress-caption {
        font-size: .9rem;
        color: #6b7280;
    }

    .batch-progress {
        height: 14px;
        border-radius: 999px;
        background: #e7ecf3;
        overflow: hidden;
    }

    .batch-progress .progress-bar {
        font-weight: 700;
        font-size: .7rem;
        line-height: 14px;
    }

    .batch-edit-btn {
        position: relative;
        z-index: 25;
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        pointer-events: auto !important;
        padding: .55rem .95rem;
        border-radius: 999px;
        border: 1px solid rgba(255, 255, 255, 0.65);
        background: linear-gradient(135deg, #1d4ed8, #2563eb);
        color: #fff;
        font-weight: 700;
        box-shadow: 0 10px 24px rgba(37, 99, 235, 0.22);
    }

    .batch-edit-btn:hover,
    .batch-edit-btn:focus {
        color: #fff;
        background: linear-gradient(135deg, #1e40af, #1d4ed8);
    }

    .batch-info-grid strong {
        display: block;
        margin-bottom: .2rem;
        color: #334155;
    }

    .batch-info-grid > div {
        margin-bottom: 1rem;
    }

    .batch-dropzone {
        position: relative;
        background: linear-gradient(135deg, #f0fdf4, #ecfeff);
        border: 2px dashed #60c7a0;
        border-radius: 16px;
        padding: 1rem;
        transition: all .2s ease;
        cursor: pointer;
    }

    .batch-dropzone.dragover {
        border-color: #198754;
        background: linear-gradient(135deg, #dcfce7, #d1fae5);
        transform: scale(1.01);
    }

    .batch-dropzone input[type="file"] {
        position: absolute;
        inset: 0;
        opacity: 0;
        cursor: pointer;
    }

    .batch-dropzone-content {
        pointer-events: none;
        text-align: center;
    }

    .batch-dropzone-icon {
        font-size: 1.8rem;
        color: #198754;
        margin-bottom: .5rem;
    }

    .batch-dropzone-title {
        font-weight: 700;
        color: #166534;
        margin-bottom: .2rem;
    }

    .batch-dropzone-text {
        color: #4b5563;
        font-size: .9rem;
        margin-bottom: .3rem;
    }

    .batch-dropzone-file {
        color: #0f766e;
        font-weight: 600;
        font-size: .88rem;
    }

    .batch-dropzone.batch-dropzone--photo {
        background: #eff8ff;
        border-color: #38bdf8;
        border-radius: 12px;
        padding: 1.35rem 1rem;
    }

    .batch-dropzone.batch-dropzone--photo .batch-dropzone-title {
        color: #0f172a;
        font-weight: 500;
    }

    .batch-dropzone.batch-dropzone--photo .batch-dropzone-text {
        color: #6b7280;
    }

    .post-bulk-summary {
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

    .post-bulk-summary__title {
        font-size: 1rem;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: .2rem;
    }

    .post-bulk-summary__text {
        margin: 0;
        color: #64748b;
        font-size: .92rem;
    }

    .post-bulk-summary__badge {
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

    .post-bulk-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(340px, 1fr));
        gap: 1rem;
    }

    .post-bulk-card {
        border: 1px solid #dbe4f0;
        border-radius: 20px;
        background: #fff;
        box-shadow: 0 16px 34px rgba(15, 23, 42, 0.07);
        overflow: hidden;
    }

    .post-bulk-card__header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: .9rem;
        padding: 1rem 1.1rem .85rem;
        background: linear-gradient(135deg, #fbfdff, #f4f8fc);
        border-bottom: 1px solid #e5edf6;
    }

    .post-bulk-card__eyebrow {
        font-size: .72rem;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: #64748b;
        margin-bottom: .35rem;
    }

    .post-bulk-card__title {
        margin: 0;
        font-size: 1rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.35;
    }

    .post-bulk-card__body {
        padding: 1rem 1.1rem 1.1rem;
    }

    .post-bulk-card__meta {
        display: grid;
        grid-template-columns: 1fr;
        gap: .75rem;
        margin-bottom: .95rem;
    }

    .post-bulk-meta-box {
        border: 1px solid #e5edf6;
        border-radius: 14px;
        background: #f8fafc;
        padding: .8rem .9rem;
    }

    .post-bulk-meta-box__label {
        font-size: .72rem;
        font-weight: 800;
        letter-spacing: .06em;
        text-transform: uppercase;
        color: #64748b;
        margin-bottom: .3rem;
    }

    .post-bulk-meta-box__value {
        color: #0f172a;
        font-size: .92rem;
        line-height: 1.45;
        word-break: break-word;
    }

    .post-bulk-dropzone {
        background: linear-gradient(135deg, #ffffff, #f8fbff);
        border-color: #93c5fd;
        min-height: 148px;
    }

    .post-bulk-dropzone .batch-dropzone-title {
        color: #0f172a;
        font-weight: 800;
    }

    .post-bulk-dropzone .batch-dropzone-text {
        color: #64748b;
    }

    .post-bulk-remark {
        margin-top: .95rem;
    }

    .post-bulk-remark textarea {
        min-height: 84px;
        resize: vertical;
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

    .batch-form-section {
        background: #fff;
        border: 1px solid #e7ecf3;
        border-radius: 14px;
        padding: 1rem 1.1rem;
        margin-bottom: 1rem;
    }

    .batch-form-section--last {
        margin-bottom: 0;
    }

    .batch-form-section__head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1rem;
        padding-bottom: .9rem;
        margin-bottom: 1rem;
        border-bottom: 1px solid #e7ecf3;
    }

    .batch-form-section__title {
        font-size: 1rem;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: .9rem;
    }

    .batch-form-section__subtitle {
        color: #6b7280;
        font-size: .9rem;
    }

    .batch-pic-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .batch-pic-card {
        border: 1px dashed #cdd8e5;
        border-radius: 12px;
        padding: 1rem;
        background: #fbfdff;
    }

    .batch-pic-card--primary {
        background: linear-gradient(135deg, #eff6ff, #f8fbff);
        border-style: solid;
    }

    .batch-pic-card__head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: .75rem;
        padding-bottom: .75rem;
        border-bottom: 1px solid #e7ecf3;
    }

    .batch-pic-card__title {
        font-weight: 700;
        color: #1f2937;
    }

    .batch-pic-card__note {
        color: #6b7280;
        font-size: .85rem;
    }

    .batch-review-card {
        border: 1px solid #e7ecf3;
        border-radius: 14px;
        padding: 1rem 1.1rem;
        background: #fbfdff;
    }

    .batch-review-card__title {
        font-weight: 700;
        color: #1f2937;
        margin-bottom: .3rem;
    }

    .batch-review-card__text {
        color: #6b7280;
        font-size: .9rem;
        margin-bottom: .8rem;
    }

    .batch-transfer-preview {
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid #e7ecf3;
    }

    .batch-transfer-preview__image {
        width: 100%;
        max-height: 300px;
        object-fit: contain;
        border-radius: 12px;
        border: 1px solid #dbe3ee;
        background: #fff;
        padding: .35rem;
    }

    .batch-pic-detail {
        display: grid;
        gap: .35rem;
        margin-top: .7rem;
    }

    .batch-pic-detail div {
        font-size: .92rem;
        color: #374151;
    }

    .batch-pic-detail strong {
        display: inline-block;
        min-width: 74px;
        color: #111827;
    }

    .batch-modal .modal-content {
        border: 0;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 18px 45px rgba(0, 0, 0, 0.18);
    }

    .batch-modal .modal-header {
        border-bottom: 0;
    }

    .batch-modal .modal-body {
        background: #f6f8fb;
        padding: 1.25rem;
    }

    .batch-modal .modal-footer {
        border-top: 0;
        background: #eef2f7;
    }

    .batch-edit-header {
        background: linear-gradient(135deg, #0f4c81, #1d7ed6);
        color: #fff;
    }

    .batch-stage-note {
        border: 1px solid #dbeafe;
        border-radius: 14px;
        background: linear-gradient(135deg, #f8fbff, #eef6ff);
        padding: .9rem 1rem;
        color: #475569;
        font-size: .92rem;
        line-height: 1.55;
        margin-bottom: 1rem;
    }

    .batch-stage-cluster-box {
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        background: #fff;
        padding: 1rem 1.1rem;
        margin-bottom: 1rem;
    }

    .batch-stage-cluster-box__title {
        font-size: .82rem;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: #475569;
        margin-bottom: .85rem;
    }

    .batch-stage-cluster-box strong {
        display: block;
        color: #334155;
        margin-bottom: .18rem;
    }

    .modal-xxl {
        max-width: 78vw;
    }

    @media (max-width: 767.98px) {
        .batch-form-section__head,
        .batch-pic-card__head,
        .batch-progress-meta {
            flex-direction: column;
        }

        .modal-xxl {
            max-width: calc(100vw - 1rem);
            margin: .5rem auto;
        }
    }
</style>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">Detail Batch Approval</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="<?= base_url('Batch_Approval_MyRep') ?>" class="btn btn-outline-secondary">Kembali</a>
                    <?php if ($canHapus): ?>
                        <form method="post" action="<?= base_url('Batch_Approval_MyRep/deleteCluster') ?>" class="d-inline" onsubmit="return confirm('Hapus cluster ini beserta Batch Approval dan seluruh flow MyRep terkait?');">
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
                <div class="alert alert-success"><?= $flashSuccess ?></div>
            <?php endif; ?>
            <?php if (!empty($flashError)): ?>
                <div class="alert alert-danger"><?= $flashError ?></div>
            <?php endif; ?>

            <div class="card card-primary shadow-sm batch-info-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">Informasi Cluster & Batch</h3>
                    <?php if ($canEdit): ?>
                        <button type="button" class="btn btn-sm batch-edit-btn" data-toggle="modal" data-target="#modal-batch-edit-detail" data-role-guard-exempt="1">
                            <i class="fas fa-pen"></i>
                            Edit Batch Approval
                        </button>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <div class="batch-progress-wrap">
                        <div class="batch-progress-meta">
                            <div>Progress Batch Approval</div>
                            <div class="d-flex align-items-center" style="gap:.75rem;">
                                <div><?= htmlspecialchars(batchDetailStatusLabel($displayStageStatus)) ?> · <?= (int) $stageMeta['percent'] ?>%</div>
                                <?php if ($stageButtonTarget !== ''): ?>
                                    <button type="button" class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="<?= htmlspecialchars($stageButtonTarget) ?>">
                                        <i class="fas fa-edit"></i> <?= htmlspecialchars($stageButtonLabel) ?>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="batch-progress-caption mb-2"><?= htmlspecialchars($stageMeta['label']) ?></div>
                        <div class="progress batch-progress">
                            <div class="progress-bar <?= htmlspecialchars($stageMeta['class']) ?>" role="progressbar" style="width: <?= (int) $stageMeta['percent'] ?>%;" aria-valuenow="<?= (int) $stageMeta['percent'] ?>" aria-valuemin="0" aria-valuemax="100"><?= (int) $stageMeta['percent'] ?>%</div>
                        </div>
                    </div>

                    <div class="row batch-info-grid">
                        <div class="col-md-4"><strong>Cluster</strong><div><?= htmlspecialchars((string) ($cluster['cluster_name'] ?? '-')) ?></div></div>
                        <div class="col-md-2"><strong>Kab / Kota</strong><div><?= htmlspecialchars((string) ($cluster['city_name'] ?? '-')) ?></div></div>
                        <div class="col-md-2"><strong>Kecamatan</strong><div><?= !empty($cluster['district_name']) ? htmlspecialchars((string) $cluster['district_name']) : '-' ?></div></div>
                        <div class="col-md-2"><strong>Desa / Kelurahan</strong><div><?= !empty($cluster['village_name']) ? htmlspecialchars((string) $cluster['village_name']) : '-' ?></div></div>
                        <div class="col-md-2"><strong>Regional</strong><div><?= htmlspecialchars((string) ($cluster['regional_name'] ?? '-')) ?></div></div>
                        <div class="col-md-2"><strong>HP Donasi</strong><div><?= number_format((float) ($cluster['hp_donasi'] ?? 0), 0, ',', '.') ?></div></div>
                        <div class="col-md-2"><strong>Tanggal Pengajuan</strong><div><?= !empty($cluster['submission_date']) ? htmlspecialchars((string) $cluster['submission_date']) : '-' ?></div></div>
                    </div>
                    <hr>
                    <div class="row batch-info-grid">
                        <div class="col-md-3"><strong>Nominal Donasi</strong><div><?= number_format((float) ($cluster['nominal_pengajuan_area'] ?? 0), 0, ',', '.') ?></div></div>
                        <div class="col-md-3"><strong>Nominal / Homepass</strong><div><?= !is_null($cluster['nominal_per_homepass'] ?? null) ? number_format((float) $cluster['nominal_per_homepass'], 2, ',', '.') : '-' ?></div></div>
                        <div class="col-md-3"><strong>Nominal Approval EMR</strong><div><?= !is_null($cluster['nominal_nego_emr'] ?? null) ? number_format((float) $cluster['nominal_nego_emr'], 0, ',', '.') : '-' ?></div></div>
                        <div class="col-md-3"><strong>Nominal Pencairan</strong><div><?= !is_null($cluster['nominal_release_finance'] ?? null) ? number_format((float) $cluster['nominal_release_finance'], 0, ',', '.') : '-' ?></div></div>
                    </div>
                    <hr>
                    <div class="row batch-info-grid">
                        <div class="col-md-4"><strong>Penerima Dana</strong><div><?= htmlspecialchars((string) ($cluster['recipient_name'] ?? '-')) ?></div></div>
                        <div class="col-md-2"><strong>No HP</strong><div><?= !empty($cluster['recipient_phone']) ? htmlspecialchars((string) $cluster['recipient_phone']) : '-' ?></div></div>
                        <div class="col-md-3"><strong>Jabatan</strong><div><?= !empty($cluster['recipient_position']) ? htmlspecialchars((string) $cluster['recipient_position']) : '-' ?></div></div>
                        <div class="col-md-3"><strong>Periode</strong><div><?= !empty($cluster['recipient_period']) ? htmlspecialchars((string) $cluster['recipient_period']) : '-' ?></div></div>
                    </div>
                    <hr>
                    <div class="row batch-info-grid">
                        <div class="col-md-4"><strong>Bank</strong><div><?= htmlspecialchars((string) ($cluster['bank_name'] ?? '-')) ?></div></div>
                        <div class="col-md-4"><strong>No Rekening</strong><div><?= htmlspecialchars((string) ($cluster['bank_account_number'] ?? '-')) ?></div></div>
                        <div class="col-md-4"><strong>No Batch Astri</strong><div><?= !empty($cluster['astri_batch_number']) ? htmlspecialchars((string) $cluster['astri_batch_number']) : '-' ?></div></div>
                    </div>
                    <?php if (!empty($batchPics)): ?>
                        <hr>
                        <div class="row">
                            <?php foreach ($batchPics as $pic): ?>
                                <div class="col-md-4 mb-3">
                                    <div class="border rounded p-3 h-100 bg-light">
                                        <strong>PIC <?= (int) ($pic['pic_no'] ?? 0) ?></strong>
                                        <div class="batch-pic-detail">
                                            <div><strong>Nama</strong> <?= htmlspecialchars((string) ($pic['pic_name'] ?? '-')) ?></div>
                                            <div><strong>No HP</strong> <?= !empty($pic['pic_phone']) ? htmlspecialchars((string) $pic['pic_phone']) : '-' ?></div>
                                            <div><strong>Jabatan</strong> <?= !empty($pic['pic_position']) ? htmlspecialchars((string) $pic['pic_position']) : '-' ?></div>
                                            <div><strong>Periode</strong> <?= !empty($pic['pic_period']) ? htmlspecialchars((string) $pic['pic_period']) : '-' ?></div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php $currentDonationStage = strtoupper(trim((string) ($cluster['display_staging_status'] ?? $cluster['staging_status'] ?? ''))); ?>
            <div class="card card-outline card-success shadow-sm batch-disbursement-card">
                <div class="card-header batch-section-header">
                    <div>
                        <h3 class="card-title mb-0">Pencairan Donasi</h3>
                    </div>
                    <?php if ($canApprove && $canApprovalAction && $currentDonationStage === 'WAITING_FINANCE_RELEASE'): ?>
                        <button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#modal-set-released">
                            Set Released
                        </button>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <div class="row batch-disbursement-grid">
                        <div class="col-md-3 mb-3">
                            <div class="batch-disbursement-metric">
                                <span class="batch-disbursement-label">Status Finance</span>
                                <strong><?= htmlspecialchars(batchDetailStatusLabel($displayStageStatus)) ?></strong>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="batch-disbursement-metric">
                                <span class="batch-disbursement-label">Tanggal Transfer</span>
                                <strong><?= batchDetailDateText($cluster['released_at'] ?? '') ?></strong>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="batch-disbursement-metric">
                                <span class="batch-disbursement-label">Nominal Approval EMR</span>
                                <strong><?= $approvalNominal !== null ? number_format($approvalNominal, 0, ',', '.') : '-' ?></strong>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="batch-disbursement-metric">
                                <span class="batch-disbursement-label">Nominal Transfer</span>
                                <strong><?= $releaseNominal !== null ? number_format($releaseNominal, 0, ',', '.') : '-' ?></strong>
                            </div>
                        </div>
                    </div>
                    <div class="row align-items-stretch">
                        <div class="col-lg-4 mb-3 mb-lg-0">
                            <div class="batch-disbursement-metric h-100">
                                <span class="batch-disbursement-label">Selisih Transfer</span>
                                <strong class="<?= $releaseVariance !== null && abs($releaseVariance) > 0 ? 'text-danger' : 'text-success' ?>">
                                    <?= $releaseVariance !== null ? number_format($releaseVariance, 0, ',', '.') : '-' ?>
                                </strong>
                            </div>
                        </div>
                        <div class="col-lg-8">
                            <div class="batch-disbursement-proof">
                                <div class="batch-disbursement-label mb-2">Bukti Transfer</div>
                                <?php if (!empty($cluster['transfer_proof_file_path']) && $isTransferProofImage): ?>
                                    <a href="<?= base_url($transferProofPath) ?>" target="_blank" class="batch-disbursement-proof__preview">
                                        <img src="<?= base_url($transferProofPath) ?>" alt="Bukti Transfer">
                                    </a>
                                    <a href="<?= base_url($transferProofPath) ?>" target="_blank" class="btn btn-sm btn-outline-success mt-2">Lihat Bukti Transfer</a>
                                <?php elseif (!empty($cluster['transfer_proof_file_path'])): ?>
                                    <a href="<?= base_url($transferProofPath) ?>" target="_blank" class="btn btn-sm btn-outline-success">Download Bukti Transfer</a>
                                <?php else: ?>
                                    <div class="batch-disbursement-empty">Belum ada bukti transfer.</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm batch-sla-card">
                <div class="card-header batch-sla-card__header">
                    <h3 class="card-title mb-0">SLA SOP Donasi</h3>
                    <span class="batch-sla-total-badge">Total SLA 17 Hari Kalender</span>
                </div>
                <div class="card-body batch-sla-card__body">
                    <div class="table-responsive">
                        <table class="table table-sm batch-sla-table mb-0">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>SOP Process</th>
                                    <th>PIC</th>
                                    <th>Durasi SLA</th>
                                    <th>Target Selesai</th>
                                    <th>Aktual Selesai</th>
                                    <th>Sisa Hari</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($slaRows as $slaIndex => $slaRow): ?>
                                    <tr>
                                        <td class="batch-sla-no"><?= $slaIndex + 1 ?></td>
                                        <td class="batch-sla-process"><?= htmlspecialchars((string) $slaRow['name']) ?></td>
                                        <td>
                                            <span class="batch-sla-pic batch-sla-pic--<?= htmlspecialchars((string) ($slaRow['pic']['class'] ?? 'default')) ?>">
                                                <i class="fas fa-<?= htmlspecialchars((string) ($slaRow['pic']['icon'] ?? 'user')) ?>"></i>
                                                <?= htmlspecialchars((string) ($slaRow['pic']['label'] ?? '-')) ?>
                                            </span>
                                        </td>
                                        <td><?= (int) $slaRow['days'] ?> Hari</td>
                                        <td><?= batchDetailDateText($slaRow['target_date'] ?? '') ?></td>
                                        <td><?= batchDetailDateText($slaRow['actual_date'] ?? '') ?></td>
                                        <td><?= htmlspecialchars((string) ($slaRow['remaining_days'] ?? '-')) ?></td>
                                        <td>
                                            <span class="batch-sla-status batch-sla-status--<?= htmlspecialchars((string) ($slaRow['status']['class'] ?? 'secondary')) ?>">
                                                <i class="fas fa-<?= htmlspecialchars((string) ($slaRow['status']['icon'] ?? 'clock')) ?>"></i>
                                                <?= htmlspecialchars((string) ($slaRow['status']['label'] ?? '-')) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <?php
            $preZeynSummary = $donationDocumentSummary['PRE_ZEYN'] ?? [];
            $preZeynRequiredCount = (int) ($preZeynSummary['required'] ?? $cluster['pre_zeyn_doc_total'] ?? 0);
            $preZeynApprovedCount = (int) ($preZeynSummary['approved'] ?? $cluster['pre_zeyn_doc_approved'] ?? 0);
            $isPreZeynFullApproved = $preZeynRequiredCount > 0 && $preZeynApprovedCount >= $preZeynRequiredCount;
            $preZeynFinanceApprovedCount = (int) ($preZeynSummary['finance_approved'] ?? 0);
            $isPreZeynFinanceFullApproved = $preZeynRequiredCount > 0 && $preZeynFinanceApprovedCount >= $preZeynRequiredCount;
            $postZeynSummary = $donationDocumentSummary['POST_ZEYN'] ?? [];
            $postZeynRequiredCount = (int) ($postZeynSummary['required'] ?? $cluster['post_zeyn_doc_total'] ?? 0);
            $allAstriRequiredCount = $preZeynRequiredCount + $postZeynRequiredCount;
            $allAstriApprovedCount = (int) ($preZeynSummary['astri_approved'] ?? 0) + (int) ($postZeynSummary['astri_approved'] ?? 0);
            $allAstriRejectedCount = (int) ($preZeynSummary['astri_rejected'] ?? 0) + (int) ($postZeynSummary['astri_rejected'] ?? 0);
            $isAllAstriFullApproved = $allAstriRequiredCount > 0 && $allAstriRejectedCount === 0 && $allAstriApprovedCount >= $allAstriRequiredCount;
            ?>
            <?php if ($canApprove && $canApprovalAction): ?>
                <div class="card card-outline card-warning shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title mb-0">Aksi Staging Donasi</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php if ($currentDonationStage === 'PRE_ZEYN_FINANCE_APPROVED' && $isPreZeynFinanceFullApproved): ?>
                                <div class="col-md-12">
                                    <form method="post" action="<?= base_url('Batch_Approval_MyRep/updateStagingProgress') ?>">
                                        <input type="hidden" name="cluster_id" value="<?= (int) ($cluster['id_myrep_cluster'] ?? 0) ?>">
                                        <input type="hidden" name="id_batch_approval" value="<?= (int) ($cluster['id_batch_approval'] ?? 0) ?>">
                                        <input type="hidden" name="redirect_to_detail" value="1">
                                        <input type="hidden" name="target_stage" value="WAITING_FINANCE_RELEASE">
                                        <button type="submit" class="btn btn-success btn-sm">Ajukan ke Finance</button>
                                    </form>
                                </div>
                            <?php elseif ($currentDonationStage === 'WAITING_FINANCE_RELEASE'): ?>
                                <div class="col-md-12 text-muted">Lengkapi pencairan melalui container Pencairan Donasi.</div>
                            <?php elseif (in_array($currentDonationStage, ['WAITING_ASTRI_SUBMISSION', 'ASTRI_ON_REVIEW'], true)): ?>
                                <div class="col-md-12">
                                    <?php if ($isAllAstriFullApproved): ?>
                                        <form method="post" action="<?= base_url('Batch_Approval_MyRep/updateStagingProgress') ?>">
                                            <input type="hidden" name="cluster_id" value="<?= (int) ($cluster['id_myrep_cluster'] ?? 0) ?>">
                                            <input type="hidden" name="id_batch_approval" value="<?= (int) ($cluster['id_batch_approval'] ?? 0) ?>">
                                            <input type="hidden" name="redirect_to_detail" value="1">
                                            <input type="hidden" name="target_stage" value="ASTRI_APPROVED">
                                            <button type="submit" class="btn btn-success btn-sm">Lock Astri Approved</button>
                                        </form>
                                    <?php elseif ($allAstriRejectedCount > 0): ?>
                                        <span class="text-muted">Masih ada <?= $allAstriRejectedCount ?> dokumen rejected Astri. Upload revisi dokumen lalu review SITAC ulang.</span>
                                    <?php else: ?>
                                        <span class="text-muted">Menunggu semua dokumen Astri full approved.</span>
                                    <?php endif; ?>
                                </div>
                            <?php elseif (in_array($currentDonationStage, ['ASTRI_APPROVED', 'PO_DONASI', 'INVOICE'], true)): ?>
                                <div class="col-md-12">
                                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modal-donation-po-invoice">
                                        <?= !empty($cluster['po_donasi_number']) ? 'Edit PO/Invoice Donasi' : 'Tambah PO Donasi' ?>
                                    </button>
                                </div>
                            <?php else: ?>
                                <div class="col-md-12 text-muted">Tidak ada aksi staging untuk status saat ini.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php if ($currentDonationStage === 'WAITING_FINANCE_RELEASE'): ?>
                    <div class="modal fade" id="modal-set-released" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <form method="post" action="<?= base_url('Batch_Approval_MyRep/updateStagingProgress') ?>" enctype="multipart/form-data">
                                    <input type="hidden" name="cluster_id" value="<?= (int) ($cluster['id_myrep_cluster'] ?? 0) ?>">
                                    <input type="hidden" name="id_batch_approval" value="<?= (int) ($cluster['id_batch_approval'] ?? 0) ?>">
                                    <input type="hidden" name="redirect_to_detail" value="1">
                                    <input type="hidden" name="target_stage" value="RELEASED">
                                    <div class="modal-header bg-success text-white">
                                        <h5 class="modal-title">Set Released</h5>
                                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                    </div>
                                    <div class="modal-body">
                                        <label>Tanggal Release</label>
                                        <input type="date" name="released_at" class="form-control mb-2" value="<?= date('Y-m-d') ?>" required>
                                        <label>Nominal Release</label>
                                        <input type="text" name="nominal_release_finance" inputmode="decimal" class="form-control js-number-format mb-2" data-decimals="0" required>
                                        <label>Bukti Transfer</label>
                                        <div class="batch-dropzone js-dropzone batch-dropzone--photo">
                                            <input type="file" name="transfer_proof" class="js-dropzone-input" accept="image/*" required>
                                            <div class="batch-dropzone-content">
                                                <div class="batch-dropzone-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                                                <div class="batch-dropzone-title">Drag & drop gambar transfer</div>
                                                <div class="batch-dropzone-text">atau klik area ini untuk pilih file gambar</div>
                                                <div class="batch-dropzone-file js-dropzone-label">Belum ada file dipilih</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Tutup</button>
                                        <button type="submit" class="btn btn-success">Set Released</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if (in_array($currentDonationStage, ['ASTRI_APPROVED', 'PO_DONASI', 'INVOICE'], true)): ?>
                    <div class="modal fade" id="modal-donation-po-invoice" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog modal-lg" role="document">
                            <div class="modal-content">
                                <form method="post" action="<?= base_url('Batch_Approval_MyRep/saveDonationPoInvoice') ?>">
                                    <input type="hidden" name="cluster_id" value="<?= (int) ($cluster['id_myrep_cluster'] ?? 0) ?>">
                                    <input type="hidden" name="id_batch_approval" value="<?= (int) ($cluster['id_batch_approval'] ?? 0) ?>">
                                    <input type="hidden" name="redirect_to_detail" value="1">
                                    <div class="modal-header bg-primary text-white">
                                        <h5 class="modal-title">PO/Invoice Donasi</h5>
                                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-md-4"><label>Nomor PO</label><input type="text" name="po_donasi_number" class="form-control mb-2" value="<?= htmlspecialchars((string) ($cluster['po_donasi_number'] ?? ''), ENT_QUOTES) ?>" required></div>
                                            <div class="col-md-4"><label>Tanggal PO</label><input type="date" name="po_donasi_date" class="form-control mb-2" value="<?= htmlspecialchars((string) ($cluster['po_donasi_date'] ?? date('Y-m-d'))) ?>" required></div>
                                            <div class="col-md-4"><label>Nilai PO</label><input type="text" name="po_donasi_value" class="form-control mb-2" value="<?= htmlspecialchars((string) ($cluster['po_donasi_value'] ?? ''), ENT_QUOTES) ?>" required></div>
                                            <div class="col-md-4"><label>Status PO</label><input type="text" name="po_donasi_status" class="form-control mb-2" value="<?= htmlspecialchars((string) ($cluster['po_donasi_status'] ?? 'ISSUED'), ENT_QUOTES) ?>"></div>
                                            <div class="col-md-4"><label>Nomor Invoice</label><input type="text" name="invoice_donasi_number" class="form-control mb-2" value="<?= htmlspecialchars((string) ($cluster['invoice_donasi_number'] ?? ''), ENT_QUOTES) ?>"></div>
                                            <div class="col-md-4"><label>Tanggal Invoice</label><input type="date" name="invoice_donasi_date" class="form-control mb-2" value="<?= htmlspecialchars((string) ($cluster['invoice_donasi_date'] ?? '')) ?>"></div>
                                            <div class="col-md-4"><label>Nilai Invoice</label><input type="text" name="invoice_donasi_value" class="form-control mb-2" value="<?= htmlspecialchars((string) ($cluster['invoice_donasi_value'] ?? ''), ENT_QUOTES) ?>"></div>
                                            <div class="col-md-4"><label>Status Invoice</label><input type="text" name="invoice_donasi_status" class="form-control mb-2" value="<?= htmlspecialchars((string) ($cluster['invoice_donasi_status'] ?? ''), ENT_QUOTES) ?>"></div>
                                            <div class="col-md-4"><label>Remark Invoice</label><input type="text" name="invoice_donasi_remark" class="form-control mb-2" value="<?= htmlspecialchars((string) ($cluster['invoice_donasi_remark'] ?? ''), ENT_QUOTES) ?>"></div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Tutup</button>
                                        <button type="submit" class="btn btn-primary">Simpan PO/Invoice Donasi</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
            <div class="card card-outline card-primary shadow-sm batch-doc-card">
                <div class="card-header">
                    <h3 class="card-title">Dokumen Batch Approval</h3>
                </div>
                <div class="card-body">
                    <?php if (!$docReady): ?>
                        <div class="alert alert-warning mb-0">Tabel dokumen Batch Approval belum tersedia.</div>
                    <?php else: ?>
                        <div class="row">
                            <div class="col-lg-7 mb-3">
                                <div class="border rounded p-3 h-100">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <strong>RAR</strong>
                                            <div class="text-muted small">Status dokumen utama batch approval</div>
                                        </div>
                                        <span class="badge badge-<?= batchDetailBadgeClass($batchDocumentStatus) ?>"><?= htmlspecialchars($batchDocumentStatus) ?></span>
                                    </div>
                                    <div class="mb-2"><strong>File:</strong> <?= !empty($batchDocument['file_name']) ? htmlspecialchars((string) $batchDocument['file_name']) : '-' ?></div>
                                    <div class="mb-3"><strong>Remark:</strong> <?= !empty($batchDocument['remark']) ? htmlspecialchars((string) $batchDocument['remark']) : '-' ?></div>
                                    <button
                                        type="button"
                                        class="btn btn-sm <?= $batchDocumentCanUpload ? 'btn-primary' : 'btn-outline-primary' ?> js-open-batch-rar-modal"
                                        data-toggle="modal"
                                        data-target="#modal-batch-rar"
                                        data-role-guard-exempt="1"
                                        data-file-name="<?= htmlspecialchars((string) ($batchDocument['file_name'] ?? ''), ENT_QUOTES) ?>"
                                        data-file-path="<?= htmlspecialchars((string) ($batchDocument['file_path'] ?? ''), ENT_QUOTES) ?>"
                                        data-remark="<?= htmlspecialchars((string) ($batchDocument['remark'] ?? ''), ENT_QUOTES) ?>"
                                        data-status-label="<?= htmlspecialchars($batchDocumentStatus, ENT_QUOTES) ?>"
                                        data-can-upload="<?= $batchDocumentCanUpload ? '1' : '0' ?>">
                                        <?= $batchDocumentCanUpload ? 'Upload RAR' : 'Lihat RAR' ?>
                                    </button>
                                </div>
                            </div>
                            <div class="col-lg-5 mb-3">
                                <div class="batch-review-card h-100">
                                    <div class="batch-review-card__title">Review Dokumen Batch</div>
                                    <div class="batch-review-card__text">Review hanya muncul saat file sudah masuk dan masih menunggu keputusan.</div>
                                    <?php if ($batchDocumentCanReview): ?>
                                        <button type="button" class="btn btn-success btn-sm js-open-batch-review-modal" data-toggle="modal" data-target="#modal-batch-approve" data-file-id="<?= (int) ($batchDocument['id_doc_file'] ?? 0) ?>" data-file-name="<?= htmlspecialchars((string) ($batchDocument['file_name'] ?? ''), ENT_QUOTES) ?>">Approve</button>
                                        <button type="button" class="btn btn-danger btn-sm js-open-batch-review-modal" data-toggle="modal" data-target="#modal-batch-reject" data-file-id="<?= (int) ($batchDocument['id_doc_file'] ?? 0) ?>" data-file-name="<?= htmlspecialchars((string) ($batchDocument['file_name'] ?? ''), ENT_QUOTES) ?>">Reject</button>
                                    <?php else: ?>
                                        <span class="text-muted small">Belum ada dokumen yang perlu direview.</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="border rounded p-3">
                            <strong>History Dokumen</strong>
                            <div class="table-responsive mt-3">
                                <table class="table table-sm table-bordered mb-0">
                                    <thead>
                                        <tr>
                                            <th>Waktu</th>
                                            <th>Aksi</th>
                                            <th>File</th>
                                            <th>Remark</th>
                                            <th>Oleh</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($batchDocumentLogs as $log): ?>
                                            <tr>
                                                <td><?= htmlspecialchars((string) ($log['action_at'] ?? '-')) ?></td>
                                                <td><?= htmlspecialchars((string) ($log['action_type'] ?? '-')) ?></td>
                                                <td><?= htmlspecialchars((string) ($log['file_name'] ?? '-')) ?></td>
                                                <td><?= !empty($log['remark']) ? htmlspecialchars((string) $log['remark']) : '-' ?></td>
                                                <td><?= !empty($log['nama_user']) ? htmlspecialchars((string) $log['nama_user']) : 'System' ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($batchDocumentLogs)): ?>
                                            <tr><td colspan="5" class="text-center text-muted">Belum ada history dokumen.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($docReady): ?>
                <?php
                $renderDonationDocumentTable = function ($title, $groupKey, array $rows) use ($cluster, $canDonationUpload, $canApprove, $canApprovalAction, $canReplaceDonationFile, $canFinanceApprovalAction) {
                    $safeGroupKey = preg_replace('/[^A-Za-z0-9_\-]/', '', (string) $groupKey);
                    $requiredRows = array_filter($rows, static function ($row) {
                        return (int) ($row['is_required'] ?? 1) === 1;
                    });
                    $requiredCount = count($requiredRows);
                    $approvedCount = 0;
                    $financeApprovedCount = 0;
                    foreach ($requiredRows as $requiredRow) {
                        $requiredStatus = strtoupper(trim((string) ($requiredRow['status_file'] ?? '')));
                        if ($requiredStatus === 'APPROVED') {
                            $approvedCount++;
                        }
                        if ($requiredStatus === 'APPROVED' && strtoupper(trim((string) ($requiredRow['finance_status'] ?? 'NY'))) === 'APPROVED') {
                            $financeApprovedCount++;
                        }
                    }
                    $progressTotal = max(1, $requiredCount);
                    $progressPercent = (int) round(($approvedCount / $progressTotal) * 100);
                    $bulkUploadRows = [];
                    $bulkAstriRows = [];
                    $bulkFinanceRows = [];
                    $onReviewCount = 0;
                    foreach ($rows as $row) {
                        $rowStatus = strtoupper(trim((string) ($row['status_file'] ?? '')));
                        $rowFinanceStatus = strtoupper(trim((string) ($row['finance_status'] ?? 'NY')));
                        $rowAstriStatus = strtoupper(trim((string) ($row['astri_status'] ?? 'NY')));
                        $isAstriRejectedRevision = $rowStatus === 'APPROVED' && $rowAstriStatus === 'REJECTED';
                        if ($rowStatus === 'UPLOADED') {
                            $onReviewCount++;
                        }
                        if ($canDonationUpload && (in_array($rowStatus, ['', 'REJECTED'], true) || $isAstriRejectedRevision)) {
                            $bulkUploadRows[] = $row;
                        }
                        if ($canApprove && $canApprovalAction && (int) ($row['id_doc_file'] ?? 0) > 0 && $rowStatus === 'APPROVED') {
                            $bulkAstriRows[] = $row;
                        }
                        if ($canFinanceApprovalAction && (int) ($row['is_required'] ?? 1) === 1 && (int) ($row['id_doc_file'] ?? 0) > 0 && $rowStatus === 'APPROVED' && $rowFinanceStatus !== 'APPROVED') {
                            $bulkFinanceRows[] = $row;
                        }
                    }
                    $docStartDate = $groupKey === 'POST_ZEYN'
                        ? ($cluster['released_at'] ?? $cluster['finance_released_at'] ?? $cluster['updated_at'] ?? '')
                        : ($cluster['astri_batch_approved_at'] ?? $cluster['submission_date'] ?? $cluster['created_at'] ?? '');
                    $metaItems = $groupKey === 'POST_ZEYN' ? [
                        'Release' => batchDetailDateText($cluster['released_at'] ?? $cluster['finance_released_at'] ?? ''),
                        'Plan Doc' => '-',
                        'Actual Doc' => $requiredCount > 0 && $approvedCount >= $requiredCount ? batchDetailDateText($cluster['post_zeyn_doc_approved_at'] ?? $cluster['updated_at'] ?? '') : '-',
                        'Submit Astri' => batchDetailDateText($cluster['final_astri_submitted_at'] ?? ''),
                        'Approved Astri' => batchDetailDateText($cluster['final_astri_approved_at'] ?? ''),
                        'Aging Doc' => batchDetailAgingText($docStartDate),
                    ] : [
                        'Pengajuan' => batchDetailDateText($cluster['submission_date'] ?? ''),
                        'Batch Astri' => htmlspecialchars((string) ($cluster['astri_batch_number'] ?? '-')),
                        'Approved Batch' => batchDetailDateText($cluster['astri_batch_approved_at'] ?? ''),
                        'Submit Finance' => batchDetailDateText($cluster['finance_submitted_at'] ?? ''),
                        'Status Finance' => htmlspecialchars(batchDetailStatusLabel($cluster['display_staging_status'] ?? $cluster['staging_status'] ?? '-')),
                        'Aging Doc' => batchDetailAgingText($docStartDate),
                    ];
                    ?>
                    <div class="card donation-upload-panel mb-3">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h3 class="donation-upload-title"><?= htmlspecialchars($title) ?></h3>
                            <div class="donation-upload-actions">
                                <span class="donation-count-badge"><?= $approvedCount ?>/<?= $requiredCount ?></span>
                                <span class="donation-count-badge">Finance <?= $financeApprovedCount ?>/<?= $requiredCount ?></span>
                                <a href="<?= base_url('Batch_Approval_MyRep/downloadDonationDocumentBundle/' . (int) ($cluster['id_myrep_cluster'] ?? 0) . '/' . rawurlencode($groupKey)) ?>" class="btn btn-sm btn-outline-primary" title="Download dokumen yang sudah approved SITAC dan Finance">
                                    <i class="fas fa-download mr-1"></i>Download All
                                </a>
                                <?php if (!empty($bulkUploadRows)): ?>
                                    <button type="button" class="btn btn-sm btn-outline-success" data-toggle="modal" data-target="#modal-bulk-donation-<?= htmlspecialchars($safeGroupKey) ?>">Bulk Upload</button>
                                <?php endif; ?>
                                <?php if (!empty($bulkAstriRows)): ?>
                                    <button type="button" class="btn btn-sm btn-outline-dark" data-toggle="modal" data-target="#modal-bulk-astri-<?= htmlspecialchars($safeGroupKey) ?>">Bulk Astri</button>
                                <?php endif; ?>
                                <?php if ($canApprove && $canApprovalAction && $onReviewCount > 0): ?>
                                    <form method="post" action="<?= base_url('Batch_Approval_MyRep/approveAllDonationDocuments') ?>" class="mb-0 d-inline js-donation-ajax-form" data-processing-text="Approving..." data-success-text="Approve All">
                                        <input type="hidden" name="cluster_id" value="<?= (int) ($cluster['id_myrep_cluster'] ?? 0) ?>">
                                        <input type="hidden" name="redirect_to_detail" value="1">
                                        <input type="hidden" name="group_key" value="<?= htmlspecialchars($groupKey) ?>">
                                        <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Approve semua dokumen pada section ini?');">Approve All</button>
                                    </form>
                                <?php endif; ?>
                                <?php if ($canFinanceApprovalAction && !empty($bulkFinanceRows)): ?>
                                    <form method="post" action="<?= base_url('Batch_Approval_MyRep/approveAllDonationFinanceDocuments') ?>" class="mb-0 d-inline js-donation-ajax-form" data-processing-text="Approving Finance..." data-success-text="Approve All Finance">
                                        <input type="hidden" name="cluster_id" value="<?= (int) ($cluster['id_myrep_cluster'] ?? 0) ?>">
                                        <input type="hidden" name="redirect_to_detail" value="1">
                                        <input type="hidden" name="group_key" value="<?= htmlspecialchars($groupKey) ?>">
                                        <button type="submit" class="btn btn-sm btn-info" onclick="return confirm('Approve Finance semua dokumen wajib pada section ini?');">Approve All Finance</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="donation-group-progress">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <span class="small font-weight-bold text-dark">Progress Grup</span>
                                    <span class="small font-weight-bold text-dark"><?= $progressPercent ?>% (<?= $approvedCount ?>/<?= $requiredCount ?>)</span>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $progressPercent ?>%;" aria-valuenow="<?= $progressPercent ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                            <div class="donation-doc-meta">
                                <?php foreach ($metaItems as $metaLabel => $metaValue): ?>
                                    <div>
                                        <div class="donation-doc-meta-label"><?= htmlspecialchars($metaLabel) ?></div>
                                        <div class="donation-doc-meta-value"><?= $metaValue !== '' ? $metaValue : '-' ?></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover donation-doc-table">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Nama Dokumen</th>
                                            <th>Verification By</th>
                                            <th>Status SITAC</th>
                                            <th>Status Finance</th>
                                            <th>File</th>
                                            <th>Uploaded At</th>
                                            <th>Reviewed SITAC</th>
                                            <th>Approved SITAC</th>
                                            <th>Reviewed Finance</th>
                                            <th>Approved Finance</th>
                                            <th>Submit Astri</th>
                                            <th>Status Astri</th>
                                            <th>Remark</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($rows as $index => $row): ?>
                                            <?php
                                            $rawStatus = strtoupper(trim((string) ($row['status_file'] ?? '')));
                                            $statusLabel = $rawStatus === 'UPLOADED' ? 'ON REVIEW' : ($rawStatus !== '' ? $rawStatus : 'NOT UPLOADED');
                                            $fileId = (int) ($row['id_doc_file'] ?? 0);
                                            $docItemId = (int) ($row['id_doc_item'] ?? 0);
                                            $docName = (string) ($row['doc_name'] ?? '-');
                                            $isImageDonationDoc = batchDetailIsImageDonationDoc($docName);
                                            $collapseSuffix = htmlspecialchars($safeGroupKey . '-' . $docItemId);
                                            $astriStatus = strtoupper(trim((string) ($row['astri_status'] ?? 'NY')));
                                            $astriStatus = $astriStatus !== '' ? $astriStatus : 'NY';
                                            $financeStatusLabel = batchDetailFinanceDocumentLabel($row);
                                            ?>
                                            <tr>
                                                <td><?= $index + 1 ?></td>
                                                <td class="<?= $isImageDonationDoc ? 'donation-file-photo-cell' : '' ?>">
                                                    <strong><?= htmlspecialchars($docName) ?></strong>
                                                    <div class="text-muted small"><?= (int) ($row['is_required'] ?? 1) === 1 ? 'Wajib' : 'Opsional' ?></div>
                                                    <?php if (!empty($row['doc_requirement_note'])): ?>
                                                        <div class="text-muted small"><?= htmlspecialchars((string) $row['doc_requirement_note']) ?></div>
                                                    <?php endif; ?>
                                                </td>
                                                <td>SITAC HO / ASTRI</td>
                                                <td><span class="badge badge-<?= batchDetailBadgeClass($statusLabel) ?>"><?= htmlspecialchars($statusLabel) ?></span></td>
                                                <td><span class="badge badge-<?= batchDetailBadgeClass($financeStatusLabel) ?>"><?= htmlspecialchars($financeStatusLabel) ?></span></td>
                                                <td>
                                                    <?php if (!empty($row['file_name'])): ?>
                                                        <?php if ($isImageDonationDoc): ?>
                                                            <div class="donation-photo-cell">
                                                                <img
                                                                    src="<?= base_url('Batch_Approval_MyRep/previewDocument/' . $fileId) ?>"
                                                                    alt="<?= htmlspecialchars($docName, ENT_QUOTES) ?>"
                                                                    class="donation-photo-thumb js-open-donation-photo"
                                                                    data-image="<?= base_url('Batch_Approval_MyRep/previewDocument/' . $fileId) ?>"
                                                                    data-title="<?= htmlspecialchars($docName, ENT_QUOTES) ?>">
                                                            </div>
                                                        <?php else: ?>
                                                            <a href="<?= base_url('Batch_Approval_MyRep/previewDocument/' . $fileId) ?>" target="_blank" class="donation-file-link">
                                                                <?= htmlspecialchars(batchDetailShortFileName((string) $row['file_name'])) ?>
                                                            </a>
                                                        <?php endif; ?>
                                                        <div class="donation-file-actions">
                                                            <a href="<?= base_url('Batch_Approval_MyRep/downloadDocument/' . $fileId) ?>" class="btn btn-sm btn-outline-primary">
                                                                <i class="fas fa-download mr-1"></i>Download
                                                            </a>
                                                            <button
                                                                type="button"
                                                                class="btn btn-sm btn-outline-info js-doc-history"
                                                                data-toggle="modal"
                                                                data-target="#modal-doc-history"
                                                                data-doc-name="<?= htmlspecialchars((string) ($row['doc_name'] ?? '-'), ENT_QUOTES) ?>"
                                                                data-history='<?= htmlspecialchars(json_encode($fileId > 0 ? $this->MBatch_Approval_MyRep->getBatchFileLogs($fileId) : []), ENT_QUOTES) ?>'>
                                                                <i class="fas fa-history mr-1"></i>History
                                                            </button>
                                                        </div>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= batchDetailDateText($row['uploaded_at'] ?? '', 'd/m/Y H:i') ?></td>
                                                <td><?= batchDetailDateText($row['reviewed_at'] ?? '', 'd/m/Y H:i') ?></td>
                                                <td><?= batchDetailDateText($row['approved_at'] ?? '', 'd/m/Y H:i') ?></td>
                                                <td><?= batchDetailDateText($row['finance_reviewed_at'] ?? '', 'd/m/Y H:i') ?></td>
                                                <td><?= batchDetailDateText($row['finance_approved_at'] ?? '', 'd/m/Y H:i') ?></td>
                                                <td><?= batchDetailDateText($row['astri_submitted_date'] ?? '') ?></td>
                                                <td><span class="badge badge-<?= batchDetailBadgeClass($astriStatus) ?>"><?= htmlspecialchars($astriStatus) ?></span></td>
                                                <td>
                                                    <div><strong>Internal:</strong> <?= !empty($row['remark']) ? htmlspecialchars((string) $row['remark']) : '-' ?></div>
                                                    <div><strong>Finance:</strong> <?= !empty($row['finance_remark']) ? htmlspecialchars((string) $row['finance_remark']) : '-' ?></div>
                                                    <div><strong>ASTRI:</strong> <?= !empty($row['astri_remark']) ? htmlspecialchars((string) $row['astri_remark']) : '-' ?></div>
                                                </td>
                                                <td style="min-width:270px;">
                                                    <div class="donation-action-stack">
                                                        <?php if ($canDonationUpload && (in_array($rawStatus, ['', 'REJECTED'], true) || ($rawStatus === 'APPROVED' && $astriStatus === 'REJECTED'))): ?>
                                                            <?php $canMarkNotRequired = strtoupper(trim((string) ($row['doc_name'] ?? ''))) === 'FORM FREE WIFI & KTP'; ?>
                                                            <a
                                                                href="#modal-donation-upload"
                                                                role="button"
                                                                class="btn btn-sm btn-primary js-open-donation-upload-modal"
                                                                data-toggle="modal"
                                                                data-target="#modal-donation-upload"
                                                                data-role-guard-exempt="1"
                                                                data-doc-item-id="<?= $docItemId ?>"
                                                                data-group-key="<?= htmlspecialchars((string) $groupKey, ENT_QUOTES) ?>"
                                                                data-doc-name="<?= htmlspecialchars((string) ($row['doc_name'] ?? 'dokumen'), ENT_QUOTES) ?>"
                                                                data-file-name="<?= htmlspecialchars((string) ($row['file_name'] ?? ''), ENT_QUOTES) ?>"
                                                                data-remark="<?= htmlspecialchars((string) ($row['remark'] ?? ''), ENT_QUOTES) ?>"
                                                                data-can-not-required="<?= $canMarkNotRequired ? '1' : '0' ?>"
                                                                data-is-image-doc="<?= batchDetailIsImageDonationDoc($row['doc_name'] ?? '') ? '1' : '0' ?>"
                                                                data-replace-file="0">
                                                                <?= $rawStatus === 'APPROVED' && $astriStatus === 'REJECTED' ? 'Upload Revisi Astri' : 'Upload' ?>
                                                            </a>
                                                        <?php elseif ($canReplaceDonationFile && $rawStatus === 'APPROVED'): ?>
                                                            <?php $canMarkNotRequired = strtoupper(trim((string) ($row['doc_name'] ?? ''))) === 'FORM FREE WIFI & KTP'; ?>
                                                            <a
                                                                href="#modal-donation-upload"
                                                                role="button"
                                                                class="btn btn-sm btn-warning js-open-donation-upload-modal"
                                                                data-toggle="modal"
                                                                data-target="#modal-donation-upload"
                                                                data-role-guard-exempt="1"
                                                                data-doc-item-id="<?= $docItemId ?>"
                                                                data-group-key="<?= htmlspecialchars((string) $groupKey, ENT_QUOTES) ?>"
                                                                data-doc-name="<?= htmlspecialchars((string) ($row['doc_name'] ?? 'dokumen'), ENT_QUOTES) ?>"
                                                                data-file-name="<?= htmlspecialchars((string) ($row['file_name'] ?? ''), ENT_QUOTES) ?>"
                                                                data-remark="<?= htmlspecialchars((string) ($row['remark'] ?? ''), ENT_QUOTES) ?>"
                                                                data-can-not-required="<?= $canMarkNotRequired ? '1' : '0' ?>"
                                                                data-is-image-doc="<?= batchDetailIsImageDonationDoc($row['doc_name'] ?? '') ? '1' : '0' ?>"
                                                                data-replace-file="1">
                                                                Replace File
                                                            </a>
                                                        <?php endif; ?>
                                                        <?php if ($canApprove && $canApprovalAction && $fileId > 0 && $rawStatus === 'UPLOADED'): ?>
                                                            <form method="post" action="<?= base_url('Batch_Approval_MyRep/approveDonationDocument') ?>" class="d-inline js-donation-ajax-form" data-processing-text="Approving..." data-success-text="Approve">
                                                                <input type="hidden" name="cluster_id" value="<?= (int) ($cluster['id_myrep_cluster'] ?? 0) ?>">
                                                                <input type="hidden" name="redirect_to_detail" value="1">
                                                                <input type="hidden" name="id_doc_file" value="<?= $fileId ?>">
                                                                <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                                            </form>
                                                            <button
                                                                type="button"
                                                                class="btn btn-sm btn-danger js-open-donation-reject-modal"
                                                                data-role-guard-exempt="1"
                                                                data-toggle="modal"
                                                                data-target="#modal-donation-reject"
                                                                data-action-url="<?= base_url('Batch_Approval_MyRep/rejectDonationDocument') ?>"
                                                                data-processing-text="Rejecting..."
                                                                data-success-text="Simpan Reject"
                                                                data-title="Reject Dokumen"
                                                                data-submit-label="Simpan Reject"
                                                                data-file-id="<?= $fileId ?>"
                                                                data-doc-name="<?= htmlspecialchars($docName, ENT_QUOTES) ?>"
                                                                data-placeholder="Alasan reject">
                                                                Reject
                                                            </button>
                                                        <?php endif; ?>
                                                        <?php if ($canApprove && $canApprovalAction && $fileId > 0 && $rawStatus === 'APPROVED'): ?>
                                                            <button
                                                                type="button"
                                                                class="btn btn-sm btn-outline-primary js-open-donation-astri-modal"
                                                                data-toggle="modal"
                                                                data-target="#modal-donation-astri"
                                                                data-file-id="<?= $fileId ?>"
                                                                data-doc-name="<?= htmlspecialchars($docName, ENT_QUOTES) ?>"
                                                                data-astri-status="<?= htmlspecialchars($astriStatus, ENT_QUOTES) ?>"
                                                                data-astri-submitted-date="<?= !empty($row['astri_submitted_date']) ? htmlspecialchars(substr((string) $row['astri_submitted_date'], 0, 10), ENT_QUOTES) : '' ?>"
                                                                data-astri-approved-date="<?= !empty($row['astri_approved_date']) ? htmlspecialchars(substr((string) $row['astri_approved_date'], 0, 10), ENT_QUOTES) : '' ?>"
                                                                data-astri-remark="<?= htmlspecialchars((string) ($row['astri_remark'] ?? ''), ENT_QUOTES) ?>">
                                                                Update Astri
                                                            </button>
                                                        <?php endif; ?>
                                                        <?php if ($canFinanceApprovalAction && $fileId > 0 && (int) ($row['is_required'] ?? 1) === 1 && $rawStatus === 'APPROVED' && strtoupper(trim((string) ($row['finance_status'] ?? 'NY'))) !== 'APPROVED'): ?>
                                                            <form method="post" action="<?= base_url('Batch_Approval_MyRep/approveDonationFinanceDocument') ?>" class="d-inline js-donation-ajax-form" data-processing-text="Approving Finance..." data-success-text="Approve Finance">
                                                                <input type="hidden" name="cluster_id" value="<?= (int) ($cluster['id_myrep_cluster'] ?? 0) ?>">
                                                                <input type="hidden" name="redirect_to_detail" value="1">
                                                                <input type="hidden" name="id_doc_file" value="<?= $fileId ?>">
                                                                <button type="submit" class="btn btn-sm btn-info">Approve Finance</button>
                                                            </form>
                                                            <button
                                                                type="button"
                                                                class="btn btn-sm btn-danger js-open-donation-reject-modal"
                                                                data-role-guard-exempt="1"
                                                                data-toggle="modal"
                                                                data-target="#modal-donation-reject"
                                                                data-action-url="<?= base_url('Batch_Approval_MyRep/rejectDonationFinanceDocument') ?>"
                                                                data-processing-text="Rejecting Finance..."
                                                                data-success-text="Simpan Reject Finance"
                                                                data-title="Reject Finance Dokumen"
                                                                data-submit-label="Simpan Reject Finance"
                                                                data-file-id="<?= $fileId ?>"
                                                                data-doc-name="<?= htmlspecialchars($docName, ENT_QUOTES) ?>"
                                                                data-placeholder="Alasan reject Finance">
                                                                Reject Finance
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($rows)): ?>
                                            <tr><td colspan="15" class="text-center text-muted">Master dokumen belum tersedia.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php if (!empty($bulkUploadRows)): ?>
                                <div class="modal fade donation-doc-modal" id="modal-bulk-donation-<?= htmlspecialchars($safeGroupKey) ?>" tabindex="-1" role="dialog" aria-hidden="true">
                                    <div class="modal-dialog modal-xl" role="document">
                                        <div class="modal-content">
                                            <form method="post" action="<?= base_url('Batch_Approval_MyRep/uploadBulkDonationDocuments') ?>" enctype="multipart/form-data" class="js-donation-bulk-upload-form" data-role-guard-exempt="1">
                                                <input type="hidden" name="cluster_id" value="<?= (int) ($cluster['id_myrep_cluster'] ?? 0) ?>">
                                                <input type="hidden" name="redirect_to_detail" value="1">
                                                <input type="hidden" name="group_key" value="<?= htmlspecialchars($groupKey) ?>">
                                                <div class="modal-header">
                                                    <div>
                                                        <h4 class="modal-title mb-1">Bulk Upload <?= htmlspecialchars($title) ?></h4>
                                                        <p class="mb-0" style="opacity:.9;">Upload beberapa file sekaligus untuk satu grup dokumen.</p>
                                                    </div>
                                                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="donation-doc-modal-panel">
                                                        <div class="font-weight-bold mb-1">Ringkasan Grup</div>
                                                        <div class="text-muted">Progress saat ini <?= $approvedCount ?>/<?= $requiredCount ?> dokumen. Isi hanya file yang ingin diupload atau diganti.</div>
                                                    </div>
                                                    <div class="donation-doc-modal-panel">
                                                        <div class="table-responsive">
                                                            <table class="table table-bordered donation-bulk-table mb-0">
                                                                <thead>
                                                                    <tr>
                                                                        <th>No</th>
                                                                        <th>Dokumen</th>
                                                                        <th>Status Saat Ini</th>
                                                                        <th>Tidak Dibutuhkan</th>
                                                                        <th>Upload File</th>
                                                                        <th>Remark</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php foreach ($bulkUploadRows as $bulkIndex => $bulkRow): ?>
                                                                        <?php
                                                                        $bulkDocItemId = (int) ($bulkRow['id_doc_item'] ?? 0);
                                                                        $bulkDocName = (string) ($bulkRow['doc_name'] ?? '-');
                                                                        $bulkRawStatus = strtoupper(trim((string) ($bulkRow['status_file'] ?? '')));
                                                                        $bulkCanNotRequired = strtoupper(trim($bulkDocName)) === 'FORM FREE WIFI & KTP';
                                                                        $bulkIsImageDoc = batchDetailIsImageDonationDoc($bulkDocName);
                                                                        ?>
                                                                        <tr>
                                                                            <td><?= $bulkIndex + 1 ?></td>
                                                                            <td>
                                                                                <div class="font-weight-bold"><?= htmlspecialchars($bulkDocName) ?></div>
                                                                                <div class="text-muted small">Pilih file baru jika dokumen ini ingin diupload atau diperbarui.</div>
                                                                            </td>
                                                                            <td><span class="badge badge-<?= batchDetailBadgeClass($bulkRawStatus !== '' ? $bulkRawStatus : 'NOT UPLOADED') ?>"><?= htmlspecialchars($bulkRawStatus !== '' ? $bulkRawStatus : 'NOT UPLOADED') ?></span></td>
                                                                            <td>
                                                                                <?php if ($bulkCanNotRequired): ?>
                                                                                    <div class="custom-control custom-checkbox">
                                                                                        <input type="checkbox" class="custom-control-input js-bulk-donation-not-required" id="bulk_not_required_<?= htmlspecialchars($safeGroupKey) ?>_<?= $bulkDocItemId ?>" name="bulk_not_required_<?= $bulkDocItemId ?>" value="1" data-file-target="#bulk_file_<?= htmlspecialchars($safeGroupKey) ?>_<?= $bulkDocItemId ?>">
                                                                                        <label class="custom-control-label font-weight-bold" for="bulk_not_required_<?= htmlspecialchars($safeGroupKey) ?>_<?= $bulkDocItemId ?>">Tidak dibutuhkan dokumen</label>
                                                                                    </div>
                                                                                <?php else: ?>
                                                                                    <span class="text-muted">-</span>
                                                                                <?php endif; ?>
                                                                            </td>
                                                                            <td>
                                                                                <?php if ($bulkIsImageDoc): ?>
                                                                                    <div class="batch-dropzone js-dropzone batch-dropzone--photo">
                                                                                        <input type="file" name="bulk_file_<?= $bulkDocItemId ?>" id="bulk_file_<?= htmlspecialchars($safeGroupKey) ?>_<?= $bulkDocItemId ?>" class="js-dropzone-input" accept="image/*">
                                                                                        <div class="batch-dropzone-content">
                                                                                            <div class="batch-dropzone-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                                                                                            <div class="batch-dropzone-title">Drop gambar di sini</div>
                                                                                            <div class="batch-dropzone-text">atau klik area ini untuk pilih gambar</div>
                                                                                            <button type="button" class="btn btn-sm btn-outline-primary py-1 px-2 mb-2">Pilih Gambar</button>
                                                                                            <div class="batch-dropzone-file js-dropzone-label">Belum ada file dipilih</div>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="text-muted small mt-1">JPG, JPEG, atau PNG. Maksimal 20 MB.</div>
                                                                                <?php else: ?>
                                                                                    <input type="file" name="bulk_file_<?= $bulkDocItemId ?>" id="bulk_file_<?= htmlspecialchars($safeGroupKey) ?>_<?= $bulkDocItemId ?>" class="form-control">
                                                                                <?php endif; ?>
                                                                            </td>
                                                                            <td><input type="text" name="bulk_remark_<?= $bulkDocItemId ?>" class="form-control" placeholder="Remark"></td>
                                                                        </tr>
                                                                    <?php endforeach; ?>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-light border" data-dismiss="modal">Tutup</button>
                                                    <button type="submit" class="btn btn-success js-donation-bulk-upload-submit">Upload Semua Yang Diisi</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($bulkAstriRows)): ?>
                                <div class="modal fade donation-doc-modal" id="modal-bulk-astri-<?= htmlspecialchars($safeGroupKey) ?>" tabindex="-1" role="dialog" aria-hidden="true">
                                    <div class="modal-dialog modal-xl" role="document">
                                        <div class="modal-content">
                                            <form method="post" action="<?= base_url('Batch_Approval_MyRep/bulkUpdateDonationAstriStatus') ?>" class="js-donation-ajax-form" data-processing-text="Saving..." data-success-text="Simpan Bulk Astri">
                                                <input type="hidden" name="cluster_id" value="<?= (int) ($cluster['id_myrep_cluster'] ?? 0) ?>">
                                                <input type="hidden" name="redirect_to_detail" value="1">
                                                <input type="hidden" name="group_key" value="<?= htmlspecialchars($groupKey) ?>">
                                                <div class="modal-header" style="background: linear-gradient(135deg, #374151, #111827);">
                                                    <div>
                                                        <h4 class="modal-title mb-1">Bulk Astri <?= htmlspecialchars($title) ?></h4>
                                                        <p class="mb-0" style="opacity:.9;">Update status Astri untuk dokumen yang sudah APPROVED.</p>
                                                    </div>
                                                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="donation-doc-modal-panel">
                                                        <div class="font-weight-bold mb-1">Sinkronisasi Submit ke Astri</div>
                                                        <div class="text-muted">Isi tanggal submit saat status bukan NY, lalu pilih status review Astri untuk tiap dokumen.</div>
                                                    </div>
                                                    <div class="donation-doc-modal-panel">
                                                        <div class="table-responsive">
                                                            <table class="table table-bordered donation-bulk-table mb-0">
                                                                <thead>
                                                                    <tr>
                                                                        <th>No</th>
                                                                        <th>Dokumen</th>
                                                                        <th>Tanggal Submit Astri</th>
                                                                        <th>Status Astri</th>
                                                                        <th>Tanggal Approved Astri</th>
                                                                        <th>Remark Astri</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php foreach ($bulkAstriRows as $bulkAstriIndex => $bulkAstriRow): ?>
                                                                        <?php
                                                                        $bulkAstriFileId = (int) ($bulkAstriRow['id_doc_file'] ?? 0);
                                                                        $bulkAstriStatus = strtoupper(trim((string) ($bulkAstriRow['astri_status'] ?? 'NY')));
                                                                        $bulkAstriStatus = $bulkAstriStatus !== '' ? $bulkAstriStatus : 'NY';
                                                                        $bulkAstriSubmittedDate = !empty($bulkAstriRow['astri_submitted_date']) ? substr((string) $bulkAstriRow['astri_submitted_date'], 0, 10) : '';
                                                                        $bulkAstriApprovedDate = !empty($bulkAstriRow['astri_approved_date']) ? substr((string) $bulkAstriRow['astri_approved_date'], 0, 10) : '';
                                                                        $bulkAstriDateId = 'bulk_astri_date_' . htmlspecialchars($safeGroupKey) . '_' . $bulkAstriFileId;
                                                                        ?>
                                                                        <tr>
                                                                            <td><?= $bulkAstriIndex + 1 ?></td>
                                                                            <td>
                                                                                <div class="font-weight-bold"><?= htmlspecialchars((string) ($bulkAstriRow['doc_name'] ?? '-')) ?></div>
                                                                                <div class="text-muted small">Internal: <span class="badge badge-success">APPROVED</span></div>
                                                                                <input type="hidden" name="id_doc_file[]" value="<?= $bulkAstriFileId ?>">
                                                                            </td>
                                                                            <td>
                                                                                <input type="date" name="astri_submitted_date[<?= $bulkAstriFileId ?>]" id="<?= $bulkAstriDateId ?>" class="form-control js-astri-submitted-date" value="<?= htmlspecialchars($bulkAstriSubmittedDate) ?>" <?= $bulkAstriStatus !== 'NY' ? 'required' : '' ?>>
                                                                            </td>
                                                                            <td>
                                                                                <select name="astri_status[<?= $bulkAstriFileId ?>]" class="form-control js-astri-status js-bulk-astri-status" data-date-input="#<?= $bulkAstriDateId ?>" data-remark-input="#bulk_astri_remark_<?= htmlspecialchars($safeGroupKey) ?>_<?= $bulkAstriFileId ?>">
                                                                                    <?php foreach (['NY', 'ON REVIEW', 'APPROVED', 'REJECTED'] as $astriOption): ?>
                                                                                        <option value="<?= $astriOption ?>" <?= $bulkAstriStatus === $astriOption ? 'selected' : '' ?>><?= $astriOption ?></option>
                                                                                    <?php endforeach; ?>
                                                                                </select>
                                                                            </td>
                                                                            <td><input type="date" name="astri_approved_date[<?= $bulkAstriFileId ?>]" class="form-control" value="<?= htmlspecialchars($bulkAstriApprovedDate) ?>"></td>
                                                                            <td><textarea name="astri_remark[<?= $bulkAstriFileId ?>]" id="bulk_astri_remark_<?= htmlspecialchars($safeGroupKey) ?>_<?= $bulkAstriFileId ?>" class="form-control js-bulk-astri-remark" rows="2" placeholder="Remark Astri"><?= htmlspecialchars((string) ($bulkAstriRow['astri_remark'] ?? ''), ENT_QUOTES) ?></textarea></td>
                                                                        </tr>
                                                                    <?php endforeach; ?>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-light border" data-dismiss="modal">Tutup</button>
                                                    <button type="submit" class="btn btn-dark">Simpan Bulk Astri</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php
                };
                $renderDonationDocumentTable('Dokumen Tahap 1 Pra-Finance Zeyn', 'PRE_ZEYN', (array) ($preZeynDocumentRows ?? []));
                $renderDonationDocumentTable('Dokumen Tahap 2 Setelah Pembayaran', 'POST_ZEYN', (array) ($postZeynDocumentRows ?? []));
                ?>
            <?php endif; ?>

            <?php if (empty($postZeynDocumentRows)): ?>
            <div class="card card-outline card-primary shadow-sm batch-post-card">
                <div class="card-header">
                    <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap:.75rem;">
                        <h3 class="card-title mb-0">Post Donasi di Detail Batch Approval</h3>
                        <div class="d-flex align-items-center flex-wrap" style="gap:.5rem;">
                            <?php if ($postDonasiDocReady && !empty($postDonasiDownloadableRows)): ?>
                                <a href="<?= base_url('Post_Donasi_MyRep/downloadDocumentBundle/' . (int) $cluster['id_myrep_cluster']) ?>" class="btn btn-sm btn-outline-dark">
                                    Download RAR
                                </a>
                            <?php endif; ?>
                            <?php if ($postDonasiDocReady && $canApprove && $canApprovalAction && !empty($postDonasiReviewableRows)): ?>
                                <form method="post" action="<?= base_url('Post_Donasi_MyRep/approveAllDocuments') ?>" class="d-inline">
                                    <input type="hidden" name="cluster_id" value="<?= (int) $cluster['id_myrep_cluster'] ?>">
                                    <input type="hidden" name="redirect_to_batch_detail" value="1">
                                    <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Approve semua dokumen post donasi yang masih bisa diproses?');">
                                        Approve All
                                    </button>
                                </form>
                            <?php endif; ?>
                            <?php if ($postDonasiDocReady && $canDonationUpload && !empty($postDonasiUploadableRows)): ?>
                                <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#modal-post-bulk-upload" data-role-guard-exempt="1">
                                    Bulk Upload 12 Dokumen
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!$postDonasiDocReady): ?>
                        <div class="alert alert-warning mb-0">Tabel dokumen post donasi belum tersedia.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Dokumen</th>
                                        <th>Catatan</th>
                                        <th>Status</th>
                                        <th>File</th>
                                        <th>Upload</th>
                                        <?php if ($canApprove && $canApprovalAction): ?><th>Review</th><?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($postDonasiRows as $row): ?>
                                        <?php
                                        $postStatus = batchDetailDocumentLabel($row);
                                        $postRawStatus = strtoupper(trim((string) ($row['status_file'] ?? '')));
                                        $postCanUpload = $canDonationUpload && (in_array($postStatus, ['BELUM UPLOAD', 'LINKED DOKUMENT'], true) || $postRawStatus === 'REJECTED');
                                        $postCanReview = $canApprove && $canApprovalAction && (
                                            (!empty($row['id_doc_file']) && $postRawStatus === 'UPLOADED')
                                            || ($postStatus === 'LINKED DOKUMENT' && !empty($row['linked_source_file_id']))
                                        );
                                        ?>
                                        <tr>
                                            <td><strong><?= htmlspecialchars((string) ($row['doc_name'] ?? '-')) ?></strong></td>
                                            <td><?= htmlspecialchars((string) ($row['doc_requirement_note'] ?? '-')) ?></td>
                                            <td><span class="badge badge-<?= batchDetailBadgeClass($postStatus) ?>"><?= htmlspecialchars($postStatus) ?></span></td>
                                            <td>
                                                <?php if (!empty($row['file_name'])): ?>
                                                    <a href="<?= base_url('Post_Donasi_MyRep/previewDocument/' . (int) $row['id_doc_file']) ?>" target="_blank" class="donation-file-link">
                                                        <?= htmlspecialchars(batchDetailShortFileName((string) $row['file_name'])) ?>
                                                    </a>
                                                    <div class="donation-file-actions">
                                                        <a href="<?= base_url('Post_Donasi_MyRep/downloadDocument/' . (int) $row['id_doc_file']) ?>" class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-download mr-1"></i>Download
                                                        </a>
                                                        <button
                                                            type="button"
                                                            class="btn btn-sm btn-outline-info js-doc-history"
                                                            data-toggle="modal"
                                                            data-target="#modal-doc-history"
                                                            data-doc-name="<?= htmlspecialchars((string) ($row['doc_name'] ?? ''), ENT_QUOTES) ?>"
                                                            data-history='<?= htmlspecialchars(json_encode(!empty($row['id_doc_file']) ? $this->MPost_Donasi_MyRep->getFileLogs((int) $row['id_doc_file']) : []), ENT_QUOTES) ?>'>
                                                            <i class="fas fa-history mr-1"></i>History
                                                        </button>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-muted">Belum ada file</span>
                                                <?php endif; ?>
                                                <?php if (!empty($row['linked_source_preview_path'])): ?>
                                                    <div class="small text-primary mt-2">
                                                        Referensi otomatis: <?= htmlspecialchars((string) ($row['linked_source_doc_name'] ?? '-')) ?> (<?= htmlspecialchars((string) ($row['linked_source_flow_type'] ?? '-')) ?>)
                                                    </div>
                                                    <a href="<?= base_url((string) $row['linked_source_preview_path']) ?>" target="_blank" class="btn btn-sm btn-outline-info mt-1">
                                                        Lihat Referensi -&gt;
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                            <td style="min-width:220px;">
                                                <?php if ($postCanUpload): ?>
                                                    <a
                                                        href="#modal-post-upload"
                                                        role="button"
                                                        class="btn btn-sm btn-primary js-open-post-upload-modal"
                                                        data-toggle="modal"
                                                        data-target="#modal-post-upload"
                                                        data-role-guard-exempt="1"
                                                        data-doc-item-id="<?= (int) $row['id_doc_item'] ?>"
                                                        data-doc-name="<?= htmlspecialchars((string) ($row['doc_name'] ?? ''), ENT_QUOTES) ?>"
                                                        data-file-name="<?= htmlspecialchars((string) ($row['file_name'] ?? ''), ENT_QUOTES) ?>"
                                                        data-remark="<?= htmlspecialchars((string) ($row['remark'] ?? ''), ENT_QUOTES) ?>">
                                                        Upload
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted small">Upload tidak tersedia</span>
                                                <?php endif; ?>
                                            </td>
                                            <?php if ($canApprove && $canApprovalAction): ?>
                                                <td style="min-width:220px;">
                                                    <?php if ($postCanReview): ?>
                                                        <button
                                                            type="button"
                                                            class="btn btn-sm btn-success js-open-post-review-modal"
                                                            data-toggle="modal"
                                                            data-target="#modal-post-approve"
                                                            data-file-id="<?= (int) $row['id_doc_file'] ?>"
                                                            data-doc-item-id="<?= (int) $row['id_doc_item'] ?>"
                                                            data-doc-name="<?= htmlspecialchars((string) ($row['doc_name'] ?? ''), ENT_QUOTES) ?>"
                                                            data-linked-file-name="<?= htmlspecialchars((string) ($row['linked_source_file_name'] ?? ''), ENT_QUOTES) ?>">
                                                            Approve
                                                        </button>
                                                        <button
                                                            type="button"
                                                            class="btn btn-sm btn-danger js-open-post-review-modal"
                                                            data-toggle="modal"
                                                            data-target="#modal-post-reject"
                                                            data-file-id="<?= (int) $row['id_doc_file'] ?>"
                                                            data-doc-item-id="<?= (int) $row['id_doc_item'] ?>"
                                                            data-doc-name="<?= htmlspecialchars((string) ($row['doc_name'] ?? ''), ENT_QUOTES) ?>"
                                                            data-linked-file-name="<?= htmlspecialchars((string) ($row['linked_source_file_name'] ?? ''), ENT_QUOTES) ?>">
                                                            Reject
                                                        </button>
                                                    <?php elseif ($postRawStatus === 'APPROVED'): ?>
                                                        <span class="text-success small font-weight-bold">Sudah approved</span>
                                                    <?php elseif ($postRawStatus === 'REJECTED'): ?>
                                                        <span class="text-danger small font-weight-bold">Sudah rejected</span>
                                                    <?php else: ?>
                                                        <span class="text-muted small">Belum ada file untuk direview</span>
                                                    <?php endif; ?>
                                                </td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($postDonasiRows)): ?>
                                        <tr><td colspan="<?= ($canApprove && $canApprovalAction) ? '6' : '5' ?>" class="text-center text-muted">Belum ada dokumen post donasi.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<?php if ($canEdit): ?>
<?php $detailBatchApprovedDate = !empty($cluster['astri_batch_approved_at']) ? substr((string) $cluster['astri_batch_approved_at'], 0, 10) : date('Y-m-d'); ?>
<div class="modal fade" id="modal-batch-edit-detail" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xxl" role="document">
        <div class="modal-content batch-modal">
            <form method="post" action="<?= base_url('Batch_Approval_MyRep/updateBatchApproval') ?>" data-role-guard-exempt="1">
                <input type="hidden" name="cluster_id" value="<?= (int) $cluster['id_myrep_cluster'] ?>">
                <input type="hidden" name="id_batch_approval" value="<?= (int) $cluster['id_batch_approval'] ?>">
                <input type="hidden" name="redirect_to_detail" value="1">
                <div class="modal-header batch-edit-header">
                    <h5 class="modal-title">Edit Batch Approval</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="batch-form-section">
                        <div class="batch-form-section__title">Informasi Cluster</div>
                        <div class="row">
                            <div class="col-md-4"><div class="form-group"><label>Regional</label><input type="text" class="form-control" value="<?= htmlspecialchars((string) ($cluster['regional_name'] ?? '')) ?>" readonly></div></div>
                            <div class="col-md-4"><div class="form-group"><label>Provinsi</label><input type="text" class="form-control" value="<?= htmlspecialchars((string) ($cluster['province_name'] ?? '')) ?>" readonly></div></div>
                            <div class="col-md-4"><div class="form-group"><label>Kab / Kota</label><input type="text" class="form-control" value="<?= htmlspecialchars((string) ($cluster['city_name'] ?? '')) ?>" readonly></div></div>
                            <div class="col-md-4"><div class="form-group"><label>Kecamatan</label><input type="text" class="form-control" value="<?= htmlspecialchars((string) ($cluster['district_name'] ?? '')) ?>" readonly></div></div>
                            <div class="col-md-4"><div class="form-group"><label>Desa / Kelurahan</label><input type="text" class="form-control" value="<?= htmlspecialchars((string) ($cluster['village_name'] ?? '')) ?>" readonly></div></div>
                            <div class="col-md-8"><div class="form-group mb-md-0"><label>Cluster</label><input type="text" class="form-control" value="<?= htmlspecialchars((string) ($cluster['cluster_name'] ?? '')) ?>" readonly></div></div>
                            <div class="col-md-4"><div class="form-group mb-0"><label>Tanggal VALSAL</label><input type="text" class="form-control" value="<?= htmlspecialchars((string) ($cluster['valsal_date'] ?? '')) ?>" readonly></div></div>
                        </div>
                    </div>

                    <div class="batch-form-section">
                        <div class="batch-form-section__title">Data Pengajuan</div>
                        <div class="row">
                            <div class="col-md-3"><div class="form-group"><label>HP VALSAL Astri</label><input type="text" id="detail_edit_homepass_valsal" class="form-control js-number-format" data-decimals="0" value="<?= !is_null($cluster['homepass_valsal'] ?? null) ? htmlspecialchars(number_format((float) $cluster['homepass_valsal'], 0, ',', '.')) : '' ?>" readonly></div></div>
                            <div class="col-md-3"><div class="form-group"><label>HP Donasi Astri</label><input type="text" name="hp_donasi" id="detail_edit_hp_donasi" inputmode="numeric" class="form-control js-number-format" data-decimals="0" value="<?= !is_null($cluster['hp_donasi'] ?? null) ? htmlspecialchars(number_format((float) $cluster['hp_donasi'], 0, ',', '.')) : '' ?>" required></div></div>
                            <div class="col-md-3"><div class="form-group"><label>Tanggal Pengajuan Astri</label><input type="date" name="submission_date" id="detail_edit_submission_date" class="form-control" value="<?= htmlspecialchars((string) ($cluster['submission_date'] ?? '')) ?>"></div></div>
                            <div class="col-md-3"><div class="form-group"><label>No Batch Astri</label><input type="text" name="astri_batch_number" id="detail_edit_astri_batch_number" class="form-control" placeholder="Batch 2026-XX" value="<?= htmlspecialchars((string) ($cluster['astri_batch_number'] ?? '')) ?>" required></div></div>
                            <div class="col-md-3"><div class="form-group"><label>Staging</label><select name="staging_status" id="detail_edit_staging_status" class="form-control"><?php foreach ($statusOptions as $statusValue => $statusLabel): ?><option value="<?= $statusValue ?>" <?= strtoupper((string) ($cluster['staging_status'] ?? '')) === $statusValue ? 'selected' : '' ?>><?= $statusLabel ?></option><?php endforeach; ?></select></div></div>
                            <div class="col-md-6"><div class="form-group"><label>Nominal Donasi</label><input type="text" name="nominal_pengajuan_area" id="detail_edit_nominal_pengajuan_area" inputmode="decimal" class="form-control js-number-format" data-decimals="0" value="<?= !is_null($cluster['nominal_pengajuan_area'] ?? null) ? htmlspecialchars(number_format((float) $cluster['nominal_pengajuan_area'], 0, ',', '.')) : '' ?>" required><input type="hidden" name="nominal_nego_emr" id="detail_edit_nominal_nego_emr" value="<?= !is_null($cluster['nominal_pengajuan_area'] ?? null) ? htmlspecialchars((string) round((float) $cluster['nominal_pengajuan_area'])) : '' ?>"></div></div>
                            <div class="col-md-3"><div class="form-group"><label>Tanggal Batch Approval</label><input type="date" name="astri_batch_approved_at" id="detail_edit_astri_batch_approved_at" class="form-control" value="<?= htmlspecialchars($detailBatchApprovedDate) ?>" required></div></div>
                            <div class="col-md-3"><div class="form-group mb-0"><label>Nominal / Homepass</label><input type="text" id="detail_edit_nominal_per_homepass" class="form-control js-number-format" data-decimals="2" value="<?= !is_null($cluster['nominal_per_homepass'] ?? null) ? htmlspecialchars(number_format((float) $cluster['nominal_per_homepass'], 2, ',', '.')) : '' ?>" readonly></div></div>
                        </div>
                    </div>

                    <div class="batch-form-section">
                        <div class="batch-form-section__title">Free Wifi</div>
                        <div class="row">
                            <div class="col-md-6"><div class="form-group mb-md-0"><label>Jumlah Free Wifi</label><input type="text" name="free_wifi_qty" inputmode="numeric" class="form-control js-number-format" data-decimals="0" value="<?= !is_null($cluster['free_wifi_qty'] ?? null) ? htmlspecialchars(number_format((float) $cluster['free_wifi_qty'], 0, ',', '.')) : '' ?>"></div></div>
                            <div class="col-md-6"><div class="form-group mb-0"><label>Periode Free Wifi</label><input type="text" name="free_wifi_period_month" inputmode="numeric" class="form-control js-number-format" data-decimals="0" value="<?= !is_null($cluster['free_wifi_period_month'] ?? null) ? htmlspecialchars(number_format((float) $cluster['free_wifi_period_month'], 0, ',', '.')) : '' ?>"></div></div>
                        </div>
                    </div>

                    <div class="batch-form-section">
                        <div class="batch-form-section__title">Penerima Dana dan Bank</div>
                        <div class="row">
                            <div class="col-md-6"><div class="form-group"><label>Nama Bank</label><input type="text" name="bank_name" class="form-control" value="<?= htmlspecialchars((string) ($cluster['bank_name'] ?? '')) ?>" required></div></div>
                            <div class="col-md-6"><div class="form-group"><label>No Rekening</label><input type="text" name="bank_account_number" class="form-control" value="<?= htmlspecialchars((string) ($cluster['bank_account_number'] ?? '')) ?>" required></div></div>
                            <div class="col-md-4"><div class="form-group"><label>Nama Penerima Dana</label><input type="text" name="recipient_name" id="detail_edit_recipient_name" class="form-control js-recipient-source" value="<?= htmlspecialchars((string) ($cluster['recipient_name'] ?? '')) ?>" required></div></div>
                            <div class="col-md-4"><div class="form-group"><label>No HP Penerima</label><input type="text" name="recipient_phone" id="detail_edit_recipient_phone" class="form-control js-recipient-source" value="<?= htmlspecialchars((string) ($cluster['recipient_phone'] ?? '')) ?>"></div></div>
                            <div class="col-md-4"><div class="form-group"><label>Jabatan Penerima</label><input type="text" name="recipient_position" id="detail_edit_recipient_position" class="form-control js-recipient-source" value="<?= htmlspecialchars((string) ($cluster['recipient_position'] ?? '')) ?>"></div></div>
                            <div class="col-md-4"><div class="form-group mb-0"><label>Masa Jabatan</label><input type="text" name="recipient_period" id="detail_edit_recipient_period" class="form-control js-recipient-source" value="<?= htmlspecialchars((string) ($cluster['recipient_period'] ?? '')) ?>"></div></div>
                        </div>
                    </div>

                    <div class="batch-form-section js-finance-fields" data-stage-scope="detail-edit" style="display:none;">
                        <div class="batch-form-section__title">Release Finance</div>
                        <div class="row">
                            <div class="col-md-12"><div class="form-group mb-0"><label>Nominal Release Finance</label><input type="text" name="nominal_release_finance" id="detail_edit_nominal_release_finance" inputmode="decimal" class="form-control js-number-format" data-decimals="0" value="<?= !is_null($cluster['nominal_release_finance'] ?? null) ? htmlspecialchars(number_format((float) $cluster['nominal_release_finance'], 0, ',', '.')) : '' ?>"></div></div>
                        </div>
                    </div>

                    <div class="batch-form-section">
                        <div class="batch-form-section__head">
                            <div>
                                <div class="batch-form-section__title mb-1">PIC Approval</div>
                                <p class="batch-form-section__subtitle mb-0">PIC 1 otomatis mengikuti data penerima dana, lalu bisa ditambah bila diperlukan.</p>
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm" id="detail_edit_add_pic">Tambah PIC</button>
                        </div>
                        <div class="batch-pic-list" id="detail_edit_pic_rows">
                            <?php foreach ($initialPics as $picIndex => $pic): ?>
                                <?php $picNo = $picIndex + 1; ?>
                                <div class="batch-pic-card <?= $picNo === 1 ? 'batch-pic-card--primary' : '' ?>" data-pic-row="<?= $picNo ?>">
                                    <div class="batch-pic-card__head">
                                        <div>
                                            <div class="batch-pic-card__title">PIC <?= $picNo ?></div>
                                            <div class="batch-pic-card__note"><?= $picNo === 1 ? 'Otomatis mengikuti penerima dana' : 'PIC tambahan' ?></div>
                                        </div>
                                        <?php if ($picNo > 1): ?>
                                            <button type="button" class="btn btn-outline-danger btn-sm js-remove-pic-row">Hapus</button>
                                        <?php endif; ?>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3"><div class="form-group"><label>Nama PIC</label><input type="text" name="pic_name[]" class="form-control js-pic-name <?= $picNo === 1 ? 'js-primary-pic-field' : '' ?>" value="<?= htmlspecialchars((string) ($pic['pic_name'] ?? '')) ?>" <?= $picNo === 1 ? 'readonly' : '' ?>></div></div>
                                        <div class="col-md-3"><div class="form-group"><label>No HP PIC</label><input type="text" name="pic_phone[]" class="form-control js-pic-phone <?= $picNo === 1 ? 'js-primary-pic-field' : '' ?>" value="<?= htmlspecialchars((string) ($pic['pic_phone'] ?? '')) ?>" <?= $picNo === 1 ? 'readonly' : '' ?>></div></div>
                                        <div class="col-md-3"><div class="form-group"><label>Jabatan PIC</label><input type="text" name="pic_position[]" class="form-control js-pic-position <?= $picNo === 1 ? 'js-primary-pic-field' : '' ?>" value="<?= htmlspecialchars((string) ($pic['pic_position'] ?? '')) ?>" <?= $picNo === 1 ? 'readonly' : '' ?>></div></div>
                                        <div class="col-md-3"><div class="form-group mb-0"><label>Masa Jabatan PIC</label><input type="text" name="pic_period[]" class="form-control js-pic-period <?= $picNo === 1 ? 'js-primary-pic-field' : '' ?>" value="<?= htmlspecialchars((string) ($pic['pic_period'] ?? '')) ?>" <?= $picNo === 1 ? 'readonly' : '' ?>></div></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="batch-form-section batch-form-section--last">
                        <div class="batch-form-section__title">Remark</div>
                        <div class="form-group mb-0"><textarea name="remark_batch_approval" rows="3" class="form-control"><?= htmlspecialchars((string) ($cluster['remark_batch_approval'] ?? '')) ?></textarea></div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary">Update Batch Approval</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-doc-history" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content batch-modal">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title">History Dokumen</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <strong>Dokumen:</strong>
                    <span id="history_doc_label">-</span>
                </div>
                <ul class="doc-history-list" id="history_doc_items">
                    <li class="text-muted">Belum ada history.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php if ($docReady): ?>
<div class="modal fade donation-doc-modal" id="modal-donation-astri" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="post" action="<?= base_url('Batch_Approval_MyRep/updateDonationAstriStatus') ?>" class="js-donation-ajax-form" data-processing-text="Saving..." data-success-text="Simpan Astri">
                <input type="hidden" name="cluster_id" value="<?= (int) ($cluster['id_myrep_cluster'] ?? 0) ?>">
                <input type="hidden" name="redirect_to_detail" value="1">
                <input type="hidden" name="id_doc_file" id="donation_astri_file_id">
                <div class="modal-header" style="background: linear-gradient(135deg, #374151, #111827);">
                    <div>
                        <h4 class="modal-title mb-1">Update Status Astri</h4>
                        <p class="mb-0" style="opacity:.9;" id="donation_astri_doc_name">-</p>
                    </div>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="donation-doc-modal-panel">
                        <div class="font-weight-bold mb-1">Sinkronisasi Submit ke Astri</div>
                        <div class="text-muted">Isi tanggal submit saat dokumen sudah dikirim ke Astri, lalu update status sesuai review di sana.</div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="donation-doc-modal-panel">
                                <label class="font-weight-bold">Tanggal Submit Astri</label>
                                <input type="date" name="astri_submitted_date" id="donation_astri_submitted_date" class="form-control">
                                <small class="form-text text-muted">Wajib diisi jika status Astri bukan NY.</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="donation-doc-modal-panel">
                                <label class="font-weight-bold">Status Astri</label>
                                <select name="astri_status" id="donation_astri_status" class="form-control js-astri-status" data-date-input="#donation_astri_submitted_date">
                                    <?php foreach (['NY', 'ON REVIEW', 'APPROVED', 'REJECTED'] as $astriOption): ?>
                                        <option value="<?= $astriOption ?>"><?= $astriOption ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="donation-doc-modal-panel">
                        <label class="font-weight-bold">Tanggal Approved Astri</label>
                        <input type="date" name="astri_approved_date" id="donation_astri_approved_date" class="form-control">
                    </div>
                    <div class="donation-doc-modal-panel mb-0">
                        <label class="font-weight-bold">Remark Astri</label>
                        <textarea name="astri_remark" id="donation_astri_remark" class="form-control" rows="3" placeholder="Catatan submit / review Astri jika diperlukan"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-dark">Simpan Astri</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade donation-doc-modal" id="modal-donation-upload" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="post" action="<?= base_url('Batch_Approval_MyRep/uploadDonationDocument') ?>" enctype="multipart/form-data" id="donation-upload-form" data-role-guard-exempt="1">
                <input type="hidden" name="cluster_id" value="<?= (int) ($cluster['id_myrep_cluster'] ?? 0) ?>">
                <input type="hidden" name="redirect_to_detail" value="1">
                <input type="hidden" name="id_doc_item" id="donation_upload_doc_item_id">
                <input type="hidden" name="group_key" id="donation_upload_group_key">
                <input type="hidden" name="replace_file" id="donation_upload_replace_file" value="0">
                <div class="modal-header">
                    <div>
                        <h4 class="modal-title mb-1">Upload Dokumen</h4>
                        <p class="mb-0" style="opacity:.9;">Pilih satu file untuk item dokumen donasi yang sedang aktif.</p>
                    </div>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="donation-doc-highlight">
                        <div class="donation-doc-highlight-title" id="donation_upload_doc_name">-</div>
                        <p class="donation-doc-highlight-note" id="donation_upload_doc_note">File yang diupload akan masuk ke dokumen ini dan menggantikan file sebelumnya jika sudah ada.</p>
                    </div>
                    <div class="alert alert-warning py-2 px-3 small d-none" id="donation_upload_replace_note">Replace file akan mengubah status menjadi ON REVIEW.</div>
                    <div class="donation-doc-modal-panel">
                        <div class="form-group mb-0">
                            <label class="font-weight-bold" id="donation_upload_file_label">Pilih File</label>
                            <div class="batch-dropzone js-dropzone" id="donation_upload_dropzone">
                                <input type="file" name="file" id="donation_upload_file" class="js-dropzone-input" required>
                                <div class="batch-dropzone-content">
                                    <div class="batch-dropzone-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                                    <div class="batch-dropzone-title" id="donation_upload_dropzone_title">Drag & drop file di sini</div>
                                    <div class="batch-dropzone-text" id="donation_upload_dropzone_text">atau klik area ini untuk memilih file dari perangkat</div>
                                    <button type="button" class="btn btn-sm btn-outline-primary py-1 px-2 mb-2" id="donation_upload_choose_label">Pilih File</button>
                                    <div class="batch-dropzone-file js-dropzone-label" id="donation_upload_file_name">Belum ada file dipilih</div>
                                </div>
                            </div>
                            <small class="form-text text-muted" id="donation_upload_file_hint">Format mengikuti jenis dokumen. Maksimal dokumen 20 MB.</small>
                        </div>
                    </div>
                    <div class="donation-doc-modal-panel" id="donation_upload_not_required_panel">
                        <div class="form-group mb-0">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="donation_upload_not_required" name="is_document_not_required" value="1">
                                <label class="custom-control-label font-weight-bold" for="donation_upload_not_required">Tidak dibutuhkan dokumen</label>
                            </div>
                            <small class="form-text text-muted">Jika dicentang, item tetap dihitung submitted dan tetap melalui proses reject/approve HO.</small>
                        </div>
                    </div>
                    <div class="donation-doc-modal-panel">
                        <div class="form-group mb-0">
                            <label class="font-weight-bold">Remark</label>
                            <textarea name="remark" id="donation_upload_remark" class="form-control" rows="3" placeholder="Tambahkan catatan singkat jika diperlukan"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-success" id="donation_upload_submit" data-role-guard-exempt="1">Upload Dokumen</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade donation-doc-modal" id="modal-donation-reject" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="post" action="#" id="donation-reject-form" class="js-donation-ajax-form" data-processing-text="Rejecting..." data-success-text="Simpan Reject" data-role-guard-exempt="1">
                <input type="hidden" name="cluster_id" value="<?= (int) ($cluster['id_myrep_cluster'] ?? 0) ?>">
                <input type="hidden" name="redirect_to_detail" value="1">
                <input type="hidden" name="id_doc_file" id="donation_reject_file_id">
                <div class="modal-header" style="background: linear-gradient(135deg, #dc3545, #7f1d1d);">
                    <div>
                        <h4 class="modal-title mb-1" id="donation_reject_title">Reject Dokumen</h4>
                        <p class="mb-0" style="opacity:.9;" id="donation_reject_doc_name">-</p>
                    </div>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="donation-doc-modal-panel mb-0">
                        <label class="font-weight-bold">Remark Reject</label>
                        <textarea name="remark" id="donation_reject_remark" class="form-control" rows="4" placeholder="Alasan reject" required></textarea>
                        <small class="form-text text-muted">Remark wajib diisi agar area tahu dokumen mana yang perlu direvisi.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-danger" id="donation_reject_submit">Simpan Reject</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="modal fade" id="modal-batch-rar" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content batch-modal">
            <form method="post" action="<?= base_url('Batch_Approval_MyRep/uploadDocument') ?>" enctype="multipart/form-data" data-role-guard-exempt="1">
                <input type="hidden" name="cluster_id" value="<?= (int) $cluster['id_myrep_cluster'] ?>">
                <input type="hidden" name="redirect_to_detail" value="1">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Dokumen RAR Batch Approval</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2"><strong>Status:</strong> <span id="batch_rar_status_label"><?= htmlspecialchars($batchDocumentStatus) ?></span></div>
                    <div class="mb-2"><strong>File saat ini:</strong> <span id="batch_rar_current_file">-</span></div>
                    <div id="batch_rar_upload_section">
                        <div class="form-group">
                            <div class="batch-dropzone js-dropzone">
                                <input type="file" name="file" class="js-dropzone-input">
                                <div class="batch-dropzone-content">
                                    <div class="batch-dropzone-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                                    <div class="batch-dropzone-title">Drag & drop file RAR</div>
                                    <div class="batch-dropzone-text">Atau klik area ini untuk memilih file</div>
                                    <div class="batch-dropzone-file js-dropzone-label">Belum ada file dipilih</div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group"><input type="text" name="remark" id="batch_rar_remark" class="form-control form-control-sm" placeholder="Remark upload"></div>
                    </div>
                    <div class="alert alert-light border d-none" id="batch_rar_readonly_note">File sudah diproses. Upload baru hanya tersedia saat status `REJECTED` atau `BELUM UPLOAD`.</div>
                    <div class="form-group mb-0">
                        <a href="#" target="_blank" id="batch_rar_preview_link" class="btn btn-sm btn-outline-secondary d-none">Preview File Saat Ini</a>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" id="batch_rar_submit_btn" class="btn btn-primary btn-sm">Simpan RAR</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-stage-to-myrep" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xxl" role="document">
        <div class="modal-content batch-modal">
            <form method="post" action="<?= base_url('Batch_Approval_MyRep/updateStagingProgress') ?>">
                <input type="hidden" name="cluster_id" value="<?= (int) $cluster['id_myrep_cluster'] ?>">
                <input type="hidden" name="id_batch_approval" value="<?= (int) $cluster['id_batch_approval'] ?>">
                <input type="hidden" name="target_stage" value="WAITING MYREP">
                <input type="hidden" name="redirect_to_detail" value="1">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Edit Staging ke WAITING MYREP</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="batch-stage-note">
                        Stagging dirubah jika telah input batch approval ke system ASTRI.
                    </div>
                    <div class="batch-stage-cluster-box">
                        <div class="batch-stage-cluster-box__title">Informasi Cluster</div>
                        <div class="row">
                            <div class="col-md-6"><strong>Cluster</strong><div><?= htmlspecialchars((string) ($cluster['cluster_name'] ?? '-')) ?></div></div>
                            <div class="col-md-3"><strong>Regional</strong><div><?= htmlspecialchars((string) ($cluster['regional_name'] ?? '-')) ?></div></div>
                            <div class="col-md-3"><strong>Kab / Kota</strong><div><?= htmlspecialchars((string) ($cluster['city_name'] ?? '-')) ?></div></div>
                            <div class="col-md-6 mt-2"><strong>Kecamatan</strong><div><?= !empty($cluster['district_name']) ? htmlspecialchars((string) $cluster['district_name']) : '-' ?></div></div>
                            <div class="col-md-6 mt-2"><strong>Desa / Kelurahan</strong><div><?= !empty($cluster['village_name']) ? htmlspecialchars((string) $cluster['village_name']) : '-' ?></div></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Staging Saat Ini</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars(batchDetailStatusLabel($displayStageStatus)) ?>" readonly>
                    </div>
                    <div class="form-group mb-0">
                        <label>Tanggal Input ke Astri</label>
                        <input type="date" name="submitted_to_astri_at" class="form-control" value="<?= !empty($cluster['submitted_to_astri_at']) ? htmlspecialchars(substr((string) $cluster['submitted_to_astri_at'], 0, 10)) : date('Y-m-d') ?>" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary">Ubah ke WAITING MYREP</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-stage-to-finance" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xxl" role="document">
        <div class="modal-content batch-modal">
            <form method="post" action="<?= base_url('Batch_Approval_MyRep/updateStagingProgress') ?>">
                <input type="hidden" name="cluster_id" value="<?= (int) $cluster['id_myrep_cluster'] ?>">
                <input type="hidden" name="id_batch_approval" value="<?= (int) $cluster['id_batch_approval'] ?>">
                <input type="hidden" name="target_stage" value="WAITING FINANCE">
                <input type="hidden" name="redirect_to_detail" value="1">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">Edit Staging ke WAITING FINANCE</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="batch-stage-note">
                        Stagging dirubah saat ASTRI APPROVED dan akan dilakukan pengajuan ke finance.
                    </div>
                    <div class="batch-stage-cluster-box">
                        <div class="batch-stage-cluster-box__title">Informasi Cluster</div>
                        <div class="row">
                            <div class="col-md-6"><strong>Cluster</strong><div><?= htmlspecialchars((string) ($cluster['cluster_name'] ?? '-')) ?></div></div>
                            <div class="col-md-3"><strong>Regional</strong><div><?= htmlspecialchars((string) ($cluster['regional_name'] ?? '-')) ?></div></div>
                            <div class="col-md-3"><strong>Kab / Kota</strong><div><?= htmlspecialchars((string) ($cluster['city_name'] ?? '-')) ?></div></div>
                            <div class="col-md-6 mt-2"><strong>Kecamatan</strong><div><?= !empty($cluster['district_name']) ? htmlspecialchars((string) $cluster['district_name']) : '-' ?></div></div>
                            <div class="col-md-6 mt-2"><strong>Desa / Kelurahan</strong><div><?= !empty($cluster['village_name']) ? htmlspecialchars((string) $cluster['village_name']) : '-' ?></div></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Nominal Pengajuan Donasi</label>
                        <input type="text" class="form-control js-number-format" data-decimals="0" value="<?= !is_null($cluster['nominal_pengajuan_area'] ?? null) ? htmlspecialchars(number_format((float) $cluster['nominal_pengajuan_area'], 0, ',', '.')) : '' ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label>Nomor Batch</label>
                        <input type="text" name="astri_batch_number" class="form-control" value="<?= htmlspecialchars((string) ($cluster['astri_batch_number'] ?? '')) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Nominal Approval dari MYREP</label>
                        <input type="text" name="nominal_nego_emr" class="form-control js-number-format" data-decimals="0" value="<?= htmlspecialchars((string) ($cluster['nominal_nego_emr'] ?? '')) ?>" required>
                    </div>
                    <div class="form-group mb-0">
                        <label>Tanggal Approved MYREP</label>
                        <input type="date" name="submitted_to_finance_at" class="form-control" value="<?= !empty($cluster['submitted_to_finance_at']) ? htmlspecialchars(substr((string) $cluster['submitted_to_finance_at'], 0, 10)) : date('Y-m-d') ?>" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-warning">Ubah ke WAITING FINANCE</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-stage-to-released" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xxl" role="document">
        <div class="modal-content batch-modal">
            <form method="post" action="<?= base_url('Batch_Approval_MyRep/updateStagingProgress') ?>" enctype="multipart/form-data">
                <input type="hidden" name="cluster_id" value="<?= (int) $cluster['id_myrep_cluster'] ?>">
                <input type="hidden" name="id_batch_approval" value="<?= (int) $cluster['id_batch_approval'] ?>">
                <input type="hidden" name="target_stage" value="RELEASED">
                <input type="hidden" name="redirect_to_detail" value="1">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title">Edit Staging ke Donasi Dibayarkan</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="batch-stage-note">
                        Stagging dirubah saat sudah pencarian dari finance terkait donasi.
                    </div>
                    <div class="batch-stage-cluster-box">
                        <div class="batch-stage-cluster-box__title">Informasi Cluster</div>
                        <div class="row">
                            <div class="col-md-6"><strong>Cluster</strong><div><?= htmlspecialchars((string) ($cluster['cluster_name'] ?? '-')) ?></div></div>
                            <div class="col-md-3"><strong>Regional</strong><div><?= htmlspecialchars((string) ($cluster['regional_name'] ?? '-')) ?></div></div>
                            <div class="col-md-3"><strong>Kab / Kota</strong><div><?= htmlspecialchars((string) ($cluster['city_name'] ?? '-')) ?></div></div>
                            <div class="col-md-6 mt-2"><strong>Kecamatan</strong><div><?= !empty($cluster['district_name']) ? htmlspecialchars((string) $cluster['district_name']) : '-' ?></div></div>
                            <div class="col-md-6 mt-2"><strong>Desa / Kelurahan</strong><div><?= !empty($cluster['village_name']) ? htmlspecialchars((string) $cluster['village_name']) : '-' ?></div></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Nominal Pengajuan Donasi</label>
                        <input type="text" class="form-control js-number-format" data-decimals="0" value="<?= !is_null($cluster['nominal_pengajuan_area'] ?? null) ? htmlspecialchars(number_format((float) $cluster['nominal_pengajuan_area'], 0, ',', '.')) : '' ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label>Nominal Approval EMR</label>
                        <input type="text" class="form-control js-number-format" data-decimals="0" value="<?= !is_null($cluster['nominal_nego_emr'] ?? null) ? htmlspecialchars(number_format((float) $cluster['nominal_nego_emr'], 0, ',', '.')) : '' ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label>Tanggal Pencairan</label>
                        <input type="date" name="released_at" class="form-control" value="<?= !empty($cluster['released_at']) ? htmlspecialchars(substr((string) $cluster['released_at'], 0, 10)) : date('Y-m-d') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Nominal Pencairan</label>
                        <input type="text" name="nominal_release_finance" class="form-control js-number-format" data-decimals="0" value="<?= htmlspecialchars((string) ($cluster['nominal_release_finance'] ?? '')) ?>" required>
                    </div>
                    <div class="form-group mb-0">
                        <label>Foto Transfer</label>
                        <div class="batch-dropzone js-dropzone">
                            <input type="file" name="transfer_proof" class="js-dropzone-input" accept="image/*" required>
                            <div class="batch-dropzone-content">
                                <div class="batch-dropzone-icon"><i class="fas fa-file-upload"></i></div>
                                <div class="batch-dropzone-title">Drag & drop foto transfer</div>
                                <div class="batch-dropzone-text">Atau klik area ini untuk memilih file</div>
                                <div class="batch-dropzone-file js-dropzone-label">Belum ada file dipilih</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-dark">Ubah ke Donasi Dibayarkan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="modal-post-upload" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content batch-modal">
            <form method="post" action="<?= base_url('Post_Donasi_MyRep/uploadDocument') ?>" enctype="multipart/form-data" data-role-guard-exempt="1">
                <input type="hidden" name="cluster_id" value="<?= (int) $cluster['id_myrep_cluster'] ?>">
                <input type="hidden" name="id_doc_item" id="post_upload_doc_item_id">
                <input type="hidden" name="redirect_to_batch_detail" value="1">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">Upload Post Donasi</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2"><strong>Dokumen:</strong> <span id="post_upload_doc_name">-</span></div>
                    <div class="mb-2"><strong>File saat ini:</strong> <span id="post_upload_file_name">-</span></div>
                    <div class="form-group">
                        <div class="batch-dropzone js-dropzone">
                            <input type="file" name="file" class="js-dropzone-input">
                            <div class="batch-dropzone-content">
                                <div class="batch-dropzone-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                                <div class="batch-dropzone-title">Drag & drop dokumen</div>
                                <div class="batch-dropzone-text">Atau klik area ini untuk memilih file</div>
                                <div class="batch-dropzone-file js-dropzone-label">Belum ada file dipilih</div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group"><input type="text" name="remark" id="post_upload_remark" class="form-control form-control-sm" placeholder="Remark upload"></div>
                    <div class="form-group form-check mb-0">
                        <input type="checkbox" class="form-check-input" id="post_upload_not_required" name="is_document_not_required" value="1">
                        <label class="form-check-label" for="post_upload_not_required">Tidak dibutuhkan</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-info btn-sm">Simpan Dokumen</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="modal fade" id="modal-post-bulk-upload" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content batch-modal">
            <form method="post" action="<?= base_url('Post_Donasi_MyRep/uploadBulkDocuments') ?>" enctype="multipart/form-data" data-role-guard-exempt="1">
                <input type="hidden" name="cluster_id" value="<?= (int) $cluster['id_myrep_cluster'] ?>">
                <input type="hidden" name="redirect_to_batch_detail" value="1">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Bulk Upload Post Donasi</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="post-bulk-summary">
                        <div>
                            <div class="post-bulk-summary__title">Upload beberapa dokumen sekaligus</div>
                            <p class="post-bulk-summary__text">Setiap kartu mewakili satu item dokumen. Cek status dan catatannya dulu, lalu pilih file yang ingin diupload dan isi remark jika diperlukan.</p>
                        </div>
                        <div class="post-bulk-summary__badge"><?= count($postDonasiUploadableRows) ?></div>
                    </div>
                    <?php if (empty($postDonasiUploadableRows)): ?>
                        <div class="text-muted">Tidak ada dokumen yang tersedia untuk bulk upload.</div>
                    <?php else: ?>
                        <div class="post-bulk-grid">
                            <?php foreach ($postDonasiUploadableRows as $bulkIndex => $bulkRow): ?>
                                <div class="post-bulk-card">
                                    <input type="hidden" name="bulk_doc_item_ids[]" value="<?= (int) ($bulkRow['id_doc_item'] ?? 0) ?>">
                                    <div class="post-bulk-card__header">
                                        <div>
                                            <div class="post-bulk-card__eyebrow">Dokumen <?= $bulkIndex + 1 ?></div>
                                            <h6 class="post-bulk-card__title"><?= htmlspecialchars((string) ($bulkRow['doc_name'] ?? '-')) ?></h6>
                                        </div>
                                        <span class="badge badge-<?= batchDetailBadgeClass(batchDetailDocumentLabel($bulkRow)) ?>"><?= htmlspecialchars(batchDetailDocumentLabel($bulkRow)) ?></span>
                                    </div>
                                    <div class="post-bulk-card__body">
                                        <div class="post-bulk-card__meta">
                                            <div class="post-bulk-meta-box">
                                                <div class="post-bulk-meta-box__label">Catatan Dokumen</div>
                                                <div class="post-bulk-meta-box__value"><?= !empty($bulkRow['doc_requirement_note']) ? htmlspecialchars((string) $bulkRow['doc_requirement_note']) : 'Tidak ada catatan khusus.' ?></div>
                                            </div>
                                            <div class="post-bulk-meta-box">
                                                <div class="post-bulk-meta-box__label">File Saat Ini</div>
                                                <div class="post-bulk-meta-box__value">
                                                    <?= !empty($bulkRow['file_name']) ? htmlspecialchars((string) $bulkRow['file_name']) : 'Belum ada file aktif.' ?>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="batch-dropzone js-dropzone post-bulk-dropzone">
                                            <input type="file" name="bulk_file_<?= (int) ($bulkRow['id_doc_item'] ?? 0) ?>" class="js-dropzone-input">
                                            <div class="batch-dropzone-content">
                                                <div class="batch-dropzone-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                                                <div class="batch-dropzone-title">Pilih file untuk <?= htmlspecialchars((string) ($bulkRow['doc_name'] ?? 'dokumen')) ?></div>
                                                <div class="batch-dropzone-text">Drag & drop file di sini atau klik area ini</div>
                                                <div class="batch-dropzone-file js-dropzone-label">Belum ada file dipilih</div>
                                            </div>
                                        </div>

                                        <div class="post-bulk-remark">
                                            <label class="mb-2 font-weight-bold">Remark Upload</label>
                                            <textarea name="bulk_remark_<?= (int) ($bulkRow['id_doc_item'] ?? 0) ?>" class="form-control" placeholder="Remark upload jika diperlukan"><?= htmlspecialchars((string) ($bulkRow['remark'] ?? '')) ?></textarea>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary" <?= empty($postDonasiUploadableRows) ? 'disabled' : '' ?>>Simpan Bulk Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php if ($canApprove && $canApprovalAction): ?>
<div class="modal fade" id="modal-batch-approve" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content batch-modal">
            <form method="post" action="<?= base_url('Batch_Approval_MyRep/approveDocument') ?>">
                <input type="hidden" name="cluster_id" value="<?= (int) $cluster['id_myrep_cluster'] ?>">
                <input type="hidden" name="id_doc_file" id="batch_approve_file_id">
                <input type="hidden" name="redirect_to_detail" value="1">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Approve Dokumen Batch</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3"><strong>File:</strong> <span id="batch_approve_file_name">-</span></div>
                    <div class="form-group mb-0">
                        <label>Remark</label>
                        <textarea name="remark" class="form-control" rows="5" placeholder="Remark approve"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success btn-sm">Approve</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-batch-reject" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content batch-modal">
            <form method="post" action="<?= base_url('Batch_Approval_MyRep/rejectDocument') ?>">
                <input type="hidden" name="cluster_id" value="<?= (int) $cluster['id_myrep_cluster'] ?>">
                <input type="hidden" name="id_doc_file" id="batch_reject_file_id">
                <input type="hidden" name="redirect_to_detail" value="1">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Reject Dokumen Batch</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3"><strong>File:</strong> <span id="batch_reject_file_name">-</span></div>
                    <div class="form-group mb-0">
                        <label>Alasan Reject</label>
                        <textarea name="remark" class="form-control" rows="5" placeholder="Alasan reject" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-danger btn-sm">Reject</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-post-approve" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content batch-modal">
            <form method="post" action="<?= base_url('Post_Donasi_MyRep/approveDocument') ?>">
                <input type="hidden" name="cluster_id" value="<?= (int) $cluster['id_myrep_cluster'] ?>">
                <input type="hidden" name="id_doc_file" id="post_approve_file_id">
                <input type="hidden" name="id_doc_item" id="post_approve_doc_item_id">
                <input type="hidden" name="linked_file_name" id="post_approve_linked_file_name">
                <input type="hidden" name="redirect_to_batch_detail" value="1">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Approve Dokumen Post Donasi</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3"><strong>Dokumen:</strong> <span id="post_approve_doc_name">-</span></div>
                    <div class="form-group mb-0">
                        <label>Remark</label>
                        <textarea name="remark" class="form-control" rows="5" placeholder="Remark approve"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success btn-sm">Approve</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-post-reject" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content batch-modal">
            <form method="post" action="<?= base_url('Post_Donasi_MyRep/rejectDocument') ?>">
                <input type="hidden" name="cluster_id" value="<?= (int) $cluster['id_myrep_cluster'] ?>">
                <input type="hidden" name="id_doc_file" id="post_reject_file_id">
                <input type="hidden" name="id_doc_item" id="post_reject_doc_item_id">
                <input type="hidden" name="linked_file_name" id="post_reject_linked_file_name">
                <input type="hidden" name="redirect_to_batch_detail" value="1">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Reject Dokumen Post Donasi</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3"><strong>Dokumen:</strong> <span id="post_reject_doc_name">-</span></div>
                    <div class="form-group mb-0">
                        <label>Alasan Reject</label>
                        <textarea name="remark" class="form-control" rows="5" placeholder="Alasan reject" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-danger btn-sm">Reject</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="donation-photo-lightbox" id="donation-photo-lightbox" aria-hidden="true">
    <div class="donation-photo-lightbox__dialog">
        <div class="donation-photo-lightbox__head">
            <div class="donation-photo-lightbox__title" id="donation-photo-lightbox-title">Foto Dokumen</div>
            <div class="donation-photo-lightbox__toolbar">
                <button type="button" class="donation-photo-lightbox__action" id="donation-photo-lightbox-zoom-out" aria-label="Zoom Out">-</button>
                <button type="button" class="donation-photo-lightbox__action" id="donation-photo-lightbox-zoom-in" aria-label="Zoom In">+</button>
                <button type="button" class="donation-photo-lightbox__close" id="donation-photo-lightbox-close" aria-label="Tutup">&times;</button>
            </div>
        </div>
        <div class="donation-photo-lightbox__stage" id="donation-photo-lightbox-stage">
            <img src="" alt="Foto Dokumen" class="donation-photo-lightbox__image" id="donation-photo-lightbox-image">
        </div>
    </div>
</div>

<script>
    (function () {
        var MAX_PIC_ROWS = 5;
        var donationPhotoLightbox = document.getElementById('donation-photo-lightbox');
        var donationPhotoImage = document.getElementById('donation-photo-lightbox-image');
        var donationPhotoTitle = document.getElementById('donation-photo-lightbox-title');
        var donationPhotoClose = document.getElementById('donation-photo-lightbox-close');
        var donationPhotoZoomIn = document.getElementById('donation-photo-lightbox-zoom-in');
        var donationPhotoZoomOut = document.getElementById('donation-photo-lightbox-zoom-out');
        var donationPhotoStage = document.getElementById('donation-photo-lightbox-stage');
        var donationPhotoScale = 1;
        var donationPhotoFitScale = 1;

        function applyDonationPhotoScale() {
            if (!donationPhotoImage) {
                return;
            }

            var naturalWidth = donationPhotoImage.naturalWidth || 0;
            if (!naturalWidth) {
                donationPhotoImage.style.width = '';
                donationPhotoImage.style.height = 'auto';
                return;
            }

            donationPhotoImage.style.width = Math.round(naturalWidth * donationPhotoScale) + 'px';
            donationPhotoImage.style.height = 'auto';
        }

        function fitDonationPhotoToStage() {
            if (!donationPhotoImage || !donationPhotoStage) {
                return;
            }

            var naturalWidth = donationPhotoImage.naturalWidth || 0;
            var naturalHeight = donationPhotoImage.naturalHeight || 0;
            var availableWidth = Math.max(donationPhotoStage.clientWidth - 32, 1);
            var availableHeight = Math.max(donationPhotoStage.clientHeight - 32, 1);

            if (!naturalWidth || !naturalHeight) {
                donationPhotoScale = 1;
                applyDonationPhotoScale();
                return;
            }

            donationPhotoFitScale = Math.min(1, availableWidth / naturalWidth, availableHeight / naturalHeight);
            donationPhotoScale = donationPhotoFitScale;
            applyDonationPhotoScale();
            donationPhotoStage.scrollLeft = 0;
            donationPhotoStage.scrollTop = 0;
        }

        function zoomDonationPhotoTo(nextScale, originEvent) {
            if (!donationPhotoLightbox || !donationPhotoImage || !donationPhotoLightbox.classList.contains('is-open')) {
                return;
            }

            var targetScale = Math.max(donationPhotoFitScale || 0.1, Math.min(3, nextScale));
            if (targetScale === donationPhotoScale) {
                return;
            }

            var stageRect = donationPhotoStage ? donationPhotoStage.getBoundingClientRect() : null;
            var originX = stageRect ? stageRect.width / 2 : 0;
            var originY = stageRect ? stageRect.height / 2 : 0;
            var contentX = 0;
            var contentY = 0;
            var scaleRatio = targetScale / donationPhotoScale;

            if (donationPhotoStage && stageRect) {
                originX = originEvent ? originEvent.clientX - stageRect.left : originX;
                originY = originEvent ? originEvent.clientY - stageRect.top : originY;
                contentX = donationPhotoStage.scrollLeft + originX;
                contentY = donationPhotoStage.scrollTop + originY;
            }

            donationPhotoScale = targetScale;
            applyDonationPhotoScale();

            if (donationPhotoStage) {
                donationPhotoStage.scrollLeft = (contentX * scaleRatio) - originX;
                donationPhotoStage.scrollTop = (contentY * scaleRatio) - originY;
            }
        }

        function openDonationPhotoLightbox(imageUrl, title) {
            if (!donationPhotoLightbox || !donationPhotoImage) {
                return;
            }

            donationPhotoScale = 1;
            donationPhotoImage.src = imageUrl || '';
            donationPhotoImage.style.width = '';
            donationPhotoImage.style.height = 'auto';
            if (donationPhotoTitle) {
                donationPhotoTitle.textContent = title || 'Foto Dokumen';
            }
            donationPhotoLightbox.classList.add('is-open');
            donationPhotoLightbox.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';

            if (donationPhotoStage) {
                donationPhotoStage.scrollLeft = 0;
                donationPhotoStage.scrollTop = 0;
            }

            if (donationPhotoImage.complete) {
                fitDonationPhotoToStage();
            }
        }

        function closeDonationPhotoLightbox() {
            if (!donationPhotoLightbox || !donationPhotoImage) {
                return;
            }

            donationPhotoLightbox.classList.remove('is-open');
            donationPhotoLightbox.setAttribute('aria-hidden', 'true');
            donationPhotoImage.src = '';
            donationPhotoImage.style.width = '';
            donationPhotoImage.style.height = 'auto';
            donationPhotoScale = 1;
            donationPhotoFitScale = 1;
            document.body.style.overflow = '';
        }

        function initDonationPhotoDrag(container) {
            if (!container || container.dataset.dragScrollReady === '1') {
                return;
            }

            container.dataset.dragScrollReady = '1';
            var isDragging = false;
            var startX = 0;
            var startY = 0;
            var startScrollLeft = 0;
            var startScrollTop = 0;
            var activePointerId = null;

            container.addEventListener('pointerdown', function (event) {
                if (event.button !== undefined && event.button !== 0) {
                    return;
                }

                isDragging = true;
                activePointerId = event.pointerId;
                startX = event.clientX;
                startY = event.clientY;
                startScrollLeft = container.scrollLeft;
                startScrollTop = container.scrollTop;
                container.classList.add('is-dragging');

                if (container.setPointerCapture && activePointerId !== null) {
                    container.setPointerCapture(activePointerId);
                }

                event.preventDefault();
            });

            container.addEventListener('pointermove', function (event) {
                if (!isDragging) {
                    return;
                }

                container.scrollLeft = startScrollLeft - (event.clientX - startX);
                container.scrollTop = startScrollTop - (event.clientY - startY);
                event.preventDefault();
            });

            ['pointerup', 'pointercancel', 'lostpointercapture'].forEach(function (eventName) {
                container.addEventListener(eventName, function () {
                    isDragging = false;
                    activePointerId = null;
                    container.classList.remove('is-dragging');
                });
            });

            container.addEventListener('dragstart', function (event) {
                event.preventDefault();
            });

            container.addEventListener('wheel', function (event) {
                if (!donationPhotoLightbox || !donationPhotoLightbox.classList.contains('is-open')) {
                    return;
                }

                event.preventDefault();
                zoomDonationPhotoTo(donationPhotoScale + (event.deltaY < 0 ? 0.15 : -0.15), event);
            }, { passive: false });
        }

        function bindDropzones() {
            $('.js-dropzone').each(function () {
                var dropzone = this;
                var input = dropzone.querySelector('.js-dropzone-input');
                var label = dropzone.querySelector('.js-dropzone-label');

                if (!input || !label || dropzone.dataset.bound === '1') {
                    return;
                }

                dropzone.dataset.bound = '1';

                ['dragenter', 'dragover'].forEach(function (eventName) {
                    dropzone.addEventListener(eventName, function (e) {
                        e.preventDefault();
                        e.stopPropagation();
                        dropzone.classList.add('dragover');
                    });
                });

                ['dragleave', 'drop'].forEach(function (eventName) {
                    dropzone.addEventListener(eventName, function (e) {
                        e.preventDefault();
                        e.stopPropagation();
                        dropzone.classList.remove('dragover');
                    });
                });

                dropzone.addEventListener('drop', function (e) {
                    if (e.dataTransfer.files && e.dataTransfer.files.length > 0) {
                        input.files = e.dataTransfer.files;
                        label.textContent = e.dataTransfer.files[0].name;
                    }
                });

                input.addEventListener('change', function () {
                    label.textContent = (input.files && input.files.length > 0)
                        ? input.files[0].name
                        : 'Belum ada file dipilih';
                });
            });
        }

        function normalizeFormattedNumber(value, decimals) {
            var normalized = String(value || '').replace(/[^\d,.\-]/g, '');
            if (normalized === '') {
                return 0;
            }

            if (typeof decimals === 'number' && decimals === 0) {
                normalized = normalized.replace(/[.,]/g, '');
                var integerNumber = parseFloat(normalized);
                return isNaN(integerNumber) ? 0 : integerNumber;
            }

            var hasComma = normalized.indexOf(',') !== -1;
            var dotCount = (normalized.match(/\./g) || []).length;

            if (hasComma) {
                normalized = normalized.replace(/\./g, '').replace(',', '.');
            } else if (dotCount > 1) {
                normalized = normalized.replace(/\./g, '');
            } else if (dotCount === 1) {
                var parts = normalized.split('.');
                var decimalLength = parts[1] ? parts[1].length : 0;
                if (decimalLength === 3) {
                    normalized = parts[0] + parts[1];
                }
            }

            var number = parseFloat(normalized);
            return isNaN(number) ? 0 : number;
        }

        function formatNumberValue(value, decimals) {
            var number = typeof value === 'number' ? value : normalizeFormattedNumber(value, decimals);
            if (!isFinite(number)) {
                number = 0;
            }

            var fixed = Number(number).toFixed(decimals);
            var parts = fixed.split('.');
            parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');

            if (decimals > 0) {
                parts[1] = (parts[1] || '').replace(/0+$/, '');
                return parts[1] ? parts[0] + ',' + parts[1] : parts[0];
            }

            return parts[0];
        }

        function applyNumberFormatting($input) {
            var decimals = parseInt($input.data('decimals'), 10);
            if (isNaN(decimals)) {
                decimals = 0;
            }

            if ($input.val() === '') {
                return;
            }

            $input.val(formatNumberValue($input.val(), decimals));
        }

        function updateNominalPerHomepass() {
            var hpDonasi = normalizeFormattedNumber($('#detail_edit_hp_donasi').val(), 0);
            var nominalDonasi = normalizeFormattedNumber($('#detail_edit_nominal_pengajuan_area').val(), 0);
            var result = hpDonasi > 0 ? (nominalDonasi / hpDonasi) : 0;
            $('#detail_edit_nominal_per_homepass').val(result > 0 ? formatNumberValue(result, 2) : '');
            $('#detail_edit_nominal_nego_emr').val(nominalDonasi > 0 ? String(Math.round(nominalDonasi)) : '');
        }

        function toggleStageFields() {
            var stageValue = $('#detail_edit_staging_status').val() || 'WAITING HO';
            var showEmr = ['WAITING MYREP', 'WAITING FINANCE', 'RELEASED', 'DONE BATCH APPROVAL'].indexOf(stageValue) !== -1;
            var showFinance = ['WAITING FINANCE', 'RELEASED', 'DONE BATCH APPROVAL'].indexOf(stageValue) !== -1;

            $('[data-stage-scope="detail-edit"].js-emr-fields').toggle(showEmr);
            $('[data-stage-scope="detail-edit"].js-finance-fields').toggle(showFinance);
        }

        function renumberPicRows() {
            $('#detail_edit_pic_rows .batch-pic-card').each(function (index) {
                var rowNumber = index + 1;
                $(this).attr('data-pic-row', rowNumber)
                    .toggleClass('batch-pic-card--primary', rowNumber === 1);
                $(this).find('.batch-pic-card__title').text('PIC ' + rowNumber);
                $(this).find('.batch-pic-card__note').text(rowNumber === 1 ? 'Otomatis mengikuti penerima dana' : 'PIC tambahan');
                $(this).find('.js-remove-pic-row').toggle(rowNumber > 1);
            });
        }

        function syncPrimaryPic() {
            var firstRow = $('#detail_edit_pic_rows .batch-pic-card').first();
            if (!firstRow.length) {
                return;
            }

            firstRow.find('.js-pic-name').val($('#detail_edit_recipient_name').val());
            firstRow.find('.js-pic-phone').val($('#detail_edit_recipient_phone').val());
            firstRow.find('.js-pic-position').val($('#detail_edit_recipient_position').val());
            firstRow.find('.js-pic-period').val($('#detail_edit_recipient_period').val());
        }

        function createPicRow(rowNumber) {
            return '' +
                '<div class="batch-pic-card" data-pic-row="' + rowNumber + '">' +
                    '<div class="batch-pic-card__head">' +
                        '<div>' +
                            '<div class="batch-pic-card__title">PIC ' + rowNumber + '</div>' +
                            '<div class="batch-pic-card__note">PIC tambahan</div>' +
                        '</div>' +
                        '<button type="button" class="btn btn-outline-danger btn-sm js-remove-pic-row">Hapus</button>' +
                    '</div>' +
                    '<div class="row">' +
                        '<div class="col-md-3"><div class="form-group"><label>Nama PIC</label><input type="text" name="pic_name[]" class="form-control js-pic-name"></div></div>' +
                        '<div class="col-md-3"><div class="form-group"><label>No HP PIC</label><input type="text" name="pic_phone[]" class="form-control js-pic-phone"></div></div>' +
                        '<div class="col-md-3"><div class="form-group"><label>Jabatan PIC</label><input type="text" name="pic_position[]" class="form-control js-pic-position"></div></div>' +
                        '<div class="col-md-3"><div class="form-group mb-0"><label>Masa Jabatan PIC</label><input type="text" name="pic_period[]" class="form-control js-pic-period"></div></div>' +
                    '</div>' +
                '</div>';
        }

        function escapeHtml(value) {
            return String(value == null ? '' : value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function currentBatchDetailScrollTop() {
            return window.pageYOffset || document.documentElement.scrollTop || 0;
        }

        function cleanupBatchModalBackdrop() {
            if ($('.modal.show').length > 0) {
                return;
            }

            $('body').removeClass('modal-open').css('padding-right', '');
            $('.modal-backdrop').remove();
            if (!donationPhotoLightbox || !donationPhotoLightbox.classList.contains('is-open')) {
                document.body.style.overflow = '';
            }
        }

        function restoreDonationUploadButtons() {
            $('.js-open-donation-upload-modal, #donation_upload_submit')
                .removeClass('role-action-disabled')
                .removeAttr('aria-disabled title')
                .css({
                    'pointer-events': '',
                    'opacity': '',
                    'cursor': ''
                })
                .prop('disabled', false)
                .data('roleBlocked', 0);
        }

        function ensureDonationUploadModalExists() {
            if ($('#modal-donation-upload').length) {
                return;
            }

            batchUploadDebug('modal missing, append fallback single donation upload modal', {});
            $('body').append(
                '<div class="modal fade donation-doc-modal" id="modal-donation-upload" tabindex="-1" role="dialog" aria-hidden="true">' +
                    '<div class="modal-dialog" role="document">' +
                        '<div class="modal-content">' +
                            '<form method="post" action="<?= base_url('Batch_Approval_MyRep/uploadDonationDocument') ?>" enctype="multipart/form-data" id="donation-upload-form" data-role-guard-exempt="1">' +
                                '<input type="hidden" name="cluster_id" value="<?= (int) ($cluster['id_myrep_cluster'] ?? 0) ?>">' +
                                '<input type="hidden" name="redirect_to_detail" value="1">' +
                                '<input type="hidden" name="id_doc_item" id="donation_upload_doc_item_id">' +
                                '<input type="hidden" name="group_key" id="donation_upload_group_key">' +
                                '<input type="hidden" name="replace_file" id="donation_upload_replace_file" value="0">' +
                                '<div class="modal-header">' +
                                    '<div>' +
                                        '<h4 class="modal-title mb-1">Upload Dokumen</h4>' +
                                        '<p class="mb-0" style="opacity:.9;">Pilih satu file untuk item dokumen donasi yang sedang aktif.</p>' +
                                    '</div>' +
                                    '<button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
                                '</div>' +
                                '<div class="modal-body">' +
                                    '<div class="donation-doc-highlight">' +
                                        '<div class="donation-doc-highlight-title" id="donation_upload_doc_name">-</div>' +
                                        '<p class="donation-doc-highlight-note" id="donation_upload_doc_note">File yang diupload akan masuk ke dokumen ini dan menggantikan file sebelumnya jika sudah ada.</p>' +
                                    '</div>' +
                                    '<div class="alert alert-warning py-2 px-3 small d-none" id="donation_upload_replace_note">Replace file akan mengubah status menjadi ON REVIEW.</div>' +
                                    '<div class="donation-doc-modal-panel">' +
                                        '<div class="form-group mb-0">' +
                                            '<label class="font-weight-bold" id="donation_upload_file_label">Pilih File</label>' +
                                            '<div class="batch-dropzone js-dropzone" id="donation_upload_dropzone">' +
                                                '<input type="file" name="file" id="donation_upload_file" class="js-dropzone-input" required>' +
                                                '<div class="batch-dropzone-content">' +
                                                    '<div class="batch-dropzone-icon"><i class="fas fa-cloud-upload-alt"></i></div>' +
                                                    '<div class="batch-dropzone-title" id="donation_upload_dropzone_title">Drag & drop file di sini</div>' +
                                                    '<div class="batch-dropzone-text" id="donation_upload_dropzone_text">atau klik area ini untuk memilih file dari perangkat</div>' +
                                                    '<button type="button" class="btn btn-sm btn-outline-primary py-1 px-2 mb-2" id="donation_upload_choose_label">Pilih File</button>' +
                                                    '<div class="batch-dropzone-file js-dropzone-label" id="donation_upload_file_name">Belum ada file dipilih</div>' +
                                                '</div>' +
                                            '</div>' +
                                            '<small class="form-text text-muted" id="donation_upload_file_hint">Format mengikuti jenis dokumen. Maksimal dokumen 20 MB.</small>' +
                                        '</div>' +
                                    '</div>' +
                                    '<div class="donation-doc-modal-panel" id="donation_upload_not_required_panel">' +
                                        '<div class="form-group mb-0">' +
                                            '<div class="custom-control custom-checkbox">' +
                                                '<input type="checkbox" class="custom-control-input" id="donation_upload_not_required" name="is_document_not_required" value="1">' +
                                                '<label class="custom-control-label font-weight-bold" for="donation_upload_not_required">Tidak dibutuhkan dokumen</label>' +
                                            '</div>' +
                                            '<small class="form-text text-muted">Jika dicentang, item tetap dihitung submitted dan tetap melalui proses reject/approve HO.</small>' +
                                        '</div>' +
                                    '</div>' +
                                    '<div class="donation-doc-modal-panel">' +
                                        '<div class="form-group mb-0">' +
                                            '<label class="font-weight-bold">Remark</label>' +
                                            '<textarea name="remark" id="donation_upload_remark" class="form-control" rows="3" placeholder="Tambahkan catatan singkat jika diperlukan"></textarea>' +
                                        '</div>' +
                                    '</div>' +
                                '</div>' +
                                '<div class="modal-footer">' +
                                    '<button type="button" class="btn btn-light border" data-dismiss="modal">Tutup</button>' +
                                    '<button type="submit" class="btn btn-success" id="donation_upload_submit" data-role-guard-exempt="1">Upload Dokumen</button>' +
                                '</div>' +
                            '</form>' +
                        '</div>' +
                    '</div>' +
                '</div>'
            );
            bindDropzones();
            restoreDonationUploadButtons();
        }

        function prepareDonationUploadModal(buttonEl) {
            ensureDonationUploadModalExists();
            var $button = $(buttonEl);
            var isReplace = String($button.data('replace-file')) === '1';
            var canNotRequired = String($button.data('can-not-required')) === '1';
            var isImageDoc = String($button.data('is-image-doc')) === '1';
            var currentFile = $button.data('file-name') || '';

            $('#donation_upload_doc_item_id').val($button.data('doc-item-id') || '');
            $('#donation_upload_group_key').val($button.data('group-key') || '');
            $('#donation_upload_replace_file').val(isReplace ? '1' : '0');
            $('#donation_upload_doc_name').text($button.data('doc-name') || '-');
            $('#donation_upload_doc_note').text(
                currentFile
                    ? 'File yang diupload akan masuk ke dokumen ini dan menggantikan file "' + currentFile + '".'
                    : 'File yang diupload akan masuk ke dokumen ini dan menggantikan file sebelumnya jika sudah ada.'
            );
            $('#donation_upload_replace_note').toggleClass('d-none', !isReplace);
            $('#donation_upload_remark').val($button.data('remark') || '');
            $('#donation_upload_file').val('').prop('disabled', false).prop('required', true).attr('accept', isImageDoc ? 'image/*' : '');
            $('#donation_upload_file_label').text(isImageDoc ? 'Foto Claim' : 'Pilih File');
            $('#donation_upload_dropzone').toggleClass('batch-dropzone--photo', isImageDoc);
            $('#donation_upload_dropzone_title').text(isImageDoc ? 'Drop foto claim di sini' : 'Drag & drop file di sini');
            $('#donation_upload_dropzone_text').text(isImageDoc ? 'atau klik area ini untuk pilih file gambar' : 'atau klik area ini untuk memilih file dari perangkat');
            $('#donation_upload_choose_label').text(isImageDoc ? 'Pilih Foto' : 'Pilih File');
            $('#donation_upload_file_hint').text(isImageDoc ? 'Format gambar yang didukung: JPG, JPEG, PNG. Maksimal dokumen 20 MB.' : 'Format mengikuti jenis dokumen. Maksimal dokumen 20 MB.');
            $('#donation_upload_file_name').text('Belum ada file dipilih');
            $('#donation_upload_not_required').prop('checked', false);
            $('#donation_upload_not_required_panel').toggle(canNotRequired);
            $('#donation_upload_submit').removeClass('btn-warning').addClass('btn-success').text(isReplace ? 'Replace File' : 'Upload Dokumen');
        }

        function softRefreshBatchDetail(url, scrollTop) {
            $.ajax({
                url: url || window.location.href,
                type: 'GET',
                dataType: 'html'
            }).done(function (html) {
                var parsed = $('<div>').append($.parseHTML(html, document, true));
                var freshContent = parsed.find('.content-wrapper').first();
                var currentContent = $('.content-wrapper').first();

                if (!freshContent.length || !currentContent.length) {
                    window.location.href = url || window.location.href;
                    return;
                }

                currentContent.html(freshContent.html());
                bindDropzones();
                closeDonationPhotoLightbox();
                cleanupBatchModalBackdrop();
                restoreDonationUploadButtons();
                window.setTimeout(function () {
                    cleanupBatchModalBackdrop();
                    restoreDonationUploadButtons();
                    window.scrollTo(0, scrollTop || 0);
                }, 50);
            }).fail(function () {
                alert('Data berhasil diproses, tapi gagal memuat ulang area detail. Silakan refresh manual jika tampilan belum berubah.');
            });
        }

        function handleBatchAjaxResponse(response, successFallbackMessage, scrollTop, onFailure) {
            if (response && response.status) {
                $('.modal.show').modal('hide');
                cleanupBatchModalBackdrop();
                alert(response.message || successFallbackMessage || 'Data berhasil diperbarui.');
                softRefreshBatchDetail(response.redirect_url || window.location.href, scrollTop);
                return true;
            }

            alert(response && response.message ? response.message : 'Update gagal.');
            if (typeof onFailure === 'function') {
                onFailure();
            }
            return false;
        }

        function batchUploadDebug(eventName, payload) {
            if (!window.console || typeof window.console.log !== 'function') {
                return;
            }
            window.console.log('[Batch Approval Upload Debug] ' + eventName, payload || {});
        }

        $(function () {
            $(document).on('click', '.batch-edit-btn', function (event) {
                event.preventDefault();
                event.stopPropagation();
                $('#modal-batch-edit-detail').modal('show');
            });

            bindDropzones();
            initDonationPhotoDrag(donationPhotoStage);
            cleanupBatchModalBackdrop();
            restoreDonationUploadButtons();
            window.setTimeout(restoreDonationUploadButtons, 150);
            window.setTimeout(restoreDonationUploadButtons, 600);

            $(document).on('hidden.bs.modal', '.modal', function () {
                window.setTimeout(cleanupBatchModalBackdrop, 25);
                window.setTimeout(restoreDonationUploadButtons, 25);
            });

            $(document).on('show.bs.modal', '.modal', function () {
                closeDonationPhotoLightbox();
                $('.modal-backdrop').not(':last').remove();
            });

            $(window).on('pageshow focus', function () {
                window.setTimeout(cleanupBatchModalBackdrop, 25);
                window.setTimeout(restoreDonationUploadButtons, 25);
            });

            document.addEventListener('click', function (event) {
                var actionCellEl = event.target && event.target.closest ? event.target.closest('.donation-action-stack') : null;
                if (actionCellEl) {
                    batchUploadDebug('capture click donation action stack', {
                        targetTag: event.target && event.target.tagName,
                        targetClass: event.target && event.target.className,
                        targetText: event.target && event.target.textContent ? String(event.target.textContent).trim().slice(0, 80) : '',
                        stackText: actionCellEl.textContent ? String(actionCellEl.textContent).trim().replace(/\s+/g, ' ').slice(0, 160) : ''
                    });
                }

                var buttonEl = event.target && event.target.closest ? event.target.closest('.js-open-donation-upload-modal') : null;
                if (!buttonEl) {
                    return;
                }

                batchUploadDebug('capture click single donation upload', {
                    tag: buttonEl.tagName,
                    className: buttonEl.className,
                    docItemId: buttonEl.getAttribute('data-doc-item-id'),
                    groupKey: buttonEl.getAttribute('data-group-key'),
                    docName: buttonEl.getAttribute('data-doc-name'),
                    replaceFile: buttonEl.getAttribute('data-replace-file'),
                    href: buttonEl.getAttribute('href'),
                    roleBlocked: $(buttonEl).data('roleBlocked'),
                    ariaDisabled: buttonEl.getAttribute('aria-disabled'),
                    pointerEvents: $(buttonEl).css('pointer-events')
                });
                event.preventDefault();
                event.stopPropagation();
                if (event.stopImmediatePropagation) {
                    event.stopImmediatePropagation();
                }
                restoreDonationUploadButtons();
                prepareDonationUploadModal(buttonEl);
                $('#modal-donation-upload').modal('show');
                batchUploadDebug('modal show requested single donation upload', {
                    modalExists: $('#modal-donation-upload').length,
                    modalVisible: $('#modal-donation-upload').is(':visible'),
                    hiddenDocItemId: $('#donation_upload_doc_item_id').val(),
                    hiddenGroupKey: $('#donation_upload_group_key').val()
                });
            }, true);

            if (donationPhotoImage) {
                donationPhotoImage.addEventListener('load', function () {
                    fitDonationPhotoToStage();
                });
            }

            $('.js-number-format').each(function () {
                applyNumberFormatting($(this));
            });

            updateNominalPerHomepass();
            toggleStageFields();
            syncPrimaryPic();
            renumberPicRows();

            $(document).on('input blur', '.js-number-format', function () {
                applyNumberFormatting($(this));
            });

            $('#detail_edit_hp_donasi, #detail_edit_nominal_pengajuan_area').on('input blur', function () {
                updateNominalPerHomepass();
            });

            $('#detail_edit_staging_status').on('change', function () {
                toggleStageFields();
            });

            $('#detail_edit_recipient_name, #detail_edit_recipient_phone, #detail_edit_recipient_position, #detail_edit_recipient_period').on('input', function () {
                syncPrimaryPic();
            });

            $('#detail_edit_add_pic').on('click', function () {
                var currentCount = $('#detail_edit_pic_rows .batch-pic-card').length;
                if (currentCount >= MAX_PIC_ROWS) {
                    return;
                }

                $('#detail_edit_pic_rows').append(createPicRow(currentCount + 1));
                renumberPicRows();
            });

            $(document).on('click', '.js-remove-pic-row', function () {
                $(this).closest('.batch-pic-card').remove();
                renumberPicRows();
            });

            $(document).on('click', '.js-doc-history', function () {
                var $button = $(this);
                var history = [];

                try {
                    history = $button.attr('data-history') ? JSON.parse($button.attr('data-history')) : [];
                } catch (e) {
                    history = [];
                }

                $('#history_doc_label').text($button.data('doc-name') || '-');

                if (!history.length) {
                    $('#history_doc_items').html('<li class="text-muted">Belum ada history.</li>');
                    return;
                }

                var html = '';
                history.forEach(function (entry) {
                    html += '<li class="doc-history-item">' +
                        '<div class="doc-history-title">' + escapeHtml(formatDocHistoryAction(entry)) + '</div>' +
                        '<div class="doc-history-meta">' + escapeHtml(entry.action_at || '-') + ' | ' + escapeHtml(entry.nama_user || 'System') + '</div>' +
                        '<div><strong>File:</strong> ' + escapeHtml(entry.file_name || '-') + '</div>' +
                        '<div><strong>Remark:</strong> ' + escapeHtml(entry.remark || '-') + '</div>' +
                    '</li>';
                });

                $('#history_doc_items').html(html);
            });

            function syncAstriDateRequirement($statusSelect) {
                var status = String($statusSelect.val() || 'NY').toUpperCase();
                var $dateInput = $($statusSelect.data('date-input'));
                var $remarkInput = $($statusSelect.data('remark-input'));

                if ($dateInput.length) {
                    $dateInput.prop('required', status !== 'NY');
                    if (status !== 'NY' && !$dateInput.val()) {
                        $dateInput.val(new Date().toISOString().slice(0, 10));
                    }
                }

                if ($remarkInput.length) {
                    $remarkInput.prop('required', status === 'REJECTED');
                }
            }

            function validateBulkAstriRejectedRemarks($form) {
                var invalidRemark = null;
                $form.find('.js-bulk-astri-status').each(function () {
                    var $status = $(this);
                    var status = String($status.val() || '').toUpperCase();
                    var $remark = $($status.data('remark-input'));
                    if (status === 'REJECTED' && $remark.length && !$.trim($remark.val())) {
                        invalidRemark = $remark;
                        return false;
                    }
                });

                if (!invalidRemark) {
                    return true;
                }

                alert('Remark Astri wajib diisi jika status dokumen rejected.');
                invalidRemark.focus();
                return false;
            }

            function formatDocHistoryAction(entry) {
                var actionType = String((entry && entry.action_type) || '').trim();
                var statusAfter = String((entry && entry.status_after) || '').trim();
                if (!actionType && statusAfter) {
                    actionType = 'ASTRI_' + statusAfter.replace(/\s+/g, '_');
                }

                return actionType ? actionType.replace(/_/g, ' ') : '-';
            }

            $(document).on('click', '.js-open-donation-astri-modal', function () {
                var $button = $(this);
                $('#donation_astri_file_id').val($button.data('file-id') || '');
                $('#donation_astri_doc_name').text($button.data('doc-name') || '-');
                $('#donation_astri_status').val($button.data('astri-status') || 'NY');
                $('#donation_astri_submitted_date').val($button.data('astri-submitted-date') || '');
                $('#donation_astri_approved_date').val($button.data('astri-approved-date') || '');
                $('#donation_astri_remark').val($button.data('astri-remark') || '');
                syncAstriDateRequirement($('#donation_astri_status'));
            });

            $(document).on('change', '.js-astri-status', function () {
                syncAstriDateRequirement($(this));
            });
            $('.js-astri-status').each(function () {
                syncAstriDateRequirement($(this));
            });

            $(document).on('click', '.js-open-donation-photo', function () {
                openDonationPhotoLightbox($(this).data('image') || this.src, $(this).data('title') || 'Foto Dokumen');
            });

            if (donationPhotoClose) {
                donationPhotoClose.addEventListener('click', closeDonationPhotoLightbox);
            }
            if (donationPhotoZoomIn) {
                donationPhotoZoomIn.addEventListener('click', function () {
                    zoomDonationPhotoTo(donationPhotoScale + 0.25);
                });
            }
            if (donationPhotoZoomOut) {
                donationPhotoZoomOut.addEventListener('click', function () {
                    zoomDonationPhotoTo(donationPhotoScale - 0.25);
                });
            }
            if (donationPhotoLightbox) {
                donationPhotoLightbox.addEventListener('click', function (event) {
                    if (event.target === donationPhotoLightbox) {
                        closeDonationPhotoLightbox();
                    }
                });
            }
            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && donationPhotoLightbox && donationPhotoLightbox.classList.contains('is-open')) {
                    closeDonationPhotoLightbox();
                }
            });

            $(document).on('click', '.js-open-donation-upload-modal', function () {
                batchUploadDebug('delegated click single donation upload', {
                    docItemId: $(this).data('doc-item-id'),
                    groupKey: $(this).data('group-key'),
                    docName: $(this).data('doc-name'),
                    replaceFile: $(this).data('replace-file')
                });
                prepareDonationUploadModal(this);
                $('#modal-donation-upload').modal('show');
            });

            $(document).on('change', '#donation_upload_not_required', function () {
                var checked = $(this).is(':checked');
                $('#donation_upload_file').prop('disabled', checked).prop('required', !checked);
                if (checked) {
                    $('#donation_upload_file').val('');
                    $('#donation_upload_file_name').text('Tidak dibutuhkan dokumen');
                } else {
                    $('#donation_upload_file_name').text('Belum ada file dipilih');
                }
            });

            $(document).on('change', '.js-bulk-donation-not-required', function () {
                var $checkbox = $(this);
                var $fileInput = $($checkbox.data('file-target'));
                $fileInput.prop('disabled', $checkbox.is(':checked'));
                if ($checkbox.is(':checked')) {
                    $fileInput.val('');
                }
            });

            $(document).on('submit', '#donation-upload-form', function (event) {
                event.preventDefault();

                var form = this;
                var $form = $(form);
                var $submitButton = $('#donation_upload_submit');
                var originalText = $submitButton.text();
                var scrollTop = currentBatchDetailScrollTop();

                batchUploadDebug('submit single donation upload', {
                    action: $form.attr('action'),
                    docItemId: $('#donation_upload_doc_item_id').val(),
                    groupKey: $('#donation_upload_group_key').val(),
                    replaceFile: $('#donation_upload_replace_file').val(),
                    fileSelected: !!($('#donation_upload_file')[0] && $('#donation_upload_file')[0].files && $('#donation_upload_file')[0].files.length),
                    notRequired: $('#donation_upload_not_required').is(':checked')
                });
                $submitButton.prop('disabled', true).text('Uploading...');
                $.ajax({
                    url: $form.attr('action'),
                    type: 'POST',
                    data: new FormData(form),
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function (response) {
                        batchUploadDebug('response single donation upload', response);
                        handleBatchAjaxResponse(response, 'Dokumen berhasil diupload.', scrollTop, function () {
                            $submitButton.prop('disabled', false).text(originalText);
                        });
                    },
                    error: function (xhr) {
                        batchUploadDebug('error single donation upload', {
                            status: xhr && xhr.status,
                            responseText: xhr && xhr.responseText ? String(xhr.responseText).slice(0, 500) : ''
                        });
                        alert('Upload gagal. Silakan coba lagi.');
                        $submitButton.prop('disabled', false).text(originalText);
                    }
                });
            });

            $(document).on('submit', '.js-donation-bulk-upload-form', function (event) {
                event.preventDefault();

                var form = this;
                var $form = $(form);
                var $submitButton = $form.find('.js-donation-bulk-upload-submit');
                var originalText = $submitButton.text();
                var scrollTop = currentBatchDetailScrollTop();

                batchUploadDebug('submit bulk donation upload', {
                    action: $form.attr('action'),
                    groupKey: $form.find('input[name="group_key"]').val()
                });
                $submitButton.prop('disabled', true).text('Uploading...');
                $.ajax({
                    url: $form.attr('action'),
                    type: 'POST',
                    data: new FormData(form),
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function (response) {
                        batchUploadDebug('response bulk donation upload', response);
                        handleBatchAjaxResponse(response, 'Bulk upload berhasil.', scrollTop, function () {
                            $submitButton.prop('disabled', false).text(originalText);
                        });
                    },
                    error: function (xhr) {
                        batchUploadDebug('error bulk donation upload', {
                            status: xhr && xhr.status,
                            responseText: xhr && xhr.responseText ? String(xhr.responseText).slice(0, 500) : ''
                        });
                        alert('Bulk upload gagal. Silakan coba lagi.');
                        $submitButton.prop('disabled', false).text(originalText);
                    }
                });
            });

            $(document).on('submit', '.js-donation-ajax-form', function (event) {
                event.preventDefault();

                var $form = $(this);
                var $submitButton = $form.find('button[type="submit"]').first();
                var originalText = $submitButton.text();
                var processingText = $form.data('processing-text') || 'Memproses...';
                var scrollTop = currentBatchDetailScrollTop();
                var $rejectRemark = $form.is('#donation-reject-form') ? $form.find('[name="remark"]') : $();

                if ($rejectRemark.length && !$.trim($rejectRemark.val())) {
                    alert('Remark reject wajib diisi.');
                    $rejectRemark.focus();
                    return;
                }
                if ($form.find('.js-bulk-astri-status').length && !validateBulkAstriRejectedRemarks($form)) {
                    return;
                }

                $submitButton.prop('disabled', true).text(processingText);
                $.ajax({
                    url: $form.attr('action'),
                    type: 'POST',
                    data: $form.serialize(),
                    dataType: 'json',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function (response) {
                        handleBatchAjaxResponse(response, 'Data berhasil diproses.', scrollTop, function () {
                            $submitButton.prop('disabled', false).text(originalText);
                        });
                    },
                    error: function () {
                        alert('Proses gagal. Silakan coba lagi.');
                        $submitButton.prop('disabled', false).text(originalText);
                    }
                });
            });

            $(document).on('click', '.js-open-post-upload-modal', function () {
                var $button = $(this);
                batchUploadDebug('click post donation upload', {
                    docItemId: $button.data('doc-item-id'),
                    docName: $button.data('doc-name'),
                    fileName: $button.data('file-name')
                });
                $('#post_upload_doc_item_id').val($button.data('doc-item-id'));
                $('#post_upload_doc_name').text($button.data('doc-name') || '-');
                $('#post_upload_file_name').text($button.data('file-name') || '-');
                $('#post_upload_remark').val($button.data('remark') || '');
                $('#post_upload_not_required').prop('checked', false);
            });

            $(document).on('click', '.js-open-donation-reject-modal', function () {
                var $button = $(this);
                var actionUrl = $button.data('action-url') || '#';
                var processingText = $button.data('processing-text') || 'Rejecting...';
                var successText = $button.data('success-text') || 'Simpan Reject';
                var submitLabel = $button.data('submit-label') || successText;

                $('#donation-reject-form')
                    .attr('action', actionUrl)
                    .data('processing-text', processingText)
                    .data('success-text', successText);
                $('#donation_reject_file_id').val($button.data('file-id') || '');
                $('#donation_reject_title').text($button.data('title') || 'Reject Dokumen');
                $('#donation_reject_doc_name').text($button.data('doc-name') || '-');
                $('#donation_reject_remark')
                    .val('')
                    .attr('placeholder', $button.data('placeholder') || 'Alasan reject');
                $('#donation_reject_submit').text(submitLabel);
            });

            $(document).on('shown.bs.modal', '#modal-donation-reject', function () {
                $('#donation_reject_remark').trigger('focus');
            });

            $(document).on('click', '.js-open-batch-rar-modal', function () {
                var $button = $(this);
                var path = $button.data('file-path') || '';
                var canUpload = String($button.data('can-upload')) === '1';

                batchUploadDebug('click batch rar upload modal', {
                    canUpload: canUpload,
                    statusLabel: $button.data('status-label'),
                    fileName: $button.data('file-name'),
                    filePath: path
                });
                $('#batch_rar_status_label').text($button.data('status-label') || '-');
                $('#batch_rar_current_file').text($button.data('file-name') || '-');
                $('#batch_rar_remark').val($button.data('remark') || '');
                $('#batch_rar_preview_link').toggleClass('d-none', !path).attr('href', path ? '<?= base_url() ?>' + path : '#');
                $('#batch_rar_upload_section').toggle(canUpload);
                $('#batch_rar_submit_btn').toggle(canUpload);
                $('#batch_rar_readonly_note').toggleClass('d-none', canUpload);
            });

            $(document).on('click', '.js-open-batch-review-modal', function () {
                var $button = $(this);
                $('#batch_approve_file_id, #batch_reject_file_id').val($button.data('file-id') || '');
                $('#batch_approve_file_name, #batch_reject_file_name').text($button.data('file-name') || '-');
            });

            $(document).on('click', '.js-open-post-review-modal', function () {
                var $button = $(this);
                $('#post_approve_file_id, #post_reject_file_id').val($button.data('file-id') || '');
                $('#post_approve_doc_item_id, #post_reject_doc_item_id').val($button.data('doc-item-id') || '');
                $('#post_approve_linked_file_name, #post_reject_linked_file_name').val($button.data('linked-file-name') || '');
                $('#post_approve_doc_name, #post_reject_doc_name').text($button.data('doc-name') || '-');
            });
        });
    })();
</script>
