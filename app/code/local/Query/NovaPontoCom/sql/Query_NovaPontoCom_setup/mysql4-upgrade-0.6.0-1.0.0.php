<?php
	
	$installer = $this;
	$installer->startSetup();

	$installer->run
	("
		ALTER TABLE `query_novapontocom_ticket_log` 
		ADD COLUMN `notify_customer` VARCHAR(1) NULL DEFAULT '0';
	");

	$installer->endSetup();