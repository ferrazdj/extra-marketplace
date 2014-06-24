<?php

class Query_NovaPontoCom_Block_Payment_Info extends Mage_Payment_Block_Info
{
    protected function _construct()
    {
		parent::_construct();
		$this->setTemplate('query/novapontocom/payment/info.phtml');
    }
    
    public function toPdf()
    {
        $this->setTemplate('payment/info/pdf/cc.phtml');
        return $this->toHtml();
    }
}
