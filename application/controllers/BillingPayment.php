<?php
defined('BASEPATH') or exit('No direct script access allowed');

class BillingPayment extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model('MBillingPayment');
    }

    public function index()
    {
        if (!empty($this->session->userdata('id_user'))) {

            $data['title'] = 'RINCIAN INVOICE';
            $data['judul'] = 'RINCIAN INVOICE';
            $data['getTargetPriorityBowheer'] = $this->MBillingPayment->getTargetPriorityBowheer();
            $data['getAllData'] = $this->MBillingPayment->getAllData();

            $this->load->view('Templates/01_Header', $data);
            $this->load->view('Templates/02_Menu');
            $this->load->view('BillingPayment/index', $data);
            $this->load->view('Templates/03_Footer');
            $this->load->view('Templates/99_JS');
        } else {
            redirect('Auth');
        }
    }

    public function getFilteredBillingPaymentAjax()
    {

        error_reporting(0);
        ini_set('display_errors', 0);

        $bowheer = $this->input->post('bowheer');
        $regional = $this->input->post('regional');
        $city = $this->input->post('city');
        $priority = $this->input->post('priority');

        $data = $this->MBillingPayment->getFilteredBillingPayment($bowheer, $regional, $city, $priority);

        // Tentukan kolom yang tampil berdasarkan filter
        $columns = ['No', 'Bowheer', 'Invoice', 'Price', 'Regional', 'Area', 'Date Submit', 'Due Date', 'Aging', "Priority", "PO Number", "Status Invoice", "Action"];

        echo json_encode([
            'columns' => $columns,
            'data' => $data
        ]);

        log_message('debug', 'Last Query: ' . $this->db->last_query());
    }

    public function getOpenInvoices()
    {
        error_reporting(0);
        ini_set('display_errors', 0);

        $keyword = $this->input->get('q');

        $this->db->select('tbp.id_billing, tbp.no_invoice, tbp.po_number, tbp.invoice_price_nett, tmbi.nama_bowheer');
        $this->db->from('tb_billingpayment tbp');
        $this->db->join('tb_master_bowheer_bilco tmbi', 'tbp.id_bowheer = tmbi.id_bowheer', 'left');
        $this->db->where('tbp.status_invoice', 'open');

        if (!empty($keyword)) {
            $this->db->group_start();
            $this->db->like('tbp.no_invoice', $keyword);
            $this->db->or_like('tbp.po_number', $keyword);
            $this->db->or_like('tmbi.nama_bowheer', $keyword);
            $this->db->group_end();
        }

        $this->db->order_by('tbp.no_invoice', 'ASC');
        $this->db->limit(20);

        $query = $this->db->get()->result_array();

        $results = [];
        foreach ($query as $row) {
            $results[] = [
                'id' => $row['id_billing'],
                'text' => $row['no_invoice'],
                'no_invoice' => $row['no_invoice'],
                'po_number' => $row['po_number'],
                'nama_bowheer' => $row['nama_bowheer'],
                'invoice_price_nett' => $row['invoice_price_nett']
            ];
        }

        echo json_encode(['results' => $results]);
    }

    public function saveBatchPayment()
    {

        error_reporting(0);
        ini_set('display_errors', 0);

        $idBilling = $this->input->post('id_billing');
        $paymentPrice = $this->input->post('invoice_price_payment');
        $paymentDate = $this->input->post('tgl_payment_invoice');

        if (empty($idBilling) || !is_array($idBilling)) {
            echo json_encode([
                'status' => false,
                'message' => 'Data invoice tidak ditemukan'
            ]);
            return;
        }

        $this->db->trans_start();

        for ($i = 0; $i < count($idBilling); $i++) {
            $id = (int) $idBilling[$i];
            $price = preg_replace('/[^\d]/', '', $paymentPrice[$i]);
            $date = $paymentDate[$i];

            if (empty($id) || empty($price) || empty($date)) {
                continue;
            }

            $this->db->where('id_billing', $id);
            $this->db->where('status_invoice', 'open');
            $this->db->update('tb_billingpayment', [
                'status_invoice' => 'paid',
                'invoice_price_payment' => $price,
                'tgl_payment_invoice' => $date
            ]);
        }

        $this->db->trans_complete();

        if ($this->db->trans_status()) {
            echo json_encode([
                'status' => true,
                'message' => 'Pembayaran berhasil disimpan'
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'message' => 'Gagal menyimpan pembayaran'
            ]);
        }
    }

}
