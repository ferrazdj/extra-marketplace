<?php

class Query_NovaPontoCom_Block_ImportOrderError extends Mage_Adminhtml_Block_Widget_Grid_Container
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

		$this->_controller = 'importOrderError';
		$this->_blockGroup = 'Query_NovaPontoCom';
		$this->_headerText = Mage::helper('Query_NovaPontoCom')->__('Import Order Errors') . $titleComplement;
		parent::__construct();
		$this->removeButton("add");
	}
}
