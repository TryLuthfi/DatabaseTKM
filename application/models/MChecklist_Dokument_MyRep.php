<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MChecklist_Dokument_MyRep extends CI_Model
{
    public function ensureClusterPackages($clusterId, $tanggalRfs = null)
    {
        $clusterId = (int) $clusterId;
        if ($clusterId <= 0) {
            return;
        }

        $groups = $this->getDocumentGroups();
        foreach ($groups as $group) {
            $existing = $this->db->get_where('tb_rfs_myrep_doc_package', [
                'cluster_id' => $clusterId,
                'id_doc_group' => (int) $group['id_doc_group'],
            ])->row_array();

            if ($existing) {
                if (!empty($tanggalRfs) && empty($existing['tanggal_rfs'])) {
                    $planAtp = $this->addBusinessDays($tanggalRfs, 7);
                    $this->db
                        ->where('id_doc_package', (int) $existing['id_doc_package'])
                        ->update('tb_rfs_myrep_doc_package', [
                            'tanggal_rfs' => $tanggalRfs,
                            'plan_atp_date' => $planAtp,
                        ]);
                }
                continue;
            }

            $planAtp = !empty($tanggalRfs) ? $this->addBusinessDays($tanggalRfs, 7) : null;
            $this->db->insert('tb_rfs_myrep_doc_package', [
                'cluster_id' => $clusterId,
                'id_doc_group' => (int) $group['id_doc_group'],
                'tanggal_rfs' => $tanggalRfs,
                'plan_atp_date' => $planAtp,
                'status_package' => 'NOT STARTED',
                'created_by' => (int) $this->session->userdata('id_user'),
                'updated_by' => (int) $this->session->userdata('id_user'),
            ]);
        }
    }

    public function getCityOptions()
    {
        $rows = $this->db
            ->distinct()
            ->select('mt.city_name')
            ->from('tb_rfs_myrep_cluster c')
            ->join('tb_rfs_myrep_monthly_target mt', 'mt.id_target = c.id_target', 'inner')
            ->where('c.status_rfs', 'FULL RFS')
            ->order_by('mt.city_name', 'ASC')
            ->get()
            ->result_array();

        $cities = [];
        foreach ($rows as $row) {
            $city = strtoupper(trim((string) ($row['city_name'] ?? '')));
            if ($city !== '') {
                $cities[$city] = $city;
            }
        }

        return array_values($cities);
    }

    public function getRegionalOptions()
    {
        $rows = $this->db
            ->distinct()
            ->select('mt.regional_name')
            ->from('tb_rfs_myrep_cluster c')
            ->join('tb_rfs_myrep_monthly_target mt', 'mt.id_target = c.id_target', 'inner')
            ->where('c.status_rfs', 'FULL RFS')
            ->order_by('mt.regional_name', 'ASC')
            ->get()
            ->result_array();

        $regionals = [];
        foreach ($rows as $row) {
            $regional = strtoupper(trim((string) ($row['regional_name'] ?? '')));
            if ($regional !== '') {
                $regionals[$regional] = $regional;
            }
        }

        return array_values($regionals);
    }

    public function getFullRfsClusters($city = '', $regional = '')
    {
        $query = $this->db
            ->select("
                c.id_cluster,
                c.cluster_name,
                c.homepass,
                c.status_rfs,
                mt.city_name,
                mt.regional_name,
                mt.province_name,
                mt.chief,
                mt.rpm,
                mt.sm,
                mt.spv,
                latest_claim.rfs_date
            ", false)
            ->from('tb_rfs_myrep_cluster c')
            ->join('tb_rfs_myrep_monthly_target mt', 'mt.id_target = c.id_target', 'inner')
            ->join('(
                SELECT cluster_id, MAX(claim_date) AS rfs_date
                FROM tb_rfs_myrep_claim
                WHERE status_claim = "APPROVED"
                GROUP BY cluster_id
            ) latest_claim', 'latest_claim.cluster_id = c.id_cluster', 'left')
            ->where('c.status_rfs', 'FULL RFS');

        if ($city !== '') {
            $query->where('UPPER(mt.city_name)', strtoupper($city));
        }

        if ($regional !== '') {
            $query->where('UPPER(mt.regional_name)', strtoupper($regional));
        }

        $rows = $query
            ->order_by('mt.city_name', 'ASC')
            ->order_by('c.cluster_name', 'ASC')
            ->get()
            ->result_array();

        return $this->enrichClusterRows($rows);
    }

    public function getClusterDetail($clusterId)
    {
        $row = $this->db
            ->select("
                c.id_cluster,
                c.cluster_name,
                c.homepass,
                c.status_rfs,
                mt.id_target,
                mt.year_num,
                mt.month_num,
                mt.city_name,
                mt.regional_name,
                mt.province_name,
                mt.chief,
                mt.rpm,
                mt.sm,
                mt.spv,
                latest_claim.rfs_date
            ", false)
            ->from('tb_rfs_myrep_cluster c')
            ->join('tb_rfs_myrep_monthly_target mt', 'mt.id_target = c.id_target', 'inner')
            ->join('(
                SELECT cluster_id, MAX(claim_date) AS rfs_date
                FROM tb_rfs_myrep_claim
                WHERE status_claim = "APPROVED"
                GROUP BY cluster_id
            ) latest_claim', 'latest_claim.cluster_id = c.id_cluster', 'left')
            ->where('c.id_cluster', (int) $clusterId)
            ->get()
            ->row_array();

        if (!$row) {
            return [];
        }

        $rows = $this->enrichClusterRows([$row]);
        return empty($rows) ? [] : $rows[0];
    }

    public function getClusterScopeTabs($clusterId)
    {
        $groups = $this->getDocumentGroups();
        $items = $this->getDocumentItems();
        $packagesByCluster = $this->getPackagesByClusterIds([(int) $clusterId]);
        $clusterPackages = isset($packagesByCluster[(int) $clusterId]) ? $packagesByCluster[(int) $clusterId] : [];
        $packageIds = [];

        foreach ($clusterPackages as $package) {
            if (!empty($package['id_doc_package'])) {
                $packageIds[] = (int) $package['id_doc_package'];
            }
        }

        $files = $this->getFilesByPackageIds($packageIds);
        $fileMap = [];
        foreach ($files as $file) {
            $fileMap[(int) $file['id_doc_package']][(int) $file['id_doc_item']] = $file;
        }

        $scopes = [
            'CLUSTER' => [],
            'SUBFEEDER' => [],
        ];

        foreach ($groups as $group) {
            $groupId = (int) $group['id_doc_group'];
            $package = isset($clusterPackages[$groupId]) ? $clusterPackages[$groupId] : [];
            $packageId = isset($package['id_doc_package']) ? (int) $package['id_doc_package'] : 0;
            $requiredDocs = isset($items[$groupId]) ? count($items[$groupId]) : 0;
            $uploadedDocs = 0;
            $approvedDocs = 0;
            $groupItems = [];

            if (isset($items[$groupId])) {
                foreach ($items[$groupId] as $item) {
                    $itemFile = ($packageId > 0 && isset($fileMap[$packageId][(int) $item['id_doc_item']]))
                        ? $fileMap[$packageId][(int) $item['id_doc_item']]
                        : [];

                    if ($this->isUploadedRow($itemFile)) {
                        $uploadedDocs++;
                    }

                    if (($itemFile['status_file'] ?? '') === 'APPROVED') {
                        $approvedDocs++;
                    }

                    $groupItems[] = [
                        'id_doc_file' => (int) ($itemFile['id_doc_file'] ?? 0),
                        'id_doc_package' => $packageId,
                        'id_doc_item' => (int) $item['id_doc_item'],
                        'doc_name' => (string) $item['doc_name'],
                        'status_file' => (string) ($itemFile['status_file'] ?? 'NOT UPLOADED'),
                        'file_name' => (string) ($itemFile['file_name'] ?? ''),
                        'file_path' => (string) ($itemFile['file_path'] ?? ''),
                        'is_document_not_required' => (int) ($itemFile['is_document_not_required'] ?? 0),
                        'remark' => (string) ($itemFile['remark'] ?? ''),
                        'uploaded_at' => $this->normalizeDateTime($itemFile['uploaded_at'] ?? null),
                        'reviewed_at' => $this->normalizeDateTime($itemFile['reviewed_at'] ?? null),
                        'approved_at' => $this->normalizeDateTime($itemFile['approved_at'] ?? null),
                        'history' => [],
                    ];
                }
            }

            $tanggalRfs = $this->normalizeDate($package['tanggal_rfs'] ?? null);
            $planAtp = $this->normalizeDate($package['plan_atp_date'] ?? null);
            $actualAtp = $this->normalizeDate($package['actual_atp_date'] ?? null);
            $planDoc = $this->normalizeDate($package['plan_submit_doc_date'] ?? null);
            $actualDoc = $this->normalizeDate($package['actual_submit_doc_date'] ?? null);

            if (!$planAtp && $tanggalRfs) {
                $planAtp = $this->addBusinessDays($tanggalRfs, 7);
            }

            if (!$planDoc) {
                if ($actualAtp) {
                    $planDoc = $this->addBusinessDays($actualAtp, 7);
                } elseif ($planAtp) {
                    $planDoc = $this->addBusinessDays($planAtp, 7);
                }
            }

            if (!$actualDoc && $requiredDocs > 0 && $uploadedDocs >= $requiredDocs) {
                $actualDoc = $this->extractLatestUploadedDate($groupItems);
            }

            $scopeKey = strtoupper((string) $group['scope_type']);
            $scopes[$scopeKey][] = [
                'id_doc_package' => $packageId,
                'group_label' => (string) $group['group_label'],
                'sow_type' => (string) $group['sow_type'],
                'required_docs' => $requiredDocs,
                'uploaded_docs' => $uploadedDocs,
                'approved_docs' => $approvedDocs,
                'tanggal_rfs' => $tanggalRfs,
                'plan_atp_date' => $planAtp,
                'actual_atp_date' => $actualAtp,
                'plan_submit_doc_date' => $planDoc,
                'actual_submit_doc_date' => $actualDoc,
                'aging_atp_days' => $this->calculateAgingDays($planAtp, $actualAtp),
                'aging_doc_days' => $this->calculateAgingDays($planDoc, $actualDoc),
                'status_package' => (string) ($package['status_package'] ?? $this->derivePackageStatus($uploadedDocs, $requiredDocs)),
                'remarks' => (string) ($package['remarks'] ?? ''),
                'items' => $groupItems,
            ];
        }

        $fileIds = [];
        foreach ($scopes as $scopeRows) {
            foreach ($scopeRows as $groupRow) {
                foreach ($groupRow['items'] as $itemRow) {
                    if (!empty($itemRow['id_doc_file'])) {
                        $fileIds[] = (int) $itemRow['id_doc_file'];
                    }
                }
            }
        }

        $historyByFileId = $this->getFileLogsByFileIds(array_values(array_unique($fileIds)));

        foreach ($scopes as &$scopeRows) {
            foreach ($scopeRows as &$groupRow) {
                foreach ($groupRow['items'] as &$itemRow) {
                    $itemRow['history'] = !empty($itemRow['id_doc_file']) && isset($historyByFileId[(int) $itemRow['id_doc_file']])
                        ? $historyByFileId[(int) $itemRow['id_doc_file']]
                        : [];
                }
                unset($itemRow);
            }
            unset($groupRow);
        }
        unset($scopeRows);

        return $scopes;
    }

    public function updatePackageTimeline($packageId, $data)
    {
        $package = $this->db->get_where('tb_rfs_myrep_doc_package', [
            'id_doc_package' => (int) $packageId,
        ])->row_array();

        if (!$package) {
            return false;
        }

        $tanggalRfs = !empty($data['tanggal_rfs']) ? $data['tanggal_rfs'] : $this->normalizeDate($package['tanggal_rfs'] ?? null);
        $actualAtp = !empty($data['actual_atp_date']) ? $data['actual_atp_date'] : null;
        $planAtp = $tanggalRfs ? $this->addBusinessDays($tanggalRfs, 7) : null;
        $planDoc = null;
        if ($actualAtp) {
            $planDoc = $this->addBusinessDays($actualAtp, 7);
        } elseif ($planAtp) {
            $planDoc = $this->addBusinessDays($planAtp, 7);
        }

        return $this->db
            ->where('id_doc_package', (int) $packageId)
            ->update('tb_rfs_myrep_doc_package', [
                'tanggal_rfs' => $tanggalRfs,
                'plan_atp_date' => $planAtp,
                'actual_atp_date' => $actualAtp,
                'plan_submit_doc_date' => $planDoc,
                'remarks' => $data['remarks'],
                'updated_by' => (int) $data['updated_by'],
            ]);
    }

    public function saveFileUpload($data)
    {
        $existing = $this->db->get_where('tb_rfs_myrep_doc_file', [
            'id_doc_package' => (int) $data['id_doc_package'],
            'id_doc_item' => (int) $data['id_doc_item'],
        ])->row_array();

        $payload = [
            'file_name' => $data['file_name'],
            'file_path' => $data['file_path'],
            'is_document_not_required' => !empty($data['is_document_not_required']) ? 1 : 0,
            'status_file' => $data['status_file'],
            'remark' => $data['remark'],
            'uploaded_by' => (int) $data['uploaded_by'],
            'uploaded_at' => date('Y-m-d H:i:s'),
        ];

        if ($existing) {
            $this->deletePhysicalFile($existing['file_path'] ?? '');
            $this->db
                ->where('id_doc_file', (int) $existing['id_doc_file'])
                ->update('tb_rfs_myrep_doc_file', $payload);
            $fileId = (int) $existing['id_doc_file'];
            $this->createFileLog([
                'id_doc_file' => $fileId,
                'id_doc_package' => (int) $data['id_doc_package'],
                'id_doc_item' => (int) $data['id_doc_item'],
                'action_type' => 'REUPLOADED',
                'status_after' => $data['status_file'],
                'file_name' => $data['file_name'] !== '' ? $data['file_name'] : '[Tanpa Dokumen]',
                'remark' => $data['remark'],
                'action_by' => (int) $data['uploaded_by'],
            ]);
        } else {
            $payload['id_doc_package'] = (int) $data['id_doc_package'];
            $payload['id_doc_item'] = (int) $data['id_doc_item'];
            $this->db->insert('tb_rfs_myrep_doc_file', $payload);
            $fileId = (int) $this->db->insert_id();
            $this->createFileLog([
                'id_doc_file' => $fileId,
                'id_doc_package' => (int) $data['id_doc_package'],
                'id_doc_item' => (int) $data['id_doc_item'],
                'action_type' => 'UPLOADED',
                'status_after' => $data['status_file'],
                'file_name' => $data['file_name'] !== '' ? $data['file_name'] : '[Tanpa Dokumen]',
                'remark' => $data['remark'],
                'action_by' => (int) $data['uploaded_by'],
            ]);
        }

        $this->refreshPackageStatus((int) $data['id_doc_package']);
        return $fileId;
    }

    public function updateFileStatus($fileId, $data)
    {
        $file = $this->db->get_where('tb_rfs_myrep_doc_file', [
            'id_doc_file' => (int) $fileId,
        ])->row_array();

        if (!$file) {
            return false;
        }

        $payload = [
            'status_file' => $data['status_file'],
            'remark' => $data['remark'],
            'approved_by' => (int) $data['approved_by'],
            'reviewed_at' => date('Y-m-d H:i:s'),
            'approved_at' => $data['status_file'] === 'APPROVED' ? date('Y-m-d H:i:s') : null,
        ];

        $result = $this->db
            ->where('id_doc_file', (int) $fileId)
            ->update('tb_rfs_myrep_doc_file', $payload);

        $this->createFileLog([
            'id_doc_file' => (int) $fileId,
            'id_doc_package' => (int) $file['id_doc_package'],
            'id_doc_item' => (int) $file['id_doc_item'],
            'action_type' => $data['status_file'],
            'status_after' => $data['status_file'],
            'file_name' => (string) ($file['file_name'] ?? ''),
            'remark' => $data['remark'],
            'action_by' => (int) $data['approved_by'],
        ]);

        $this->refreshPackageStatus((int) $file['id_doc_package']);
        return $result;
    }

    public function getFileById($fileId)
    {
        return $this->db
            ->get_where('tb_rfs_myrep_doc_file', [
                'id_doc_file' => (int) $fileId,
            ])
            ->row_array();
    }

    public function getFileLogsByFileIds($fileIds)
    {
        if (empty($fileIds)) {
            return [];
        }

        $rows = $this->db
            ->select('l.*, u.nama_user')
            ->from('tb_rfs_myrep_doc_file_log l')
            ->join('tb_master_user u', 'u.id_user = l.action_by', 'left')
            ->where_in('l.id_doc_file', $fileIds)
            ->order_by('l.action_at', 'DESC')
            ->order_by('l.id_doc_file_log', 'DESC')
            ->get()
            ->result_array();

        $logs = [];
        foreach ($rows as $row) {
            $logs[(int) $row['id_doc_file']][] = $row;
        }

        return $logs;
    }

    private function enrichClusterRows($rows)
    {
        if (empty($rows)) {
            return [];
        }

        $clusterIds = array_map(function ($row) {
            return (int) $row['id_cluster'];
        }, $rows);

        $groups = $this->getDocumentGroups();
        $packagesByCluster = $this->getPackagesByClusterIds($clusterIds);
        $packageIds = [];

        foreach ($packagesByCluster as $clusterPackages) {
            foreach ($clusterPackages as $package) {
                if (!empty($package['id_doc_package'])) {
                    $packageIds[] = (int) $package['id_doc_package'];
                }
            }
        }

        $fileSummary = $this->getFileSummaryByPackageIds($packageIds);
        $fileStatusSummary = $this->getFileStatusSummaryByPackageIds($packageIds);
        $result = [];

        foreach ($rows as $row) {
            $clusterId = (int) $row['id_cluster'];
            $clusterPackages = isset($packagesByCluster[$clusterId]) ? $packagesByCluster[$clusterId] : [];
            $docSummary = [
                'CW ATP' => ['uploaded' => 0, 'required' => 0, 'approved' => 0, 'on_review' => 0, 'rejected' => 0, 'ny' => 0],
                'FULL OPM' => ['uploaded' => 0, 'required' => 0, 'approved' => 0, 'on_review' => 0, 'rejected' => 0, 'ny' => 0],
                'RFS' => ['uploaded' => 0, 'required' => 0, 'approved' => 0, 'on_review' => 0, 'rejected' => 0, 'ny' => 0],
            ];
            $planAtpDates = [];
            $actualAtpDates = [];
            $planDocDates = [];
            $actualDocDates = [];

            foreach ($groups as $group) {
                $groupId = (int) $group['id_doc_group'];
                $package = isset($clusterPackages[$groupId]) ? $clusterPackages[$groupId] : [];
                $packageId = isset($package['id_doc_package']) ? (int) $package['id_doc_package'] : 0;
                $required = (int) $group['required_docs'];
                $uploaded = ($packageId > 0 && isset($fileSummary[$packageId])) ? (int) $fileSummary[$packageId]['uploaded_docs'] : 0;
                $sowType = (string) $group['sow_type'];
                $statusSummary = ($packageId > 0 && isset($fileStatusSummary[$packageId])) ? $fileStatusSummary[$packageId] : [
                    'approved' => 0,
                    'on_review' => 0,
                    'rejected' => 0,
                    'existing' => 0,
                ];

                $docSummary[$sowType]['required'] += $required;
                $docSummary[$sowType]['uploaded'] += min($uploaded, $required);
                $docSummary[$sowType]['approved'] += (int) $statusSummary['approved'];
                $docSummary[$sowType]['on_review'] += (int) $statusSummary['on_review'];
                $docSummary[$sowType]['rejected'] += (int) $statusSummary['rejected'];
                $docSummary[$sowType]['ny'] += max(0, $required - (int) $statusSummary['existing']);

                $planAtp = $this->normalizeDate($package['plan_atp_date'] ?? null);
                $actualAtp = $this->normalizeDate($package['actual_atp_date'] ?? null);
                $planDoc = $this->normalizeDate($package['plan_submit_doc_date'] ?? null);
                $actualDoc = $this->normalizeDate($package['actual_submit_doc_date'] ?? null);

                if ($planAtp) {
                    $planAtpDates[] = $planAtp;
                }
                if ($actualAtp) {
                    $actualAtpDates[] = $actualAtp;
                }
                if ($planDoc) {
                    $planDocDates[] = $planDoc;
                }
                if ($actualDoc) {
                    $actualDocDates[] = $actualDoc;
                }
            }

            $tanggalRfs = $this->normalizeDate($row['rfs_date'] ?? null);
            $summaryPlanAtp = !empty($planAtpDates) ? max($planAtpDates) : ($tanggalRfs ? $this->addBusinessDays($tanggalRfs, 7) : null);
            $summaryActualAtp = !empty($actualAtpDates) ? max($actualAtpDates) : null;
            if (!empty($planDocDates)) {
                $summaryPlanDoc = max($planDocDates);
            } elseif ($summaryActualAtp) {
                $summaryPlanDoc = $this->addBusinessDays($summaryActualAtp, 7);
            } elseif ($summaryPlanAtp) {
                $summaryPlanDoc = $this->addBusinessDays($summaryPlanAtp, 7);
            } else {
                $summaryPlanDoc = null;
            }
            $summaryActualDoc = !empty($actualDocDates) ? max($actualDocDates) : null;

            $row['tanggal_rfs'] = $tanggalRfs;
            $row['plan_atp_date'] = $summaryPlanAtp;
            $row['actual_atp_date'] = $summaryActualAtp;
            $row['plan_submit_doc_date'] = $summaryPlanDoc;
            $row['actual_submit_doc_date'] = $summaryActualDoc;
            $row['aging_atp_days'] = $this->calculateAgingDays($summaryPlanAtp, $summaryActualAtp);
            $row['aging_doc_days'] = $this->calculateAgingDays($summaryPlanDoc, $summaryActualDoc);
            $row['doc_cw_atp_uploaded'] = $docSummary['CW ATP']['uploaded'];
            $row['doc_cw_atp_required'] = $docSummary['CW ATP']['required'];
            $row['doc_cw_atp_approved'] = $docSummary['CW ATP']['approved'];
            $row['doc_cw_atp_on_review'] = $docSummary['CW ATP']['on_review'];
            $row['doc_cw_atp_rejected'] = $docSummary['CW ATP']['rejected'];
            $row['doc_cw_atp_ny'] = $docSummary['CW ATP']['ny'];
            $row['doc_full_opm_uploaded'] = $docSummary['FULL OPM']['uploaded'];
            $row['doc_full_opm_required'] = $docSummary['FULL OPM']['required'];
            $row['doc_full_opm_approved'] = $docSummary['FULL OPM']['approved'];
            $row['doc_full_opm_on_review'] = $docSummary['FULL OPM']['on_review'];
            $row['doc_full_opm_rejected'] = $docSummary['FULL OPM']['rejected'];
            $row['doc_full_opm_ny'] = $docSummary['FULL OPM']['ny'];
            $row['doc_rfs_uploaded'] = $docSummary['RFS']['uploaded'];
            $row['doc_rfs_required'] = $docSummary['RFS']['required'];
            $row['doc_rfs_approved'] = $docSummary['RFS']['approved'];
            $row['doc_rfs_on_review'] = $docSummary['RFS']['on_review'];
            $row['doc_rfs_rejected'] = $docSummary['RFS']['rejected'];
            $row['doc_rfs_ny'] = $docSummary['RFS']['ny'];

            $result[] = $row;
        }

        return $result;
    }

    private function getDocumentGroups()
    {
        return $this->db
            ->select('g.id_doc_group, g.scope_type, g.sow_type, g.group_label, g.sort_no, COUNT(i.id_doc_item) AS required_docs', false)
            ->from('md_rfs_myrep_doc_group g')
            ->join('md_rfs_myrep_doc_item i', 'i.id_doc_group = g.id_doc_group AND i.is_active = 1 AND i.is_required = 1', 'left')
            ->where('g.is_active', 1)
            ->group_by(['g.id_doc_group', 'g.scope_type', 'g.sow_type', 'g.group_label', 'g.sort_no'])
            ->order_by('g.sort_no', 'ASC')
            ->order_by('g.id_doc_group', 'ASC')
            ->get()
            ->result_array();
    }

    private function getDocumentItems()
    {
        $rows = $this->db
            ->select('id_doc_item, id_doc_group, doc_name, sort_no')
            ->from('md_rfs_myrep_doc_item')
            ->where('is_active', 1)
            ->where('is_required', 1)
            ->order_by('sort_no', 'ASC')
            ->order_by('id_doc_item', 'ASC')
            ->get()
            ->result_array();

        $items = [];
        foreach ($rows as $row) {
            $items[(int) $row['id_doc_group']][] = $row;
        }

        return $items;
    }

    private function getPackagesByClusterIds($clusterIds)
    {
        if (empty($clusterIds)) {
            return [];
        }

        $rows = $this->db
            ->select('p.*, g.scope_type, g.sow_type, g.group_label')
            ->from('tb_rfs_myrep_doc_package p')
            ->join('md_rfs_myrep_doc_group g', 'g.id_doc_group = p.id_doc_group', 'inner')
            ->where_in('p.cluster_id', $clusterIds)
            ->get()
            ->result_array();

        $packages = [];
        foreach ($rows as $row) {
            $packages[(int) $row['cluster_id']][(int) $row['id_doc_group']] = $row;
        }

        return $packages;
    }

    private function getFileSummaryByPackageIds($packageIds)
    {
        if (empty($packageIds)) {
            return [];
        }

        $rows = $this->db
            ->select("
                id_doc_package,
                SUM(CASE WHEN ((file_path IS NOT NULL AND file_path <> '') OR is_document_not_required = 1) AND status_file IN ('UPLOADED','APPROVED') THEN 1 ELSE 0 END) AS uploaded_docs,
                SUM(CASE WHEN status_file = 'APPROVED' THEN 1 ELSE 0 END) AS approved_docs
            ", false)
            ->from('tb_rfs_myrep_doc_file')
            ->where_in('id_doc_package', $packageIds)
            ->group_by('id_doc_package')
            ->get()
            ->result_array();

        $summary = [];
        foreach ($rows as $row) {
            $summary[(int) $row['id_doc_package']] = [
                'uploaded_docs' => (int) $row['uploaded_docs'],
                'approved_docs' => (int) $row['approved_docs'],
            ];
        }

        return $summary;
    }

    private function getFileStatusSummaryByPackageIds($packageIds)
    {
        if (empty($packageIds)) {
            return [];
        }

        $rows = $this->db
            ->select("
                id_doc_package,
                SUM(CASE WHEN status_file = 'APPROVED' THEN 1 ELSE 0 END) AS approved,
                SUM(CASE WHEN status_file = 'UPLOADED' THEN 1 ELSE 0 END) AS on_review,
                SUM(CASE WHEN status_file = 'REJECTED' THEN 1 ELSE 0 END) AS rejected,
                COUNT(*) AS existing
            ", false)
            ->from('tb_rfs_myrep_doc_file')
            ->where_in('id_doc_package', $packageIds)
            ->group_by('id_doc_package')
            ->get()
            ->result_array();

        $summary = [];
        foreach ($rows as $row) {
            $summary[(int) $row['id_doc_package']] = [
                'approved' => (int) $row['approved'],
                'on_review' => (int) $row['on_review'],
                'rejected' => (int) $row['rejected'],
                'existing' => (int) $row['existing'],
            ];
        }

        return $summary;
    }

    private function getFilesByPackageIds($packageIds)
    {
        if (empty($packageIds)) {
            return [];
        }

        return $this->db
            ->select('id_doc_file, id_doc_package, id_doc_item, file_name, file_path, is_document_not_required, status_file, remark, uploaded_at, reviewed_at, approved_at')
            ->from('tb_rfs_myrep_doc_file')
            ->where_in('id_doc_package', $packageIds)
            ->get()
            ->result_array();
    }

    private function createFileLog($data)
    {
        $this->db->insert('tb_rfs_myrep_doc_file_log', [
            'id_doc_file' => (int) $data['id_doc_file'],
            'id_doc_package' => (int) $data['id_doc_package'],
            'id_doc_item' => (int) $data['id_doc_item'],
            'action_type' => $data['action_type'],
            'status_after' => $data['status_after'],
            'file_name' => $data['file_name'],
            'remark' => $data['remark'],
            'action_by' => (int) $data['action_by'],
            'action_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function deletePhysicalFile($relativePath)
    {
        if (empty($relativePath)) {
            return;
        }

        $fullPath = FCPATH . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relativePath);
        if (is_file($fullPath)) {
            @unlink($fullPath);
        }
    }

    private function refreshPackageStatus($packageId)
    {
        $package = $this->db->get_where('tb_rfs_myrep_doc_package', [
            'id_doc_package' => (int) $packageId,
        ])->row_array();

        if (!$package) {
            return;
        }

        $required = (int) $this->db
            ->from('md_rfs_myrep_doc_item')
            ->where('id_doc_group', (int) $package['id_doc_group'])
            ->where('is_active', 1)
            ->where('is_required', 1)
            ->count_all_results();

        $uploaded = (int) $this->db->query(
            "SELECT COUNT(*) AS total
             FROM tb_rfs_myrep_doc_file
             WHERE id_doc_package = ?
             AND ((file_path IS NOT NULL AND file_path <> '') OR is_document_not_required = 1)
             AND status_file IN ('UPLOADED','APPROVED')",
            [(int) $packageId]
        )->row()->total;

        $latestUploaded = $this->db->query(
            "SELECT MAX(uploaded_at) AS latest_uploaded
             FROM tb_rfs_myrep_doc_file
             WHERE id_doc_package = ?
             AND ((file_path IS NOT NULL AND file_path <> '') OR is_document_not_required = 1)
             AND status_file IN ('UPLOADED','APPROVED')",
            [(int) $packageId]
        )->row_array();

        $statusPackage = $this->derivePackageStatus($uploaded, $required);
        $actualSubmit = null;
        if ($required > 0 && $uploaded >= $required && !empty($latestUploaded['latest_uploaded'])) {
            $actualSubmit = substr((string) $latestUploaded['latest_uploaded'], 0, 10);
        }

        $this->db
            ->where('id_doc_package', (int) $packageId)
            ->update('tb_rfs_myrep_doc_package', [
                'status_package' => $statusPackage,
                'actual_submit_doc_date' => $actualSubmit,
                'updated_by' => (int) $this->session->userdata('id_user'),
            ]);
    }

    private function normalizeDate($date)
    {
        if (empty($date) || $date === '0000-00-00') {
            return null;
        }

        return $date;
    }

    private function normalizeDateTime($dateTime)
    {
        if (empty($dateTime) || $dateTime === '0000-00-00 00:00:00') {
            return null;
        }

        return $dateTime;
    }

    private function addBusinessDays($date, $days)
    {
        if (!$date) {
            return null;
        }

        $dateTime = new DateTime($date);
        $dateTime->modify('+' . (int) $days . ' day');
        return $dateTime->format('Y-m-d');
    }

    private function calculateAgingDays($planDate, $actualDate = null)
    {
        if (!$planDate) {
            return null;
        }

        $endDate = $actualDate ?: date('Y-m-d');
        $start = new DateTime($planDate);
        $end = new DateTime($endDate);
        $invert = $start > $end ? -1 : 1;
        $diff = $start->diff($end);

        return $diff->days * $invert;
    }

    private function derivePackageStatus($uploadedDocs, $requiredDocs)
    {
        if ($requiredDocs <= 0 || $uploadedDocs <= 0) {
            return 'NOT STARTED';
        }

        if ($uploadedDocs >= $requiredDocs) {
            return 'DONE';
        }

        return 'ON PROGRESS';
    }

    private function extractLatestUploadedDate($items)
    {
        $dates = [];
        foreach ($items as $item) {
            if (!empty($item['uploaded_at'])) {
                $dates[] = substr((string) $item['uploaded_at'], 0, 10);
            }
        }

        return empty($dates) ? null : max($dates);
    }

    private function isUploadedRow($row)
    {
        return !empty($row)
            && (
                !empty($row['file_path'])
                || !empty($row['is_document_not_required'])
            )
            && in_array((string) ($row['status_file'] ?? ''), ['UPLOADED', 'APPROVED'], true);
    }
}
