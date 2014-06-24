<?php

class Query_NovaPontoCom_Model_Ticket_Log extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('Query_NovaPontoCom/ticket_log');
    }

    public function loadByAttribute($attribute, $value)
	{
		$collection = Mage::getModel('Query_NovaPontoCom/ticket_log')
			->getCollection()
			->addFieldToFilter($attribute, $value)
			->setPageSize(1);
		
		foreach($collection as $object)
		{
			return $object;
		}
		
		return  Mage::getModel('Query_NovaPontoCom/ticket_log');
	}
	
	public function getTicket()
	{
		$ticket = Mage::getModel('Query_NovaPontoCom/ticket')->load($this->getTicketId());
		
		if($ticket && $ticket->getId())
		{
			return $ticket;
		}
		else
		{
			return Mage::getModel('Query_NovaPontoCom/ticket');
		}
	}
}