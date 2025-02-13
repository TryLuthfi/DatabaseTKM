<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MLogistik_Stok_Detail extends CI_Model
{

    public function getDetailStokAksesoriesArea()
    {

        $url_path = $_SERVER['REQUEST_URI']; // Ambil seluruh URL setelah domain
        $segments = explode("/", $url_path); // Pecah berdasarkan "/"
        $last_segment = end($segments); // Ambil bagian terakhir dari URL

        $this->db->query("SET SESSION group_concat_max_len = 100000");
        $query = "SELECT GROUP_CONCAT(
            DISTINCT CONCAT(
                'COALESCE(SUM(CASE WHEN ki.nama_item = ''', 
                nama_item, 
                ''' THEN CASE 
                           WHEN sm.status_sumber_material = ''IN'' THEN ls.jumlah_stok 
                           WHEN sm.status_sumber_material = ''OUT'' THEN -ls.jumlah_stok 
                           ELSE 0 
                         END END), 0) AS `', 
                nama_item, '`'
            )
          ) AS columns FROM tb_master_logistik_kode_item WHERE kategori_item = 'Aksesories'";
        $result = $this->db->query($query);
        $row = $result->row_array();
        $columns = $row['columns'];

        // Buat query utama
        $sql = "SELECT lg.kota_lokasi_gudang, $columns
        FROM tb_master_logistik_lokasi_gudang lg
        LEFT JOIN tb_logistik_stok ls ON lg.id_lokasi_gudang = ls.id_lokasi_gudang
        LEFT JOIN tb_master_logistik_kode_item ki ON ls.id_kode_item = ki.id_kode_item
        LEFT JOIN tb_master_logistik_sumber_material sm ON ls.id_sumber_material = sm.id_sumber_material
        WHERE lg.kota_lokasi_gudang = '$last_segment'";

        // Jalankan query dan tampilkan hasil
        $data = $this->db->query($sql)->result_array();
        return $data;
    }

    public function getDetailStokClosureArea()
    {

        $url_path = $_SERVER['REQUEST_URI']; // Ambil seluruh URL setelah domain
        $segments = explode("/", $url_path); // Pecah berdasarkan "/"
        $last_segment = end($segments); // Ambil bagian terakhir dari URL

        $this->db->query("SET SESSION group_concat_max_len = 100000");
        $query = "SELECT GROUP_CONCAT(
            DISTINCT CONCAT(
                'COALESCE(SUM(CASE WHEN ki.nama_item = ''', 
                nama_item, 
                ''' THEN CASE 
                           WHEN sm.status_sumber_material = ''IN'' THEN ls.jumlah_stok 
                           WHEN sm.status_sumber_material = ''OUT'' THEN -ls.jumlah_stok 
                           ELSE 0 
                         END END), 0) AS `', 
                nama_item, '`'
            )
          ) AS columns FROM tb_master_logistik_kode_item WHERE kategori_item = 'Closure'";
        $result = $this->db->query($query);
        $row = $result->row_array();
        $columns = $row['columns'];

        // Buat query utama
        $sql = "SELECT lg.kota_lokasi_gudang, $columns
        FROM tb_master_logistik_lokasi_gudang lg
        LEFT JOIN tb_logistik_stok ls ON lg.id_lokasi_gudang = ls.id_lokasi_gudang
        LEFT JOIN tb_master_logistik_kode_item ki ON ls.id_kode_item = ki.id_kode_item
        LEFT JOIN tb_master_logistik_sumber_material sm ON ls.id_sumber_material = sm.id_sumber_material
        WHERE lg.kota_lokasi_gudang = '$last_segment'";

        // Jalankan query dan tampilkan hasil
        $data = $this->db->query($sql)->result_array();
        return $data;
    }

    public function getDetailStokFATArea()
    {

        $url_path = $_SERVER['REQUEST_URI']; // Ambil seluruh URL setelah domain
        $segments = explode("/", $url_path); // Pecah berdasarkan "/"
        $last_segment = end($segments); // Ambil bagian terakhir dari URL

        $this->db->query("SET SESSION group_concat_max_len = 100000");
        $query = "SELECT GROUP_CONCAT(
            DISTINCT CONCAT(
                'COALESCE(SUM(CASE WHEN ki.nama_item = ''', 
                nama_item, 
                ''' THEN CASE 
                           WHEN sm.status_sumber_material = ''IN'' THEN ls.jumlah_stok 
                           WHEN sm.status_sumber_material = ''OUT'' THEN -ls.jumlah_stok 
                           ELSE 0 
                         END END), 0) AS `', 
                nama_item, '`'
            )
          ) AS columns FROM tb_master_logistik_kode_item WHERE kategori_item = 'FAT'";
        $result = $this->db->query($query);
        $row = $result->row_array();
        $columns = $row['columns'];

        // Buat query utama
        $sql = "SELECT lg.kota_lokasi_gudang, $columns
        FROM tb_master_logistik_lokasi_gudang lg
        LEFT JOIN tb_logistik_stok ls ON lg.id_lokasi_gudang = ls.id_lokasi_gudang
        LEFT JOIN tb_master_logistik_kode_item ki ON ls.id_kode_item = ki.id_kode_item
        LEFT JOIN tb_master_logistik_sumber_material sm ON ls.id_sumber_material = sm.id_sumber_material
        WHERE lg.kota_lokasi_gudang = '$last_segment'";

        // Jalankan query dan tampilkan hasil
        $data = $this->db->query($sql)->result_array();
        return $data;
    }

    public function getDetailStokFDTArea()
    {

        $url_path = $_SERVER['REQUEST_URI']; // Ambil seluruh URL setelah domain
        $segments = explode("/", $url_path); // Pecah berdasarkan "/"
        $last_segment = end($segments); // Ambil bagian terakhir dari URL

        $this->db->query("SET SESSION group_concat_max_len = 100000");
        $query = "SELECT GROUP_CONCAT(
            DISTINCT CONCAT(
                'COALESCE(SUM(CASE WHEN ki.nama_item = ''', 
                nama_item, 
                ''' THEN CASE 
                           WHEN sm.status_sumber_material = ''IN'' THEN ls.jumlah_stok 
                           WHEN sm.status_sumber_material = ''OUT'' THEN -ls.jumlah_stok 
                           ELSE 0 
                         END END), 0) AS `', 
                nama_item, '`'
            )
          ) AS columns FROM tb_master_logistik_kode_item WHERE kategori_item = 'FDT'";
        $result = $this->db->query($query);
        $row = $result->row_array();
        $columns = $row['columns'];

        // Buat query utama
        $sql = "SELECT lg.kota_lokasi_gudang, $columns
        FROM tb_master_logistik_lokasi_gudang lg
        LEFT JOIN tb_logistik_stok ls ON lg.id_lokasi_gudang = ls.id_lokasi_gudang
        LEFT JOIN tb_master_logistik_kode_item ki ON ls.id_kode_item = ki.id_kode_item
        LEFT JOIN tb_master_logistik_sumber_material sm ON ls.id_sumber_material = sm.id_sumber_material
        WHERE lg.kota_lokasi_gudang = '$last_segment'";

        // Jalankan query dan tampilkan hasil
        $data = $this->db->query($sql)->result_array();
        return $data;
    }

    public function getDetailStokHDPEArea()
    {

        $url_path = $_SERVER['REQUEST_URI']; // Ambil seluruh URL setelah domain
        $segments = explode("/", $url_path); // Pecah berdasarkan "/"
        $last_segment = end($segments); // Ambil bagian terakhir dari URL

        $this->db->query("SET SESSION group_concat_max_len = 100000");
        $query = "SELECT GROUP_CONCAT(
            DISTINCT CONCAT(
                'COALESCE(SUM(CASE WHEN ki.nama_item = ''', 
                nama_item, 
                ''' THEN CASE 
                           WHEN sm.status_sumber_material = ''IN'' THEN ls.jumlah_stok 
                           WHEN sm.status_sumber_material = ''OUT'' THEN -ls.jumlah_stok 
                           ELSE 0 
                         END END), 0) AS `', 
                nama_item, '`'
            )
          ) AS columns FROM tb_master_logistik_kode_item WHERE kategori_item = 'HDPE'";
        $result = $this->db->query($query);
        $row = $result->row_array();
        $columns = $row['columns'];

        // Buat query utama
        $sql = "SELECT lg.kota_lokasi_gudang, $columns
        FROM tb_master_logistik_lokasi_gudang lg
        LEFT JOIN tb_logistik_stok ls ON lg.id_lokasi_gudang = ls.id_lokasi_gudang
        LEFT JOIN tb_master_logistik_kode_item ki ON ls.id_kode_item = ki.id_kode_item
        LEFT JOIN tb_master_logistik_sumber_material sm ON ls.id_sumber_material = sm.id_sumber_material
        WHERE lg.kota_lokasi_gudang = '$last_segment'";

        // Jalankan query dan tampilkan hasil
        $data = $this->db->query($sql)->result_array();
        return $data;
    }

    public function getDetailStokKabelArea()
    {

        $url_path = $_SERVER['REQUEST_URI']; // Ambil seluruh URL setelah domain
        $segments = explode("/", $url_path); // Pecah berdasarkan "/"
        $last_segment = end($segments); // Ambil bagian terakhir dari URL

        $this->db->query("SET SESSION group_concat_max_len = 100000");
        $query = "SELECT GROUP_CONCAT(
            DISTINCT CONCAT(
                'COALESCE(SUM(CASE WHEN ki.nama_item = ''', 
                nama_item, 
                ''' THEN CASE 
                           WHEN sm.status_sumber_material = ''IN'' THEN ls.jumlah_stok 
                           WHEN sm.status_sumber_material = ''OUT'' THEN -ls.jumlah_stok 
                           ELSE 0 
                         END END), 0) AS `', 
                nama_item, '`'
            )
          ) AS columns FROM tb_master_logistik_kode_item WHERE kategori_item = 'Kabel'";
        $result = $this->db->query($query);
        $row = $result->row_array();
        $columns = $row['columns'];

        // Buat query utama
        $sql = "SELECT lg.kota_lokasi_gudang, $columns
        FROM tb_master_logistik_lokasi_gudang lg
        LEFT JOIN tb_logistik_stok ls ON lg.id_lokasi_gudang = ls.id_lokasi_gudang
        LEFT JOIN tb_master_logistik_kode_item ki ON ls.id_kode_item = ki.id_kode_item
        LEFT JOIN tb_master_logistik_sumber_material sm ON ls.id_sumber_material = sm.id_sumber_material
        WHERE lg.kota_lokasi_gudang = '$last_segment'";

        // Jalankan query dan tampilkan hasil
        $data = $this->db->query($sql)->result_array();
        return $data;
    }

    public function getDetailStokOTBArea()
    {

        $url_path = $_SERVER['REQUEST_URI']; // Ambil seluruh URL setelah domain
        $segments = explode("/", $url_path); // Pecah berdasarkan "/"
        $last_segment = end($segments); // Ambil bagian terakhir dari URL

        $this->db->query("SET SESSION group_concat_max_len = 100000");
        $query = "SELECT GROUP_CONCAT(
            DISTINCT CONCAT(
                'COALESCE(SUM(CASE WHEN ki.nama_item = ''', 
                nama_item, 
                ''' THEN CASE 
                           WHEN sm.status_sumber_material = ''IN'' THEN ls.jumlah_stok 
                           WHEN sm.status_sumber_material = ''OUT'' THEN -ls.jumlah_stok 
                           ELSE 0 
                         END END), 0) AS `', 
                nama_item, '`'
            )
          ) AS columns FROM tb_master_logistik_kode_item WHERE kategori_item = 'OTB'";
        $result = $this->db->query($query);
        $row = $result->row_array();
        $columns = $row['columns'];

        // Buat query utama
        $sql = "SELECT lg.kota_lokasi_gudang, $columns
        FROM tb_master_logistik_lokasi_gudang lg
        LEFT JOIN tb_logistik_stok ls ON lg.id_lokasi_gudang = ls.id_lokasi_gudang
        LEFT JOIN tb_master_logistik_kode_item ki ON ls.id_kode_item = ki.id_kode_item
        LEFT JOIN tb_master_logistik_sumber_material sm ON ls.id_sumber_material = sm.id_sumber_material
        WHERE lg.kota_lokasi_gudang = '$last_segment'";

        // Jalankan query dan tampilkan hasil
        $data = $this->db->query($sql)->result_array();
        return $data;
    }

    public function getDetailStokTiangArea()
    {

        $url_path = $_SERVER['REQUEST_URI']; // Ambil seluruh URL setelah domain
        $segments = explode("/", $url_path); // Pecah berdasarkan "/"
        $last_segment = end($segments); // Ambil bagian terakhir dari URL

        $this->db->query("SET SESSION group_concat_max_len = 100000");
        $query = "SELECT GROUP_CONCAT(
            DISTINCT CONCAT(
                'COALESCE(SUM(CASE WHEN ki.nama_item = ''', 
                nama_item, 
                ''' THEN CASE 
                           WHEN sm.status_sumber_material = ''IN'' THEN ls.jumlah_stok 
                           WHEN sm.status_sumber_material = ''OUT'' THEN -ls.jumlah_stok 
                           ELSE 0 
                         END END), 0) AS `', 
                nama_item, '`'
            )
          ) AS columns FROM tb_master_logistik_kode_item WHERE kategori_item = 'Tiang'";
        $result = $this->db->query($query);
        $row = $result->row_array();
        $columns = $row['columns'];

        // Buat query utama
        $sql = "SELECT lg.kota_lokasi_gudang, $columns
        FROM tb_master_logistik_lokasi_gudang lg
        LEFT JOIN tb_logistik_stok ls ON lg.id_lokasi_gudang = ls.id_lokasi_gudang
        LEFT JOIN tb_master_logistik_kode_item ki ON ls.id_kode_item = ki.id_kode_item
        LEFT JOIN tb_master_logistik_sumber_material sm ON ls.id_sumber_material = sm.id_sumber_material
        WHERE lg.kota_lokasi_gudang = '$last_segment'";

        // Jalankan query dan tampilkan hasil
        $data = $this->db->query($sql)->result_array();
        return $data;
    }

    

}
