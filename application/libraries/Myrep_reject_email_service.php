<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once APPPATH . 'helpers/myrep_pic_helper.php';

class Myrep_reject_email_service
{
    private $ci;
    private $env;
    private $tablesEnsured = false;

    public function __construct()
    {
        $this->ci =& get_instance();
        $this->env = $this->loadEnv();
    }

    public function enqueueReject($moduleName, $fileId, array $options = [])
    {
        if (!$this->isEnabled()) {
            return false;
        }

        $fileId = (int) $fileId;
        if ($fileId <= 0 || !$this->ensureTables()) {
            return false;
        }

        $sourceType = strtoupper(trim((string) ($options['source_type'] ?? 'FLOW')));
        $context = $this->resolveRejectContext((string) $moduleName, $fileId, $sourceType);
        if (empty($context['cluster_ref_id'])) {
            return false;
        }

        $delayMinutes = max(1, (int) $this->envValue('MYREP_REJECT_EMAIL_DELAY_MINUTES', '5'));
        $now = date('Y-m-d H:i:s');
        $scheduledAt = date('Y-m-d H:i:s', time() + ($delayMinutes * 60));

        $queue = $this->ci->db
            ->from('tb_myrep_reject_email_queue')
            ->where('module_name', (string) $moduleName)
            ->where('source_type', $sourceType)
            ->where('cluster_ref_id', (int) $context['cluster_ref_id'])
            ->where('status_queue', 'PENDING')
            ->order_by('id_queue', 'DESC')
            ->limit(1)
            ->get()
            ->row_array();

        if (!empty($queue['id_queue'])) {
            $queueId = (int) $queue['id_queue'];
            $this->ci->db
                ->where('id_queue', $queueId)
                ->update('tb_myrep_reject_email_queue', [
                    'scheduled_at' => $scheduledAt,
                    'updated_at' => $now,
                ]);
        } else {
            $this->ci->db->insert('tb_myrep_reject_email_queue', [
                'module_name' => (string) $moduleName,
                'module_label' => $this->moduleLabel((string) $moduleName),
                'source_type' => $sourceType,
                'cluster_ref_id' => (int) $context['cluster_ref_id'],
                'cluster_name' => (string) ($context['cluster_name'] ?? ''),
                'city_name' => (string) ($context['city_name'] ?? ''),
                'province_name' => (string) ($context['province_name'] ?? ''),
                'regional_name' => (string) ($context['regional_name'] ?? ''),
                'detail_url' => (string) ($context['detail_url'] ?? ''),
                'status_queue' => 'PENDING',
                'scheduled_at' => $scheduledAt,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $queueId = (int) $this->ci->db->insert_id();
        }

        if ($queueId <= 0) {
            return false;
        }

        $item = [
            'id_queue' => $queueId,
            'source_file_id' => $fileId,
            'doc_name' => (string) ($context['doc_name'] ?? 'Dokumen'),
            'file_name' => (string) ($context['file_name'] ?? ''),
            'remark' => (string) ($context['remark'] ?? ''),
            'submitter_user_id' => (int) ($context['submitter_user_id'] ?? 0),
            'rejecter_user_id' => (int) ($context['rejecter_user_id'] ?? 0),
            'rejected_at' => (string) ($context['rejected_at'] ?? $now),
            'created_at' => $now,
            'updated_at' => $now,
        ];

        $existingItem = $this->ci->db
            ->select('id_item')
            ->from('tb_myrep_reject_email_queue_item')
            ->where('id_queue', $queueId)
            ->where('source_file_id', $fileId)
            ->limit(1)
            ->get()
            ->row_array();

        if (!empty($existingItem['id_item'])) {
            return (bool) $this->ci->db
                ->where('id_item', (int) $existingItem['id_item'])
                ->update('tb_myrep_reject_email_queue_item', $item);
        }

        return (bool) $this->ci->db->insert('tb_myrep_reject_email_queue_item', $item);
    }

    public function processDueQueues($limit = null)
    {
        if (!$this->isEnabled() || !$this->ensureTables()) {
            return ['processed' => 0, 'sent' => 0, 'failed' => 0, 'skipped' => 0];
        }

        $this->releaseStaleProcessingQueues();

        $limit = $limit === null ? (int) $this->envValue('MYREP_REJECT_EMAIL_PROCESS_LIMIT', '5') : (int) $limit;
        $limit = max(1, min(20, $limit));

        $queues = (array) $this->ci->db
            ->from('tb_myrep_reject_email_queue')
            ->where('status_queue', 'PENDING')
            ->where('scheduled_at <=', date('Y-m-d H:i:s'))
            ->order_by('scheduled_at', 'ASC')
            ->limit($limit)
            ->get()
            ->result_array();

        $result = ['processed' => 0, 'sent' => 0, 'failed' => 0, 'skipped' => 0];
        foreach ($queues as $queue) {
            $result['processed']++;
            $queueId = (int) ($queue['id_queue'] ?? 0);
            if ($queueId <= 0 || !$this->lockQueue($queueId)) {
                $result['skipped']++;
                continue;
            }

            $sendResult = $this->sendQueue($queueId);
            if (!empty($sendResult['sent'])) {
                $result['sent']++;
            } else {
                $result['failed']++;
            }

            $sleepMs = max(0, (int) $this->envValue('MYREP_REJECT_EMAIL_RATE_SLEEP_MS', '500'));
            if ($sleepMs > 0) {
                usleep($sleepMs * 1000);
            }
        }

        return $result;
    }

    private function sendQueue($queueId)
    {
        $queue = $this->ci->db
            ->from('tb_myrep_reject_email_queue')
            ->where('id_queue', (int) $queueId)
            ->limit(1)
            ->get()
            ->row_array();

        $items = (array) $this->ci->db
            ->from('tb_myrep_reject_email_queue_item')
            ->where('id_queue', (int) $queueId)
            ->order_by('rejected_at', 'ASC')
            ->order_by('id_item', 'ASC')
            ->get()
            ->result_array();

        if (empty($queue) || empty($items)) {
            $this->markQueueFailed($queueId, 'Queue tidak memiliki item dokumen.');
            return ['sent' => false];
        }

        $recipients = $this->resolveRecipients($queue, $items);
        if (empty($recipients['to'])) {
            $this->markQueueFailed($queueId, 'Email submitter/admin area tidak ditemukan.');
            return ['sent' => false];
        }

        $subject = $this->buildSubject($queue, count($items));
        $message = $this->buildMessage($queue, $items);
        $sendResult = $this->sendEmail($recipients['to'], $recipients['cc'], $subject, $message);

        if (!empty($sendResult['sent'])) {
            $this->ci->db
                ->where('id_queue', (int) $queueId)
                ->update('tb_myrep_reject_email_queue', [
                    'status_queue' => 'SENT',
                    'sent_at' => date('Y-m-d H:i:s'),
                    'last_error' => null,
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            return ['sent' => true];
        }

        $this->rescheduleFailedQueue($queueId, (string) ($sendResult['error'] ?? 'SMTP gagal mengirim email.'));
        return ['sent' => false];
    }

    private function resolveRejectContext($moduleName, $fileId, $sourceType)
    {
        if ($sourceType === 'CHECKLIST_CLUSTER') {
            return $this->resolveChecklistClusterContext($moduleName, $fileId);
        }

        if ($sourceType === 'CHECKLIST_MAINFEEDER') {
            return $this->resolveChecklistMainfeederContext($moduleName, $fileId);
        }

        return $this->resolveFlowContext($moduleName, $fileId);
    }

    private function resolveFlowContext($moduleName, $fileId)
    {
        if (!$this->ci->db->table_exists('tb_myrep_flow_doc_file')) {
            return [];
        }

        $row = $this->ci->db
            ->select('
                f.id_doc_file,
                f.file_name,
                f.remark,
                f.uploaded_by,
                f.approved_by,
                f.reviewed_at,
                p.id_myrep_cluster,
                p.flow_type,
                c.cluster_name,
                c.city_name,
                c.province_name,
                c.regional_name,
                i.doc_name
            ')
            ->from('tb_myrep_flow_doc_file f')
            ->join('tb_myrep_flow_doc_package p', 'p.id_doc_package = f.id_doc_package', 'left')
            ->join('tb_myrep_cluster c', 'c.id_myrep_cluster = p.id_myrep_cluster', 'left')
            ->join('md_myrep_flow_doc_item i', 'i.id_doc_item = f.id_doc_item', 'left')
            ->where('f.id_doc_file', (int) $fileId)
            ->limit(1)
            ->get()
            ->row_array();

        if (empty($row)) {
            return [];
        }

        $clusterId = (int) ($row['id_myrep_cluster'] ?? 0);
        return [
            'cluster_ref_id' => $clusterId,
            'cluster_name' => (string) ($row['cluster_name'] ?? ''),
            'city_name' => (string) ($row['city_name'] ?? ''),
            'province_name' => (string) ($row['province_name'] ?? ''),
            'regional_name' => (string) ($row['regional_name'] ?? ''),
            'doc_name' => (string) ($row['doc_name'] ?? $this->moduleLabel($moduleName)),
            'file_name' => (string) ($row['file_name'] ?? ''),
            'remark' => (string) ($row['remark'] ?? ''),
            'submitter_user_id' => (int) ($row['uploaded_by'] ?? 0),
            'rejecter_user_id' => (int) ($row['approved_by'] ?? 0),
            'rejected_at' => (string) ($row['reviewed_at'] ?? date('Y-m-d H:i:s')),
            'detail_url' => $this->buildDetailUrl($moduleName, $clusterId),
        ];
    }

    private function resolveChecklistClusterContext($moduleName, $fileId)
    {
        $row = $this->ci->db
            ->select('
                f.id_doc_file,
                f.file_name,
                f.remark,
                f.uploaded_by,
                f.approved_by,
                f.reviewed_at,
                p.cluster_id,
                c.cluster_name,
                mt.city_name,
                mt.province_name,
                mt.regional_name,
                i.doc_name
            ')
            ->from('tb_rfs_myrep_doc_file f')
            ->join('tb_rfs_myrep_doc_package p', 'p.id_doc_package = f.id_doc_package', 'left')
            ->join('tb_rfs_myrep_cluster c', 'c.id_cluster = p.cluster_id', 'left')
            ->join('tb_rfs_myrep_monthly_target mt', 'mt.id_target = c.id_target', 'left')
            ->join('md_rfs_myrep_doc_item i', 'i.id_doc_item = f.id_doc_item', 'left')
            ->where('f.id_doc_file', (int) $fileId)
            ->limit(1)
            ->get()
            ->row_array();

        if (empty($row)) {
            return [];
        }

        $clusterId = (int) ($row['cluster_id'] ?? 0);
        return [
            'cluster_ref_id' => $clusterId,
            'cluster_name' => (string) ($row['cluster_name'] ?? ''),
            'city_name' => (string) ($row['city_name'] ?? ''),
            'province_name' => (string) ($row['province_name'] ?? ''),
            'regional_name' => (string) ($row['regional_name'] ?? ''),
            'doc_name' => (string) ($row['doc_name'] ?? 'Checklist Document'),
            'file_name' => (string) ($row['file_name'] ?? ''),
            'remark' => (string) ($row['remark'] ?? ''),
            'submitter_user_id' => (int) ($row['uploaded_by'] ?? 0),
            'rejecter_user_id' => (int) ($row['approved_by'] ?? 0),
            'rejected_at' => (string) ($row['reviewed_at'] ?? date('Y-m-d H:i:s')),
            'detail_url' => base_url('Checklist_Dokument_MyRep/detail/' . $clusterId),
        ];
    }

    private function resolveChecklistMainfeederContext($moduleName, $fileId)
    {
        $row = $this->ci->db
            ->select('
                f.id_doc_file_mainfeeder,
                f.file_name,
                f.remark,
                f.uploaded_by,
                f.approved_by,
                f.reviewed_at,
                p.id_mainfeeder,
                mf.mainfeeder_name,
                mt.city_name,
                mt.province_name,
                mt.regional_name,
                i.doc_name
            ')
            ->from('tb_rfs_myrep_mainfeeder_doc_file f')
            ->join('tb_rfs_myrep_mainfeeder_doc_package p', 'p.id_doc_package_mainfeeder = f.id_doc_package_mainfeeder', 'left')
            ->join('tb_rfs_myrep_mainfeeder mf', 'mf.id_mainfeeder = p.id_mainfeeder', 'left')
            ->join('tb_rfs_myrep_monthly_target mt', 'mt.id_target = mf.id_target', 'left')
            ->join('md_rfs_myrep_mainfeeder_doc_item i', 'i.id_doc_item_mainfeeder = f.id_doc_item_mainfeeder', 'left')
            ->where('f.id_doc_file_mainfeeder', (int) $fileId)
            ->limit(1)
            ->get()
            ->row_array();

        if (empty($row)) {
            return [];
        }

        $mainfeederId = (int) ($row['id_mainfeeder'] ?? 0);
        return [
            'cluster_ref_id' => $mainfeederId,
            'cluster_name' => (string) ($row['mainfeeder_name'] ?? ''),
            'city_name' => (string) ($row['city_name'] ?? ''),
            'province_name' => (string) ($row['province_name'] ?? ''),
            'regional_name' => (string) ($row['regional_name'] ?? ''),
            'doc_name' => (string) ($row['doc_name'] ?? 'Checklist Mainfeeder'),
            'file_name' => (string) ($row['file_name'] ?? ''),
            'remark' => (string) ($row['remark'] ?? ''),
            'submitter_user_id' => (int) ($row['uploaded_by'] ?? 0),
            'rejecter_user_id' => (int) ($row['approved_by'] ?? 0),
            'rejected_at' => (string) ($row['reviewed_at'] ?? date('Y-m-d H:i:s')),
            'detail_url' => base_url('Checklist_Dokument_MyRep/detailMainfeeder/' . $mainfeederId),
        ];
    }

    private function resolveRecipients(array $queue, array $items)
    {
        $submitterIds = [];
        $rejecterIds = [];
        foreach ($items as $item) {
            $submitterId = (int) ($item['submitter_user_id'] ?? 0);
            if ($submitterId > 0) {
                $submitterIds[$submitterId] = true;
            }

            $rejecterId = (int) ($item['rejecter_user_id'] ?? 0);
            if ($rejecterId > 0) {
                $rejecterIds[$rejecterId] = true;
            }
        }

        $submitters = $this->getUsersByIds(array_keys($submitterIds));
        $rejecters = $this->getUsersByIds(array_keys($rejecterIds));
        $mappingUsers = $this->getMappedCityUsers($queue);

        $to = $this->extractEmails($submitters);
        if (empty($to)) {
            $to = $this->extractEmails($mappingUsers['admin_area'] ?? []);
        }

        $cc = array_merge(
            $this->extractEmails($mappingUsers['rpm_area'] ?? []),
            $this->extractEmails($mappingUsers['sm_area'] ?? []),
            $this->extractEmails($rejecters),
            $this->parseEmailList($this->envValue('MYREP_REJECT_FIXED_CC', ''))
        );

        $to = $this->uniqueEmails($to);
        $cc = $this->uniqueEmails($cc, $to);

        return ['to' => $to, 'cc' => $cc];
    }

    private function getMappedCityUsers(array $queue)
    {
        if (!$this->ci->db->table_exists('tb_myrep_pic_mapping_city')) {
            return [];
        }

        $city = strtoupper(trim((string) ($queue['city_name'] ?? '')));
        if ($city === '') {
            return [];
        }

        $this->ci->db->from('tb_myrep_pic_mapping_city')->where('UPPER(city_name)', $city);
        if (trim((string) ($queue['province_name'] ?? '')) !== '') {
            $this->ci->db->where('UPPER(province_name)', strtoupper(trim((string) $queue['province_name'])));
        }
        if (trim((string) ($queue['regional_name'] ?? '')) !== '') {
            $this->ci->db->where('UPPER(regional_name)', strtoupper(trim((string) $queue['regional_name'])));
        }
        if ($this->ci->db->field_exists('is_active', 'tb_myrep_pic_mapping_city')) {
            $this->ci->db->where('is_active', 1);
        }
        $mapping = $this->ci->db->limit(1)->get()->row_array();

        if (empty($mapping)) {
            $mapping = $this->ci->db
                ->from('tb_myrep_pic_mapping_city')
                ->where('UPPER(city_name)', $city)
                ->limit(1)
                ->get()
                ->row_array();
        }

        if (empty($mapping)) {
            return [];
        }

        return [
            'admin_area' => $this->getUsersByNiks(myrep_pic_nik_list($mapping['admin_area'] ?? '')),
            'rpm_area' => $this->getUsersByNiks(myrep_pic_nik_list($mapping['rpm_area'] ?? '')),
            'sm_area' => $this->getUsersByNiks(myrep_pic_nik_list($mapping['sm_area'] ?? '')),
        ];
    }

    private function getUsersByIds(array $ids)
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if (empty($ids) || !$this->ci->db->table_exists('tb_master_user_new')) {
            return [];
        }

        return (array) $this->ci->db
            ->select('id, nik, nama_karyawan, email_kantor')
            ->from('tb_master_user_new')
            ->where_in('id', $ids)
            ->get()
            ->result_array();
    }

    private function getUsersByNiks(array $niks)
    {
        $niks = array_values(array_unique(array_filter(array_map('trim', $niks))));
        if (empty($niks) || !$this->ci->db->table_exists('tb_master_user_new')) {
            return [];
        }

        return (array) $this->ci->db
            ->select('id, nik, nama_karyawan, email_kantor')
            ->from('tb_master_user_new')
            ->where_in('nik', $niks)
            ->get()
            ->result_array();
    }

    private function buildSubject(array $queue, $itemCount)
    {
        $moduleLabel = trim((string) ($queue['module_label'] ?? $this->moduleLabel((string) ($queue['module_name'] ?? 'MyRep'))));
        $clusterName = trim((string) ($queue['cluster_name'] ?? 'Cluster'));
        $cityName = trim((string) ($queue['city_name'] ?? ''));
        $docCount = (int) $itemCount;
        $countLabel = $docCount > 1 ? ($docCount . ' Dokumen - ') : '';
        $locationLabel = $cityName !== '' ? (' - ' . $cityName) : '';

        return '[DOC REJECTED] ' . $moduleLabel . ' - ' . $countLabel . 'Cluster ' . $clusterName . $locationLabel;
    }

    private function buildMessage(array $queue, array $items)
    {
        $clusterName = htmlspecialchars(trim((string) ($queue['cluster_name'] ?? '-')), ENT_QUOTES, 'UTF-8');
        $cityName = htmlspecialchars(trim((string) ($queue['city_name'] ?? '-')), ENT_QUOTES, 'UTF-8');
        $moduleLabel = htmlspecialchars(trim((string) ($queue['module_label'] ?? $this->moduleLabel((string) ($queue['module_name'] ?? 'MyRep')))), ENT_QUOTES, 'UTF-8');
        $detailUrl = trim((string) ($queue['detail_url'] ?? ''));

        $html = '<p>Kepada Yth. Bapak/Ibu,</p>';
        $html .= '<p>Request dokumen Anda telah <strong>ditolak</strong> dengan detail berikut:</p>';
        $html .= '<table cellpadding="4" cellspacing="0" border="0">';
        $html .= '<tr><td>Cluster</td><td>:</td><td>' . $clusterName . ' | ' . $cityName . '</td></tr>';
        $html .= '<tr><td>Modul</td><td>:</td><td>' . $moduleLabel . '</td></tr>';
        $html .= '<tr><td>Total Dokumen</td><td>:</td><td>' . count($items) . '</td></tr>';
        $html .= '</table>';
        $html .= '<p>Daftar dokumen ditolak:</p><ol>';

        foreach ($items as $item) {
            $docName = htmlspecialchars((string) ($item['doc_name'] ?? 'Dokumen'), ENT_QUOTES, 'UTF-8');
            $remark = htmlspecialchars(trim((string) ($item['remark'] ?? '')), ENT_QUOTES, 'UTF-8');
            $remark = $remark !== '' ? $remark : '-';
            $html .= '<li><strong>' . $docName . '</strong><br>Remarks: ' . nl2br($remark) . '</li>';
        }

        $html .= '</ol>';
        if ($detailUrl !== '') {
            $safeUrl = htmlspecialchars($detailUrl, ENT_QUOTES, 'UTF-8');
            $html .= '<p>Link Detail: <a href="' . $safeUrl . '">' . $safeUrl . '</a></p>';
        }
        $html .= '<p>Silakan melakukan revisi yang diperlukan dan submit kembali dokumen tersebut melalui aplikasi ZEYN.</p>';
        $html .= '<p>Jika ada pertanyaan terkait request ini, silakan menghubungi reviewer/rejecter yang tercantum dalam email ini.</p>';
        $html .= '<p>Terima kasih.</p>';
        $html .= $this->buildCompanyFooterHtml();

        return $html;
    }

    private function buildCompanyFooterHtml()
    {
        $logoUrl = base_url('assets/dist/img/solid%20logo%20tkm%20landscape%20transparent.png');

        return ''
            . '<br>'
            . '<p style="margin:0;">Thanks &amp; Regards</p>'
            . '<p style="margin:10px 0 8px 0;">'
            . '<img src="' . htmlspecialchars($logoUrl, ENT_QUOTES, 'UTF-8') . '" alt="Logo TKM" style="max-width:220px;height:auto;">'
            . '</p>'
            . '<p style="margin:0;"><strong>PT. TECHNOLOGY KARYA MANDIRI</strong></p>'
            . '<p style="margin:0;">Rukan Puri Botanical Blok H.9 / No.22-23</p>'
            . '<p style="margin:0;">Jl. Raya Meruya Selatan - Joglo</p>'
            . '<p style="margin:0;">Jakarta 11640</p>'
            . '<p style="margin:0;"><strong>Telp</strong>: 021 - 5855552 (Hunting) / <strong>Fax</strong>: 021 - 5852896</p>'
            . '<p style="margin:0;"><strong>Mobile</strong>: +62 8953 3675 0905</p>'
            . '<p style="margin:0;"><a href="mailto:lutfi@tkm.co.id" style="color:#2563eb;">Email : lutfi@tkm.co.id</a></p>'
            . '<p style="margin:0;"><a href="https://www.tkm.co.id" style="color:#2563eb;">www.tkm.co.id</a></p>';
    }

    private function sendEmail(array $to, array $cc, $subject, $messageHtml)
    {
        $this->ci->load->library('email');
        $smtpHost = $this->envValue('SMTP_HOST', '');
        $smtpPort = (int) $this->envValue('SMTP_PORT', '587');
        $smtpUser = $this->envValue('SMTP_USER', '');
        $smtpPass = $this->envValue('SMTP_PASS', '');
        $smtpCrypto = strtolower(trim($this->envValue('SMTP_CRYPTO', 'tls')));
        $fromEmail = $this->envValue('SMTP_FROM_EMAIL', $smtpUser !== '' ? $smtpUser : 'no-reply@tkm.co.id');
        $fromName = $this->envValue('SMTP_FROM_NAME', 'Database TKM');
        $allowInvalidTls = $this->normalizeBoolean($this->envValue('MYREP_REJECT_EMAIL_ALLOW_INVALID_TLS', 'false'));
        $smtpTimeout = max(5, min(60, (int) $this->envValue('MYREP_REJECT_EMAIL_SMTP_TIMEOUT', '15')));

        if ($allowInvalidTls) {
            stream_context_set_default([
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true,
                ],
            ]);
        }

        if ($smtpHost !== '' && $smtpUser !== '') {
            $this->ci->email->initialize([
                'protocol' => 'smtp',
                'smtp_host' => $smtpHost,
                'smtp_port' => $smtpPort > 0 ? $smtpPort : 587,
                'smtp_user' => $smtpUser,
                'smtp_pass' => $smtpPass,
                'smtp_crypto' => in_array($smtpCrypto, ['tls', 'ssl'], true) ? $smtpCrypto : 'tls',
                'mailtype' => 'html',
                'charset' => 'utf-8',
                'newline' => "\r\n",
                'crlf' => "\r\n",
                'smtp_timeout' => $smtpTimeout,
            ]);
        }

        $this->ci->email->clear(true);
        $this->ci->email->from($fromEmail, $fromName);
        $this->ci->email->to($to);
        if (!empty($cc)) {
            $this->ci->email->cc($cc);
        }
        $this->ci->email->subject($subject);
        $this->ci->email->message($messageHtml);
        $this->ci->email->set_mailtype('html');

        $sent = (bool) $this->ci->email->send();
        $debugger = strip_tags((string) $this->ci->email->print_debugger(['headers', 'subject', 'body']));
        if (!$sent) {
            $debugger = $this->normalizeEmailError($debugger);
            log_message('error', 'MyRep reject email failed: ' . $debugger);
        }

        return [
            'sent' => $sent,
            'error' => $sent ? '' : ($debugger !== '' ? $debugger : 'SMTP gagal mengirim email.'),
        ];
    }

    private function normalizeEmailError($message)
    {
        $message = trim(preg_replace('/\s+/', ' ', (string) $message));
        if ($message === '') {
            return '';
        }

        return mb_substr($message, 0, 1000);
    }

    private function lockQueue($queueId)
    {
        return (bool) $this->ci->db
            ->where('id_queue', (int) $queueId)
            ->where('status_queue', 'PENDING')
            ->update('tb_myrep_reject_email_queue', [
                'status_queue' => 'PROCESSING',
                'locked_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
    }

    private function releaseStaleProcessingQueues()
    {
        $minutes = max(5, (int) $this->envValue('MYREP_REJECT_EMAIL_LOCK_TIMEOUT_MINUTES', '15'));
        $cutoff = date('Y-m-d H:i:s', time() - ($minutes * 60));
        $this->ci->db
            ->where('status_queue', 'PROCESSING')
            ->group_start()
            ->where('locked_at <', $cutoff)
            ->or_where('locked_at IS NULL', null, false)
            ->group_end()
            ->update('tb_myrep_reject_email_queue', [
                'status_queue' => 'PENDING',
                'locked_at' => null,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
    }

    private function markQueueFailed($queueId, $error)
    {
        $this->ci->db
            ->where('id_queue', (int) $queueId)
            ->update('tb_myrep_reject_email_queue', [
                'status_queue' => 'FAILED',
                'last_error' => (string) $error,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
    }

    private function rescheduleFailedQueue($queueId, $error)
    {
        $queue = $this->ci->db
            ->select('attempt_count')
            ->from('tb_myrep_reject_email_queue')
            ->where('id_queue', (int) $queueId)
            ->limit(1)
            ->get()
            ->row_array();

        $attempt = (int) ($queue['attempt_count'] ?? 0) + 1;
        $maxAttempts = max(1, (int) $this->envValue('MYREP_REJECT_EMAIL_MAX_ATTEMPTS', '3'));
        $status = $attempt >= $maxAttempts ? 'FAILED' : 'PENDING';
        $retryMinutes = min(60, max(5, $attempt * 10));

        $this->ci->db
            ->where('id_queue', (int) $queueId)
            ->update('tb_myrep_reject_email_queue', [
                'status_queue' => $status,
                'attempt_count' => $attempt,
                'scheduled_at' => date('Y-m-d H:i:s', time() + ($retryMinutes * 60)),
                'last_error' => (string) $error,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
    }

    private function ensureTables()
    {
        if ($this->tablesEnsured) {
            return true;
        }

        $this->ci->db->query("CREATE TABLE IF NOT EXISTS `tb_myrep_reject_email_queue` (
            `id_queue` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `module_name` VARCHAR(100) NOT NULL,
            `module_label` VARCHAR(150) NULL,
            `source_type` VARCHAR(50) NOT NULL DEFAULT 'FLOW',
            `cluster_ref_id` INT UNSIGNED NOT NULL,
            `cluster_name` VARCHAR(255) NULL,
            `city_name` VARCHAR(150) NULL,
            `province_name` VARCHAR(150) NULL,
            `regional_name` VARCHAR(150) NULL,
            `detail_url` VARCHAR(500) NULL,
            `status_queue` VARCHAR(20) NOT NULL DEFAULT 'PENDING',
            `scheduled_at` DATETIME NOT NULL,
            `locked_at` DATETIME NULL,
            `sent_at` DATETIME NULL,
            `attempt_count` INT UNSIGNED NOT NULL DEFAULT 0,
            `last_error` TEXT NULL,
            `created_at` DATETIME NOT NULL,
            `updated_at` DATETIME NOT NULL,
            PRIMARY KEY (`id_queue`),
            KEY `idx_myrep_reject_queue_due` (`status_queue`, `scheduled_at`),
            KEY `idx_myrep_reject_queue_group` (`module_name`, `source_type`, `cluster_ref_id`, `status_queue`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $this->ci->db->query("CREATE TABLE IF NOT EXISTS `tb_myrep_reject_email_queue_item` (
            `id_item` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_queue` INT UNSIGNED NOT NULL,
            `source_file_id` INT UNSIGNED NOT NULL,
            `doc_name` VARCHAR(255) NULL,
            `file_name` VARCHAR(255) NULL,
            `remark` TEXT NULL,
            `submitter_user_id` INT UNSIGNED NULL,
            `rejecter_user_id` INT UNSIGNED NULL,
            `rejected_at` DATETIME NULL,
            `created_at` DATETIME NOT NULL,
            `updated_at` DATETIME NOT NULL,
            PRIMARY KEY (`id_item`),
            UNIQUE KEY `uq_myrep_reject_queue_file` (`id_queue`, `source_file_id`),
            KEY `idx_myrep_reject_queue_item_queue` (`id_queue`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $this->tablesEnsured = $this->ci->db->table_exists('tb_myrep_reject_email_queue')
            && $this->ci->db->table_exists('tb_myrep_reject_email_queue_item');

        return $this->tablesEnsured;
    }

    private function extractEmails(array $users)
    {
        $emails = [];
        foreach ($users as $user) {
            $emails = array_merge($emails, $this->parseEmailList($user['email_kantor'] ?? ''));
        }
        return $emails;
    }

    private function parseEmailList($value)
    {
        $parts = preg_split('/[,;]+/', (string) $value);
        $emails = [];
        foreach ($parts as $part) {
            $email = strtolower(trim((string) $part));
            if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $emails[] = $email;
            }
        }
        return $emails;
    }

    private function uniqueEmails(array $emails, array $exclude = [])
    {
        $excludeSet = [];
        foreach ($exclude as $email) {
            $excludeSet[strtolower(trim((string) $email))] = true;
        }

        $result = [];
        foreach ($emails as $email) {
            $email = strtolower(trim((string) $email));
            if ($email === '' || isset($excludeSet[$email]) || isset($result[$email])) {
                continue;
            }
            $result[$email] = $email;
        }
        return array_values($result);
    }

    private function moduleLabel($moduleName)
    {
        $map = [
            'BAK_MyRep' => 'BAK MyRep',
            'VALSAL_MyRep' => 'VALSAL MyRep',
            'Batch_Approval_MyRep' => 'Batch Approval MyRep',
            'DRM_MyRep' => 'DRM MyRep',
            'Post_Donasi_MyRep' => 'Post Donasi MyRep',
            'Checklist_Dokument_MyRep' => 'Checklist Document',
        ];

        return $map[$moduleName] ?? str_replace('_', ' ', (string) $moduleName);
    }

    private function buildDetailUrl($moduleName, $clusterId)
    {
        $clusterId = (int) $clusterId;
        if ($clusterId <= 0) {
            return base_url((string) $moduleName);
        }

        if (in_array($moduleName, ['Batch_Approval_MyRep', 'DRM_MyRep', 'Post_Donasi_MyRep'], true)) {
            return base_url($moduleName . '/detail/' . $clusterId);
        }

        return base_url((string) $moduleName);
    }

    private function isEnabled()
    {
        return $this->normalizeBoolean($this->envValue('MYREP_REJECT_EMAIL_ENABLED', 'true'));
    }

    private function normalizeBoolean($value)
    {
        return in_array(strtolower(trim((string) $value)), ['1', 'true', 'yes', 'on'], true);
    }

    private function envValue($key, $default = '')
    {
        return array_key_exists($key, $this->env) ? trim((string) $this->env[$key]) : $default;
    }

    private function loadEnv()
    {
        $envPath = APPPATH . '../.env';
        if (!is_file($envPath)) {
            return [];
        }

        $env = @parse_ini_file($envPath);
        return is_array($env) ? $env : [];
    }
}
