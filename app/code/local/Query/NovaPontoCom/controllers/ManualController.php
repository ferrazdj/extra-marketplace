<?php

class Query_NovaPontoCom_ManualController extends Mage_Adminhtml_Controller_Action
{
    /* 
	 * 
	 * Pagina de entrada
	 * 
	 */

    public function indexAction()
	{
		$this->loadLayout()->_setActiveMenu('novapontocom/manual');
		$this->_title($this->__('Extra.com User Manual'));
		$this->renderLayout();
	}
}