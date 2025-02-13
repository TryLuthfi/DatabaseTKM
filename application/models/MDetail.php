<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MDetail extends CI_Model
{
    protected $table = 'tb_detail_rincian';

    public function add(array $input)
    {
        return $this->db->insert_batch('tb_detail_rincian', $input);
    }

    public function findKode(array $kode)
    {
        $new = implode(',', $kode);
        ?> 
            <script>
                var judul = <?php echo json_encode($new); ?>;
                console.log(judul);
            </script>
            <?php
        return $this->db->query("SELECT * FROM tb_detail_rincian JOIN tb_kode ON tb_detail_rincian.kode COLLATE utf8mb4_unicode_ci = tb_kode.kode_akun COLLATE utf8mb4_unicode_ci WHERE kode_rincian IN ($new)  AND type_rincian = 'K'")->result_array();
    }
}
