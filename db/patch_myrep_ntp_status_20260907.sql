ALTER TABLE `tb_myrep_cluster`
  MODIFY COLUMN `status_current` ENUM(
    'DRAFT',
    'NTP',
    'BA OPEN',
    'BAK',
    'VALSAL',
    'WAITING HO',
    'WAITING MYREP',
    'WAITING FINANCE',
    'RELEASED',
    'DONE BATCH APPROVAL',
    'DRM',
    'RFS',
    'ATP',
    'CHECKLIST DOKUMENT',
    'DONE',
    'REJECTED',
    'HOLD'
  ) NOT NULL DEFAULT 'DRAFT';
