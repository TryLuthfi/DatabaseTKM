<?php
defined('BASEPATH') or exit('No direct script access allowed');

class PO_Monitor extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('MPO_Monitor');
    }

    public function index()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $data['title'] = 'PO Monitoring';
        $data['poList'] = $this->MPO_Monitor->getPOsSummary();
        $data['bowheerSummary'] = $this->MPO_Monitor->getPOSummaryByBowheer();
        $data['bowheerTermBreakdown'] = $this->MPO_Monitor->getBowheerTermBreakdown();

        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Templates/02_Menu');
        $this->load->view('PO_Monitor/index', $data);
        $this->load->view('Templates/03_Footer');
        $this->load->view('Templates/99_JS');
    }

    public function detail($id_po = 0)
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $po = $this->MPO_Monitor->getPOById((int) $id_po);
        if (!$po) {
            show_404();
            return;
        }

        $data['title'] = 'PO Detail: ' . $po['po_number'];
        $data['po'] = $po;
        $data['terms'] = $this->MPO_Monitor->getPOTerms((int) $id_po);

        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Templates/02_Menu');
        $this->load->view('PO_Monitor/detail', $data);
        $this->load->view('Templates/03_Footer');
        $this->load->view('Templates/99_JS');
    }

    private function normalizeAmount($value)
    {
        if ($value === null || $value === '') {
            return 0;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        $normalized = trim((string) $value);
        $normalized = str_replace(' ', '', $normalized);

        $lastDot = strrpos($normalized, '.');
        $lastComma = strrpos($normalized, ',');

        if ($lastDot !== false && $lastComma !== false) {
            if ($lastDot > $lastComma) {
                $normalized = str_replace(',', '', $normalized);
            } else {
                $normalized = str_replace('.', '', $normalized);
                $normalized = str_replace(',', '.', $normalized);
            }
        } elseif ($lastComma !== false) {
            $normalized = str_replace('.', '', $normalized);
            $normalized = str_replace(',', '.', $normalized);
        } else {
            $parts = explode('.', $normalized);
            if (count($parts) > 2) {
                $decimalPart = array_pop($parts);
                $normalized = implode('', $parts) . '.' . $decimalPart;
            }
        }

        return (float) preg_replace('/[^\d.\-]/', '', $normalized);
    }

    public function create()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $data['title'] = 'Tambah PO';
        // term masters
        $data['masters'] = $this->db->order_by('id_master')->get('tb_term_master')->result_array();
        // bowheers (projects) - optional
        $data['bowheers'] = $this->db->select('id_bowheer, nama_bowheer')->order_by('nama_bowheer')->get('tb_master_bowheer_bilco')->result_array();

        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Templates/02_Menu');
        $this->load->view('PO_Monitor/create', $data);
        $this->load->view('Templates/03_Footer');
        $this->load->view('Templates/99_JS');
    }

    public function store()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $po_number = trim((string) $this->input->post('po_number'));
        $po_date_raw = $this->input->post('po_date');
        $po_date = $po_date_raw ? date('Y-m-d', strtotime($po_date_raw)) : null;
        $id_bowheer = (int) $this->input->post('id_bowheer');
        $total_value = $this->normalizeAmount($this->input->post('total_value'));
        $master_id = (int) $this->input->post('master_id');

        if ($po_number === '' || $total_value <= 0) {
            $this->session->set_flashdata('status', false);
            $this->session->set_flashdata('error_log', 'PO number and total value are required');
            redirect('PO_Monitor/create');
            return;
        }

        // duplicate check
        if ($this->db->get_where('tb_po', ['po_number' => $po_number])->num_rows() > 0) {
            $this->session->set_flashdata('status', false);
            $this->session->set_flashdata('error_log', 'PO number already exists');
            redirect('PO_Monitor/create');
            return;
        }

        // insert PO
        $this->db->insert('tb_po', [
            'po_number' => $po_number,
            'po_date' => $po_date,
            'id_bowheer' => $id_bowheer > 0 ? $id_bowheer : null,
            'total_value' => $total_value,
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => $this->session->userdata('id_user'),
            'notes' => $this->input->post('notes')
        ]);

        $id_po = $this->db->insert_id();

        if (!$id_po) {
            $this->session->set_flashdata('status', false);
            $this->session->set_flashdata('error_log', 'Failed to create PO');
            redirect('PO_Monitor/create');
            return;
        }

        // create initial amendment (amend_no = 1)
        $this->db->insert('tb_po_amend', [
            'id_po' => $id_po,
            'amend_no' => 1,
            'release_value' => $total_value,
            'release_date' => $po_date,
            'notes' => 'Initial release'
        ]);

        $id_amend = $this->db->insert_id();

        // create terms based on selected master, fallback single-term 100%
        $splits = [];
        if ($master_id > 0) {
            $splits = $this->db->order_by('term_index')->get_where('tb_term_master_split', ['id_master' => $master_id])->result_array();
        }

        if (empty($splits)) {
            $splits = [[ 'term_index' => 1, 'percent' => 100.00 ]];
        }

        $remaining = (float) $total_value;
        $count = count($splits);
        $i = 0;
        foreach ($splits as $s) {
            $i++;
            // last term gets remainder to avoid rounding issues
            if ($i === $count) {
                $value = $remaining;
            } else {
                $value = round($total_value * (floatval($s['percent']) / 100), 2);
                $remaining -= $value;
            }

            $this->db->insert('tb_po_term', [
                'id_po' => $id_po,
                'id_amend' => $id_amend,
                'term_index' => (int) $s['term_index'],
                'percent' => (float) $s['percent'],
                'value' => $value,
                'due_date' => $this->input->post('due_date_' . $s['term_index']) ?: null,
                'sla_days' => $this->input->post('sla_days_' . $s['term_index']) ?: null
            ]);
        }

        $this->session->set_flashdata('status', true);
        $this->session->set_flashdata('error_log', 'PO created successfully');
        redirect('PO_Monitor');
    }

    public function allocate()
    {
        if (empty($this->session->userdata('id_user'))) {
            echo json_encode(['status' => false, 'message' => 'Not authenticated']);
            return;
        }

        $po_id = $this->input->post('id_po');
        if (empty($po_id)) {
            echo json_encode(['status' => false, 'message' => 'Missing id_po']);
            return;
        }

        $po = $this->MPO_Monitor->getPOById((int) $po_id);
        if (!$po) {
            echo json_encode(['status' => false, 'message' => 'PO not found']);
            return;
        }

        $res = $this->MPO_Monitor->allocateInvoicesToTermsByPoNumber($po['po_number']);
        echo json_encode($res);
    }
}
