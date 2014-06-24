<?php

class Query_NovaPontoCom_Model_ImportOrderError extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('Query_NovaPontoCom/importOrderError');
    }

    public function loadByOrderApiId($orderApiId)
    {
    	if(!$orderApiId)
    	{
    		return $this;
    	}

    	$collection = $this->getCollection();
    	$collection->addFieldToFilter("order_api_id", $orderApiId);

    	if(count($collection) != 1)
    	{
    		return $this;
    	}

    	return $collection->getFirstItem();
    }
}