<?php

class Query_NovaPontoCom_Block_Manual_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

	public function __construct()
	{
		parent::__construct();
		$this->setId('novapontocom_manual_tabs');
		$this->setDestElementId('content');
		$this->setTitle(Mage::helper('Query_NovaPontoCom')->__('Menu'));
	}

	protected function _beforeToHtml()
	{
		$this->addTab('general', array
		(
			'label'     => Mage::helper('Query_NovaPontoCom')->__('General'),
			'title'     => Mage::helper('Query_NovaPontoCom')->__('General'),
			'content'   => $this->getLayout()->createBlock('Query_NovaPontoCom/manual_content')->setPage("general")->toHtml(),
		));

		$this->addTab('products', array
		(
			'label'     => Mage::helper('Query_NovaPontoCom')->__('Products'),
			'title'     => Mage::helper('Query_NovaPontoCom')->__('Products'),
			'content'   => $this->getLayout()->createBlock('Query_NovaPontoCom/manual_content')->setPage("products")->toHtml(),
		));

		$this->addTab('orders', array
		(
			'label'     => Mage::helper('Query_NovaPontoCom')->__('Orders'),
			'title'     => Mage::helper('Query_NovaPontoCom')->__('Orders'),
			'content'   => $this->getLayout()->createBlock('Query_NovaPontoCom/manual_content')->setPage("orders")->toHtml(),
		));

		$this->addTab('tickets', array
		(
			'label'     => Mage::helper('Query_NovaPontoCom')->__('Tickets'),
			'title'     => Mage::helper('Query_NovaPontoCom')->__('Tickets'),
			'content'   => $this->getLayout()->createBlock('Query_NovaPontoCom/manual_content')->setPage("tickets")->toHtml(),
		));

		$this->addTab('notes', array
		(
			'label'     => Mage::helper('Query_NovaPontoCom')->__('Version notes'),
			'title'     => Mage::helper('Query_NovaPontoCom')->__('Version notes'),
			'content'   => $this->getLayout()->createBlock('Query_NovaPontoCom/manual_content')->setPage("notes")->toHtml(),
		));
		
		return parent::_beforeToHtml();
	}
}