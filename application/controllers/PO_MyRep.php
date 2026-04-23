<?php
defined('BASEPATH') or exit('No direct script access allowed');

class PO_MyRep extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('MPO_MyRep');
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

        $result = $this->MPO_MyRep->updateTermin($terminId, [
            'status_termin' => $statusTermin,
            'invoice_number' => trim((string) $this->input->post('invoice_number')),
            'invoice_date' => $this->normalizeDate($this->input->post('invoice_date')),
            'bast_date' => $this->normalizeDate($this->input->post('bast_date')),
            'payment_date' => $this->normalizeDate($this->input->post('payment_date')),
            'remark_termin' => trim((string) $this->input->post('remark_termin')),
            'updated_by' => (int) $this->session->userdata('id_user'),
        ]);

        $this->session->set_flashdata($result ? 'success' : 'error', $result ? 'Termin berhasil diupdate.' : 'Termin gagal diupdate.');
        redirect('PO_MyRep/detail/' . (int) ($termin['id_myrep_cluster'] ?? 0));
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
}
