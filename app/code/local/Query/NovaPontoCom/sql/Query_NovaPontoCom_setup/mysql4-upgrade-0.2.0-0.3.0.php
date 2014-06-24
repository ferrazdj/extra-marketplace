<?php
	
	$installer = $this;
	$installer->startSetup();



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


	$setup->addAttribute('catalog_product', 'novaPontoCom_importError', array
	(
		'type'              => 'text',
		'backend'           => '',
		'frontend'          => '',
		'label'             => 'Import Error - NovaPontoCom',
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

	$installer->endSetup();