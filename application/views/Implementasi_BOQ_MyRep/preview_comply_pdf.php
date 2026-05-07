<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <h1 class="m-0">Preview PDF Foto Comply</h1>
                    <div class="text-muted small"><?= htmlspecialchars((string) ($cluster['cluster_name'] ?? '-')) ?></div>
                </div>
                <div>
                    <a href="<?= htmlspecialchars((string) ($pdfUrl ?? '#')) ?>" target="_blank" class="btn btn-outline-dark btn-sm">
                        <i class="fas fa-external-link-alt mr-1"></i>Buka PDF Langsung
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card shadow-sm">
                <div class="card-body p-2">
                    <iframe
                        src="<?= htmlspecialchars((string) ($pdfUrl ?? '#')) ?>"
                        title="Preview PDF Foto Comply"
                        style="width: 100%; height: 88vh; border: 0; border-radius: 8px; background: #f8fafc;">
                    </iframe>
                </div>
            </div>
        </div>
    </section>
</div>
