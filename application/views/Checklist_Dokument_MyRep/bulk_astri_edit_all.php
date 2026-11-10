<?php
function checklist_doc_render_astri_edit_all($panelId, $hasProjectOpname = false)
{
    ?>
    <details class="bulk-astri-edit-all border rounded p-3 mb-3">
        <summary class="font-weight-bold">Edit Status All</summary>
        <p class="text-muted small mt-2 mb-3">
            Centang dokumen yang ingin diubah. APPROVED dan REJECTED tidak dicentang otomatis.
            Pilih APPROVED hanya untuk dokumen yang sudah disetujui di ASTRI.
            <?= $hasProjectOpname ? 'Status khusus Project Opname diatur per dokumen.' : '' ?>
        </p>
        <div class="form-row align-items-end">
            <div class="form-group col-sm-4">
                <label for="<?= $panelId ?>-status">Status ASTRI</label>
                <select id="<?= $panelId ?>-status" class="form-control bulk-astri-all-status">
                    <option value="">Pilih status</option>
                    <option value="NY">NY</option>
                    <option value="ON REVIEW">ON REVIEW</option>
                    <option value="APPROVED">APPROVED</option>
                </select>
            </div>
            <div class="form-group col-sm-4">
                <label for="<?= $panelId ?>-date">Tanggal Submit ASTRI</label>
                <input id="<?= $panelId ?>-date" type="date" class="form-control bulk-astri-all-date" disabled>
            </div>
            <div class="form-group col-sm-auto">
                <button type="button" class="btn btn-outline-dark bulk-astri-apply-all">Terapkan ke Semua</button>
            </div>
        </div>
        <p class="text-muted small mb-0">Tanggal ini mengisi baris yang masih kosong. Pilihan NY mengosongkan tanggal pada baris yang dicentang.</p>
        <p class="bulk-astri-all-error text-danger small mb-0" role="alert"></p>
        <p class="bulk-astri-all-result text-success small mb-0" role="status"></p>
    </details>
    <?php
}

function checklist_doc_render_astri_edit_all_checkbox($docName, $currentStatus)
{
    ?>
    <label class="form-check small mt-1 mb-0 font-weight-normal">
        <input type="checkbox" class="form-check-input bulk-astri-select-row"
            aria-label="Ikut Edit Status All: <?= htmlspecialchars((string) $docName, ENT_QUOTES) ?>"
            <?= !in_array($currentStatus, ['APPROVED', 'REJECTED'], true) ? 'checked' : '' ?>>
        Ikut Edit Status All
    </label>
    <?php
}

function checklist_doc_render_astri_edit_all_script()
{
    ?>
    <script>
        $(document).on('change', '.bulk-astri-all-status', function() {
            var panel = $(this).closest('.bulk-astri-edit-all');
            var status = this.value;
            var date = panel.find('.bulk-astri-all-date');
            date.prop('disabled', status === '' || status === 'NY');
            panel.find('.bulk-astri-all-error').empty();
        });

        $(document).on('click', '.bulk-astri-apply-all', function() {
            var form = $(this).closest('form');
            var panel = $(this).closest('.bulk-astri-edit-all');
            var statusInput = panel.find('.bulk-astri-all-status');
            var dateInput = panel.find('.bulk-astri-all-date');
            var error = panel.find('.bulk-astri-all-error').empty();
            var result = panel.find('.bulk-astri-all-result').empty();
            var status = statusInput.val();
            var date = dateInput.val();
            var rows = form.find('.bulk-astri-select-row:checked').closest('tr');

            if (!status) {
                error.text('Pilih status ASTRI terlebih dahulu.');
                statusInput.trigger('focus');
                return;
            }
            if (!rows.length) {
                error.text('Centang minimal satu dokumen yang ingin diubah.');
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
            result.text('Status diterapkan ke ' + rows.length + ' dokumen yang dicentang. Klik Simpan Bulk ASTRI untuk menyimpan perubahan.');
        });

        function checklistBulkAstriPayload(form) {
            if (!form.data('astri-edit-all-applied')) {
                return form.serialize();
            }

            var error = form.find('.bulk-astri-all-error').empty();
            var changedRows = form.find('.bulk-astri-status').filter(function() {
                var status = this;
                var initial = Array.from(status.options).find(function(option) { return option.defaultSelected; }) || status.options[0];
                var row = status.closest('tr');
                var date = row.querySelector('input[type="date"]');
                var remark = row.querySelector('textarea');
                return status.value !== initial.value || date.value !== date.defaultValue || remark.value !== remark.defaultValue;
            }).closest('tr');

            if (!changedRows.length) {
                form.find('.bulk-astri-edit-all').prop('open', true);
                form.find('.bulk-astri-all-result').empty();
                error.text('Belum ada perubahan untuk disimpan.');
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
