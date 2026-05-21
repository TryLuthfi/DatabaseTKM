-- Upsert routing ke mode CITY_ROLE berbasis mapping kota
UPDATE tb_myrep_notification_route
SET target_type='CITY_ROLE', target_user_id=NULL, target_role='SND_HO'
WHERE module_name='BAK_MyRep' AND event_name IN ('cluster_masuk','document_masuk','document_revised');

UPDATE tb_myrep_notification_route
SET target_type='CITY_ROLE', target_user_id=NULL, target_role='RFS_HO'
WHERE module_name='VALSAL_MyRep' AND event_name IN ('cluster_masuk','document_masuk','document_revised');

UPDATE tb_myrep_notification_route
SET target_type='CITY_ROLE', target_user_id=NULL, target_role='RFS_HO'
WHERE module_name='DRM_MyRep' AND event_name IN ('cluster_masuk','document_masuk','document_revised');

UPDATE tb_myrep_notification_route
SET target_type='CITY_ROLE', target_user_id=NULL, target_role='DC_HO'
WHERE module_name='Batch_Approval_MyRep' AND event_name IN ('cluster_masuk','document_masuk','document_revised');

UPDATE tb_myrep_notification_route
SET target_type='CITY_ROLE', target_user_id=NULL, target_role='DC_HO'
WHERE module_name='Monitoring_RFS_MyRep' AND event_name='claim_rfs_approved';

-- Biarkan Checklist_Dokument_MyRep tetap CLUSTER_PIC dulu (dinamis PIC cluster)
