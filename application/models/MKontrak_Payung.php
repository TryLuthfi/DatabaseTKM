<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MKontrak_Payung extends CI_Model
{
    public function getPksRows($jenis = '')
    {
        if ($jenis !== '') {
            $this->db->where('jenis_pks', $jenis);
        }

        return $this->db
            ->order_by('tanggal_pks', 'DESC')
            ->order_by('created_at', 'DESC')
            ->get('tb_logistik_pks')
            ->result_array();
    }

    public function getPksById($idPks)
    {
        return $this->db->get_where('tb_logistik_pks', ['id_pks' => (int) $idPks])->row_array();
    }

    public function getSpkRows($idPks = null)
    {
        $this->db->select('s.*, p.nomor_pks, p.pic_pks, p.jenis_pks');
        $this->db->from('tb_logistik_spk s');
        $this->db->join('tb_logistik_pks p', 'p.id_pks = s.id_pks', 'left');

        if ($idPks !== null) {
            $this->db->where('s.id_pks', (int) $idPks);
        }

        return $this->db
            ->order_by('s.tanggal_spk', 'DESC')
            ->order_by('s.created_at', 'DESC')
            ->get()
            ->result_array();
    }

    public function getSpkById($idSpk)
    {
        $this->db->select('s.*, p.nomor_pks, p.pic_pks, p.jenis_pks');
        $this->db->from('tb_logistik_spk s');
        $this->db->join('tb_logistik_pks p', 'p.id_pks = s.id_pks', 'left');
        $this->db->where('s.id_spk', (int) $idSpk);
        return $this->db->get()->row_array();
    }

    public function savePks(array $payload, $idPks = null)
    {
        if ($idPks) {
            $this->db->where('id_pks', (int) $idPks)->update('tb_logistik_pks', $payload);
            return (int) $idPks;
        }

        $this->db->insert('tb_logistik_pks', $payload);
        return (int) $this->db->insert_id();
    }

    public function saveSpk(array $payload, $idSpk = null)
    {
        if ($idSpk) {
            $this->db->where('id_spk', (int) $idSpk)->update('tb_logistik_spk', $payload);
            return (int) $idSpk;
        }

        $this->db->insert('tb_logistik_spk', $payload);
        return (int) $this->db->insert_id();
    }

    public function updatePksApproval($idPks, $statusWorkflow, $approvalNote = '', $approvedBy = null)
    {
        $payload = [
            'workflow_status' => $statusWorkflow,
            'approval_note' => $approvalNote,
            'approved_by' => $approvedBy,
            'approved_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        return $this->db->where('id_pks', (int) $idPks)->update('tb_logistik_pks', $payload);
    }

    public function setPksSignedDoc($idPks, $fieldName, $fileName)
    {
        return $this->db
            ->where('id_pks', (int) $idPks)
            ->update('tb_logistik_pks', [
                $fieldName => $fileName,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
    }

    public function generateNomorPks($jenisPks, $tanggalPks)
    {
        $year = (int) date('Y', strtotime($tanggalPks));
        $month = (int) date('n', strtotime($tanggalPks));
        $monthRoman = $this->toRoman($month);

        $this->db->select_max('pks_seq', 'max_seq');
        $this->db->where('tahun_doc', $year);
        $row = $this->db->get('tb_logistik_pks')->row_array();
        $nextSeq = (int) ($row['max_seq'] ?? 0) + 1;

        $nomor = sprintf('TEC.%03d/TKM.00/%s/%s/%d', $nextSeq, $jenisPks, $monthRoman, $year);

        return [
            'nomor' => $nomor,
            'seq' => $nextSeq,
            'bulan' => $month,
            'tahun' => $year,
        ];
    }

    public function generateNomorSpk($idPks, $jenisPks, $tanggalSpk)
    {
        $pks = $this->getPksById($idPks);
        $pksSeq = (int) ($pks['pks_seq'] ?? 0);

        $year = (int) date('Y', strtotime($tanggalSpk));
        $month = (int) date('n', strtotime($tanggalSpk));
        $monthRoman = $this->toRoman($month);

        $this->db->select_max('spk_seq_global', 'max_seq');
        $row = $this->db->get('tb_logistik_spk')->row_array();
        $globalSeq = (int) ($row['max_seq'] ?? 0) + 1;

        $nomor = sprintf('TEC.%03d.%03d/TKM-07/%s/%s/%d', $pksSeq, $globalSeq, $jenisPks, $monthRoman, $year);

        return [
            'nomor' => $nomor,
            'seq_global' => $globalSeq,
            'bulan' => $month,
            'tahun' => $year,
            'pks_seq' => $pksSeq,
        ];
    }

    private function toRoman($month)
    {
        $romans = [1 => 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
        return $romans[(int) $month] ?? '-';
    }
}
