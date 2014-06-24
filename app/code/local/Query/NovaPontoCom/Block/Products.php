<?php

class Query_NovaPontoCom_Block_Products extends Mage_Adminhtml_Block_Widget_Grid_Container
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
		$this->_controller = 'products';
		$this->_blockGroup = 'Query_NovaPontoCom';
		$this->_headerText = Mage::helper('Query_NovaPontoCom')->__('Products') . $titleComplement;
		$this->_removeButton('add');
	}
}