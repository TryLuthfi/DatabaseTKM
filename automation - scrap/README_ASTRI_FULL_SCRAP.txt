ASTRI FULL SCRAP AUTOMATION
===========================

Lokasi
------
DatabaseTKM\automation - scrap

File
----
astri_full_scrap.ps1
run_astri_full_scrap_trial.bat

Cara Trial
----------
Double-click:

automation - scrap\run_astri_full_scrap_trial.bat

Default trial:

LimitClusters = 3
PageStart = 1
PageLimit = 1
DelaySeconds = 3
MaxRetries = 3

Output
------
Folder output:

automation - scrap\output

File hasil:

astri_scrap_YYYYMMDD_HHMMSS_cluster_status.csv
astri_scrap_YYYYMMDD_HHMMSS_document_detail.csv
astri_scrap_YYYYMMDD_HHMMSS.xlsx
astri_scrap_YYYYMMDD_HHMMSS.log.txt

Sheet Excel
-----------
1. Cluster_Status

Satu row per cluster.
Berisi:

- Cluster Code
- Name (Clean List)
- WO Number
- Area
- Status RFS
- Document CW ATP Status
- Document Full OPM Status
- Document RFS Status
- Document FAC Status
- Worst Document Status

2. Document_Detail

Breakdown dokumen per cluster/route.
Jika status summary route adalah CERTIFICATE SENT, detail route tersebut tidak dibuka dan ditandai:

SKIPPED_CERTIFICATE_SENT

Tujuannya supaya scraping tidak membebani ASTRI untuk cluster yang sudah selesai.

Warna
-----
Cluster_Status memakai tab/header biru.
Document_Detail memakai tab/header hijau.

Row status:

APPROVED = hijau muda
ON REVIEW = kuning
REVISION = orange
NOT UPLOADED = putih
SKIPPED_CERTIFICATE_SENT = abu-abu
PROJECT_OPNAME_NOT_FOUND = abu-abu
ROUTE_ERROR = merah muda

Cara Naik Bertahap
------------------
Jangan langsung full ribuan cluster.

Contoh:

powershell -NoProfile -ExecutionPolicy Bypass -File "automation - scrap\astri_full_scrap.ps1" -LimitClusters 10 -PageStart 1 -PageLimit 2 -DelaySeconds 5 -MaxRetries 5

Jika stabil, naikkan ke:

LimitClusters 50
LimitClusters 100
LimitClusters 500

Catatan
-------
Script membaca credential dari .env:

ASTRI_BASE_URL
ASTRI_USERNAME
ASTRI_PASSWORD
