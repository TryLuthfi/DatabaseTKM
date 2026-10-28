# Koreksi staging dan approval Saku

Fitur khusus akun **Super Admin** pada halaman detail Batch Approval MyRep.
Tombol **Koreksi Staging / Approval Saku** tersedia pada setiap status jika tabel
history siap. Form edit data biasa tetap tidak dapat mengubah staging.

## Penggunaan

1. Buka detail batch dan pilih tombol koreksi khusus.
2. Pilih staging tujuan serta aksi Saku: pertahankan, ajukan sekarang, setujui
   sekarang berdasarkan bukti, tolak, atau reset catatan sebelum pembayaran.
3. Isi alasan. Aksi Saku juga wajib menyertakan referensi bukti yang diverifikasi.
4. Simpan. Sistem memeriksa ulang data, status, dokumen dan bukti pembayaran.
   Jika data berubah atau ada usulan perubahan pending, muat ulang/selesaikan
   usulan tersebut terlebih dahulu.

Review Saku yang dicatat melalui koreksi menggunakan pelaku dan waktu aktual saat
disimpan. Tanggal/reviewer historis tidak dibuat otomatis. Metadata pembayaran
aktual, rekening/penerima, PIC dan status proyek cluster tidak diedit fitur ini.

## Validasi

- POST dengan CSRF sesi; pemeriksaan Super Admin di controller dan model.
- Lock cluster/batch, fingerprint seluruh konteks, penolakan usulan pending.
- Nomor/tanggal approval yang masih ada tidak dapat diturunkan menjadi tahap awal
  yang akan dibaca ulang sebagai Batch Approved oleh aturan canonical existing.
- Tahap setelah approval memerlukan nomor/tanggal batch dan nominal EMR positif.
  Pengajuan/persetujuan Saku memerlukan dokumen pra-finance disetujui Finance.
- Menunggu pembayaran memerlukan approval Saku tercatat. Tahap pembayaran dan
  setelahnya memerlukan tanggal, nominal positif dan bukti transfer tersimpan.
  Koreksi tidak membuat pembayaran. Bukti pembayaran menghalangi reset approval
  atau pengembalian ke tahap sebelum pembayaran.
- Tahap dokumen, Astri, PO dan invoice tetap melewati validasi kelengkapannya.
- Semua koreksi, termasuk dari Draft, disimpan sebagai history khusus dengan
  alasan, referensi bukti, nilai sebelum/sesudah, pelaku dan waktu. Ini berbeda
  dari aturan tanpa history untuk edit data biasa pada tiga status awal.
- Penulisan data dan history satu transaksi; kegagalan membatalkan keduanya.

## Status yang tidak konsisten

Approval perubahan data batch, review dokumen Finance, dan approval Saku merupakan
catatan terpisah. Label history kini menjelaskan perbedaannya. `NY` pada tahap yang
sudah melewati pengajuan Saku ditampilkan **Belum Tercatat** dengan peringatan;
tidak otomatis diubah menjadi Approved. Pada tahap sebelum pengajuan Saku, `NY`
tetap berarti **Belum Diajukan**. Approval tersimpan tidak disembunyikan hanya
karena staging lebih awal. Tanggal SLA Saku memakai catatan Saku sebenarnya;
tanggal pembayaran/Finance tidak dipakai sebagai tanggal approval Saku.

## Deployment dan pengujian

Tidak ada migrasi atau pembaruan massal batch. Memakai tabel history edit yang
sudah ada. Deploy tidak merekonsiliasi batch 15112 secara otomatis; operator harus
memilih koreksi sesuai bukti yang terverifikasi.

Tes: `batch_workflow_policy.php`, `batch_workflow_controller_test.php`,
`batch_workflow_integration.php`, `batch_workflow_views.php`,
`batch_workflow_ui.test.cjs`. Integrasi memakai database lokal sementara dengan
nama unik yang dibuat/dihapus oleh tes sendiri; tidak memakai data produksi.
CIKO memeriksa independen policy, controller, transaksi MariaDB, render PHP dan
koordinator JavaScript. Uji visual/klik browser produksi belum terverifikasi
karena koneksi alat browser tidak tersedia; hasil render/tes bukan bukti E2E.
