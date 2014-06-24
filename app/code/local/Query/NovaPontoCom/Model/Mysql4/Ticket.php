<?php

class Query_NovaPontoCom_Model_Mysql4_Ticket extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('Query_NovaPontoCom/ticket', 'ticket_id');
    }
}