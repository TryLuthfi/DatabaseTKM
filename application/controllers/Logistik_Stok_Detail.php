<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Logistik_Stok_Detail extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model('MLogistik_Stok_Detail');
    }

    public function detail()
    {
        if (!empty($this->session->userdata('id_user'))) {
            $decoded_url_area = $this->getDecodedLastSegment();
            $data = $this->buildAreaDetailData($decoded_url_area);
            $this->renderView('Logistik_Stok_Detail/index', $data, false);
        } else {
            redirect('Auth');
        }
    }

    public function detail_revamp()
    {
        if (!empty($this->session->userdata('id_user'))) {
            $decoded_url_area = $this->getDecodedLastSegment();
            $data = $this->buildAreaDetailData($decoded_url_area);
            $data['title'] = 'Detail Stok Revamp ' . strtoupper($decoded_url_area);
            $data['judul'] = 'Detail Stok Revamp ' . strtoupper($decoded_url_area);
            $this->renderView('Logistik_Stok_Detail/revamp_area', $data);
        } else {
            redirect('Auth');
        }
    }

    public function detail_kategori($kategori_item)
    {
        if (!empty($this->session->userdata('id_user'))) {
            $decoded_url_area = $this->getDecodedLastSegment();
            $data = $this->buildCategoryDetailData($decoded_url_area);
            $this->renderView('Logistik_Stok_Detail/indexkategori', $data, false);
        } else {
            redirect('Auth');
        }
    }

    public function detail_kategori_revamp($kategori_item)
    {
        if (!empty($this->session->userdata('id_user'))) {
            $decoded_url_area = $this->getDecodedLastSegment();
            $data = $this->buildCategoryDetailData($decoded_url_area);
            $data['title'] = 'Detail Stok Revamp ' . strtoupper($decoded_url_area);
            $data['judul'] = 'Detail Stok Revamp ' . $decoded_url_area;
            $this->renderView('Logistik_Stok_Detail/revamp_category', $data);
        } else {
            redirect('Auth');
        }
    }

    public function filter_kategori()
    {
        if (!empty($this->session->userdata('id_user'))) {
            $rincianData = json_decode($this->input->post('rincianData'), true);
            $rincianData2 = json_decode($this->input->post('rincianData2'), true);
            $rincianData3 = json_decode($this->input->post('rincianData3'), true);
            $kategoriItem = $this->input->post('kategori_item');

            if (!empty($rincianData)) {
                $this->session->set_userdata('rincianFilterDashboardLogistik', $rincianData);
                $this->session->set_userdata('getRincianDashboardFilteredBowheer', $rincianData2);
                $this->session->set_userdata('getInOutHistoryFiltered', $rincianData3);
                $this->session->set_userdata('kategori_item', $kategoriItem);
            }

            // Redirect manual dengan JavaScript di AJAX
        } else {
            redirect('Auth');
        }
    }

    public function filter_kategori_rdr()
    {

        if (!empty($this->session->userdata('id_user'))) {
            $data = $this->buildFilteredCategoryData();
            $this->renderView('Logistik_Stok_Detail/indexfilter', $data);
        } else {
            redirect('Auth');
        }
    }

    public function filter_kategori_rdr_revamp()
    {
        if (!empty($this->session->userdata('id_user'))) {
            $data = $this->buildFilteredCategoryData();
            $data['title'] = 'Detail Stok Revamp ' . strtoupper($data['kategori_item']);
            $data['judul'] = 'Detail Stok Revamp ' . strtoupper($data['kategori_item']);
            $this->renderView('Logistik_Stok_Detail/revamp_category', $data);
        } else {
            redirect('Auth');
        }
    }

    private function buildAreaDetailData($decoded_url_area)
    {
        $data['title'] = 'Detail Stok ' . strtoupper($decoded_url_area);
        $data['judul'] = 'Detail Stok ' . strtoupper($decoded_url_area);
        $data['lokasi'] = strtoupper($decoded_url_area);
        $data['getStokDetailArea'] = $this->MLogistik_Stok_Detail->getStokDetailArea();
        $data['getSummaryDetailArea'] = $this->MLogistik_Stok_Detail->getSummaryDetailArea();
        $data['getHistoriInOUtLogistikArea'] = $this->MLogistik_Stok_Detail->getHistoriInOUtLogistikArea();
        return $data;
    }

    private function buildCategoryDetailData($decoded_url_area)
    {
        $data['title'] = 'Detail Stok ' . strtoupper($decoded_url_area);
        $data['judul'] = 'Detail Stok ' . $decoded_url_area;
        $data['kategori_item'] = strtoupper($decoded_url_area);
        $data['getStokPerBowheer'] = $this->MLogistik_Stok_Detail->getStokPerBowheer();
        $data['getDistribusiPerBowheer'] = $this->MLogistik_Stok_Detail->getDistribusiPerBowheer();
        $data['getHistoriInOUtLogistikKategori'] = $this->MLogistik_Stok_Detail->getHistoriInOUtLogistikKategori();
        return $data;
    }

    private function buildFilteredCategoryData()
    {
        $data['rincianFilterDashboardLogistik'] = $this->session->userdata('rincianFilterDashboardLogistik');
        $data['getRincianDashboardFilteredBowheer'] = $this->session->userdata('getRincianDashboardFilteredBowheer');
        $data['getInOutHistoryFiltered'] = $this->session->userdata('getInOutHistoryFiltered');
        $data['kategori_item'] = $this->input->get('kategori');
        $data['title'] = strtoupper($data['kategori_item']);
        $data['judul'] = strtoupper($data['kategori_item']);
        return $data;
    }

    private function getDecodedLastSegment()
    {
        $url_path = $_SERVER['REQUEST_URI'];
        $segments = explode("/", $url_path);
        $last_segment = end($segments);
        return urldecode($last_segment);
    }

    private function renderView($view, $data, $includeFooter = true)
    {
        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Templates/02_Menu');
        $this->load->view($view, $data);
        if ($includeFooter) {
            $this->load->view('Templates/03_Footer');
        }
        $this->load->view('Templates/99_JS');
    }

}
