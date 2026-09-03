<!-- OPTIONAL SCRIPTS -->
<script src="<?= base_url('assets') ?>/dist/js/demo.js"></script>

<!-- PAGE PLUGINS -->
<!-- jQuery Mapael -->
<script src="<?= base_url('assets') ?>/plugins/jquery-mousewheel/jquery.mousewheel.js"></script>
<script src="<?= base_url('assets') ?>/plugins/raphael/raphael.min.js"></script>
<script src="<?= base_url('assets') ?>/plugins/jquery-mapael/jquery.mapael.min.js"></script>
<script src="<?= base_url('assets') ?>/plugins/jquery-mapael/maps/usa_states.min.js"></script>
<!-- Input Mask -->
<!-- <script src="<?= base_url('assets') ?>/plugins/moment/moment.min.js"></script> -->
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script src="<?= base_url('assets') ?>/plugins/inputmask/min/jquery.inputmask.bundle.min.js"></script>
<!-- ChartJS -->
<script src="<?= base_url('assets') ?>/plugins/chart.js/Chart.min.js"></script>
<!-- DataTables -->
<script src="<?= base_url('assets') ?>/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="<?= base_url('assets') ?>/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="<?= base_url('assets') ?>/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="<?= base_url('assets') ?>/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
<!-- date-range-picker -->
<!-- <script src="<?= base_url('assets') ?>/plugins/daterangepicker/daterangepicker.js"></script> -->
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script src="<?= base_url('assets') ?>/plugins/select2/js/select2.full.min.js"></script>


<script>
    $(function() {
        if ($("#tabel_pemasukan").length) {
            $("#tabel_pemasukan").DataTable({
                "responsive": true,
                "autoWidth": true
            });
        }

        if ($('#example2').length) {
            $('#example2').DataTable({
                "paging": true,
                "lengthChange": false,
                "searching": false,
                "ordering": true,
                "info": true,
                "autoWidth": false,
                "responsive": true,
            });
        }
    });
</script>

<?php
$accessPageContext = [
    'enabled' => false,
    'can_tambah' => true,
    'can_edit' => true,
    'can_hapus' => true,
    'can_approval' => true,
];

if (
    function_exists('get_user_page_access_controller_map') &&
    function_exists('has_user_page_access') &&
    !empty($this->session->userdata('id_user')) &&
    (string) $this->session->userdata('nama_level') !== 'Super Admin'
) {
    $controllerMap = (array) get_user_page_access_controller_map();
    $controllerKey = strtoupper(trim((string) $this->uri->segment(1)));
    if ($controllerKey !== '' && isset($controllerMap[$controllerKey])) {
        $entry = (array) $controllerMap[$controllerKey];
        $moduleKey = (string) ($entry['module_key'] ?? '');
        $pageKey = (string) ($entry['page_key'] ?? '');

        if ($moduleKey !== '' && $pageKey !== '') {
            $accessPageContext['enabled'] = true;
            $accessPageContext['can_tambah'] = has_user_page_access($moduleKey, $pageKey, 'TAMBAH');
            $accessPageContext['can_edit'] = has_user_page_access($moduleKey, $pageKey, 'EDIT');
            $accessPageContext['can_hapus'] = has_user_page_access($moduleKey, $pageKey, 'HAPUS');
            $accessPageContext['can_approval'] = has_user_page_access($moduleKey, $pageKey, 'APPROVAL');
        }
    }
}
?>
<script>
    (function() {
        var ctx = <?= json_encode($accessPageContext, JSON_UNESCAPED_SLASHES) ?> || {};
        if (!ctx.enabled) {
            return;
        }

        function normalizeUrl(url) {
            if (!url) {
                return '';
            }
            return String(url).toLowerCase();
        }

        function inferActionByUrl(url) {
            var u = normalizeUrl(url);
            if (!u) {
                return '';
            }

            if (
                u.indexOf('/approve') !== -1 ||
                u.indexOf('/reject') !== -1 ||
                u.indexOf('/submit_approval') !== -1 ||
                u.indexOf('/approveall') !== -1
            ) {
                return 'APPROVAL';
            }
            if (u.indexOf('/delete') !== -1 || u.indexOf('/remove') !== -1) {
                return 'HAPUS';
            }
            if (u.indexOf('/edit') !== -1 || u.indexOf('/update') !== -1 || u.indexOf('/allocate') !== -1) {
                return 'EDIT';
            }
            if (
                u.indexOf('/save') !== -1 ||
                u.indexOf('/add') !== -1 ||
                u.indexOf('/create') !== -1 ||
                u.indexOf('/import') !== -1 ||
                u.indexOf('/upload') !== -1 ||
                u.indexOf('/store') !== -1
            ) {
                return 'TAMBAH';
            }

            return '';
        }

        function inferActionByElement($el) {
            if (!$el || !$el.length) {
                return '';
            }

            var text = (
                String($el.attr('class') || '') + ' ' +
                String($el.attr('id') || '') + ' ' +
                String($.trim($el.text() || '')) + ' ' +
                String($el.val() || '')
            ).toLowerCase();

            if (!text) {
                return '';
            }

            var className = String($el.attr('class') || '').toLowerCase();
            var elementId = String($el.attr('id') || '').toLowerCase();

            if (/(^|\\s)(approve|approval|reject|verif|verify)[-_a-z0-9]*/i.test(className) || /(approve|approval|reject|verif|verify)/i.test(elementId)) {
                return 'APPROVAL';
            }
            if (/(^|\\s)(hapus|delete|remove|trash|batal|cancel)[-_a-z0-9]*/i.test(className) || /(hapus|delete|remove|trash|batal|cancel)/i.test(elementId)) {
                return 'HAPUS';
            }
            if (/(^|\\s)(edit|ubah|update|allocate|revisi|revise)[-_a-z0-9]*/i.test(className) || /(edit|ubah|update|allocate|revisi|revise)/i.test(elementId)) {
                return 'EDIT';
            }
            if (/(^|\\s)(tambah|add|input|import|upload|simpan|save|create|store|payment|bayar|pencairan|submit|kirim)[-_a-z0-9]*/i.test(className) || /(tambah|add|input|import|upload|simpan|save|create|store|payment|bayar|pencairan|submit|kirim)/i.test(elementId)) {
                return 'TAMBAH';
            }

            if (/(approve|approval|reject|verif|verify)/i.test(text)) {
                return 'APPROVAL';
            }
            if (/(hapus|delete|remove|trash|batal|cancel)/i.test(text)) {
                return 'HAPUS';
            }
            if (/(edit|ubah|update|allocate|revisi|revise)/i.test(text)) {
                return 'EDIT';
            }
            if (/(tambah|input|import|upload|simpan|save|create|store|payment|bayar|pencairan|submit|kirim)/i.test(text)) {
                return 'TAMBAH';
            }

            return '';
        }

        function isAllowed(actionKey) {
            if (!actionKey) {
                return true;
            }
            if (actionKey === 'TAMBAH') {
                return !!ctx.can_tambah;
            }
            if (actionKey === 'EDIT') {
                return !!ctx.can_edit;
            }
            if (actionKey === 'HAPUS') {
                return !!ctx.can_hapus;
            }
            if (actionKey === 'APPROVAL') {
                return !!ctx.can_approval;
            }
            return true;
        }

        function markDisabled($el) {
            if (!$el || !$el.length) {
                return;
            }
            if ($el.data('roleBlocked') === 1) {
                return;
            }

            $el
                .attr('aria-disabled', 'true')
                .attr('title', 'Akses role tidak diizinkan')
                .css({
                    'pointer-events': 'none',
                    'opacity': 0.55,
                    'cursor': 'not-allowed'
                })
                .addClass('role-action-disabled')
                .data('roleBlocked', 1);

            if ($el.is('button,input[type=submit],input[type=button]')) {
                $el.prop('disabled', true);
            }
        }

        function resolveActionKey($el) {
            if (!$el || !$el.length) {
                return '';
            }

            var explicit = String($el.attr('data-required-action') || '').toUpperCase().trim();
            if (explicit) {
                return explicit;
            }

            var actionKey = inferActionByUrl($el.attr('href') || $el.attr('data-url') || '');
            if (!actionKey) {
                var $form = $el.closest('form[action]');
                if ($form.length) {
                    actionKey = inferActionByUrl($form.attr('action') || '');
                }
            }
            if (!actionKey && $el.is('a')) {
                return '';
            }
            if (!actionKey) {
                actionKey = inferActionByElement($el);
            }

            return actionKey;
        }

        function isRoleGuardExempt($el) {
            return !!($el && $el.length && ($el.is('[data-role-guard-exempt="1"]') || $el.closest('[data-role-guard-exempt="1"]').length));
        }

        function protectByUrl($targets, urlAccessor) {
            $targets.each(function() {
                var $el = $(this);
                if (isRoleGuardExempt($el)) {
                    return;
                }
                var rawUrl = urlAccessor($el);
                var actionKey = inferActionByUrl(rawUrl);
                if (!actionKey) {
                    return;
                }
                if (!isAllowed(actionKey)) {
                    markDisabled($el);
                }
            });
        }

        function applyRoleUiGuard() {
            protectByUrl($('a[href]'), function($el) {
                return $el.attr('href');
            });

            protectByUrl($('[data-url]'), function($el) {
                return $el.attr('data-url');
            });

            $('form[action]').each(function() {
                var $form = $(this);
                if (isRoleGuardExempt($form)) {
                    return;
                }
                var actionKey = inferActionByUrl($form.attr('action'));
                if (!actionKey || isAllowed(actionKey)) {
                    return;
                }
                $form.find('button[type=submit], input[type=submit], .btn[type=button]').each(function() {
                    markDisabled($(this));
                });
            });

            $('button, input[type=button], input[type=submit], .btn').each(function() {
                var $el = $(this);
                if (isRoleGuardExempt($el)) {
                    return;
                }
                if ($el.is('a') && !$el.closest('form[action]').length) {
                    return;
                }
                var actionKey = resolveActionKey($el);
                if (actionKey && !isAllowed(actionKey)) {
                    markDisabled($el);
                }
            });
        }

        $(function() {
            applyRoleUiGuard();

            // Re-apply untuk elemen dinamis dari AJAX / DataTables / render JS.
            $(document).ajaxComplete(function() {
                applyRoleUiGuard();
            });

            if (window.MutationObserver) {
                var observer = new MutationObserver(function(mutations) {
                    for (var i = 0; i < mutations.length; i++) {
                        if (mutations[i].addedNodes && mutations[i].addedNodes.length) {
                            applyRoleUiGuard();
                            break;
                        }
                    }
                });
                observer.observe(document.body, {
                    childList: true,
                    subtree: true
                });
            }

            $(document).on('click', 'a[href], [data-url], button, input[type=submit]', function(e) {
                var $el = $(this);
                if (isRoleGuardExempt($el)) {
                    return true;
                }
                if ($el.is('a') && !$el.attr('data-required-action') && !$el.attr('data-url') && !$el.closest('form[action]').length) {
                    return true;
                }
                var actionKey = resolveActionKey($el);
                if (actionKey && !isAllowed(actionKey)) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    return false;
                }
            });
        });
    })();
</script>
<script>
    $(function() {
        if ($('input[name="date"]').length) {
            $('input[name="date"]').daterangepicker({
                opens: 'left'
            }, function(start, end, label) {
                console.log("A new date selection was made: " + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD'));
            });
        }
    });
</script>

<?php if (
    false &&
    isset($jml_hari_kosong) &&
    is_array($jml_hari_kosong) &&
    isset($jml_hari_kosong['jml']) &&
    isset($sisa_kemarin) &&
    is_array($sisa_kemarin) &&
    isset($sisa_kemarin['sisa'])
) {
    $jml_tanpa_akhir = (int) $jml_hari_kosong['jml'];
    for ($i = 0; $i < $jml_tanpa_akhir; $i++) {
?>
    <script type="text/javascript">
        $(window).on('load', function() {
            $('#sisamodal').modal('show');
        });
    </script>

    <div class="modal fade" id="sisamodal">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Peringatan <?= $i ?></h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="post" action="#">
                    <div class="modal-body">
                        <h3>Sisa saldo kemarin adalah <?php echo $jml_tanpa_akhir ?></h3>
                        <h3 class="text-danger"><?= "Rp. " . number_format($sisa_kemarin['sisa'], 0, ',', '.') ?> </h3>
                        <div class="form-group">
                            <input type="hidden" class="form-control" name="sisa" value="<?= $sisa_kemarin['sisa'] ?>">
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Oke</button>
                        </div>
                    </div>
            </div>
        </div>
    </div>
<?php
    }
} ?>
</body>

</html>
