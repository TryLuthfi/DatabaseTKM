<?php
defined('BASEPATH') or exit('No direct script access allowed');

class SPK extends CI_Controller
{
    private $lampOutputDir = 'uploads/spk_lamp/generated/';

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
        $data['bowheerRows'] = $this->MKontrak_Payung->getMasterBowheer();
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
            'nilai_amandemen_1' => $this->nullableNumber($this->input->post('nilai_amandemen_1')),
            'tanggal_amandemen_2' => $this->nullableDate($this->input->post('tanggal_amandemen_2')),
            'nilai_amandemen_2' => $this->nullableNumber($this->input->post('nilai_amandemen_2')),
            'status_spk' => in_array($statusSpk, ['active', 'non aktif'], true) ? $statusSpk : 'active',
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($this->db->field_exists('akhir_kontrak', 'tb_logistik_spk')) {
            $payload['akhir_kontrak'] = $this->calculateAkhirKontrak(
                (string) $payload['tanggal_spk'],
                trim((string) $tocSpk)
            );
        }

        if ($this->db->field_exists('cluster_ref', 'tb_logistik_spk')) {
            $payload['cluster_ref'] = trim((string) $this->input->post('cluster_ref'));
        }

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

    public function delete($idSpk = 0)
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $idSpk = (int) $idSpk;
        if ($idSpk <= 0) {
            $this->session->set_flashdata('error', 'ID SPK tidak valid.');
            redirect('SPK');
            return;
        }

        $spk = $this->MKontrak_Payung->getSpkById($idSpk);
        if (empty($spk)) {
            $this->session->set_flashdata('error', 'Data SPK tidak ditemukan.');
            redirect('SPK');
            return;
        }

        $this->db->where('id_spk', $idSpk)->delete('tb_logistik_spk');
        $this->session->set_flashdata('success', 'SPK berhasil dihapus.');
        redirect('SPK');
    }

    public function update_amandement()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $idSpk = (int) $this->input->post('id_spk');
        $targetAmd = (int) $this->input->post('target_amandement');
        $nomorAmandementText = trim((string) $this->input->post('nomor_amandement'));
        $tanggal = $this->nullableDate($this->input->post('tanggal_amandement'));
        $nilai = $this->nullableNumber($this->input->post('nilai_amandement'));

        if ($idSpk <= 0) {
            $this->session->set_flashdata('error', 'ID SPK tidak valid.');
            redirect('SPK');
            return;
        }

        $spk = $this->MKontrak_Payung->getSpkById($idSpk);
        if (empty($spk)) {
            $this->session->set_flashdata('error', 'Data SPK tidak ditemukan.');
            redirect('SPK');
            return;
        }

        if (!in_array($targetAmd, [1, 2], true)) {
            $this->session->set_flashdata('error', 'Target amandement wajib dipilih.');
            redirect('SPK');
            return;
        }

        if ($nomorAmandementText === '') {
            $this->session->set_flashdata('error', 'Nomor amandement wajib diisi.');
            redirect('SPK');
            return;
        }

        if ($tanggal === null || $nilai === null) {
            $this->session->set_flashdata('error', 'Tanggal dan nilai amandement wajib diisi.');
            redirect('SPK');
            return;
        }

        $payload = [
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($targetAmd === 1) {
            $payload['tanggal_amandemen_1'] = $tanggal;
            $payload['nilai_amandemen_1'] = $nilai;
            if ($this->db->field_exists('nomor_amandemen_1', 'tb_logistik_spk')) {
                $payload['nomor_amandemen_1'] = $nomorAmandementText;
            } elseif ($this->db->field_exists('nomor_amandement_1', 'tb_logistik_spk')) {
                $payload['nomor_amandement_1'] = $nomorAmandementText;
            }
        } else {
            $payload['tanggal_amandemen_2'] = $tanggal;
            $payload['nilai_amandemen_2'] = $nilai;
            if ($this->db->field_exists('nomor_amandemen_2', 'tb_logistik_spk')) {
                $payload['nomor_amandemen_2'] = $nomorAmandementText;
            } elseif ($this->db->field_exists('nomor_amandement_2', 'tb_logistik_spk')) {
                $payload['nomor_amandement_2'] = $nomorAmandementText;
            }
        }

        $this->MKontrak_Payung->saveSpk($payload, $idSpk);
        $this->session->set_flashdata('success', 'Amandement SPK berhasil disimpan.');
        redirect('SPK');
    }

    public function cluster_options()
    {
        if (empty($this->session->userdata('id_user'))) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status' => 'error',
                    'message' => 'Unauthorized',
                    'rows' => [],
                ]));
            return;
        }

        $bowheer = trim((string) $this->input->get('bowheer'));
        $rows = $this->MKontrak_Payung->getSpkClusterOptionsByBowheer($bowheer);

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => 'success',
                'rows' => $rows,
            ]));
    }

    public function generate_lamp_spk()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $idSpk = (int) $this->input->post('id_spk');
        $warehouseTarget = trim((string) $this->input->post('warehouse_target'));
        if ($idSpk <= 0) {
            $this->session->set_flashdata('error', 'ID SPK tidak valid.');
            redirect('SPK');
            return;
        }
        if (!in_array($warehouseTarget, ['jawa_sumatera', 'kalsul'], true)) {
            $this->session->set_flashdata('error', 'Pilihan target warehouse tidak valid.');
            redirect('SPK');
            return;
        }

        $spk = $this->MKontrak_Payung->getSpkById($idSpk);
        if (empty($spk)) {
            $this->session->set_flashdata('error', 'Data SPK tidak ditemukan.');
            redirect('SPK');
            return;
        }

        $templatePath = $this->getLampTemplatePath();
        if ($templatePath === null) {
            $this->session->set_flashdata('error', 'Template Lamp SPK belum tersedia di server.');
            redirect('SPK');
            return;
        }

        $uploadDir = FCPATH . 'uploads/spk_lamp/tmp/';
        if (!is_dir($uploadDir) && !@mkdir($uploadDir, 0777, true) && !is_dir($uploadDir)) {
            $this->session->set_flashdata('error', 'Gagal menyiapkan folder upload sementara.');
            redirect('SPK');
            return;
        }

        $clusterPath = $this->uploadBoqFile('boq_cluster_file', $uploadDir);
        if ($clusterPath === null) {
            redirect('SPK');
            return;
        }

        $subfeederPath = $this->uploadBoqFile('boq_subfeeder_file', $uploadDir);
        if ($subfeederPath === null) {
            @unlink($clusterPath);
            redirect('SPK');
            return;
        }

        $previousErrorReporting = error_reporting();
        error_reporting($previousErrorReporting & ~E_DEPRECATED & ~E_WARNING & ~E_NOTICE);
        try {
            $this->loadPHPExcel();
            $clusterExcel = PHPExcel_IOFactory::load($clusterPath);
            $subfeederExcel = PHPExcel_IOFactory::load($subfeederPath);
            $lampExcel = PHPExcel_IOFactory::load($templatePath);

            $clusterSheet = $clusterExcel->getSheetByName('BoQ NRO Cluster');
            if ($clusterSheet === null) {
                throw new RuntimeException("Sheet 'BoQ NRO Cluster' tidak ditemukan pada file BOQ Cluster.");
            }

            $subfeederSheet = $subfeederExcel->getSheetByName('BoQ NRO All Feeder');
            if ($subfeederSheet === null) {
                throw new RuntimeException("Sheet 'BoQ NRO All Feeder' tidak ditemukan pada file BOQ Subfeeder.");
            }

            $clusterMap = $this->buildBoqQtyMap($clusterSheet);
            $subfeederMap = $this->buildBoqQtyMap($subfeederSheet);

            $clusterTargetSheet = $lampExcel->getSheetByName('BoQ NRO Cluster');
            if ($clusterTargetSheet === null) {
                $clusterTargetSheet = $lampExcel->getSheet(0);
            }

            $subfeederTargetSheet = $lampExcel->getSheetByName('BoQ NRO All Feeder');
            if ($subfeederTargetSheet === null) {
                $subfeederTargetSheet = $lampExcel->getSheetCount() > 1 ? $lampExcel->getSheet(1) : $clusterTargetSheet;
            }

            $clusterResult = $this->applyQtyMapToLampSheet($clusterTargetSheet, $clusterMap, 'G', 'H', $warehouseTarget);
            $subfeederResult = $this->applyQtyMapToLampSheet($subfeederTargetSheet, $subfeederMap, 'I', 'J', $warehouseTarget);

            $outputAbsDir = FCPATH . $this->lampOutputDir;
            if (!is_dir($outputAbsDir) && !@mkdir($outputAbsDir, 0777, true) && !is_dir($outputAbsDir)) {
                throw new RuntimeException('Gagal menyiapkan folder output Lamp SPK.');
            }

            $safeSpk = preg_replace('/[^A-Za-z0-9\-_]/', '_', (string) ($spk['nomor_spk'] ?? 'SPK'));
            $outputFilename = 'Lamp_SPK_' . $safeSpk . '_' . date('Ymd_His') . '.xlsx';
            $outputPath = $outputAbsDir . $outputFilename;

            $writer = PHPExcel_IOFactory::createWriter($lampExcel, 'Excel2007');
            $writer->save($outputPath);

            $probeItem = $this->normalizeItemName('Instal FO core type SM G.652.D-ADSS 24 cores');
            $clusterProbe = isset($clusterMap[$probeItem]) ? ('Cluster src row ' . $clusterMap[$probeItem]['row'] . ' (M=' . $clusterMap[$probeItem]['material'] . ', S=' . $clusterMap[$probeItem]['service'] . ')') : 'Cluster src: item tidak ditemukan';
            $subfeederProbe = isset($subfeederMap[$probeItem]) ? ('Subfeeder src row ' . $subfeederMap[$probeItem]['row'] . ' (M=' . $subfeederMap[$probeItem]['material'] . ', S=' . $subfeederMap[$probeItem]['service'] . ')') : 'Subfeeder src: item tidak ditemukan';
            $this->session->set_flashdata('success', 'File Lamp SPK berhasil dibuat. Cluster match: ' . $clusterResult['matched'] . ', Subfeeder match: ' . $subfeederResult['matched'] . '. ' . $clusterProbe . '. ' . $subfeederProbe . '.');
            $this->session->set_flashdata('lamp_spk_download', $outputFilename);
        } catch (Exception $e) {
            $this->session->set_flashdata('error', 'Generate Lamp SPK gagal: ' . $e->getMessage());
        } finally {
            error_reporting($previousErrorReporting);
        }

        @unlink($clusterPath);
        @unlink($subfeederPath);
        redirect('SPK');
    }

    public function download_lamp_spk($filename = '')
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $filename = basename((string) $filename);
        if ($filename === '') {
            show_error('File tidak valid.', 400);
            return;
        }

        $fullPath = FCPATH . $this->lampOutputDir . $filename;
        if (!is_file($fullPath)) {
            show_error('File tidak ditemukan.', 404);
            return;
        }

        $this->load->helper('download');
        force_download($fullPath, null);
    }

    private function nullableDate($value)
    {
        $value = trim((string) $value);
        return $value === '' ? null : $value;
    }

    private function nullableNumber($value)
    {
        $value = trim((string) $value);
        return $value === '' ? null : (float) $value;
    }

    private function calculateAkhirKontrak($tanggalSpk, $tocSpk)
    {
        $tanggalSpk = trim((string) $tanggalSpk);
        $tocSpk = trim((string) $tocSpk);
        if ($tanggalSpk === '' || $tocSpk === '' || !ctype_digit($tocSpk)) {
            return null;
        }

        try {
            $date = new DateTime($tanggalSpk);
            $date->modify('+' . (int) $tocSpk . ' days');
            return $date->format('Y-m-d');
        } catch (Exception $e) {
            return null;
        }
    }

    private function uploadBoqFile($fieldName, $uploadDir)
    {
        if (empty($_FILES[$fieldName]['name'])) {
            $this->session->set_flashdata('error', 'File untuk ' . $fieldName . ' wajib diupload.');
            return null;
        }

        $config = [
            'upload_path' => $uploadDir,
            'allowed_types' => 'xls|xlsx',
            'max_size' => 10240,
            'encrypt_name' => true,
        ];
        $this->load->library('upload');
        $this->upload->initialize($config, true);

        if (!$this->upload->do_upload($fieldName)) {
            $this->session->set_flashdata('error', $this->upload->display_errors('', ''));
            return null;
        }

        $uploaded = $this->upload->data();
        return $uploaded['full_path'] ?? null;
    }

    private function loadPHPExcel()
    {
        if (!class_exists('PHPExcel')) {
            require_once APPPATH . 'third_party/PHPExcel/Classes/PHPExcel.php';
        }
    }

    private function getLampTemplatePath()
    {
        $primary = APPPATH . 'templates/Lamp_SPK_Template.xlsx';
        if (is_file($primary)) {
            return $primary;
        }

        $candidates = [
            $primary,
            FCPATH . 'uploads/templates/Lamp_SPK_Template.xlsx',
            FCPATH . 'uploads/templates/Lamp_SPK_FTTH_EMR_TAHAP_1_PERIODE_MEI_AREA_BANDUNG_MITRA_OTIS.xlsx',
        ];

        foreach ($candidates as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return null;
    }

    private function buildBoqQtyMap($sheet)
    {
        $highestRow = (int) $sheet->getHighestRow();
        $map = [];
        for ($row = 1; $row <= $highestRow; $row++) {
            $item = $this->canonicalItemKey($sheet->getCell('B' . $row)->getValue());
            if ($item === '') {
                continue;
            }

            $qtyMaterial = $this->getCellNumericValue($sheet, 'D' . $row);
            $qtyService = $this->getCellNumericValue($sheet, 'E' . $row);
            $candidateTotal = abs($qtyMaterial) + abs($qtyService);
            if (!isset($map[$item])) {
                $map[$item] = [
                    'material' => $qtyMaterial,
                    'service' => $qtyService,
                    'row' => $row,
                    '_total' => $candidateTotal,
                ];
                continue;
            }

            // Jika item duplikat, pakai baris dengan total qty terbesar
            if ($candidateTotal > $map[$item]['_total']) {
                $map[$item]['material'] = $qtyMaterial;
                $map[$item]['service'] = $qtyService;
                $map[$item]['row'] = $row;
                $map[$item]['_total'] = $candidateTotal;
            }
        }

        foreach ($map as $key => $value) {
            unset($map[$key]['_total']);
        }

        return $map;
    }

    private function applyQtyMapToLampSheet($sheet, array $map, $materialColumn = 'G', $serviceColumn = 'H', $warehouseTarget = 'jawa_sumatera')
    {
        $highestRow = (int) $sheet->getHighestRow();
        $matched = 0;
        $warehouseQty = $this->findWarehouseQty($map);

        for ($row = 1; $row <= $highestRow; $row++) {
            $item = $this->canonicalItemKey($sheet->getCell('C' . $row)->getValue());
            if ($item === 'warehouse') {
                $sheet->setCellValueExplicit($materialColumn . $row, 0, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                $sheet->setCellValueExplicit($serviceColumn . $row, 0, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                continue;
            }
            if ($item === 'handling warehouse (jawa-sumatera)' || $item === 'handling warehouse (kalsul)') {
                $isSelected = ($warehouseTarget === 'jawa_sumatera' && $item === 'handling warehouse (jawa-sumatera)')
                    || ($warehouseTarget === 'kalsul' && $item === 'handling warehouse (kalsul)');
                $sheet->setCellValueExplicit($materialColumn . $row, $isSelected ? $warehouseQty['material'] : 0, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                $sheet->setCellValueExplicit($serviceColumn . $row, $isSelected ? $warehouseQty['service'] : 0, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                if ($isSelected && ($warehouseQty['material'] != 0.0 || $warehouseQty['service'] != 0.0)) {
                    $matched++;
                }
                continue;
            }

            if ($item === '' || !isset($map[$item])) {
                continue;
            }

            $sheet->setCellValueExplicit($materialColumn . $row, $map[$item]['material'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit($serviceColumn . $row, $map[$item]['service'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $matched++;
        }

        return ['matched' => $matched];
    }

    private function findWarehouseQty(array $map)
    {
        $aliases = [
            'warehouse',
            'handling warehouse',
            'jasa warehouse',
            'warehouse handling',
        ];

        foreach ($aliases as $alias) {
            if (isset($map[$alias])) {
                return [
                    'material' => (float) ($map[$alias]['material'] ?? 0),
                    'service' => (float) ($map[$alias]['service'] ?? 0),
                ];
            }
        }

        foreach ($map as $item => $qty) {
            if (strpos($item, 'warehouse') !== false) {
                return [
                    'material' => (float) ($qty['material'] ?? 0),
                    'service' => (float) ($qty['service'] ?? 0),
                ];
            }
        }

        return ['material' => 0.0, 'service' => 0.0];
    }

    private function normalizeItemName($value)
    {
        $text = trim((string) $value);
        if ($text === '') {
            return '';
        }

        $text = preg_replace('/\s+/', ' ', $text);
        return strtolower($text);
    }

    private function canonicalItemKey($value)
    {
        $key = $this->normalizeItemName($value);
        if ($key === '') {
            return '';
        }

        $aliases = [
            'pengamanan & persiapan (kesehatan dan keselamatan kerja)' => 'pengamanan perizinan dan k3',
            'pengamanan dan persiapan (kesehatan dan keselamatan kerja)' => 'pengamanan perizinan dan k3',
        ];

        return $aliases[$key] ?? $key;
    }

    private function toNumeric($value)
    {
        if (is_numeric($value)) {
            return (float) $value;
        }
        $text = trim((string) $value);
        if ($text === '') {
            return 0.0;
        }

        // 1,276 or 1.276 (thousands grouping) => 1276
        if (preg_match('/^-?\d{1,3}([.,]\d{3})+$/', $text)) {
            return (float) str_replace([',', '.'], '', $text);
        }

        // 12,5 => 12.5
        if (preg_match('/^-?\d+[.,]\d+$/', $text)) {
            return (float) str_replace(',', '.', $text);
        }

        $clean = preg_replace('/[^0-9\.-]/', '', $text);
        return is_numeric($clean) ? (float) $clean : 0.0;
    }

    private function getCellNumericValue($sheet, $coordinate)
    {
        $cell = $sheet->getCell($coordinate);
        $value = $cell->getValue();

        // Hindari kalkulasi runtime PHPExcel: pakai cached result jika sel formula.
        if (is_string($value) && strlen($value) > 0 && $value[0] === '=') {
            $cached = $cell->getOldCalculatedValue();
            if ($cached !== null && $cached !== '') {
                return $this->toNumeric($cached);
            }
        }

        return $this->toNumeric($value);
    }
}
