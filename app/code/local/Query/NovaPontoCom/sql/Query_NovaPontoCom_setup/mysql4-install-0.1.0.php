<?php
	
	$installer = $this;
	$installer->startSetup();

	/*
	$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

	$setup->addAttribute('catalog_product', 'volume_width', array
	(
		'position' 		=> 1,
		'required'		=> 0,
		'label' 		=> 'Width (cm)',
		'type' 			=> 'int',
		'input'			=> 'text',
		'apply_to'		=> 'simple,bundle,grouped,configurable'
	));
	
	$setup->addAttribute('catalog_product', 'volume_height', array
	(
		'position' 		=> 1,
		'required'		=> 0,
		'label' 		=> 'Height (cm)',
		'type' 			=> 'int',
		'input'			=> 'text',
		'apply_to'		=> 'simple,bundle,grouped,configurable'
	));
	
	$setup->addAttribute('catalog_product', 'volume_length', array
	(
		'position' 		=> 1,
		'required'		=> 0,
		'label' 		=> 'Length (cm)',
		'type' 			=> 'int',
		'input'			=> 'text',
		'apply_to'		=> 'simple,bundle,grouped,configurable'
	));
	*/
	
	// ========================================
	// criacao das tabelas no banco de dados
	// ========================================
	$installer->run
	("
		DROP TABLE IF EXISTS `query_novapontocom_session_config`;
		CREATE TABLE `query_novapontocom_session_config`
		(
			`config_id` int(11) NOT NULL AUTO_INCREMENT,
			`code` varchar(65) DEFAULT NULL,
			`value` varchar(255) DEFAULT NULL,
			PRIMARY KEY (`config_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=latin1;

	");

	$installer->run("INSERT INTO `query_novapontocom_session_config` (code, value) VALUES ('status', 'stopped');");
	$installer->run("INSERT INTO `query_novapontocom_session_config` (code, value) VALUES ('product_last_sync', '0');");
	$installer->run("INSERT INTO `query_novapontocom_session_config` (code, value) VALUES ('order_last_sync', '0');");

	$installer->run
	("
		DROP TABLE IF EXISTS `query_novapontocom_config`;
		CREATE TABLE `query_novapontocom_config`
		(
			`config_id` int(11) NOT NULL AUTO_INCREMENT,
			`name` varchar(50) DEFAULT NULL,
			`value` varchar(255) DEFAULT NULL,
			PRIMARY KEY (`config_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=latin1;
	");

	$installer->run
	("
		DROP TABLE IF EXISTS `query_novapontocom_log`;
		CREATE TABLE `query_novapontocom_log`
		(
			`log_id` int(11) NOT NULL AUTO_INCREMENT,
			`type` varchar(60) DEFAULT NULL,
			`action` varchar(60) DEFAULT NULL,
			`entity` varchar(100) DEFAULT NULL,
			`entity_id` int(11) DEFAULT NULL,
			`sync_start_time` int(11) DEFAULT NULL,
			`sync_finish_time` int(11) DEFAULT NULL,
			`time_spent` int(11) DEFAULT NULL,
			`response_code` int(11) DEFAULT NULL,
			`message` longtext,
			`user_id` int(11) DEFAULT NULL,
			`novapontocom_apiid` varchar(50) DEFAULT NULL,
			PRIMARY KEY (`log_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=latin1;
	");

	$installer->run
	("
		DROP TABLE IF EXISTS `query_novapontocom_ticket`;
		CREATE TABLE `query_novapontocom_ticket`
		(
			`ticket_id` int(11) NOT NULL AUTO_INCREMENT,
			`customer_name` varchar(100) DEFAULT NULL,
			`api_id` int(11) DEFAULT NULL,
			`description` longtext,
			`api_opener_code` varchar(45) DEFAULT NULL,
			`created_at` datetime DEFAULT NULL,
			`reason` longtext,
			`ticket_action` longtext,
			`api_responsible` varchar(45) DEFAULT NULL,
			`api_code` varchar(45) DEFAULT NULL,
			`api_order_id` varchar(45) DEFAULT NULL,
			`subject` varchar(100) DEFAULT NULL,
			`closed_at` datetime DEFAULT NULL,
			`status` varchar(65) DEFAULT NULL,
			`updated_at` datetime DEFAULT NULL,
			`order_id` int DEFAULT NULL,
			PRIMARY KEY (`ticket_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=latin1;
	");

	$installer->run
	("
		DROP TABLE IF EXISTS `query_novapontocom_ticket_log`;
		CREATE TABLE `query_novapontocom_ticket_log`
		(
			`log_id` int(11) NOT NULL AUTO_INCREMENT,
			`ticket_id` int(11) DEFAULT NULL,
			`comment` longtext,
			`created_at` datetime DEFAULT NULL,
			`updated_at` datetime DEFAULT NULL,
			PRIMARY KEY (`log_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=latin1;
	");


	// ========================================
	// criacao dos atributos dos produtos
	// ========================================
	if(Mage::getVersion() >= 1.5)
	{
		$setup = Mage::getModel('catalog/resource_setup', 'core_setup');
	}
	else
	{
		$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
	}
	
	$setup->addAttribute('catalog_product', 'novaPontoCom_importerInfoId', array
	(
		'type'              => 'varchar',
		'backend'           => '',
		'frontend'          => '',
		'label'             => 'Importer Info NovaPontoCom',
		'input'             => 'text',
		'class'             => '',
		'source'            => '',
		'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
		'visible'           => false,
		'required'          => false,
		'user_defined'      => false,
		'default'           => '',
		'searchable'        => false,
		'filterable'        => false,
		'comparable'        => false,
		'visible_on_front'  => false,
		'unique'            => false,
		'apply_to'          => 'simple,bundle,grouped,configurable',
		'is_configurable'   => false
	));
	
	$setup->addAttribute('catalog_product', 'novaPontoCom_status', array
	(
		'type'              => 'varchar',
		'backend'           => '',
		'frontend'          => '',
		'label'             => 'Importer Status NovaPontoCom',
		'input'             => 'text',
		'class'             => '',
		'source'            => '',
		'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
		'visible'           => false,
		'required'          => false,
		'user_defined'      => false,
		'default'           => '',
		'searchable'        => false,
		'filterable'        => false,
		'comparable'        => false,
		'visible_on_front'  => false,
		'unique'            => false,
		'apply_to'          => 'simple,bundle,grouped,configurable',
		'is_configurable'   => false
	));

	$setup->addAttribute('catalog_product', 'novaPontoCom_statusCode', array
	(
		'type'              => 'int',
		'backend'           => '',
		'frontend'          => '',
		'label'             => 'Importer Status Code NovaPontoCom',
		'input'             => 'text',
		'class'             => '',
		'source'            => '',
		'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
		'visible'           => false,
		'required'          => false,
		'user_defined'      => false,
		'default'           => '-1',
		'searchable'        => false,
		'filterable'        => false,
		'comparable'        => false,
		'visible_on_front'  => false,
		'unique'            => false,
		'apply_to'          => 'simple,bundle,grouped,configurable',
		'is_configurable'   => false
	));
	
	$setup->addAttribute('catalog_product', 'novaPontoCom_apiSku', array
	(
		'type'              => 'varchar',
		'backend'           => '',
		'frontend'          => '',
		'label'             => 'API SKU NovaPontoCom',
		'input'             => 'text',
		'class'             => '',
		'source'            => '',
		'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
		'visible'           => false,
		'required'          => false,
		'user_defined'      => false,
		'default'           => '',
		'searchable'        => false,
		'filterable'        => false,
		'comparable'        => false,
		'visible_on_front'  => false,
		'unique'            => false,
		'apply_to'          => 'simple,bundle,grouped,configurable',
		'is_configurable'   => false
	));
	
	$setup->addAttribute('catalog_product', 'novaPontoCom_associatedProds', array
	(
		'type'              => 'text',
		'backend'           => '',
		'frontend'          => '',
		'label'             => 'NovaPontoCom Associated Products',
		'input'             => 'text',
		'class'             => '',
		'source'            => '',
		'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
		'visible'           => false,
		'required'          => false,
		'user_defined'      => false,
		'default'           => '',
		'searchable'        => false,
		'filterable'        => false,
		'comparable'        => false,
		'visible_on_front'  => false,
		'unique'            => false,
		'apply_to'          => 'simple,bundle,grouped,configurable',
		'is_configurable'   => false
	));


	// ========================================
	// criacao dos atributos dos pedidos
	// ========================================
	if(Mage::getVersion() >= 1.5)
	{
		$setup = Mage::getModel('sales/resource_setup', 'core_setup');
	}
	else
	{
		$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
	}
	
	$setup->addAttribute('order', 'novaPontoCom_apiId', array
	(
		'type' => 'varchar',
    	'label' => 'NovaPontoCom API ID',
    	'global' => 1,
    	'visible' => 0,
    	'required' => 0,
    	'user_defined' => 0,
    	'visible_on_front' => 0
	));

	if(Mage::getVersion() < 1.5)
	{
		try
		{
			$installer->run
			("
				ALTER TABLE `sales_flat_order` 
				ADD COLUMN `novaPontoCom_apiId` VARCHAR(255) NULL;
			");
		}
		catch(Exception $e)
		{
			
		}
	}

	$setup->addAttribute('order', 'novaPontoCom_status', array
	(
		'type' => 'varchar',
    	'label' => 'NovaPontoCom Status',
    	'global' => 1,
    	'visible' => 0,
    	'required' => 0,
    	'user_defined' => 0,
    	'visible_on_front' => 0
	));

	if(Mage::getVersion() < 1.5)
	{
		$installer->run
		("
			ALTER TABLE `sales_flat_order` 
			ADD COLUMN `novaPontoCom_status` VARCHAR(255) NULL;
		");
	}

	$installer->endSetup();