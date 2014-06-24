<?php

class Query_NovaPontoCom_Model_Adminhtml_OrdersId
{

    public function toOptionArray($emptyValue = false)
    {
        $collection = Mage::getResourceModel('sales/order_grid_collection');
		$collection->getSelect()->join('sales_flat_order', 'main_table.entity_id = sales_flat_order.entity_id', array('novaPontoCom_apiId', 'novaPontoCom_status'));
		$collection->addAttributeToFilter('novaPontoCom_apiId', array('neq' => 0));
		$collection->addAttributeToSort('entity_id', 'DESC');

		$result = array();
		
		if($emptyValue)
		{
			$result[] = array('value' => '', 'label' => Mage::helper('Query_NovaPontoCom')->__(''));
		}

		foreach($collection as $order)
		{
			$result[] = array
			(
				'value' => $order->getId(), 
				'label' => $order->getIncrementId()
			);
		}
		
		return $result;
    }

    public function toArray()
    {
        $collection = Mage::getResourceModel('sales/order_grid_collection');
		$collection->getSelect()->join('sales_flat_order', 'main_table.entity_id = sales_flat_order.entity_id', array('novaPontoCom_apiId', 'novaPontoCom_status'));
		$collection->addAttributeToFilter('novaPontoCom_apiId', array('neq' => 0));

		$result = array();
		
		foreach($collection as $order)
		{
			$result[] = array
			(
				$order->getId() => $order->getIncrementId()
			);
		}
		
		return $result;
    }

}