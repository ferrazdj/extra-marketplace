<?php

class Query_NovaPontoCom_Model_Mysql4_Ticket_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('Query_NovaPontoCom/ticket');
    }
}