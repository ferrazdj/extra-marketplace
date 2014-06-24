<?php
	
	$installer = $this;
	$installer->startSetup();

	// ========================================
	// criacao dos atributo de controle no envio
	// ========================================
	$installer->run
	("
		CREATE  TABLE `query_novapontocom_shipment_error` 
		(
			`shipment_id` INT NOT NULL,
			`error` VARCHAR(255) NOT NULL,
			PRIMARY KEY (`shipment_id`)
		);
	");

	$installer->endSetup();