<?php

class Query_NovaPontoCom_ImportOrderErrorController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
	{
		$this->loadLayout()->_setActiveMenu('novapontocom/orders/errors');
		$this->_title($this->__('NovaPontoCom Import Order Errors'));
		$this->renderLayout();
	}
}