<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/

//Auth
// $route['Auth'] = 'login';
// $route['Auth/logout'] = 'logout';

//Dashboard
// $route['Dashboard'] = 'dashboard';

$route['default_controller'] = 'Auth';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;
$route['backup'] = 'backup/index';
$route['backup/create_backup'] = 'backup/create_backup';
$route['backup/download_backup/(:any)'] = 'backup/download_backup/$1';
$route['Backup/import_local_backup'] = 'backup/import_local_backup';
$route['Backup/upload_local_backup_import'] = 'backup/upload_local_backup_import';
$route['Backup/import_vps_to_local'] = 'backup/import_vps_to_local';

$route['StockOpname/periode/(:num)'] = 'StockOpname/periode/$1';
$route['StockOpname/periode/(:num)/lokasi/(:num)'] = 'StockOpname/periode/$1/$2';
$route['StockOpname/revamp'] = 'StockOpname/revamp';
$route['StockOpname/revamp/periode/(:num)'] = 'StockOpname/periode_revamp/$1';
$route['StockOpname/revamp/periode/(:num)/lokasi/(:num)'] = 'StockOpname/periode_revamp/$1/$2';
$route['StockOpname/generateBA/(:num)/(:num)'] = 'StockOpname/generateBA/$1/$2';
$route['StockOpname/print_ba/(:num)/(:num)'] = 'StockOpname/print_ba/$1/$2';
$route['StockOpname/uploadSignedBA'] = 'StockOpname/uploadSignedBA';
$route['StockOpname/approveBA'] = 'StockOpname/approveBA';
$route['Dashboard_Logistik_Stok/getReportStokByData'] = 'Dashboard_Logistik_Stok/getReportStokByData';
$route['Dashboard_Logistik_Stok/revamp'] = 'Dashboard_Logistik_Stok/revamp';
$route['maintenance-preview'] = 'MaintenancePreview/index';
$route['SuperAdmin_MyRep_Config'] = 'SuperAdmin_MyRep_Config/index';
$route['SuperAdmin_MyRep_Config/saveAccessMatrix'] = 'SuperAdmin_MyRep_Config/saveAccessMatrix';
$route['SuperAdmin_MyRep_Config/saveApprovalMatrix'] = 'SuperAdmin_MyRep_Config/saveAccessMatrix';
$route['SuperAdmin_MyRep_Config/savePermission'] = 'SuperAdmin_MyRep_Config/savePermission';
$route['SuperAdmin_MyRep_Config/saveNotificationRoute'] = 'SuperAdmin_MyRep_Config/saveNotificationRoute';
$route['SuperAdmin_MyRep_Config/saveNotificationRouteBulk'] = 'SuperAdmin_MyRep_Config/saveNotificationRouteBulk';
$route['SuperAdmin_MyRep_Config/deletePermission/(:num)'] = 'SuperAdmin_MyRep_Config/deletePermission/$1';
$route['SuperAdmin_MyRep_Config/deleteNotificationRoute/(:num)'] = 'SuperAdmin_MyRep_Config/deleteNotificationRoute/$1';
$route['SuperAdmin_MyRep_CityMapping'] = 'SuperAdmin_MyRep_CityMapping/index';
$route['SuperAdmin_MyRep_CityMapping/saveBulk'] = 'SuperAdmin_MyRep_CityMapping/saveBulk';
$route['SuperAdmin_MyRep_CityMapping/userOptions'] = 'SuperAdmin_MyRep_CityMapping/userOptions';
$route['MyRep_Email_Queue/processRejectQueue'] = 'MyRep_Email_Queue/processRejectQueue';
$route['Checklist_Dokument_MyRep/exportItemRefreshData'] = 'Checklist_Dokument_MyRep/exportItemRefreshData';
$route['Checklist_Dokument_MyRep/refreshitemdata'] = 'Checklist_Dokument_MyRep/refreshitemdata';
$route['Checklist_Dokument_MyRep/refreshPurchaseOrderData'] = 'Checklist_Dokument_MyRep/refreshPurchaseOrderData';
$route['checklist_dokument_myrep/exportItemRefreshData'] = 'Checklist_Dokument_MyRep/exportItemRefreshData';
$route['checklist_dokument_myrep/refreshitemdata'] = 'Checklist_Dokument_MyRep/refreshitemdata';
$route['checklist_dokument_myrep/refreshpurchaseorderdata'] = 'Checklist_Dokument_MyRep/refreshPurchaseOrderData';
$route['PO_EMR_MyRep'] = 'PO_EMR_Myrep/index';
$route['PO_EMR_MyRep/(:any)'] = 'PO_EMR_Myrep/$1';
$route['PO_Breakdown'] = 'PO_Breakdown/index';
$route['po_emr_myrep'] = 'PO_EMR_Myrep/index';
$route['po_emr_myrep/(:any)'] = 'PO_EMR_Myrep/$1';
$route['MyRepublik_Project/refreshListClusterData'] = 'MyRepublik_Project/refreshListClusterData';
$route['myrepublik_project/refreshlistclusterdata'] = 'MyRepublik_Project/refreshListClusterData';
$route['api/mobile/auth/login'] = 'api/Mobile/login';
$route['api/mobile/auth/logout'] = 'api/Mobile/logout';
$route['api/mobile/me'] = 'api/Mobile/me';
$route['api/mobile/myrep/dashboard'] = 'api/Mobile/dashboard';
$route['api/mobile/myrep/filters'] = 'api/Mobile/filters';
$route['api/mobile/myrep/checklists'] = 'api/Mobile/checklists';
$route['api/mobile/myrep/checklists/(:num)'] = 'api/Mobile/checklist/$1';
$route['api/mobile/myrep/documents/upload'] = 'api/Mobile/uploadChecklistDocument';
$route['api/mobile/myrep/documents/approve'] = 'api/Mobile/approveChecklistDocument';
$route['api/mobile/myrep/documents/reject'] = 'api/Mobile/rejectChecklistDocument';
$route['api/mobile/myrep/documents/astri'] = 'api/Mobile/updateChecklistAstri';
$route['api/mobile/myrep/timeline'] = 'api/Mobile/updateChecklistTimeline';
$route['api/mobile/myrep/bak/filters'] = 'api/Mobile/bakFilters';
$route['api/mobile/myrep/bak/clusters'] = 'api/Mobile/bakClusters';
$route['api/mobile/myrep/bak/clusters/(:num)'] = 'api/Mobile/bakCluster/$1';
$route['api/mobile/myrep/bak/documents/upload'] = 'api/Mobile/uploadBakDocument';
$route['api/mobile/myrep/bak/documents/approve'] = 'api/Mobile/approveBakDocument';
$route['api/mobile/myrep/bak/documents/reject'] = 'api/Mobile/rejectBakDocument';
$route['api/mobile/myrep/bak/documents/approve-all'] = 'api/Mobile/approveAllBakDocuments';
$route['api/mobile/myrep/valsal/filters'] = 'api/Mobile/valsalFilters';
$route['api/mobile/myrep/valsal/clusters'] = 'api/Mobile/valsalClusters';
$route['api/mobile/myrep/valsal/clusters/(:num)'] = 'api/Mobile/valsalCluster/$1';
$route['api/mobile/myrep/valsal/documents/upload'] = 'api/Mobile/uploadValsalDocument';
$route['api/mobile/myrep/valsal/documents/approve'] = 'api/Mobile/approveValsalDocument';
$route['api/mobile/myrep/valsal/documents/reject'] = 'api/Mobile/rejectValsalDocument';
$route['api/mobile/myrep/valsal/documents/approve-all'] = 'api/Mobile/approveAllValsalDocuments';
$route['api/mobile/myrep/post-donasi/filters'] = 'api/Mobile/postDonasiFilters';
$route['api/mobile/myrep/post-donasi/clusters'] = 'api/Mobile/postDonasiClusters';
$route['api/mobile/myrep/post-donasi/clusters/(:num)'] = 'api/Mobile/postDonasiCluster/$1';
$route['api/mobile/myrep/post-donasi/documents/upload'] = 'api/Mobile/uploadPostDonasiDocument';
$route['api/mobile/myrep/post-donasi/documents/approve'] = 'api/Mobile/approvePostDonasiDocument';
$route['api/mobile/myrep/post-donasi/documents/reject'] = 'api/Mobile/rejectPostDonasiDocument';
$route['api/mobile/myrep/post-donasi/documents/approve-all'] = 'api/Mobile/approveAllPostDonasiDocuments';
$route['api/mobile/myrep/batch/filters'] = 'api/Mobile/batchFilters';
$route['api/mobile/myrep/batch/clusters'] = 'api/Mobile/batchClusters';
$route['api/mobile/myrep/batch/clusters/(:num)'] = 'api/Mobile/batchCluster/$1';
$route['api/mobile/myrep/batch/documents/upload'] = 'api/Mobile/uploadBatchDocument';
$route['api/mobile/myrep/batch/documents/approve'] = 'api/Mobile/approveBatchDocument';
$route['api/mobile/myrep/batch/documents/reject'] = 'api/Mobile/rejectBatchDocument';
$route['api/mobile/myrep/drm/filters'] = 'api/Mobile/drmFilters';
$route['api/mobile/myrep/drm/clusters'] = 'api/Mobile/drmClusters';
$route['api/mobile/myrep/drm/clusters/(:num)'] = 'api/Mobile/drmCluster/$1';
$route['api/mobile/myrep/drm/documents/upload'] = 'api/Mobile/uploadDrmDocument';
$route['api/mobile/myrep/drm/documents/approve'] = 'api/Mobile/approveDrmDocument';
$route['api/mobile/myrep/drm/documents/reject'] = 'api/Mobile/rejectDrmDocument';
$route['api/mobile/myrep/atp/filters'] = 'api/Mobile/atpFilters';
$route['api/mobile/myrep/atp/clusters'] = 'api/Mobile/atpClusters';
$route['api/mobile/myrep/atp/clusters/(:num)'] = 'api/Mobile/atpCluster/$1';
$route['api/mobile/myrep/atp/update'] = 'api/Mobile/updateAtpCluster';
$route['api/mobile/myrep/atp/documents/upload'] = 'api/Mobile/uploadAtpDocument';
