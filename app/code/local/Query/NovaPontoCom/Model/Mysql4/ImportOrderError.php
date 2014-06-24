<?php

class Query_NovaPontoCom_Model_Mysql4_ImportOrderError extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('Query_NovaPontoCom/importOrderError', 'error_id');
    }
}