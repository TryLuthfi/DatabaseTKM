<?php
defined('BASEPATH') or exit('No direct script access allowed');

class PO_MyRep extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('MPO_MyRep');
        $this->load->model('MChecklist_Dokument_MyRep');
        $this->load->library('Myrep_access_service', null, 'myrepAccess');
        if (!empty($this->session->userdata('id_user'))) {
            $this->myrepAccess->enforceView('PO_MyRep');
            $this->myrepAccess->enforceByMethod('PO_MyRep', (string) $this->router->fetch_method(), [
                'saveTerminCertificate' => 'EDIT',
                'batchInvoiceTermin' => 'EDIT',
                'batchTerminCertificate' => 'EDIT',
            ]);
        }
    }

    public function index()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $selectedCity = strtoupper(trim((string) $this->input->get('city')));
        $selectedStatus = strtoupper(trim((string) $this->input->get('status')));

        $data['title'] = 'PO MyRep';
        $data['selectedCity'] = $selectedCity;
        $data['selectedStatus'] = $selectedStatus;
        $data['isReady'] = $this->MPO_MyRep->tablesReady();
        $data['cityOptions'] = $this->MPO_MyRep->getCityOptions();
        $data['clusterRows'] = $data['isReady']
            ? $this->MPO_MyRep->getRows($selectedCity, $selectedStatus)
            : [];
        $data['poListRows'] = $data['isReady']
            ? $this->MPO_MyRep->getPoListRows($selectedCity, $selectedStatus)
            : [];
        $data['terminBreakdownRows'] = $data['isReady']
            ? $this->MPO_MyRep->getTerminBreakdownByType($selectedCity, $selectedStatus)
            : [];
        $data['certificateSummaryRows'] = $data['isReady']
            ? $this->MPO_MyRep->getCertificateSummaryByTerm($selectedCity, $selectedStatus)
            : [];
        $data['certificateBatchRows'] = $data['isReady']
            ? $this->MPO_MyRep->getCertificateDetailRows($selectedCity, $selectedStatus, '', 0, 'ALL')
            : [];
        $data['certificateReleasedUninvoicedSummary'] = $data['isReady']
            ? $this->MPO_MyRep->getCertificateReleasedUninvoicedSummary($selectedCity, $selectedStatus)
            : [];
        $data['canBatchInvoice'] = $this->myrepAccess->hasPermission('PO_MyRep', 'EDIT');
        $data['canBatchCertificate'] = $data['canBatchInvoice']
            && (
                strtoupper(trim((string) $this->session->userdata('lokasi_user'))) === 'HO'
                || (string) $this->session->userdata('nama_level') === 'Super Admin'
            );
        $data['summary'] = $this->MPO_MyRep->getDashboardSummary($data['clusterRows']);

        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Templates/02_Menu');
        $this->load->view('PO_MyRep/index', $data);
        $this->load->view('Templates/03_Footer');
        $this->load->view('Templates/99_JS');
    }

    public function detail($clusterId = 0)
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $clusterId = (int) $clusterId;
        if ($clusterId <= 0) {
            redirect('PO_MyRep');
            return;
        }

        $cluster = $this->MPO_MyRep->getClusterById($clusterId);
        if (empty($cluster)) {
            $this->session->set_flashdata('error', 'Detail cluster PO tidak ditemukan.');
            redirect('PO_MyRep');
            return;
        }

        $this->MPO_MyRep->syncTerminEstimatesForCluster($clusterId, '', (int) $this->session->userdata('id_user'));
        $cluster = $this->MPO_MyRep->getClusterById($clusterId);

        $poHeaders = $this->MPO_MyRep->getPoHeadersByClusterId($clusterId);
        $poGroups = [
            'CLUSTER' => [],
            'SUBFEEDER' => [],
        ];

        foreach ($poHeaders as $header) {
            $header['termin_rows'] = $this->MPO_MyRep->getTerminRowsByPoId((int) ($header['id_po_header'] ?? 0));
            $groupKey = strtoupper(trim((string) ($header['po_type'] ?? 'CLUSTER')));
            if (!isset($poGroups[$groupKey])) {
                $poGroups[$groupKey] = [];
            }
            $poGroups[$groupKey][] = $header;
        }

        $data['title'] = 'Detail PO MyRep';
        $data['cluster'] = $cluster;
        $data['poGroups'] = $poGroups;
        $data['certificateTerms'] = $this->MChecklist_Dokument_MyRep->getCertificateTermRows(
            (int) ($cluster['rfs_cluster_id'] ?? 0),
            $clusterId
        );
        $data['poTypeOptions'] = ['CLUSTER' => 'PO Cluster', 'SUBFEEDER' => 'PO Subfeeder'];
        $data['poCategoryOptions'] = ['INITIAL' => 'PO Initial', 'FINAL' => 'PO Final', 'AMANDMENT' => 'PO Amandement'];
        $data['poStatusOptions'] = ['NOT ISSUED' => 'NOT ISSUED', 'ISSUED' => 'ISSUED', 'PARTIAL PAYMENT' => 'PARTIAL PAYMENT', 'FULLY PAID' => 'FULLY PAID', 'CLOSED' => 'CLOSED'];
        $data['terminStatusOptions'] = ['NOT READY' => 'NOT READY', 'READY BILLING' => 'READY BILLING', 'BILLED' => 'BILLED', 'PAID' => 'PAID'];

        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Templates/02_Menu');
        $this->load->view('PO_MyRep/detail', $data);
        $this->load->view('Templates/03_Footer');
        $this->load->view('Templates/99_JS');
    }

    public function savePo()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        if (!$this->MPO_MyRep->tablesReady()) {
            $this->session->set_flashdata('error', 'Tabel PO MyRep belum tersedia.');
            redirect('PO_MyRep');
            return;
        }

        $clusterId = (int) $this->input->post('cluster_id');
        $poType = strtoupper(trim((string) $this->input->post('po_type')));
        $poCategory = strtoupper(trim((string) $this->input->post('po_category')));
        $poNumber = trim((string) $this->input->post('po_number'));
        $poDate = $this->normalizeDate($this->input->post('po_date'));
        $poValue = $this->normalizeNumber($this->input->post('po_value'));
        $statusPo = strtoupper(trim((string) $this->input->post('status_po')));
        $poVersionLabel = trim((string) $this->input->post('po_version_label'));
        $remarkPo = trim((string) $this->input->post('remark_po'));
        $parentPoHeaderId = (int) $this->input->post('parent_po_header_id');

        if ($clusterId <= 0 || $poNumber === '' || $poDate === null || $poValue <= 0) {
            $this->session->set_flashdata('error', 'Cluster, nomor PO, tanggal PO, dan nilai PO wajib diisi.');
            redirect('PO_MyRep/detail/' . $clusterId);
            return;
        }

        if (!in_array($poType, ['CLUSTER', 'SUBFEEDER'], true)) {
            $poType = 'CLUSTER';
        }

        if (!in_array($poCategory, ['INITIAL', 'FINAL', 'AMANDMENT'], true)) {
            $poCategory = 'INITIAL';
        }

        if (!in_array($statusPo, ['NOT ISSUED', 'ISSUED', 'PARTIAL PAYMENT', 'FULLY PAID', 'CLOSED'], true)) {
            $statusPo = 'ISSUED';
        }

        $userId = (int) $this->session->userdata('id_user');
        $result = $this->MPO_MyRep->createPoHeader($clusterId, [
            'parent_po_header_id' => $parentPoHeaderId > 0 ? $parentPoHeaderId : null,
            'po_type' => $poType,
            'po_category' => $poCategory,
            'po_number' => $poNumber,
            'po_date' => $poDate,
            'po_value' => $poValue,
            'status_po' => $statusPo,
            'po_version_label' => $poVersionLabel,
            'remark_po' => $remarkPo,
            'created_by' => $userId,
            'updated_by' => $userId,
        ]);

        $this->session->set_flashdata($result > 0 ? 'success' : 'error', $result > 0 ? 'PO berhasil disimpan.' : 'PO gagal disimpan.');
        redirect('PO_MyRep/detail/' . $clusterId);
    }

    public function updateTermin()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        if (!$this->MPO_MyRep->tablesReady()) {
            $this->session->set_flashdata('error', 'Tabel PO MyRep belum tersedia.');
            redirect('PO_MyRep');
            return;
        }

        $terminId = (int) $this->input->post('id_po_termin');
        $termin = $this->MPO_MyRep->getTerminById($terminId);
        if (empty($termin)) {
            $this->session->set_flashdata('error', 'Data termin tidak ditemukan.');
            redirect('PO_MyRep');
            return;
        }

        $statusTermin = strtoupper(trim((string) $this->input->post('status_termin')));
        if (!in_array($statusTermin, ['NOT READY', 'READY BILLING', 'BILLED', 'PAID'], true)) {
            $statusTermin = 'NOT READY';
        }
        $invoiceDate = $this->normalizeDate($this->input->post('invoice_date'));
        $invoiceValue = $this->normalizeNumber($this->input->post('invoice_value')) ?: null;
        $terminNo = (int) ($termin['termin_no'] ?? 0);
        if (in_array($statusTermin, ['BILLED', 'PAID'], true) && $invoiceDate === null) {
            $this->session->set_flashdata('error', 'Termin belum bisa berstatus ' . $statusTermin . ' karena tanggal invoice wajib diisi.');
            redirect('PO_MyRep/detail/' . (int) ($termin['id_myrep_cluster'] ?? 0));
            return;
        }
        if (
            $terminNo >= 2
            && $terminNo <= 5
            && in_array($statusTermin, ['BILLED', 'PAID'], true)
            && $this->normalizeCertificateDateValue((string) ($termin['sertifikat_invoice_date'] ?? '')) === ''
        ) {
            $this->session->set_flashdata('error', 'Termin ' . $terminNo . ' belum bisa ditagihkan karena sertifikat belum berisi tanggal release yang valid.');
            redirect('PO_MyRep/detail/' . (int) ($termin['id_myrep_cluster'] ?? 0));
            return;
        }
        if ($terminNo === 5 && in_array($statusTermin, ['BILLED', 'PAID'], true) && !$this->isFacTerminDue($termin)) {
            $this->session->set_flashdata('error', 'Termin 5 FAC belum bisa ditagihkan karena belum BJT 90 hari dari tanggal sertifikat RFS.');
            redirect('PO_MyRep/detail/' . (int) ($termin['id_myrep_cluster'] ?? 0));
            return;
        }

        $result = $this->MPO_MyRep->updateTermin($terminId, [
            'status_termin' => $statusTermin,
            'invoice_number' => trim((string) $this->input->post('invoice_number')),
            'invoice_date' => $invoiceDate,
            'invoice_value' => $invoiceValue,
            'bast_date' => $this->normalizeDate($this->input->post('bast_date')),
            'payment_date' => $this->normalizeDate($this->input->post('payment_date')),
            'remark_termin' => trim((string) $this->input->post('remark_termin')),
            'updated_by' => (int) $this->session->userdata('id_user'),
        ]);

        $this->session->set_flashdata($result ? 'success' : 'error', $result ? 'Termin berhasil diupdate.' : 'Termin gagal diupdate.');
        redirect('PO_MyRep/detail/' . (int) ($termin['id_myrep_cluster'] ?? 0));
    }

    public function batchInvoiceTermin()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        if (!$this->MPO_MyRep->tablesReady()) {
            $this->session->set_flashdata('error', 'Tabel PO MyRep belum tersedia.');
            redirect('PO_MyRep');
            return;
        }

        $invoiceDate = $this->normalizeDate($this->input->post('invoice_date'));
        $poNumbers = (array) $this->input->post('po_number');
        $termInputs = (array) $this->input->post('term_no');
        $invoiceValues = (array) $this->input->post('invoice_value');

        if ($invoiceDate === null) {
            $this->session->set_flashdata('error', 'Tanggal invoice general wajib diisi.');
            redirect('PO_MyRep');
            return;
        }

        if (!$this->MPO_MyRep->ensurePoTerminInvoiceValueColumn()) {
            $this->session->set_flashdata('error', 'Kolom nilai invoice belum tersedia dan gagal dibuat.');
            redirect('PO_MyRep');
            return;
        }

        $updatedCount = 0;
        $skippedMessages = [];
        $seenKeys = [];
        foreach ($poNumbers as $index => $poNumber) {
            $poNumber = trim((string) $poNumber);
            $termNo = $this->normalizeTerminNoInput($termInputs[$index] ?? '');
            $invoiceValue = $this->normalizeNumber($invoiceValues[$index] ?? '');

            if ($poNumber === '' && $termNo <= 0 && $invoiceValue <= 0) {
                continue;
            }

            $rowLabel = $poNumber !== '' ? $poNumber . ' T' . ($termNo > 0 ? $termNo : '?') : 'Baris ' . ($index + 1);
            if ($poNumber === '' || $termNo < 1 || $termNo > 5) {
                $skippedMessages[] = $rowLabel . ': nomor PO atau term tidak valid.';
                continue;
            }
            if ($invoiceValue <= 0) {
                $skippedMessages[] = $rowLabel . ': nilai invoice wajib lebih dari 0.';
                continue;
            }

            $dedupeKey = strtoupper($poNumber) . '|' . $termNo;
            if (isset($seenKeys[$dedupeKey])) {
                $skippedMessages[] = $rowLabel . ': duplikat dalam batch.';
                continue;
            }
            $seenKeys[$dedupeKey] = true;

            $termin = $this->MPO_MyRep->getTerminByPoNumberAndTerm($poNumber, $termNo);
            if (empty($termin)) {
                $skippedMessages[] = $rowLabel . ': termin PO tidak ditemukan.';
                continue;
            }

            $statusTermin = strtoupper(trim((string) ($termin['status_termin'] ?? 'NOT READY')));
            if ($statusTermin === 'PAID') {
                $skippedMessages[] = $rowLabel . ': termin sudah PAID.';
                continue;
            }
            if ($termNo >= 2 && $termNo <= 5 && $this->normalizeCertificateDateValue((string) ($termin['sertifikat_invoice_date'] ?? '')) === '') {
                $skippedMessages[] = $rowLabel . ': sertifikat belum release.';
                continue;
            }
            if ($termNo === 5 && !$this->isFacTerminDue($termin)) {
                $skippedMessages[] = $rowLabel . ': FAC belum BJT 90 hari.';
                continue;
            }

            $updated = $this->MPO_MyRep->updateTermin((int) ($termin['id_po_termin'] ?? 0), [
                'status_termin' => 'BILLED',
                'invoice_number' => '',
                'invoice_date' => $invoiceDate,
                'invoice_value' => $invoiceValue,
                'bast_date' => $termin['bast_date'] ?? null,
                'payment_date' => $termin['payment_date'] ?? null,
                'remark_termin' => trim((string) ($termin['remark_termin'] ?? '')),
                'updated_by' => (int) $this->session->userdata('id_user'),
            ]);

            if ($updated) {
                $updatedCount++;
            } else {
                $skippedMessages[] = $rowLabel . ': gagal disimpan.';
            }
        }

        if ($updatedCount > 0) {
            $message = $updatedCount . ' invoice termin berhasil disimpan.';
            if (!empty($skippedMessages)) {
                $message .= ' ' . count($skippedMessages) . ' baris dilewati: ' . implode('; ', array_slice($skippedMessages, 0, 5));
            }
            $this->session->set_flashdata('success', $message);
        } else {
            $this->session->set_flashdata('error', 'Tidak ada invoice termin yang berhasil disimpan. ' . implode('; ', array_slice($skippedMessages, 0, 5)));
        }

        redirect('PO_MyRep');
    }

    public function saveTerminCertificate()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        if (!$this->MPO_MyRep->tablesReady()) {
            $this->session->set_flashdata('error', 'Tabel PO MyRep belum tersedia.');
            redirect('PO_MyRep');
            return;
        }

        $canReleaseCertificate = strtoupper(trim((string) $this->session->userdata('lokasi_user'))) === 'HO'
            || (string) $this->session->userdata('nama_level') === 'Super Admin';
        if (!$canReleaseCertificate) {
            $this->session->set_flashdata('error', 'Anda tidak memiliki akses untuk menyimpan sertifikat.');
            redirect('PO_MyRep');
            return;
        }

        $clusterId = (int) $this->input->post('cluster_id');
        $terminId = (int) $this->input->post('id_po_termin');
        $certificateValue = trim((string) $this->input->post('sertifikat_invoice'));

        if ($clusterId <= 0 || $terminId <= 0) {
            $this->session->set_flashdata('error', 'Data sertifikat tidak valid.');
            redirect('PO_MyRep');
            return;
        }

        $cluster = $this->MPO_MyRep->getClusterById($clusterId);
        if (empty($cluster)) {
            $this->session->set_flashdata('error', 'Cluster PO tidak ditemukan.');
            redirect('PO_MyRep');
            return;
        }

        $termRows = $this->MChecklist_Dokument_MyRep->getCertificateTermRows(
            (int) ($cluster['rfs_cluster_id'] ?? 0),
            $clusterId
        );
        $selectedTerm = null;
        foreach ($termRows as $termRow) {
            if ((int) ($termRow['id_po_termin'] ?? 0) === $terminId) {
                $selectedTerm = $termRow;
                break;
            }
        }

        if (empty($selectedTerm)) {
            $this->session->set_flashdata('error', 'Termin sertifikat tidak ditemukan di cluster ini.');
            redirect('PO_MyRep/detail/' . $clusterId);
            return;
        }

        $isReady = !empty($selectedTerm['is_release_ready']);
        $isReleased = !empty($selectedTerm['is_certificate_released']);
        if (!$isReady && !$isReleased) {
            $this->session->set_flashdata('error', 'Sertifikat belum bisa disimpan karena syarat release belum terpenuhi.');
            redirect('PO_MyRep/detail/' . $clusterId);
            return;
        }

        $result = $this->MChecklist_Dokument_MyRep->updateTerminCertificate(
            $terminId,
            $certificateValue,
            (int) $this->session->userdata('id_user')
        );

        $this->session->set_flashdata($result ? 'success' : 'error', $result ? 'Sertifikat termin berhasil disimpan.' : 'Sertifikat termin gagal disimpan.');
        if (trim((string) $this->input->post('redirect_scope')) === 'dashboard') {
            $query = [];
            $selectedCity = strtoupper(trim((string) $this->input->post('selected_city')));
            $selectedStatus = strtoupper(trim((string) $this->input->post('selected_status')));
            if ($selectedCity !== '') {
                $query['city'] = $selectedCity;
            }
            if ($selectedStatus !== '') {
                $query['status'] = $selectedStatus;
            }
            $suffix = !empty($query) ? '?' . http_build_query($query) : '';
            redirect('PO_MyRep' . $suffix);
            return;
        }
        redirect('PO_MyRep/detail/' . $clusterId);
    }

    public function batchTerminCertificate()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        if (!$this->MPO_MyRep->tablesReady()) {
            $this->session->set_flashdata('error', 'Tabel PO MyRep belum tersedia.');
            redirect('PO_MyRep');
            return;
        }

        $canReleaseCertificate = strtoupper(trim((string) $this->session->userdata('lokasi_user'))) === 'HO'
            || (string) $this->session->userdata('nama_level') === 'Super Admin';
        if (!$canReleaseCertificate) {
            $this->session->set_flashdata('error', 'Anda tidak memiliki akses untuk batch update status/tanggal sertifikat.');
            redirect('PO_MyRep');
            return;
        }

        $poNumbers = (array) $this->input->post('certificate_po_number');
        $termInputs = (array) $this->input->post('certificate_term_no');
        $certificateValues = (array) $this->input->post('certificate_value');
        $allowedStatuses = $this->getAllowedCertificateStatusValues();

        $updatedCount = 0;
        $skippedMessages = [];
        $seenKeys = [];
        $termRowsCache = [];
        foreach ($poNumbers as $index => $poNumber) {
            $poNumber = trim((string) $poNumber);
            $termNo = $this->normalizeTerminNoInput($termInputs[$index] ?? '');
            $certificateValue = trim((string) ($certificateValues[$index] ?? ''));
            $rowLabel = $poNumber !== '' ? $poNumber . ' T' . ($termNo > 0 ? $termNo : '?') : 'Baris ' . ($index + 1);

            if ($poNumber === '' && $termNo <= 0 && $certificateValue === '') {
                continue;
            }
            if ($poNumber === '' || $termNo < 2 || $termNo > 5) {
                $skippedMessages[] = $rowLabel . ': nomor PO wajib dan term sertifikat harus 2-5.';
                continue;
            }
            if ($certificateValue === '') {
                $skippedMessages[] = $rowLabel . ': status/tanggal sertifikat wajib diisi.';
                continue;
            }

            $dedupeKey = strtoupper($poNumber) . '|' . $termNo;
            if (isset($seenKeys[$dedupeKey])) {
                $skippedMessages[] = $rowLabel . ': duplikat dalam batch.';
                continue;
            }
            $seenKeys[$dedupeKey] = true;

            $normalizedDate = $this->normalizeCertificateDateValue($certificateValue);
            $normalizedStatus = strtoupper(preg_replace('/\s+/', ' ', $certificateValue));
            $isDateValue = $normalizedDate !== '';
            $isStatusValue = in_array($normalizedStatus, $allowedStatuses, true);
            if (!$isDateValue && !$isStatusValue) {
                $skippedMessages[] = $rowLabel . ': isi bukan tanggal valid atau status sertifikat yang dikenal.';
                continue;
            }

            $termin = $this->MPO_MyRep->getTerminByPoNumberAndTerm($poNumber, $termNo);
            if (empty($termin)) {
                $skippedMessages[] = $rowLabel . ': termin PO tidak ditemukan.';
                continue;
            }

            if ($isDateValue) {
                $clusterId = (int) ($termin['id_myrep_cluster'] ?? 0);
                if (!isset($termRowsCache[$clusterId])) {
                    $cluster = $this->MPO_MyRep->getClusterById($clusterId);
                    $termRowsCache[$clusterId] = !empty($cluster)
                        ? $this->MChecklist_Dokument_MyRep->getCertificateTermRows((int) ($cluster['rfs_cluster_id'] ?? 0), $clusterId)
                        : [];
                }

                $selectedTerm = null;
                foreach ($termRowsCache[$clusterId] as $termRow) {
                    if ((int) ($termRow['id_po_termin'] ?? 0) === (int) ($termin['id_po_termin'] ?? 0)) {
                        $selectedTerm = $termRow;
                        break;
                    }
                }
                if (empty($selectedTerm) || (empty($selectedTerm['is_release_ready']) && empty($selectedTerm['is_certificate_released']))) {
                    $skippedMessages[] = $rowLabel . ': tanggal sertifikat belum bisa disimpan karena syarat release belum terpenuhi.';
                    continue;
                }
                $saveValue = $normalizedDate;
            } else {
                $saveValue = $normalizedStatus;
            }

            $updated = $this->MChecklist_Dokument_MyRep->updateTerminCertificate(
                (int) ($termin['id_po_termin'] ?? 0),
                $saveValue,
                (int) $this->session->userdata('id_user')
            );

            if ($updated) {
                $updatedCount++;
            } else {
                $skippedMessages[] = $rowLabel . ': gagal disimpan.';
            }
        }

        if ($updatedCount > 0) {
            $message = $updatedCount . ' status/tanggal sertifikat berhasil disimpan.';
            if (!empty($skippedMessages)) {
                $message .= ' ' . count($skippedMessages) . ' baris dilewati: ' . implode('; ', array_slice($skippedMessages, 0, 5));
            }
            $this->session->set_flashdata('success', $message);
        } else {
            $this->session->set_flashdata('error', 'Tidak ada status/tanggal sertifikat yang berhasil disimpan. ' . implode('; ', array_slice($skippedMessages, 0, 5)));
        }

        redirect('PO_MyRep');
    }

    public function getTerminBreakdownDetail()
    {
        if (empty($this->session->userdata('id_user'))) {
            return $this->output
                ->set_status_header(401)
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => false, 'message' => 'Unauthorized']));
        }

        $city = strtoupper(trim((string) $this->input->post('city')));
        $status = strtoupper(trim((string) $this->input->post('status')));
        $poType = strtoupper(trim((string) $this->input->post('po_type')));
        $metric = trim((string) $this->input->post('metric'));
        $termNo = (int) $this->input->post('term_no');

        $rows = $this->MPO_MyRep->getTerminBreakdownDetailRows($city, $status, $poType, $metric, $termNo);
        $metricLabel = strtoupper($poType) . ' - ' . strtoupper($metric);
        if ($metric === 'outstanding_term' && $termNo > 0) {
            $metricLabel = strtoupper($poType) . ' - OUTSTANDING ' . $termNo;
        } elseif ($metric === 'invoice_term' && $termNo > 0) {
            $metricLabel = strtoupper($poType) . ' - INVOICE ' . $termNo;
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => true,
                'title' => $metricLabel,
                'rows' => $rows,
            ]));
    }

    public function getCertificateSummaryDetail()
    {
        if (empty($this->session->userdata('id_user'))) {
            return $this->output
                ->set_status_header(401)
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => false, 'message' => 'Unauthorized']));
        }

        $city = strtoupper(trim((string) $this->input->post('city')));
        $status = strtoupper(trim((string) $this->input->post('status')));
        $poType = strtoupper(trim((string) $this->input->post('po_type')));
        $termNo = (int) $this->input->post('term_no');
        $certificateStatus = strtoupper(trim((string) $this->input->post('certificate_status')));

        $rows = $this->MPO_MyRep->getCertificateDetailRows($city, $status, $poType, $termNo, $certificateStatus);
        $canReleaseCertificate = $this->myrepAccess->hasPermission('PO_MyRep', 'EDIT')
            && (
                strtoupper(trim((string) $this->session->userdata('lokasi_user'))) === 'HO'
                || (string) $this->session->userdata('nama_level') === 'Super Admin'
            );

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => true,
                'title' => $this->buildCertificateDetailTitle($poType, $termNo, $certificateStatus),
                'can_release_certificate' => $canReleaseCertificate,
                'rows' => $rows,
            ]));
    }

    public function getCertificateReleasedUninvoicedDetail()
    {
        if (empty($this->session->userdata('id_user'))) {
            return $this->output
                ->set_status_header(401)
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => false, 'message' => 'Unauthorized']));
        }

        $city = strtoupper(trim((string) $this->input->post('city')));
        $status = strtoupper(trim((string) $this->input->post('status')));
        $termNo = (int) $this->input->post('term_no');
        if ($termNo < 2 || $termNo > 5) {
            $termNo = 0;
        }

        $rows = $this->MPO_MyRep->getCertificateReleasedUninvoicedDetailRows($city, $status, $termNo);
        $title = $termNo > 0
            ? 'Detail Ready Invoice - Term ' . $termNo
            : 'Detail Ready Invoice - Semua Term';

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => true,
                'title' => $title,
                'term_no' => $termNo,
                'ready_rows' => $rows['ready'] ?? [],
                'blocked_rows' => $rows['blocked'] ?? [],
            ]));
    }

    private function normalizeDate($value)
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        $timestamp = strtotime($value);
        return $timestamp ? date('Y-m-d', $timestamp) : null;
    }

    private function buildCertificateDetailTitle($poType, $termNo, $certificateStatus)
    {
        $labels = [
            'ALL' => 'Semua Sertifikat',
            'RELEASED' => 'Released',
            'READY' => 'Ready Release',
            'WAITING_ASTRI' => 'Waiting ASTRI',
            'WAITING_FAC' => 'Waiting FAC/BJT',
            'BLOCKED_BILLING' => 'Blocked Billing',
        ];
        $title = $labels[$certificateStatus] ?? 'Detail Sertifikat';
        if (in_array($poType, ['CLUSTER', 'SUBFEEDER'], true)) {
            $title .= ' - ' . $poType;
        }
        if ($termNo >= 2 && $termNo <= 5) {
            $title .= ' Term ' . $termNo;
        }

        return $title;
    }

    private function isFacTerminDue(array $termin)
    {
        $headerId = (int) ($termin['id_po_header'] ?? 0);
        if ($headerId <= 0 || !$this->db->table_exists('tb_myrep_po_termin')) {
            return false;
        }
        if (!$this->db->field_exists('sertifikat_invoice_date', 'tb_myrep_po_termin')) {
            return false;
        }

        $term4 = $this->db
            ->select('sertifikat_invoice_date')
            ->from('tb_myrep_po_termin')
            ->where('id_po_header', $headerId)
            ->where('termin_no', 4)
            ->limit(1)
            ->get()
            ->row_array();
        $rfsCertificateDate = $this->normalizeCertificateDateValue((string) ($term4['sertifikat_invoice_date'] ?? ''));
        if ($rfsCertificateDate === '') {
            return false;
        }

        return date('Y-m-d') >= date('Y-m-d', strtotime($rfsCertificateDate . ' +90 days'));
    }

    private function normalizeCertificateDateValue($value)
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return date('Y-m-d', strtotime($value));
        }
        if (preg_match('/^\d{1,2}[\/-]\d{1,2}[\/-]\d{2,4}$/', $value)) {
            $timestamp = strtotime($value);
            return $timestamp ? date('Y-m-d', $timestamp) : '';
        }

        return '';
    }

    private function getAllowedCertificateStatusValues()
    {
        return [
            'REVISI',
            'FULL UPLOAD',
            'APPROVED 1',
            'LOGISTIK',
            'PLANNING',
            'TEAM LEADER',
            'WASPANG',
            'PERMIT',
        ];
    }

    private function normalizeNumber($value)
    {
        $normalized = preg_replace('/[^0-9.,-]/', '', (string) $value);
        if ($normalized === '' || $normalized === null) {
            return 0;
        }

        $hasComma = strpos($normalized, ',') !== false;
        $dotCount = substr_count($normalized, '.');

        if ($hasComma) {
            $normalized = str_replace('.', '', $normalized);
            $normalized = str_replace(',', '.', $normalized);
        } elseif ($dotCount > 1) {
            $normalized = str_replace('.', '', $normalized);
        }

        return (float) $normalized;
    }

    private function normalizeTerminNoInput($value)
    {
        $value = strtoupper(trim((string) $value));
        if ($value === '') {
            return 0;
        }

        if (preg_match('/([1-5])/', $value, $matches)) {
            return (int) $matches[1];
        }

        return 0;
    }
}
