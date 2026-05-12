<?php
defined('BASEPATH') or exit('No direct script access allowed');

class SPK extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('MKontrak_Payung');
    }

    public function index()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $data['title'] = 'SPK';
        $data['pksRows'] = $this->MKontrak_Payung->getPksRows();
        $data['spkRows'] = $this->MKontrak_Payung->getSpkRows();

        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Templates/02_Menu');
        $this->load->view('SPK/index', $data);
        $this->load->view('Templates/03_Footer');
        $this->load->view('Templates/99_JS');
    }

    public function save()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $idSpk = (int) $this->input->post('id_spk');
        $idPks = (int) $this->input->post('id_pks');
        $bowheer = trim((string) $this->input->post('bowheer'));
        $project = trim((string) $this->input->post('project_name'));
        $tanggalSpk = trim((string) $this->input->post('tanggal_spk'));
        $nilaiSpk = (float) $this->input->post('nilai_spk');
        $tocSpk = trim((string) $this->input->post('toc_spk'));
        $statusSpk = strtolower(trim((string) $this->input->post('status_spk')));

        $pks = $this->MKontrak_Payung->getPksById($idPks);
        if (empty($pks)) {
            $this->session->set_flashdata('error', 'PKS untuk SPK tidak valid.');
            redirect('SPK');
            return;
        }

        $nomorSpkData = $idSpk > 0 ? null : $this->MKontrak_Payung->generateNomorSpk($idPks, (string) $pks['jenis_pks'], $tanggalSpk ?: date('Y-m-d'));

        $payload = [
            'id_pks' => $idPks,
            'tanggal_spk' => $tanggalSpk ?: date('Y-m-d'),
            'bowheer' => $bowheer,
            'project_name' => $project,
            'nilai_spk' => $nilaiSpk,
            'toc_spk' => $tocSpk,
            'tanggal_amandemen_1' => $this->nullableDate($this->input->post('tanggal_amandemen_1')),
            'nilai_amandemen_1' => (float) $this->input->post('nilai_amandemen_1'),
            'tanggal_amandemen_2' => $this->nullableDate($this->input->post('tanggal_amandemen_2')),
            'nilai_amandemen_2' => (float) $this->input->post('nilai_amandemen_2'),
            'status_spk' => in_array($statusSpk, ['active', 'non aktif'], true) ? $statusSpk : 'active',
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($idSpk <= 0) {
            $payload['nomor_spk'] = $nomorSpkData['nomor'];
            $payload['spk_seq_global'] = $nomorSpkData['seq_global'];
            $payload['bulan_doc'] = $nomorSpkData['bulan'];
            $payload['tahun_doc'] = $nomorSpkData['tahun'];
            $payload['created_at'] = date('Y-m-d H:i:s');
        }

        $this->MKontrak_Payung->saveSpk($payload, $idSpk > 0 ? $idSpk : null);
        $this->session->set_flashdata('success', $idSpk > 0 ? 'SPK berhasil diperbarui.' : 'SPK berhasil dibuat.');
        redirect('SPK');
    }

    public function print_doc($idSpk)
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $spk = $this->MKontrak_Payung->getSpkById((int) $idSpk);
        if (empty($spk)) {
            show_error('SPK tidak ditemukan.', 404);
            return;
        }

        $this->load->view('format_spk_print', ['spk' => $spk]);
    }

    private function nullableDate($value)
    {
        $value = trim((string) $value);
        return $value === '' ? null : $value;
    }
}
