<?php

class Query_NovaPontoCom_Block_Orders extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	public function __construct()
	{
		// confere se o modulo estah ativo
        if(!Mage::helper('Query_NovaPontoCom')->getConfigSetting('active'))
        {
            $titleComplement =  " - " . $this->__("Module not active");
        }
        else
        {
        	$titleComplement = "";
        }

		parent::__construct();
		$this->_controller = 'orders';
		$this->_blockGroup = 'Query_NovaPontoCom';
		$this->_headerText = Mage::helper('Query_NovaPontoCom')->__('Orders') . $titleComplement;
		$this->_removeButton('add');
	}
}