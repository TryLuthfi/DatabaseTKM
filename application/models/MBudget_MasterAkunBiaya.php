<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MBudget_MasterAkunBiaya extends CI_Model
{
    private $table = 'tb_budget_items';

    public function getItems($keyword = '')
    {
        $this->db->from($this->table);
        if ($keyword !== '') {
            $this->db->group_start()
                ->like('item_code', $keyword)
                ->or_like('item_name', $keyword)
                ->or_like('item_category', $keyword)
                ->or_like('item_group', $keyword)
                ->group_end();
        }

        $this->db->order_by('is_active', 'DESC');
        $this->db->order_by('item_name', 'ASC');

        return $this->db->get()->result_array();
    }

    public function getItemById($id)
    {
        return $this->db
            ->get_where($this->table, ['id_budget_item' => (int) $id])
            ->row_array();
    }

    public function saveItem(array $data)
    {
        $payload = [
            'item_code' => trim($data['item_code'] ?? ''),
            'item_name' => trim($data['item_name'] ?? ''),
            'item_category' => trim($data['item_category'] ?? ''),
            'item_group' => trim($data['item_group'] ?? ''),
            'uom' => trim($data['uom'] ?? ''),
            'default_direction' => strtoupper(trim($data['default_direction'] ?? 'DEBIT')),
            'is_active' => !empty($data['is_active']) ? 1 : 0,
        ];

        $this->db->insert($this->table, $payload);

        return $this->db->insert_id();
    }

    public function updateItem($id, array $data)
    {
        $payload = [
            'item_code' => trim($data['item_code'] ?? ''),
            'item_name' => trim($data['item_name'] ?? ''),
            'item_category' => trim($data['item_category'] ?? ''),
            'item_group' => trim($data['item_group'] ?? ''),
            'uom' => trim($data['uom'] ?? ''),
            'default_direction' => strtoupper(trim($data['default_direction'] ?? 'DEBIT')),
            'is_active' => !empty($data['is_active']) ? 1 : 0,
        ];

        $this->db->where('id_budget_item', (int) $id);
        $this->db->update($this->table, $payload);

        return $this->db->affected_rows() >= 0;
    }

    public function deleteItem($id)
    {
        $this->db->delete($this->table, ['id_budget_item' => (int) $id]);

        return $this->db->affected_rows() > 0;
    }
}
