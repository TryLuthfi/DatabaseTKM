<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Test extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model('MTest');
        $this->load->library('session');
    }

    public function popabrik()
    {
        if (!empty($this->session->userdata('id_user'))) {
        $data['title'] = 'Test';

        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Templates/02_Menu');
        $this->load->view('Test/popabrik', $data);
        $this->load->view('Templates/03_Footer');
        $this->load->view('Templates/99_JS');
        } else {
            redirect('Auth');
        }
    }

    public function prarea()
    {
        if (!empty($this->session->userdata('id_user'))) {
        $data['title'] = 'Test';

        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Templates/02_Menu');
        $this->load->view('Test/prarea', $data);
        $this->load->view('Templates/03_Footer');
        $this->load->view('Templates/99_JS');
        } else {
            redirect('Auth');
        }
    }
    
    public function print_po_pdf()
    {
        // Data yang akan ditampilkan di PDF
        $data = [
            'nomor_po' => 'TEC.002/TKM-SK/PO/I/2025',
            'tanggal' => '17 Januari 2024',
            'nama_project' => 'Fiberisasi Iforte',
            'lokasi_project' => 'Padang Sidempuan',
            'items' => [
                ['kode' => 'DEN', 'uraian' => 'Breket A', 'satuan' => 'pcs', 'boq' => '3,000', 'stock' => '1,500', 'po' => '5,000', 'realisasi' => '', 'keterangan' => 'Untuk Project Iforte'],
                ['kode' => 'Slack hanger', 'uraian' => 'Slack hanger', 'satuan' => 'pcs', 'boq' => '100', 'stock' => '5', 'po' => '100', 'realisasi' => '', 'keterangan' => 'IF-PSP-Q2 NEW SITE 2025 DF 184'],
                ['kode' => 'HELL', 'uraian' => 'Helical Fitting', 'satuan' => 'pcs', 'boq' => '3,000', 'stock' => '1,890', 'po' => '5,000', 'realisasi' => '', 'keterangan' => 'IF-PSP-Q2 NEW SITE 2025 DF 095'],
                ['kode' => 'STP', 'uraian' => 'Stopping', 'satuan' => 'pcs', 'boq' => '3,000', 'stock' => '9,690', 'po' => '5,000', 'realisasi' => '', 'keterangan' => ''],
            ],
            'provider' => 'Iforte',
            'alamat_pengiriman' => 'Jl. Jamalayu lubis, Kel. Sihtang, Padang Sidempuan',
            'pic_penerima' => 'Bp. Indra Saputra (0812-6931-9644)',
            'ttd' => [
                'logistik_area' => 'INDRA SAPUTRA',
                'finance_manager' => 'EDDY SUWARDI',
                'logistik_ga' => 'IRFAN MUSA',
                'control_project_manager' => 'WARDAN SETIAWAN',
                'planning_manager' => 'YAYA SUNARYA',
                'finance' => 'ALMAIDA'
            ]
        ];

        // Buat objek PDF
        $pdf = new \TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetTitle('Purchase Order');
        $pdf->SetHeaderData('', 0, 'PT. TECHNOLOGY KARYA MANDIRI', 'PURCHASE ORDER - LOKASI SUMUT');
        $pdf->setHeaderFont(['helvetica', '', 12]);
        $pdf->setFooterFont(['helvetica', '', 10]);
        $pdf->SetMargins(10, 30, 10);
        $pdf->SetAutoPageBreak(true, 10);
        $pdf->AddPage();

        // Konten PDF
        $html = '<h3 style="text-align:center;">KEBUTUHAN MATERIAL</h3>';
        $html .= '<table border="0" cellpadding="5">
                    <tr><td><strong>Nomor PO:</strong></td><td>' . $data['nomor_po'] . '</td></tr>
                    <tr><td><strong>Tanggal:</strong></td><td>' . $data['tanggal'] . '</td></tr>
                    <tr><td><strong>Nama Project:</strong></td><td>' . $data['nama_project'] . '</td></tr>
                    <tr><td><strong>Lokasi Project:</strong></td><td>' . $data['lokasi_project'] . '</td></tr>
                  </table><br>';

        // Tabel Material
        $html .= '<table border="1" cellpadding="5">
                    <tr style="background-color:#cccccc;">
                        <th><b>No</b></th>
                        <th><b>Kode Material</b></th>
                        <th><b>Uraian Material</b></th>
                        <th><b>Satuan</b></th>
                        <th><b>BoQ</b></th>
                        <th><b>Stock Area</b></th>
                        <th><b>PO</b></th>
                        <th><b>Realisasi</b></th>
                        <th><b>Keterangan</b></th>
                    </tr>';
        $no = 1;
        foreach ($data['items'] as $item) {
            $html .= '<tr>
                        <td>' . $no++ . '</td>
                        <td>' . $item['kode'] . '</td>
                        <td>' . $item['uraian'] . '</td>
                        <td>' . $item['satuan'] . '</td>
                        <td>' . $item['boq'] . '</td>
                        <td>' . $item['stock'] . '</td>
                        <td>' . $item['po'] . '</td>
                        <td>' . $item['realisasi'] . '</td>
                        <td>' . $item['keterangan'] . '</td>
                      </tr>';
        }
        $html .= '</table><br>';

        // Informasi tambahan
        $html .= '<h4>Berikut Syarat dan Ketentuan:</h4>';
        $html .= '<ul>
                    <li>Waktu Pengiriman Paling Lambat: -</li>
                    <li>Provider: ' . $data['provider'] . '</li>
                    <li>Alamat Pengiriman: ' . $data['alamat_pengiriman'] . '</li>
                    <li>PIC Penerima Material: ' . $data['pic_penerima'] . '</li>
                  </ul><br>';

        // Tanda tangan
        $html .= '<table border="0" cellpadding="5">
                    <tr>
                        <td align="center"><b>Mengetahui</b></td>
                        <td align="center"><b>Menyetujui</b></td>
                    </tr>
                    <tr>
                        <td align="center"><br><br>_________________________<br>' . $data['ttd']['logistik_area'] . '<br>Logistik Area</td>
                        <td align="center"><br><br>_________________________<br>' . $data['ttd']['finance_manager'] . '<br>Finance Manager</td>
                    </tr>
                  </table>';

        $pdf->writeHTML($html, true, false, true, false, '');

        // Output PDF
        $pdf->Output('Purchase_Order.pdf', 'D');
    }
}
