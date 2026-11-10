<?php
function checklist_doc_render_astri_edit_all($modalId, $groupLabel, $itemCount, $hasProjectOpname = false)
{
    ?>
    <div class="modal fade bulk-astri-edit-all-modal" id="<?= $modalId ?>" tabindex="-1" role="dialog"
        aria-labelledby="<?= $modalId ?>-title" aria-describedby="<?= $modalId ?>-scope" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-dark">
                    <div>
                        <h4 class="modal-title mb-1" id="<?= $modalId ?>-title">Edit Status All ASTRI</h4>
                        <p class="mb-0"><?= htmlspecialchars((string) $groupLabel, ENT_QUOTES) ?></p>
                    </div>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <p id="<?= $modalId ?>-scope">
                        Status akan diterapkan ke <?= (int) $itemCount ?> dokumen yang memenuhi syarat, termasuk yang sudah APPROVED atau REJECTED.
                        <?= $hasProjectOpname ? 'Project Opname tetap diatur per dokumen.' : '' ?>
                    </p>
                    <div class="form-group">
                        <label for="<?= $modalId ?>-status">Status ASTRI</label>
                        <select id="<?= $modalId ?>-status" class="form-control bulk-astri-all-status">
                            <option value="">Pilih status</option>
                            <option value="NY">NY</option>
                            <option value="ON REVIEW">ON REVIEW</option>
                            <option value="APPROVED">APPROVED</option>
                        </select>
                        <small class="form-text text-muted">Pilih APPROVED jika dokumen sudah disetujui di ASTRI. Gunakan REJECTED per dokumen.</small>
                    </div>
                    <div class="form-group mb-0">
                        <label for="<?= $modalId ?>-date">Tanggal Submit ASTRI</label>
                        <input id="<?= $modalId ?>-date" type="date" class="form-control bulk-astri-all-date" disabled>
                        <small class="form-text text-muted">Tanggal ini mengisi baris yang masih kosong. Pilihan NY mengosongkan tanggal submit.</small>
                    </div>
                    <p class="bulk-astri-all-error text-danger small mb-0" role="alert"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-dark bulk-astri-apply-all">Terapkan ke Semua</button>
                </div>
            </div>
        </div>
    </div>
    <?php
}

function checklist_doc_render_astri_edit_all_script()
{
    ?>
    <script>
        $(document).on('click', '.bulk-astri-open-edit-all', function() {
            var opener = this;
            var bulk = $(opener).closest('.modal');
            var child = $(document.getElementById(opener.getAttribute('aria-controls')));
            if (!bulk.hasClass('show') || !child.length) {
                return;
            }
            var scrollTop = bulk.scrollTop();
            var pageOpener = document.querySelector('[data-toggle="modal"][data-target="#' + bulk.attr('id') + '"]');
            function openChild() {
                child.data('astri-bulk-modal', bulk);
                child.find('.bulk-astri-all-status').val('').trigger('change');
                child.find('.bulk-astri-all-date').val('');
                child.one('shown.bs.modal', function() {
                    child.find('.bulk-astri-all-status').trigger('focus');
                }).one('hidden.bs.modal', function() {
                    child.removeData('astri-bulk-modal');
                    bulk.one('shown.bs.modal', function() {
                        bulk.scrollTop(scrollTop);
                        opener.focus({ preventScroll: true });
                    }).modal('show');
                    if (pageOpener) {
                        bulk.one('hidden.bs.modal', function() {
                            pageOpener.focus({ preventScroll: true });
                        });
                    }
                }).modal('show');
            }
            // Bootstrap 4 manages one active modal and backdrop at a time.
            opener.blur();
            bulk.one('hidden.bs.modal', openChild).modal('hide');
            if (bulk.hasClass('show')) {
                bulk.off('hidden.bs.modal', openChild);
                opener.focus({ preventScroll: true });
            }
        });

        $(document).on('hide.bs.modal', '.bulk-astri-edit-all-modal', function() {
            if (this.contains(document.activeElement)) {
                document.activeElement.blur();
            }
        });

        $(document).on('change', '.bulk-astri-all-status', function() {
            var panel = $(this).closest('.bulk-astri-edit-all-modal');
            var status = this.value;
            var date = panel.find('.bulk-astri-all-date');
            date.prop('disabled', status === '' || status === 'NY');
            panel.find('.bulk-astri-all-error').empty();
        });

        $(document).on('click', '.bulk-astri-apply-all', function() {
            var panel = $(this).closest('.bulk-astri-edit-all-modal');
            var bulk = panel.data('astri-bulk-modal');
            if (!bulk) {
                return;
            }
            var form = bulk.find('form');
            var statusInput = panel.find('.bulk-astri-all-status');
            var dateInput = panel.find('.bulk-astri-all-date');
            var error = panel.find('.bulk-astri-all-error').empty();
            var status = statusInput.val();
            var date = dateInput.val();
            var rows = form.find('.bulk-astri-edit-all-row');

            if (!status) {
                error.text('Pilih status ASTRI terlebih dahulu.');
                statusInput.trigger('focus');
                return;
            }
            if (!rows.length) {
                error.text('Belum ada dokumen yang bisa diupdate melalui Edit Status All.');
                return;
            }
            var dates = rows.find('input[type="date"]');
            var missingDate = dates.filter(function() { return !this.value; }).length > 0;
            if (status !== 'NY' && missingDate && !date) {
                error.text('Isi Tanggal Submit ASTRI untuk dokumen yang belum memiliki tanggal.');
                dateInput.trigger('focus');
                return;
            }
            if (status !== 'NY' && !dateInput[0].reportValidity()) {
                return;
            }

            rows.find('.bulk-astri-status').val(status).trigger('change');
            if (status !== 'NY') {
                dates.filter(function() { return !this.value; }).val(date);
            }
            form.data('astri-edit-all-applied', true);
            // Validasi saat simpan dibatasi pada baris yang berubah.
            form.prop('noValidate', true);
            form.find('.bulk-astri-save-message').text('Status diterapkan ke ' + rows.length + ' dokumen. Klik Simpan Bulk ASTRI untuk menyimpan perubahan.');
            panel.modal('hide');
        });

        function checklistBulkAstriPayload(form) {
            if (!form.data('astri-edit-all-applied')) {
                return form.serialize();
            }

            var changedRows = form.find('.bulk-astri-status').filter(function() {
                var status = this;
                var initial = Array.from(status.options).find(function(option) { return option.defaultSelected; }) || status.options[0];
                var row = status.closest('tr');
                var date = row.querySelector('input[type="date"]');
                var remark = row.querySelector('textarea');
                return status.value !== initial.value || date.value !== date.defaultValue || remark.value !== remark.defaultValue;
            }).closest('tr');

            if (!changedRows.length) {
                form.find('.bulk-astri-save-message').text('Belum ada perubahan untuk disimpan.');
                return null;
            }

            var dateInputs = changedRows.find('input[type="date"]');
            for (var i = 0; i < dateInputs.length; i++) {
                if (!dateInputs[i].reportValidity()) {
                    return null;
                }
            }

            return form.find(':input').filter(function() {
                var row = this.closest('tr');
                return !row || changedRows.index(row) !== -1;
            }).serialize();
        }
    </script>
    <?php
}
