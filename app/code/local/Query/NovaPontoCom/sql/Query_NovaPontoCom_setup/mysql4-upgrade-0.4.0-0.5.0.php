<?php
	
	$installer = $this;
	$installer->startSetup();

	// ========================================
	// criacao da tabela de erros na importacao
	// de pedidos
	// ========================================
	$installer->run
	("
		CREATE TABLE `query_novapontocom_import_order_error`
		(
			`error_id` int(11) NOT NULL AUTO_INCREMENT,
			`order_api_id` varchar(255) DEFAULT NULL,
			`message` longtext,
			`value` double DEFAULT NULL,
			`created_at` datetime DEFAULT NULL,
			PRIMARY KEY (`error_id`)
		);
	");

	$installer->endSetup();