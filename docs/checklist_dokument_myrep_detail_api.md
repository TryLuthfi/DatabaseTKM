# Dokumentasi API Checklist Dokument MyRep Detail

Dokumen ini merangkum endpoint yang dipakai halaman:

```text
GET /Checklist_Dokument_MyRep/detail/11537
```

Project ini masih memakai session web CodeIgniter, jadi endpoint di bawah bukan Bearer-token REST API. Untuk dicoba di Postman atau Insomnia, login dulu lalu gunakan cookie session yang sama.

## Base URL

Local XAMPP biasanya:

```text
http://localhost/DatabaseTKM/
```

Jika domain/server berbeda, ganti base URL mengikuti host aplikasi.

## Authentication

### Login

```http
POST /Auth
Content-Type: application/x-www-form-urlencoded
```

Body:

| Key | Value |
| --- | --- |
| `username` | username aplikasi |
| `pass` | password aplikasi |

Response login berupa redirect/HTML. Simpan cookie dari response, terutama:

```text
ci_session=<nilai_cookie>
```

Untuk endpoint AJAX, tambahkan header:

```text
X-Requested-With: XMLHttpRequest
Cookie: ci_session=<nilai_cookie>
```

Catatan: CSRF di config saat ini `FALSE`, jadi tidak perlu token CSRF.

## Common JSON Response

Mayoritas endpoint POST/AJAX mengembalikan:

```json
{
  "status": true,
  "message": "Pesan hasil proses",
  "redirect_url": "http://localhost/DatabaseTKM/Checklist_Dokument_MyRep/detail/11537"
}
```

Jika session habis:

```json
{
  "status": false,
  "message": "Session habis. Silakan login ulang.",
  "redirect_url": ""
}
```

## 1. Detail Checklist By Cluster Code

```http
POST /Checklist_Dokument_MyRep/apiDetailByClusterCode
Content-Type: application/json
```

Kegunaan: mengambil data detail checklist dokumen secara general dengan input `cluster_code`. Endpoint ini akan resolve `cluster_code` ke `cluster_id` RFS, memastikan package checklist tersedia, lalu mengembalikan data cluster dan semua tab dokumen.

Body JSON:

```json
{
  "cluster_code": "CLUSTER-CODE-ANDA"
}
```

Alternatif `x-www-form-urlencoded`:

| Key | Wajib | Contoh |
| --- | --- | --- |
| `cluster_code` | Ya | `CLUSTER-CODE-ANDA` |

Contoh Postman:

```http
POST {{base_url}}Checklist_Dokument_MyRep/apiDetailByClusterCode
Cookie: ci_session={{ci_session}}
X-Requested-With: XMLHttpRequest
Content-Type: application/json

{
  "cluster_code": "CLUSTER-CODE-ANDA"
}
```

Response sukses:

```json
{
  "status": true,
  "message": "Data checklist dokumen berhasil diambil.",
  "cluster_code": "CLUSTER-CODE-ANDA",
  "cluster": {
    "id_cluster": 11537,
    "cluster_code": "CLUSTER-CODE-ANDA",
    "cluster_name": "Nama Cluster",
    "city_name": "KOTA"
  },
  "scope_tabs": {
    "CLUSTER": [],
    "SUBFEEDER": []
  },
  "detail_url": "http://localhost/DatabaseTKM/Checklist_Dokument_MyRep/detail/11537"
}
```

Response gagal jika code tidak ditemukan atau user tidak punya akses kota:

```json
{
  "status": false,
  "message": "Cluster code tidak ditemukan atau tidak bisa diakses user ini.",
  "cluster_code": "CLUSTER-CODE-ANDA",
  "cluster": null,
  "scope_tabs": []
}
```

Catatan penting:

- `scope_tabs.CLUSTER[].items[]` dan `scope_tabs.SUBFEEDER[].items[]` berisi ID yang dibutuhkan untuk action berikutnya, seperti `id_doc_package`, `id_doc_item`, dan `id_doc_file`.
- Untuk upload/approve/reject, endpoint lama masih memakai `cluster_id` karena struktur tabel file/package checklist memang berbasis ID internal.

## 2. Detail Halaman Cluster

```http
GET /Checklist_Dokument_MyRep/detail/11537
```

Kegunaan: membuka halaman detail checklist dokumen cluster.

Response: HTML.

Postman:

```http
GET {{base_url}}Checklist_Dokument_MyRep/detail/11537
Cookie: ci_session={{ci_session}}
```

## 3. History Dokumen

```http
GET /Checklist_Dokument_MyRep/documentHistoryData/{id_doc_file}
```

Kegunaan: mengambil history upload/reject/approve untuk satu file dokumen.

Contoh:

```http
GET {{base_url}}Checklist_Dokument_MyRep/documentHistoryData/123
Cookie: ci_session={{ci_session}}
X-Requested-With: XMLHttpRequest
```

Response sukses:

```json
{
  "status": true,
  "message": "",
  "history": [
    {
      "action_type": "UPLOADED",
      "action_at": "2026-07-22 10:00:00",
      "nama_user": "Nama User",
      "file_name": "DOC_11537_1_2_CONTOH.pdf",
      "remark": "Catatan"
    }
  ]
}
```

## 4. Simpan Timeline ATP

```http
POST /Checklist_Dokument_MyRep/saveTimeline
Content-Type: application/x-www-form-urlencoded
```

Kegunaan: update realisasi tanggal ATP cluster.

Body:

| Key | Wajib | Contoh | Keterangan |
| --- | --- | --- | --- |
| `cluster_id` | Ya | `11537` | ID cluster |
| `actual_atp_date` | Tidak | `2026-07-22` | Format `YYYY-MM-DD`; kosong berarti null |

Contoh:

```http
POST {{base_url}}Checklist_Dokument_MyRep/saveTimeline
Cookie: ci_session={{ci_session}}
X-Requested-With: XMLHttpRequest
Content-Type: application/x-www-form-urlencoded

cluster_id=11537&actual_atp_date=2026-07-22
```

## 5. Upload Dokumen Tunggal

```http
POST /Checklist_Dokument_MyRep/uploadDocument
Content-Type: multipart/form-data
```

Kegunaan: upload satu dokumen checklist atau menandai dokumen tidak dibutuhkan.

Body form-data:

| Key | Type | Wajib | Contoh | Keterangan |
| --- | --- | --- | --- | --- |
| `cluster_id` | Text | Ya | `11537` | ID cluster |
| `id_doc_package` | Text | Ya | `10` | ID package dokumen |
| `id_doc_item` | Text | Ya | `25` | ID item dokumen |
| `doc_name` | Text | Ya | `BAST ATP` | Nama dokumen |
| `file` | File | Ya, kecuali `is_document_not_required=1` | `dokumen.pdf` | Max 100 MB |
| `remark` | Text | Tidak | `submit awal` | Catatan upload |
| `is_document_not_required` | Text | Tidak | `1` | Isi `1` bila dokumen tidak diperlukan |

Contoh upload file:

```http
POST {{base_url}}Checklist_Dokument_MyRep/uploadDocument
Cookie: ci_session={{ci_session}}
X-Requested-With: XMLHttpRequest
Content-Type: multipart/form-data

cluster_id: 11537
id_doc_package: 10
id_doc_item: 25
doc_name: BAST ATP
file: <pilih file>
remark: submit awal
```

Contoh tanpa dokumen:

```http
POST {{base_url}}Checklist_Dokument_MyRep/uploadDocument
Cookie: ci_session={{ci_session}}
X-Requested-With: XMLHttpRequest
Content-Type: multipart/form-data

cluster_id: 11537
id_doc_package: 10
id_doc_item: 25
doc_name: BAST ATP
is_document_not_required: 1
remark: tidak dibutuhkan untuk cluster ini
```

## 6. Bulk Upload Dokumen

```http
POST /Checklist_Dokument_MyRep/bulkUploadDocuments
Content-Type: multipart/form-data
```

Kegunaan: submit beberapa item dokumen dalam satu package.

Body form-data:

| Key | Type | Wajib | Contoh | Keterangan |
| --- | --- | --- | --- | --- |
| `cluster_id` | Text | Ya | `11537` | ID cluster |
| `id_doc_package` | Text | Ya | `10` | ID package |
| `id_doc_item[]` | Text | Ya | `25` | Ulangi untuk tiap item |
| `doc_name[]` | Text | Ya | `BAST ATP` | Urutan mengikuti `id_doc_item[]` |
| `bulk_file_{id_doc_item}` | File | Ya, kecuali item masuk `bulk_not_required[]` | `dokumen.pdf` | Nama key harus mengandung ID item |
| `bulk_not_required[]` | Text | Tidak | `25` | Isi ID item yang tidak butuh dokumen |

Contoh 2 dokumen:

```text
cluster_id: 11537
id_doc_package: 10
id_doc_item[]: 25
doc_name[]: BAST ATP
bulk_file_25: <pilih file>
id_doc_item[]: 26
doc_name[]: Foto Evidence
bulk_file_26: <pilih file>
```

Contoh 1 file dan 1 tidak dibutuhkan:

```text
cluster_id: 11537
id_doc_package: 10
id_doc_item[]: 25
doc_name[]: BAST ATP
bulk_file_25: <pilih file>
id_doc_item[]: 26
doc_name[]: Foto Evidence
bulk_not_required[]: 26
```

## 7. Approve Semua Dokumen Dalam Package

```http
POST /Checklist_Dokument_MyRep/approveAllDocuments
Content-Type: application/x-www-form-urlencoded
```

Kegunaan: approve semua dokumen dalam package yang statusnya `UPLOADED` atau `REJECTED`.

Syarat akses: user `HO` atau `Super Admin`.

Body:

| Key | Wajib | Contoh |
| --- | --- | --- |
| `cluster_id` | Ya | `11537` |
| `id_doc_package` | Ya | `10` |

Contoh:

```http
POST {{base_url}}Checklist_Dokument_MyRep/approveAllDocuments
Cookie: ci_session={{ci_session}}
X-Requested-With: XMLHttpRequest
Content-Type: application/x-www-form-urlencoded

cluster_id=11537&id_doc_package=10
```

## 8. Approve Dokumen

```http
POST /Checklist_Dokument_MyRep/approveDocument
Content-Type: application/x-www-form-urlencoded
```

Syarat akses: user `HO` atau `Super Admin`.

Body:

| Key | Wajib | Contoh |
| --- | --- | --- |
| `cluster_id` | Ya | `11537` |
| `id_doc_file` | Ya | `123` |
| `remark` | Tidak | `OK` |

Contoh:

```http
POST {{base_url}}Checklist_Dokument_MyRep/approveDocument
Cookie: ci_session={{ci_session}}
X-Requested-With: XMLHttpRequest
Content-Type: application/x-www-form-urlencoded

cluster_id=11537&id_doc_file=123&remark=OK
```

## 9. Reject Dokumen

```http
POST /Checklist_Dokument_MyRep/rejectDocument
Content-Type: application/x-www-form-urlencoded
```

Syarat akses: user `HO` atau `Super Admin`.

Body:

| Key | Wajib | Contoh |
| --- | --- | --- |
| `cluster_id` | Ya | `11537` |
| `id_doc_file` | Ya | `123` |
| `remark` | Ya | `File belum sesuai` |

Contoh:

```http
POST {{base_url}}Checklist_Dokument_MyRep/rejectDocument
Cookie: ci_session={{ci_session}}
X-Requested-With: XMLHttpRequest
Content-Type: application/x-www-form-urlencoded

cluster_id=11537&id_doc_file=123&remark=File%20belum%20sesuai
```

## 10. Update Status ASTRI Dokumen

```http
POST /Checklist_Dokument_MyRep/saveAstriStatus
Content-Type: application/x-www-form-urlencoded
```

Syarat akses: user `HO` atau `Super Admin`.

Valid status:

```text
NY
ON REVIEW
WAITING WASPANG
WAITING PLANNING
WAITING TL
WAITING LOGISTIK
REJECTED
APPROVED
```

Catatan:

- Status selain `NY` wajib isi `astri_submitted_date`.
- Dokumen harus `APPROVED` internal sebelum status ASTRI selain `NY`.
- Status `WAITING WASPANG`, `WAITING PLANNING`, `WAITING TL`, `WAITING LOGISTIK` hanya untuk special Project Opname.

Body:

| Key | Wajib | Contoh |
| --- | --- | --- |
| `cluster_id` | Ya | `11537` |
| `id_doc_file` | Ya | `123` |
| `astri_status` | Ya | `ON REVIEW` |
| `astri_submitted_date` | Wajib jika status bukan `NY` | `2026-07-22` |
| `astri_remark` | Tidak | `submit ke ASTRI` |

Contoh:

```http
POST {{base_url}}Checklist_Dokument_MyRep/saveAstriStatus
Cookie: ci_session={{ci_session}}
X-Requested-With: XMLHttpRequest
Content-Type: application/x-www-form-urlencoded

cluster_id=11537&id_doc_file=123&astri_status=ON%20REVIEW&astri_submitted_date=2026-07-22&astri_remark=submit%20ke%20ASTRI
```

## 11. Bulk Update Status ASTRI

```http
POST /Checklist_Dokument_MyRep/bulkEditAstriStatus
Content-Type: application/x-www-form-urlencoded
```

Syarat akses: user `HO` atau `Super Admin`.

Body memakai array keyed by `id_doc_file`.

Contoh:

```http
POST {{base_url}}Checklist_Dokument_MyRep/bulkEditAstriStatus
Cookie: ci_session={{ci_session}}
X-Requested-With: XMLHttpRequest
Content-Type: application/x-www-form-urlencoded

cluster_id=11537
&id_doc_file[]=123
&id_doc_file[]=124
&astri_status[123]=ON%20REVIEW
&astri_submitted_date[123]=2026-07-22
&astri_remark[123]=submit%20dokumen%20123
&astri_status[124]=NY
&astri_submitted_date[124]=
&astri_remark[124]=reset
```

## 12. Preview File Dokumen

```http
GET /Checklist_Dokument_MyRep/previewDocument/{id_doc_file}
```

Kegunaan: preview file inline di browser/Postman.

Contoh:

```http
GET {{base_url}}Checklist_Dokument_MyRep/previewDocument/123
Cookie: ci_session={{ci_session}}
```

Response: binary file dengan `Content-Disposition: inline`.

## 13. Download File Dokumen

```http
GET /Checklist_Dokument_MyRep/downloadDocument/{id_doc_file}
```

Kegunaan: download file dokumen.

Contoh:

```http
GET {{base_url}}Checklist_Dokument_MyRep/downloadDocument/123
Cookie: ci_session={{ci_session}}
```

Response: binary download.

## 14. Download Format Dokumen

```http
GET /Checklist_Dokument_MyRep/downloadDocumentFormat/{id_doc_item}
```

Kegunaan: download template/format dokumen untuk item checklist tertentu, jika tersedia.

Contoh:

```http
GET {{base_url}}Checklist_Dokument_MyRep/downloadDocumentFormat/25
Cookie: ci_session={{ci_session}}
```

## Cara Ambil ID Package, Item, dan File

Halaman detail `GET /Checklist_Dokument_MyRep/detail/11537` merender ID ke HTML button/form. Cara paling cepat:

1. Buka halaman detail di browser.
2. Inspect tombol upload/approve/reject.
3. Ambil atribut seperti:
   - `data-package-id`
   - `data-item-id`
   - `data-file-id`
   - `data-cluster-id`

Untuk Postman/Insomnia, nilai `cluster_id` untuk kasus ini adalah `11537`.
