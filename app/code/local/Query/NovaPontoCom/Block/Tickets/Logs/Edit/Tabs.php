<?php

class Query_NovaPontoCom_Block_Tickets_Logs_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

	public function __construct()
	{
		parent::__construct();
		$this->setId('novapontocom_ticket_logs_tabs');
		$this->setDestElementId('edit_form');
		$this->setTitle(Mage::helper('Query_NovaPontoCom')->__('Message'));
	}

	protected function _beforeToHtml()
	{
		$this->addTab('form_section', array
		(
			'label'     => Mage::helper('Query_NovaPontoCom')->__('Message'),
			'title'     => Mage::helper('Query_NovaPontoCom')->__('Message'),
			'content'   => $this->getLayout()->createBlock('Query_NovaPontoCom/tickets_logs_edit_tab_form')->toHtml(),
		));
		
		return parent::_beforeToHtml();
	}
}