<?php
	
	$installer = $this;
	$installer->startSetup();

	// ========================================
	// insere referencia da ultima data de 
	// atualizacao dos tickets
	// ========================================
	$installer->run
	("
		INSERT INTO `query_novapontocom_session_config` (`code`, `value`)  VALUES ('ticket_last_update', '')
	");
	
	$installer->run
	("
		ALTER TABLE `query_novapontocom_ticket_log` 
		ADD COLUMN `api_id` VARCHAR(55) NULL DEFAULT NULL;
	");

	$installer->endSetup();