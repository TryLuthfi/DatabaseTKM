<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MMyRep_Cleanup extends CI_Model
{
    public function deleteAllClusters()
    {
        if (!$this->db->table_exists('tb_myrep_cluster')) {
            return 0;
        }

        $rows = $this->db
            ->select('id_myrep_cluster')
            ->from('tb_myrep_cluster')
            ->order_by('id_myrep_cluster', 'asc')
            ->get()
            ->result_array();

        if (empty($rows)) {
            return 0;
        }

        $deletedCount = 0;
        foreach ($rows as $row) {
            $clusterId = (int) ($row['id_myrep_cluster'] ?? 0);
            if ($clusterId > 0 && $this->deleteWholeCluster($clusterId)) {
                $deletedCount++;
            }
        }

        return $deletedCount;
    }

    public function deleteWholeCluster($myrepClusterId)
    {
        $myrepClusterId = (int) $myrepClusterId;
        if ($myrepClusterId <= 0 || !$this->db->table_exists('tb_myrep_cluster')) {
            return false;
        }

        $cluster = $this->db
            ->select('id_myrep_cluster, rfs_cluster_id')
            ->from('tb_myrep_cluster')
            ->where('id_myrep_cluster', $myrepClusterId)
            ->get()
            ->row_array();

        if (empty($cluster['id_myrep_cluster'])) {
            return false;
        }

        $rfsClusterId = (int) ($cluster['rfs_cluster_id'] ?? 0);
        $pathsToDelete = array_merge(
            $this->collectMyrepFilePaths($myrepClusterId),
            $this->collectRfsFilePaths($rfsClusterId)
        );

        $this->db->trans_start();

        if ($rfsClusterId > 0) {
            $this->deleteRfsChecklistData($rfsClusterId);
        }

        $this->deleteMyrepFlowDocuments($myrepClusterId);
        $this->deleteMyrepImplementationData($myrepClusterId);
        $this->deleteMyrepDrmData($myrepClusterId);
        $this->deleteMyrepBatchData($myrepClusterId);
        $this->deleteMyrepValsalData($myrepClusterId);
        $this->deleteMyrepBakData($myrepClusterId);

        $this->db->where('id_myrep_cluster', $myrepClusterId)->delete('tb_myrep_cluster');

        $this->db->trans_complete();

        if (!$this->db->trans_status()) {
            return false;
        }

        $this->deletePhysicalFiles($pathsToDelete);
        return true;
    }

    public function deleteWholeClusterByRfsCluster($rfsClusterId)
    {
        $rfsClusterId = (int) $rfsClusterId;
        if ($rfsClusterId <= 0 || !$this->db->table_exists('tb_myrep_cluster')) {
            return false;
        }

        $cluster = $this->db
            ->select('id_myrep_cluster')
            ->from('tb_myrep_cluster')
            ->where('rfs_cluster_id', $rfsClusterId)
            ->get()
            ->row_array();

        if (empty($cluster['id_myrep_cluster'])) {
            return false;
        }

        return $this->deleteWholeCluster((int) $cluster['id_myrep_cluster']);
    }

    public function deleteWholeMainfeeder($mainfeederId)
    {
        $mainfeederId = (int) $mainfeederId;
        if ($mainfeederId <= 0 || !$this->db->table_exists('tb_rfs_myrep_mainfeeder')) {
            return false;
        }

        $mainfeeder = $this->db
            ->select('id_mainfeeder')
            ->from('tb_rfs_myrep_mainfeeder')
            ->where('id_mainfeeder', $mainfeederId)
            ->get()
            ->row_array();

        if (empty($mainfeeder['id_mainfeeder'])) {
            return false;
        }

        $pathsToDelete = $this->collectMainfeederFilePaths($mainfeederId);

        $this->db->trans_start();

        $this->deleteMainfeederChecklistData($mainfeederId);
        $this->deleteMainfeederFlowDocuments($mainfeederId);
        $this->deleteMainfeederImplementationData($mainfeederId);
        $this->deleteMainfeederBoqData($mainfeederId);
        $this->deleteMainfeederPoData($mainfeederId);

        if ($this->db->table_exists('tb_myrep_mainfeeder_atp_file')) {
            $this->db->where('id_mainfeeder', $mainfeederId)->delete('tb_myrep_mainfeeder_atp_file');
        }
        if ($this->db->table_exists('tb_myrep_mainfeeder_drm')) {
            $this->db->where('id_mainfeeder', $mainfeederId)->delete('tb_myrep_mainfeeder_drm');
        }

        $this->db->where('id_mainfeeder', $mainfeederId)->delete('tb_rfs_myrep_mainfeeder');

        $this->db->trans_complete();

        if (!$this->db->trans_status()) {
            return false;
        }

        $this->deletePhysicalFiles($pathsToDelete);
        return true;
    }

    private function collectMyrepFilePaths($myrepClusterId)
    {
        $paths = [];

        if ($this->db->table_exists('tb_myrep_flow_doc_package') && $this->db->table_exists('tb_myrep_flow_doc_file')) {
            $rows = $this->db
                ->select('f.file_path')
                ->from('tb_myrep_flow_doc_package p')
                ->join('tb_myrep_flow_doc_file f', 'f.id_doc_package = p.id_doc_package', 'inner')
                ->where('p.id_myrep_cluster', (int) $myrepClusterId)
                ->get()
                ->result_array();

            foreach ($rows as $row) {
                $paths[] = (string) ($row['file_path'] ?? '');
            }
        }

        if ($this->db->table_exists('tb_myrep_boq_progress_photo') && $this->db->table_exists('tb_myrep_boq_progress_item')) {
            $rows = $this->db
                ->select('photo.file_path')
                ->from('tb_myrep_boq_progress_item progress')
                ->join('tb_myrep_boq_progress_photo photo', 'photo.id_progress_item = progress.id_progress_item', 'inner')
                ->where('progress.id_myrep_cluster', (int) $myrepClusterId)
                ->get()
                ->result_array();

            foreach ($rows as $row) {
                $paths[] = (string) ($row['file_path'] ?? '');
            }
        }

        return $paths;
    }

    private function collectRfsFilePaths($rfsClusterId)
    {
        if ($rfsClusterId <= 0) {
            return [];
        }

        $paths = [];

        if ($this->db->table_exists('tb_rfs_myrep_claim')) {
            $rows = $this->db
                ->select('photo_path')
                ->from('tb_rfs_myrep_claim')
                ->where('cluster_id', (int) $rfsClusterId)
                ->get()
                ->result_array();

            foreach ($rows as $row) {
                $paths[] = (string) ($row['photo_path'] ?? '');
            }
        }

        if ($this->db->table_exists('tb_rfs_myrep_doc_package') && $this->db->table_exists('tb_rfs_myrep_doc_file')) {
            $rows = $this->db
                ->select('f.file_path')
                ->from('tb_rfs_myrep_doc_package p')
                ->join('tb_rfs_myrep_doc_file f', 'f.id_doc_package = p.id_doc_package', 'inner')
                ->where('p.cluster_id', (int) $rfsClusterId)
                ->get()
                ->result_array();

            foreach ($rows as $row) {
                $paths[] = (string) ($row['file_path'] ?? '');
            }
        }

        return $paths;
    }

    private function collectMainfeederFilePaths($mainfeederId)
    {
        $paths = [];

        if ($this->db->table_exists('tb_rfs_myrep_mainfeeder_doc_package') && $this->db->table_exists('tb_rfs_myrep_mainfeeder_doc_file')) {
            $rows = $this->db
                ->select('f.file_path')
                ->from('tb_rfs_myrep_mainfeeder_doc_package p')
                ->join('tb_rfs_myrep_mainfeeder_doc_file f', 'f.id_doc_package_mainfeeder = p.id_doc_package_mainfeeder', 'inner')
                ->where('p.id_mainfeeder', (int) $mainfeederId)
                ->get()
                ->result_array();

            foreach ($rows as $row) {
                $paths[] = (string) ($row['file_path'] ?? '');
            }
        }

        if ($this->db->table_exists('tb_myrep_mainfeeder_doc_package') && $this->db->table_exists('tb_myrep_mainfeeder_doc_file')) {
            $rows = $this->db
                ->select('f.file_path')
                ->from('tb_myrep_mainfeeder_doc_package p')
                ->join('tb_myrep_mainfeeder_doc_file f', 'f.id_doc_package_mainfeeder_flow = p.id_doc_package_mainfeeder_flow', 'inner')
                ->where('p.id_mainfeeder', (int) $mainfeederId)
                ->get()
                ->result_array();

            foreach ($rows as $row) {
                $paths[] = (string) ($row['file_path'] ?? '');
            }
        }

        if ($this->db->table_exists('tb_myrep_mainfeeder_impl_daily_activity') && $this->db->table_exists('tb_myrep_mainfeeder_impl_daily_activity_photo')) {
            $rows = $this->db
                ->select('photo.file_path')
                ->from('tb_myrep_mainfeeder_impl_daily_activity activity')
                ->join('tb_myrep_mainfeeder_impl_daily_activity_photo photo', 'photo.id_daily_activity_mainfeeder = activity.id_daily_activity_mainfeeder', 'inner')
                ->where('activity.id_mainfeeder', (int) $mainfeederId)
                ->get()
                ->result_array();

            foreach ($rows as $row) {
                $paths[] = (string) ($row['file_path'] ?? '');
            }
        }

        if ($this->db->table_exists('tb_myrep_mainfeeder_atp_file')) {
            $rows = $this->db
                ->select('file_path')
                ->from('tb_myrep_mainfeeder_atp_file')
                ->where('id_mainfeeder', (int) $mainfeederId)
                ->get()
                ->result_array();

            foreach ($rows as $row) {
                $paths[] = (string) ($row['file_path'] ?? '');
            }
        }

        return $paths;
    }

    private function deleteRfsChecklistData($rfsClusterId)
    {
        if ($rfsClusterId <= 0) {
            return;
        }

        $packageIds = [];
        if ($this->db->table_exists('tb_rfs_myrep_doc_package')) {
            $packageRows = $this->db
                ->select('id_doc_package')
                ->from('tb_rfs_myrep_doc_package')
                ->where('cluster_id', (int) $rfsClusterId)
                ->get()
                ->result_array();

            foreach ($packageRows as $row) {
                $packageIds[] = (int) $row['id_doc_package'];
            }
        }

        if (!empty($packageIds) && $this->db->table_exists('tb_rfs_myrep_doc_file_log')) {
            $this->db->where_in('id_doc_package', $packageIds)->delete('tb_rfs_myrep_doc_file_log');
        }

        if (!empty($packageIds) && $this->db->table_exists('tb_rfs_myrep_doc_file')) {
            $this->db->where_in('id_doc_package', $packageIds)->delete('tb_rfs_myrep_doc_file');
        }

        if (!empty($packageIds) && $this->db->table_exists('tb_rfs_myrep_doc_package')) {
            $this->db->where_in('id_doc_package', $packageIds)->delete('tb_rfs_myrep_doc_package');
        }

        if ($this->db->table_exists('tb_rfs_myrep_claim')) {
            $this->db->where('cluster_id', (int) $rfsClusterId)->delete('tb_rfs_myrep_claim');
        }

        if ($this->db->table_exists('tb_rfs_myrep_cluster_plan')) {
            $this->db->where('cluster_id', (int) $rfsClusterId)->delete('tb_rfs_myrep_cluster_plan');
        }

        if ($this->db->table_exists('tb_rfs_myrep_cluster')) {
            $this->db->where('id_cluster', (int) $rfsClusterId)->delete('tb_rfs_myrep_cluster');
        }
    }

    private function deleteMainfeederChecklistData($mainfeederId)
    {
        $packageIds = [];
        if ($this->db->table_exists('tb_rfs_myrep_mainfeeder_doc_package')) {
            $packageRows = $this->db
                ->select('id_doc_package_mainfeeder')
                ->from('tb_rfs_myrep_mainfeeder_doc_package')
                ->where('id_mainfeeder', (int) $mainfeederId)
                ->get()
                ->result_array();

            foreach ($packageRows as $row) {
                $packageIds[] = (int) $row['id_doc_package_mainfeeder'];
            }
        }

        if (!empty($packageIds) && $this->db->table_exists('tb_rfs_myrep_mainfeeder_doc_file_log')) {
            $this->db->where_in('id_doc_package_mainfeeder', $packageIds)->delete('tb_rfs_myrep_mainfeeder_doc_file_log');
        }
        if (!empty($packageIds) && $this->db->table_exists('tb_rfs_myrep_mainfeeder_doc_file')) {
            $this->db->where_in('id_doc_package_mainfeeder', $packageIds)->delete('tb_rfs_myrep_mainfeeder_doc_file');
        }
        if (!empty($packageIds) && $this->db->table_exists('tb_rfs_myrep_mainfeeder_doc_package')) {
            $this->db->where_in('id_doc_package_mainfeeder', $packageIds)->delete('tb_rfs_myrep_mainfeeder_doc_package');
        }
    }

    private function deleteMainfeederFlowDocuments($mainfeederId)
    {
        $packageIds = [];
        if ($this->db->table_exists('tb_myrep_mainfeeder_doc_package')) {
            $packageRows = $this->db
                ->select('id_doc_package_mainfeeder_flow')
                ->from('tb_myrep_mainfeeder_doc_package')
                ->where('id_mainfeeder', (int) $mainfeederId)
                ->get()
                ->result_array();

            foreach ($packageRows as $row) {
                $packageIds[] = (int) $row['id_doc_package_mainfeeder_flow'];
            }
        }

        if (!empty($packageIds) && $this->db->table_exists('tb_myrep_mainfeeder_doc_file_log')) {
            $this->db->where_in('id_doc_package_mainfeeder_flow', $packageIds)->delete('tb_myrep_mainfeeder_doc_file_log');
        }
        if (!empty($packageIds) && $this->db->table_exists('tb_myrep_mainfeeder_doc_file')) {
            $this->db->where_in('id_doc_package_mainfeeder_flow', $packageIds)->delete('tb_myrep_mainfeeder_doc_file');
        }
        if (!empty($packageIds) && $this->db->table_exists('tb_myrep_mainfeeder_doc_package')) {
            $this->db->where_in('id_doc_package_mainfeeder_flow', $packageIds)->delete('tb_myrep_mainfeeder_doc_package');
        }
    }

    private function deleteMainfeederImplementationData($mainfeederId)
    {
        $activityIds = [];
        if ($this->db->table_exists('tb_myrep_mainfeeder_impl_daily_activity')) {
            $activityRows = $this->db
                ->select('id_daily_activity_mainfeeder')
                ->from('tb_myrep_mainfeeder_impl_daily_activity')
                ->where('id_mainfeeder', (int) $mainfeederId)
                ->get()
                ->result_array();

            foreach ($activityRows as $row) {
                $activityIds[] = (int) $row['id_daily_activity_mainfeeder'];
            }
        }

        if (!empty($activityIds) && $this->db->table_exists('tb_myrep_mainfeeder_impl_daily_activity_photo')) {
            $this->db->where_in('id_daily_activity_mainfeeder', $activityIds)->delete('tb_myrep_mainfeeder_impl_daily_activity_photo');
        }
        if (!empty($activityIds) && $this->db->table_exists('tb_myrep_mainfeeder_impl_daily_activity')) {
            $this->db->where_in('id_daily_activity_mainfeeder', $activityIds)->delete('tb_myrep_mainfeeder_impl_daily_activity');
        }
        if ($this->db->table_exists('tb_myrep_mainfeeder_boq_progress_item')) {
            $this->db->where('id_mainfeeder', (int) $mainfeederId)->delete('tb_myrep_mainfeeder_boq_progress_item');
        }
    }

    private function deleteMainfeederBoqData($mainfeederId)
    {
        $baselineIds = [];
        if ($this->db->table_exists('tb_myrep_mainfeeder_boq_baseline')) {
            $baselineRows = $this->db
                ->select('id_mainfeeder_boq_baseline')
                ->from('tb_myrep_mainfeeder_boq_baseline')
                ->where('id_mainfeeder', (int) $mainfeederId)
                ->get()
                ->result_array();

            foreach ($baselineRows as $row) {
                $baselineIds[] = (int) $row['id_mainfeeder_boq_baseline'];
            }
        }

        if (!empty($baselineIds) && $this->db->table_exists('tb_myrep_mainfeeder_boq_baseline_item')) {
            $this->db->where_in('id_mainfeeder_boq_baseline', $baselineIds)->delete('tb_myrep_mainfeeder_boq_baseline_item');
        }
        if (!empty($baselineIds) && $this->db->table_exists('tb_myrep_mainfeeder_boq_baseline')) {
            $this->db->where_in('id_mainfeeder_boq_baseline', $baselineIds)->delete('tb_myrep_mainfeeder_boq_baseline');
        }

        $boqIds = [];
        if ($this->db->table_exists('tb_myrep_mainfeeder_drm_boq')) {
            $boqRows = $this->db
                ->select('id_mainfeeder_drm_boq')
                ->from('tb_myrep_mainfeeder_drm_boq')
                ->where('id_mainfeeder', (int) $mainfeederId)
                ->get()
                ->result_array();

            foreach ($boqRows as $row) {
                $boqIds[] = (int) $row['id_mainfeeder_drm_boq'];
            }
        }

        if (!empty($boqIds) && $this->db->table_exists('tb_myrep_mainfeeder_drm_boq_item')) {
            $this->db->where_in('id_mainfeeder_drm_boq', $boqIds)->delete('tb_myrep_mainfeeder_drm_boq_item');
        }
        if (!empty($boqIds) && $this->db->table_exists('tb_myrep_mainfeeder_drm_boq')) {
            $this->db->where_in('id_mainfeeder_drm_boq', $boqIds)->delete('tb_myrep_mainfeeder_drm_boq');
        }
    }

    private function deleteMainfeederPoData($mainfeederId)
    {
        if (!$this->db->field_exists('id_mainfeeder', 'tb_myrep_po_header')) {
            return;
        }

        $poIds = [];
        $poRows = $this->db
            ->select('id_po_header')
            ->from('tb_myrep_po_header')
            ->where('id_mainfeeder', (int) $mainfeederId)
            ->get()
            ->result_array();

        foreach ($poRows as $row) {
            $poIds[] = (int) $row['id_po_header'];
        }

        if (!empty($poIds) && $this->db->table_exists('tb_myrep_po_termin')) {
            $this->db->where_in('id_po_header', $poIds)->delete('tb_myrep_po_termin');
        }
        if (!empty($poIds) && $this->db->table_exists('tb_myrep_po_header')) {
            $this->db->where_in('id_po_header', $poIds)->delete('tb_myrep_po_header');
        }
    }

    private function deleteMyrepFlowDocuments($myrepClusterId)
    {
        if (!$this->db->table_exists('tb_myrep_flow_doc_package')) {
            return;
        }

        $packageRows = $this->db
            ->select('id_doc_package')
            ->from('tb_myrep_flow_doc_package')
            ->where('id_myrep_cluster', (int) $myrepClusterId)
            ->get()
            ->result_array();

        $packageIds = [];
        foreach ($packageRows as $row) {
            $packageIds[] = (int) $row['id_doc_package'];
        }

        if (!empty($packageIds) && $this->db->table_exists('tb_myrep_flow_doc_file_log')) {
            $this->db->where_in('id_doc_package', $packageIds)->delete('tb_myrep_flow_doc_file_log');
        }

        if (!empty($packageIds) && $this->db->table_exists('tb_myrep_flow_doc_file')) {
            $this->db->where_in('id_doc_package', $packageIds)->delete('tb_myrep_flow_doc_file');
        }

        if (!empty($packageIds)) {
            $this->db->where_in('id_doc_package', $packageIds)->delete('tb_myrep_flow_doc_package');
        }
    }

    private function deleteMyrepImplementationData($myrepClusterId)
    {
        if ($this->db->table_exists('tb_myrep_boq_progress_item') && $this->db->table_exists('tb_myrep_boq_progress_photo')) {
            $progressRows = $this->db
                ->select('id_progress_item')
                ->from('tb_myrep_boq_progress_item')
                ->where('id_myrep_cluster', (int) $myrepClusterId)
                ->get()
                ->result_array();

            $progressIds = [];
            foreach ($progressRows as $row) {
                $progressIds[] = (int) $row['id_progress_item'];
            }

            if (!empty($progressIds)) {
                $this->db->where_in('id_progress_item', $progressIds)->delete('tb_myrep_boq_progress_photo');
                $this->db->where_in('id_progress_item', $progressIds)->delete('tb_myrep_boq_progress_item');
            }
        }

        if ($this->db->table_exists('tb_myrep_boq_baseline')) {
            $baselineRows = $this->db
                ->select('id_boq_baseline')
                ->from('tb_myrep_boq_baseline')
                ->where('id_myrep_cluster', (int) $myrepClusterId)
                ->get()
                ->result_array();

            $baselineIds = [];
            foreach ($baselineRows as $row) {
                $baselineIds[] = (int) $row['id_boq_baseline'];
            }

            if (!empty($baselineIds) && $this->db->table_exists('tb_myrep_boq_baseline_item')) {
                $this->db->where_in('id_boq_baseline', $baselineIds)->delete('tb_myrep_boq_baseline_item');
            }

            if (!empty($baselineIds)) {
                $this->db->where_in('id_boq_baseline', $baselineIds)->delete('tb_myrep_boq_baseline');
            }
        }
    }

    private function deleteMyrepDrmData($myrepClusterId)
    {
        if ($this->db->table_exists('tb_myrep_drm_boq')) {
            $headerRows = $this->db
                ->select('id_drm_boq')
                ->from('tb_myrep_drm_boq')
                ->where('id_myrep_cluster', (int) $myrepClusterId)
                ->get()
                ->result_array();

            $headerIds = [];
            foreach ($headerRows as $row) {
                $headerIds[] = (int) $row['id_drm_boq'];
            }

            if (!empty($headerIds) && $this->db->table_exists('tb_myrep_drm_boq_item')) {
                $this->db->where_in('id_drm_boq', $headerIds)->delete('tb_myrep_drm_boq_item');
            }

            if (!empty($headerIds)) {
                $this->db->where_in('id_drm_boq', $headerIds)->delete('tb_myrep_drm_boq');
            }
        }

        if ($this->db->table_exists('tb_myrep_drm')) {
            $this->db->where('id_myrep_cluster', (int) $myrepClusterId)->delete('tb_myrep_drm');
        }
    }

    private function deleteMyrepBatchData($myrepClusterId)
    {
        if ($this->db->table_exists('tb_myrep_batch_approval')) {
            $batchRows = $this->db
                ->select('id_batch_approval')
                ->from('tb_myrep_batch_approval')
                ->where('id_myrep_cluster', (int) $myrepClusterId)
                ->get()
                ->result_array();

            $batchIds = [];
            foreach ($batchRows as $row) {
                $batchIds[] = (int) $row['id_batch_approval'];
            }

            if (!empty($batchIds) && $this->db->table_exists('tb_myrep_batch_approval_pic')) {
                $this->db->where_in('id_batch_approval', $batchIds)->delete('tb_myrep_batch_approval_pic');
            }

            if (!empty($batchIds)) {
                $this->db->where_in('id_batch_approval', $batchIds)->delete('tb_myrep_batch_approval');
            }
        }
    }

    private function deleteMyrepValsalData($myrepClusterId)
    {
        if ($this->db->table_exists('tb_myrep_valsal')) {
            $this->db->where('id_myrep_cluster', (int) $myrepClusterId)->delete('tb_myrep_valsal');
        }
    }

    private function deleteMyrepBakData($myrepClusterId)
    {
        if ($this->db->table_exists('tb_myrep_bak')) {
            $this->db->where('id_myrep_cluster', (int) $myrepClusterId)->delete('tb_myrep_bak');
        }
    }

    private function deletePhysicalFiles($paths)
    {
        $paths = array_unique(array_filter(array_map('trim', (array) $paths)));
        foreach ($paths as $path) {
            $fullPath = FCPATH . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
            if (is_file($fullPath)) {
                @unlink($fullPath);
            }
        }
    }
}
