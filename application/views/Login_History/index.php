<?php
$filters = (array) ($filters ?? []);
$summary = (array) ($summary ?? []);
$rows = (array) ($rows ?? []);

if (!function_exists('login_history_escape')) {
    function login_history_escape($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('login_history_datetime')) {
    function login_history_datetime($value)
    {
        $time = strtotime((string) $value);
        return $time === false ? '-' : date('d M Y H:i:s', $time);
    }
}

if (!function_exists('login_history_duration')) {
    function login_history_duration($seconds)
    {
        if ($seconds === null || $seconds === '') {
            return '-';
        }

        $seconds = max((int) $seconds, 0);
        if ($seconds < 60) {
            return $seconds . ' detik';
        }

        $minutes = (int) floor($seconds / 60);
        if ($minutes < 60) {
            return $minutes . ' menit';
        }

        $hours = (int) floor($minutes / 60);
        $remainingMinutes = $minutes % 60;
        return $hours . ' jam' . ($remainingMinutes > 0 ? ' ' . $remainingMinutes . ' menit' : '');
    }
}

if (!function_exists('login_history_short_url')) {
    function login_history_short_url($value)
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '-';
        }

        $parts = parse_url($value);
        if (!is_array($parts)) {
            return $value;
        }

        $path = trim((string) ($parts['path'] ?? ''), '/');
        $query = trim((string) ($parts['query'] ?? ''));
        $display = $path !== '' ? $path : '/';
        return $query !== '' ? $display . '?' . $query : $display;
    }
}

if (!function_exists('login_history_device_label')) {
    function login_history_device_label($userAgent)
    {
        $ua = (string) $userAgent;
        if (trim($ua) === '') {
            return '-';
        }

        $platform = 'Device';
        if (stripos($ua, 'Windows NT') !== false) {
            $platform = 'Windows';
        } elseif (stripos($ua, 'Android') !== false) {
            $platform = 'Android';
        } elseif (stripos($ua, 'iPhone') !== false) {
            $platform = 'iPhone';
        } elseif (stripos($ua, 'iPad') !== false) {
            $platform = 'iPad';
        } elseif (stripos($ua, 'Mac OS X') !== false || stripos($ua, 'Macintosh') !== false) {
            $platform = 'macOS';
        } elseif (stripos($ua, 'Linux') !== false) {
            $platform = 'Linux';
        }

        $browser = 'Browser';
        if (stripos($ua, 'Edg/') !== false || stripos($ua, 'Edge/') !== false) {
            $browser = 'Edge';
        } elseif (stripos($ua, 'OPR/') !== false || stripos($ua, 'Opera') !== false) {
            $browser = 'Opera';
        } elseif (stripos($ua, 'Firefox/') !== false) {
            $browser = 'Firefox';
        } elseif (stripos($ua, 'Chrome/') !== false || stripos($ua, 'CriOS/') !== false) {
            $browser = 'Chrome';
        } elseif (stripos($ua, 'Safari/') !== false) {
            $browser = 'Safari';
        }

        return $platform . ' - ' . $browser;
    }
}

$statusMeta = [
    'online' => ['label' => 'Online', 'class' => 'success'],
    'idle' => ['label' => 'Idle', 'class' => 'warning'],
    'offline' => ['label' => 'Offline', 'class' => 'secondary'],
];
?>

<div class="content-wrapper login-history-page">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-8">
                    <h1 class="m-0 text-dark"><?= login_history_escape($judul ?? 'Login History') ?></h1>
                    <p class="text-muted mb-0">
                        Web session monitor. Online &le; <?= (int) ($onlineMinutes ?? 10) ?> menit,
                        idle &le; <?= (int) ($idleMinutes ?? 30) ?> menit, history <?= (int) ($retentionDays ?? 90) ?> hari.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="login-history-summary">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3><?= number_format((int) ($summary['total'] ?? 0), 0, ',', '.') ?></h3>
                        <p>Total Login</p>
                    </div>
                    <div class="icon"><i class="fas fa-history"></i></div>
                </div>
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3><?= number_format((int) ($summary['online'] ?? 0), 0, ',', '.') ?></h3>
                        <p>Online</p>
                    </div>
                    <div class="icon"><i class="fas fa-signal"></i></div>
                </div>
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3><?= number_format((int) ($summary['idle'] ?? 0), 0, ',', '.') ?></h3>
                        <p>Idle</p>
                    </div>
                    <div class="icon"><i class="fas fa-clock"></i></div>
                </div>
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3><?= number_format((int) ($summary['night'] ?? 0), 0, ',', '.') ?></h3>
                        <p>Login 00:00-05:00</p>
                    </div>
                    <div class="icon"><i class="fas fa-moon"></i></div>
                </div>
            </div>

            <div class="card card-outline card-primary shadow-sm login-history-card mb-3">
                <div class="card-header">
                    <h3 class="card-title mb-0">Filter Login History</h3>
                </div>
                <div class="card-body">
                    <form method="get" action="<?= base_url('Login_History') ?>" class="login-history-filter">
                        <div class="form-group mb-0">
                            <label for="date_from">Tanggal Mulai</label>
                            <input type="date" class="form-control" id="date_from" name="date_from" value="<?= login_history_escape($filters['date_from'] ?? '') ?>">
                        </div>
                        <div class="form-group mb-0">
                            <label for="date_to">Tanggal Akhir</label>
                            <input type="date" class="form-control" id="date_to" name="date_to" value="<?= login_history_escape($filters['date_to'] ?? '') ?>">
                        </div>
                        <div class="form-group mb-0">
                            <label for="activity_status">Status</label>
                            <select class="form-control" id="activity_status" name="activity_status">
                                <option value="">Semua Status</option>
                                <?php foreach ($statusMeta as $statusKey => $meta): ?>
                                    <option value="<?= login_history_escape($statusKey) ?>" <?= (string) ($filters['activity_status'] ?? '') === $statusKey ? 'selected' : '' ?>>
                                        <?= login_history_escape($meta['label']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group mb-0 login-history-filter__keyword">
                            <label for="keyword">Search</label>
                            <input type="text" class="form-control" id="keyword" name="keyword" value="<?= login_history_escape($filters['keyword'] ?? '') ?>" placeholder="NIK, nama, username, IP, halaman">
                        </div>
                        <div class="login-history-filter__actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search mr-1"></i> Tampilkan
                            </button>
                            <a href="<?= base_url('Login_History') ?>" class="btn btn-light">
                                <i class="fas fa-redo-alt mr-1"></i> Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm login-history-card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h3 class="card-title mb-0">Riwayat Login Website</h3>
                    <span class="badge badge-light"><?= number_format(count($rows), 0, ',', '.') ?> data terbaru</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="loginHistoryTable" class="table table-bordered table-hover table-striped">
                            <thead class="bg-info">
                                <tr>
                                    <th>No</th>
                                    <th>Status</th>
                                    <th>NIK</th>
                                    <th>Nama</th>
                                    <th>Username</th>
                                    <th>Level</th>
                                    <th>Login</th>
                                    <th>Last Seen</th>
                                    <th>Halaman Dibuka</th>
                                    <th>Logout</th>
                                    <th>IP</th>
                                    <th>Device</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($rows)): ?>
                                    <tr>
                                        <td colspan="12" class="text-center text-muted">Belum ada data login pada filter ini.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php $no = 1; ?>
                                    <?php foreach ($rows as $row): ?>
                                        <?php
                                        $status = strtolower((string) ($row['activity_status'] ?? 'offline'));
                                        $meta = $statusMeta[$status] ?? $statusMeta['offline'];
                                        $nik = $row['current_nik'] ?: ($row['nik'] ?? '-');
                                        $nama = $row['current_nama_user'] ?: ($row['nama_user'] ?? '-');
                                        $username = $row['current_username_user'] ?: ($row['username_user'] ?? '-');
                                        $level = $row['current_nama_level'] ?: ($row['nama_level'] ?? '-');
                                        ?>
                                        <tr class="<?= !empty($row['is_night_login']) ? 'login-history-night-row' : '' ?>">
                                            <td><?= $no++ ?></td>
                                            <td>
                                                <span class="badge badge-<?= login_history_escape($meta['class']) ?>">
                                                    <?= login_history_escape($meta['label']) ?>
                                                </span>
                                                <?php if (!empty($row['is_night_login'])): ?>
                                                    <span class="badge badge-danger ml-1">00-05</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= login_history_escape($nik ?: '-') ?></td>
                                            <td><?= login_history_escape($nama ?: '-') ?></td>
                                            <td><?= login_history_escape($username ?: '-') ?></td>
                                            <td><?= login_history_escape($level ?: '-') ?></td>
                                            <td><?= login_history_datetime($row['login_at'] ?? '') ?></td>
                                            <td>
                                                <?= login_history_datetime($row['last_seen_at'] ?? '') ?>
                                                <small class="text-muted d-block"><?= login_history_duration($row['seconds_since_seen'] ?? null) ?> lalu</small>
                                            </td>
                                            <td class="login-history-page-opened">
                                                <?php if (!empty($row['last_page_url']) || !empty($row['last_page_title'])): ?>
                                                    <div class="font-weight-bold"><?= login_history_escape($row['last_page_title'] ?? 'Halaman') ?></div>
                                                    <?php if (!empty($row['last_page_url'])): ?>
                                                        <a href="<?= login_history_escape($row['last_page_url']) ?>" target="_blank" rel="noopener" class="d-block text-truncate">
                                                            <?= login_history_escape(login_history_short_url($row['last_page_url'])) ?>
                                                        </a>
                                                    <?php endif; ?>
                                                    <small class="text-muted d-block">
                                                        <?= login_history_escape($row['last_page_method'] ?? 'GET') ?>
                                                        <?php if (!empty($row['last_page_at'])): ?>
                                                            - <?= login_history_datetime($row['last_page_at']) ?>
                                                        <?php endif; ?>
                                                    </small>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?= !empty($row['logout_at']) ? login_history_datetime($row['logout_at']) : '-' ?>
                                                <?php if (!empty($row['logout_reason'])): ?>
                                                    <small class="text-muted d-block"><?= login_history_escape($row['logout_reason']) ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= login_history_escape($row['ip_address'] ?? '-') ?></td>
                                            <td class="login-history-device" title="<?= login_history_escape($row['user_agent'] ?? '-') ?>">
                                                <?= login_history_escape(login_history_device_label($row['user_agent'] ?? '')) ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
    $(function () {
        if ($.fn.DataTable) {
            $('#loginHistoryTable').DataTable({
                paging: true,
                searching: true,
                info: true,
                ordering: true,
                responsive: false,
                autoWidth: false,
                scrollX: true,
                pageLength: 25,
                order: [[6, 'desc']],
                language: {
                    search: 'Search:',
                    lengthMenu: 'Tampilkan _MENU_ data',
                    info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
                    paginate: { previous: 'Prev', next: 'Next' },
                    zeroRecords: 'Tidak ada data yang cocok'
                }
            });
        }
    });
</script>

<style>
    .login-history-summary {
        display: grid;
        grid-template-columns: repeat(4, minmax(160px, 1fr));
        gap: 14px;
        margin-bottom: 1rem;
    }
    .login-history-card {
        border-radius: 8px;
        overflow: hidden;
    }
    .login-history-filter {
        display: grid;
        grid-template-columns: minmax(150px, 180px) minmax(150px, 180px) minmax(150px, 180px) minmax(220px, 1fr) auto;
        gap: 12px;
        align-items: end;
    }
    .login-history-filter label {
        margin-bottom: 6px;
        font-size: 0.82rem;
        font-weight: 700;
        color: #485766;
        text-transform: uppercase;
    }
    .login-history-filter__actions {
        display: flex;
        gap: 8px;
        justify-content: flex-end;
        white-space: nowrap;
    }
    .login-history-night-row td {
        background-color: #fff7ed;
    }
    .login-history-device {
        min-width: 260px;
        max-width: 420px;
        white-space: normal;
        word-break: break-word;
    }
    .login-history-page-opened {
        min-width: 240px;
        max-width: 360px;
        white-space: normal;
        word-break: break-word;
    }
    @media (max-width: 1199.98px) {
        .login-history-summary {
            grid-template-columns: repeat(2, minmax(160px, 1fr));
        }
        .login-history-filter {
            grid-template-columns: repeat(2, minmax(150px, 1fr));
        }
        .login-history-filter__keyword,
        .login-history-filter__actions {
            grid-column: 1 / -1;
        }
        .login-history-filter__actions {
            justify-content: flex-start;
        }
    }
    @media (max-width: 767.98px) {
        .login-history-summary,
        .login-history-filter {
            grid-template-columns: 1fr;
        }
        .login-history-filter__actions {
            display: grid;
            grid-template-columns: 1fr;
        }
    }
</style>
