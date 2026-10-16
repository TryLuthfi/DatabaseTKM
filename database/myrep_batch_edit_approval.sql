-- Explicit additive migration. Run with the normal deployment procedure, never on page load.
-- Existing tb_myrep_cluster, tb_myrep_batch_approval and *_pic MUST use InnoDB.
CREATE TABLE IF NOT EXISTS tb_myrep_batch_edit_request (
 id_request BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
 id_batch_approval INT NOT NULL,
 id_myrep_cluster INT NOT NULL,
 pending_batch_id INT NULL,
 status VARCHAR(32) NOT NULL,
 reason TEXT NOT NULL,
 before_json LONGTEXT NOT NULL,
 after_json LONGTEXT NOT NULL,
 baseline_hash CHAR(64) NOT NULL,
 staging_at_submit VARCHAR(64) NOT NULL,
 finance_required TINYINT(1) NOT NULL DEFAULT 0,
 submitted_by INT NOT NULL,
 submitted_at DATETIME NOT NULL,
 sitac_reviewed_by INT NULL,
 finance_reviewed_by INT NULL,
 reviewed_at DATETIME NULL,
 PRIMARY KEY (id_request),
 UNIQUE KEY uq_batch_edit_pending (pending_batch_id),
 KEY ix_batch_edit_history (id_batch_approval,id_request)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS tb_myrep_batch_edit_event (
 id_event BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
 id_request BIGINT UNSIGNED NOT NULL,
 event_type VARCHAR(32) NOT NULL,
 actor_id INT NOT NULL,
 actor_name VARCHAR(255) NOT NULL,
 staging_status VARCHAR(64) NOT NULL,
 note TEXT NOT NULL,
 created_at DATETIME NOT NULL,
 PRIMARY KEY (id_event),
 KEY ix_batch_edit_event (id_request,id_event)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
