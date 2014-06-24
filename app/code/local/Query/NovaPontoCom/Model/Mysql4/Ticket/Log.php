<?php

class Query_NovaPontoCom_Model_Mysql4_Ticket_log extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('Query_NovaPontoCom/ticket_log', 'log_id');
    }
}