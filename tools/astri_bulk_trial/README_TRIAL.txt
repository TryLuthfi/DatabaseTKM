ASTRI BULK TRIAL - 3 CLUSTERS
============================

Tujuan
------
Trial ini mengambil 3 cluster awal dari halaman ASTRI CW ATP, lalu mengecek route:

1. ATP CLUSTER - CW ATP
2. ATP CLUSTER - FULL OPM
3. ATP CLUSTER - RFS
4. PROJECT OPNAME - CLUSTER
5. ATP SUBFEEDER - CW ATP
6. ATP SUBFEEDER - FULL OPM
7. ATP SUBFEEDER - RFS

Cara Jalan
----------
Double-click:

tools\astri_bulk_trial\run_astri_bulk_trial.bat

Output
------
Hasil akan dibuat di folder:

tmp_astri_bulk_trial

File utama:

astri_bulk_trial_YYYYMMDD_HHMMSS.csv
astri_bulk_trial_YYYYMMDD_HHMMSS.xlsx
astri_bulk_trial_YYYYMMDD_HHMMSS.log.txt

Konfigurasi Aman
----------------
Default trial:

LimitClusters = 3
DelaySeconds = 3
MaxRetries = 3

Artinya script hanya mengambil 3 cluster awal, memberi jeda 3 detik antar request, dan mencoba ulang request gagal sampai 3x.

Jika muncul "curl (56) Recv failure: Connection was reset", itu biasanya ASTRI/server/jaringan memutus response.
Naikkan DelaySeconds menjadi 5-10 jika kondisi ASTRI sedang lambat.

Contoh manual:

powershell -NoProfile -ExecutionPolicy Bypass -File tools\astri_bulk_trial\astri_bulk_trial.ps1 -LimitClusters 3 -DelaySeconds 5 -MaxRetries 5

Credential
----------
Script membaca credential dari file .env:

ASTRI_BASE_URL
ASTRI_USERNAME
ASTRI_PASSWORD

Catatan
-------
Untuk produksi/full data, jangan langsung ubah LimitClusters menjadi angka besar.
Naikkan bertahap: 10, 50, 100, 500, lalu ukur durasi dan error rate.
