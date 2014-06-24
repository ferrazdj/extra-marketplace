<?php
	
	$installer = $this;
	$installer->startSetup();

	// ========================================
	// criacao das tabelas no banco de dados
	// ========================================
	$installer->run
	("
		ALTER TABLE `query_novapontocom_ticket` 
		ADD COLUMN `type` VARCHAR(45) NULL DEFAULT NULL AFTER `order_id`;
	");
	
	$installer->endSetup();