<?php

class Query_NovaPontoCom_Model_Ticket extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('Query_NovaPontoCom/ticket');
    }

    public function delete()
    {
        $collection = Mage::getModel('Query_NovaPontoCom/ticket_log')->getCollection();
		$collection->addFieldToFilter('ticket_id', $this->getId());

		foreach($collection as $log)
		{
			$log->delete();
		}

        parent::delete();
    }
	
	public function loadByAttribute($attribute, $value)
	{
		$collection = Mage::getModel('Query_NovaPontoCom/ticket')
			->getCollection()
			->addFieldToFilter($attribute, $value)
			->setPageSize(1);
		
		foreach($collection as $object)
		{
			return $object;
		}
		
		return  Mage::getModel('Query_NovaPontoCom/ticket');
	}
}