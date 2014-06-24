<?php

class Query_NovaPontoCom_Block_Manual_Content extends Mage_Adminhtml_Block_Template
{
    //protected $_template = "query/novapontocom/manual/products.phtml";


    public function setPage($templatePage)
    {
    	if($templatePage)
    	{
    		$this->setTemplate("query/novapontocom/manual/" . $templatePage . ".phtml");
    	}

    	return $this;
    }
}
