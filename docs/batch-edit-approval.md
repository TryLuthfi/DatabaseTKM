# Approval perubahan Batch MyRep

Implementasi lokal, 16 Oktober 2026. Tidak ada push/deploy/DDL otomatis ke produksi.

## Aturan bisnis

- Status tersimpan `DRAFT`, `WAITING_BATCH_APPROVAL`, `NEED_REVISE` (Reject Data Batch): edit langsung tanpa request/history edit. Resubmit revisi existing dari NEED_REVISE tetap ke WAITING_BATCH_APPROVAL dan status cluster WAITING HO.
- Interpretasi status mengikuti aturan canonical aplikasi existing: raw DRAFT/WAITING_BATCH_APPROVAL/WAITING HO/kosong pada batch existing dengan nomor atau tanggal approval Astri dianggap BATCH_APPROVED. Record yang UI-nya sudah approved tetap melalui perlindungan edit; nilai raw awal tidak menjadi celah bypass. NEED_REVISE tetap pengecualian reject data batch yang eksplisit, kecuali ada bukti pembayaran.
- Event dan staging pengajuan mencatat status canonical; fingerprint tetap berdasarkan row raw. Edit tidak mengganti nilai raw staging. Mengosongkan kedua bukti approval bila akan menurunkan status effective ke status awal ditolak agar form umum tidak menjadi jalur downgrade.
- Status lain: pemohon area/admin mengajukan perubahan; data aktif tetap. SITAC HO sesuai mapping aktif city/province/regional melakukan approve/reject dengan catatan.
- SITAC HO sesuai mapping wilayah dapat edit langsung dengan alasan dan history lengkap. Nama level Admin/Super Admin saja tidak memberi bypass SITAC.
- Satu pengajuan pending per batch. Form kedaluwarsa atau data/workflow berubah sejak pengajuan tidak boleh menimpa data terbaru. Review atas baseline berubah menutup request sebagai STALE, menyimpan event dan membuka slot pengajuan ulang.
- Jika ada bukti pembayaran (released_at, transfer_proof_file_path, nominal_release_finance > 0), atau status pasca bayar, perlindungan tetap berlaku walaupun status tersimpan kembali menjadi draft/revisi. Ini menjaga koreksi transaksi pasca bayar tidak lolos tanpa review.
- Koreksi pasca bayar terhadap hp_donasi, nominal_pengajuan_area, nominal_nego_emr, nominal_per_homepass, bank_name, bank_account_number, recipient_name/phone/position/period, atau PIC memerlukan review Finance setelah SITAC. Pengajuan oleh SITAC langsung menunggu Finance. Reviewer Finance berbeda dari pemohon dan reviewer SITAC. Perubahan catatan/lokasi/Wifi/tanggal saja tetap mengikuti approval SITAC biasa.
- Nominal pembayaran aktual `nominal_release_finance`, `released_at`, bukti transfer dan metadata workflow dikeluarkan dari whitelist edit umum: nilai historis pembayaran tidak ditimpa. Perubahan field pengajuan setelah Finance approve adalah koreksi data pengajuan, bukan pembayaran ulang atau pengesahan ulang dokumen.
- Dropdown staging hanya menampilkan kondisi saat ini. Posted staging diabaikan; transisi melalui alur existing terpisah.
- Edit umum tidak menjalankan validasi kelengkapan dokumen untuk naik staging, karena edit tidak mengubah staging. Gate dokumen tetap pada endpoint transisi existing; validasi data lengkap untuk resubmit NEED_REVISE tetap dipertahankan. Koreksi pada batch lama/import tidak tersumbat oleh dokumen yang belum lengkap dan tidak mengesahkan ulang dokumen.
- Nominal Approval EMR adalah field tersendiri, ditampilkan dengan nilai tersimpan dan tidak otomatis disamakan dengan Nominal Donasi ketika edit catatan/field lain. Bila form lama tidak mengirim field EMR/Astri, nilai existing dipertahankan. Tanggal approval Astri dari kontrol date yang hari-nya sama mempertahankan jam datetime existing; mengganti hari mengikuti normalisasi tanggal aplikasi.
- Import tidak dapat overwrite batch protected; pengguna harus memakai pengajuan perubahan.

## Migrasi dan operasi

Jalankan `database/myrep_batch_edit_approval.sql` melalui prosedur migrasi database pada target yang sudah diverifikasi. Migrasi hanya menambah dua tabel InnoDB: request (snapshot before/after) dan append-only event. Tidak mengubah data bisnis/tabel existing. Tabel cluster, batch dan PIC existing wajib InnoDB; layanan menolak menulis bila prerequisite belum siap. Tidak ada DDL on-request.

History dan review berada di halaman detail batch, panel **Approval Perubahan & History Data Batch**. Form pada daftar maupun detail menggunakan endpoint yang sama. Review endpoint memakai VIEW pada lapisan akses halaman agar Finance dapat mengaksesnya; otorisasi mutasi memakai mapping reviewer exact di model, bukan permission VIEW saja. Kedua endpoint wajib POST dan token anti-CSRF acak yang terikat sesi, meskipun CSRF global aplikasi belum diaktifkan.

Lock transaksi: cluster lalu batch lalu request. Unique nullable `pending_batch_id` mencegah dua pending. Fingerprint seluruh row cluster/batch dan PIC melindungi dari perubahan workflow/data. Apply dan audit berada dalam satu transaksi; event direct/submit/approve/reject/stale memuat pelaku, waktu, staging dan catatan. Snapshot lokasi dan PIC dinormalisasi untuk comparison dan hanya field whitelist yang diterapkan.

## Verifikasi

`D:/XAMPP/php/php.exe tests/batch_edit_integration.php`

Skrip menggunakan CI query builder dan MariaDB aktual; autentikasi .env dibaca opak tanpa dicetak. Target hanya schema lokal `zeyn_batch_edit_test_20261016`; hostname wajib loopback dan schema wajib prefix `zeyn_batch_edit_test_`. Schema perlu tersedia dengan struktur tabel aplikasi kosong. Skrip menambah tabel migrasi dan menghapus fixture di tujuh tabel **hanya schema test**, menyisipkan baris sintetis; tidak boleh diarahkan ke database aplikasi. Dua proses PHP menguji submit bersamaan, trigger sintetis menguji rollback audit + apply atomik. Tidak menyalin data bisnis produksi.

Fixture akhir browser: cluster 990101, batch 990201, pending request area 990002; reviewer SITAC 990001 (nik QA_SITAC), SITAC kedua 990005; Finance 990003, unmapped 990004. Semua nama/rekening/telepon sintetis. Lingkungan sesi HTTP QA dikelola ZEYN terpisah dari aplikasi utama.

## Hasil akhir lokal — 16 Oktober 2026

- ZEYN menjalankan suite final: **61/61** assertion MariaDB aktual PASS, delapan file PHP lint PASS, dan pemeriksaan whitespace diff PASS.
- CIKO melakukan QC independen melalui browser aktual pada aplikasi QA terisolasi: daftar/detail, submit area, review SITAC dan Finance non-admin, edit langsung SITAC dengan history, preservation nominal EMR berbeda dan datetime approval, panel history untuk viewer tanpa edit, serta viewport 1440/768/390/320. Temuan mayor posisi panel di luar content wrapper dan kelengkapan field Astri pada form daftar diperbaiki lalu diverifikasi CLOSED.
- Pemeriksaan HTTP mencakup POST-only, anti-CSRF, ID request tidak sesuai, pemisahan role review, dan status canonical yang sudah approved meskipun raw status awal. Bukti independen: `C:/Users/TKM/Desktop/AI AGENT/outputs/batch-edit-qa/ciko-report.md`. Status akhir CIKO **PASS**, tidak ada mayor/kritis terbuka pada cakupan yang diuji.
- ZEYN menerapkan migrasi additive ke database aplikasi **LOKAL loopback** pada 16 Oktober 2026. Kedua tabel baru berhasil tersedia dengan nol baris; jumlah baris tiga tabel bisnis existing tidak berubah. Ini mengaktifkan fitur pada kode aplikasi lokal, tanpa deployment/migrasi produksi atau push Git.

File implementasi: `application/controllers/Batch_Approval_MyRep.php`, `application/models/MBatch_Approval_MyRep.php`, `application/models/MBatch_edit_approval.php`, `application/libraries/Batch_edit_policy.php`, `application/views/Batch_Approval_MyRep/index.php`, `detail.php`, `edit_history.php`, `database/myrep_batch_edit_approval.sql`, `tests/batch_edit_integration.php`, dan dokumen ini.

Batasan: alur upload/import CSV penuh tidak diuji E2E; guard transaksi import protected ditinjau pada kode. Approval dokumen atau transaksi downstream tidak otomatis dinilai ulang. Pemberitahuan email/Telegram aplikasi baru tidak ditambahkan. Tidak ada jadwal background. Pengujian memakai data sintetis terisolasi; data bisnis produksi tidak diubah.
