<?php

class Query_NovaPontoCom_Model_Observer_Creditmemo
{
	public function beforeCreate($observer)
	{
		// confere se o modulo estah ativo
		if(!Mage::helper('Query_NovaPontoCom')->getConfigSetting('active'))
		{
			return;
		}
		
		$creditmemo = $observer->getEvent()->getCreditmemo();
		$order = Mage::getModel('sales/order')->load($creditmemo->getOrderId());
		
		if(!$order->getData('novaPontoCom_apiId'))
		{
			return;
		}
		
		// calcula os valores de frete
		$refundedItems = 0;
		
		foreach($creditmemo->getItemsCollection() as $item)
		{
			$refundedItems += $item->getQty();
		}
		
		$totalItems = 0;
		
		foreach($order->getItemsCollection() as $item)
		{
			//$totalItems += $item->getQtyOrdered() - $item->getQtyRefunded();
			$totalItems += $item->getQtyOrdered();
		}
		
		// forca os valores de frete a serem os calculados
		$previousBaseShippingAmmount = $creditmemo->getBaseShippingAmount();
		$previousShippingAmmount = $creditmemo->getShippingAmount();
		
		$currentBaseShippingAmmount = $order->getBaseShippingAmount() / $totalItems * $refundedItems;
		$currentShippingAmmount = $order->getShippingAmount() / $totalItems * $refundedItems;
		
		$creditmemo->setBaseShippingAmount($currentBaseShippingAmmount);
		$creditmemo->setShippingAmount($currentShippingAmmount);
		
		$creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() - ($previousBaseShippingAmmount - $currentBaseShippingAmmount));
		$creditmemo->setGrandTotal($creditmemo->getGrandTotal() - ($previousShippingAmmount - $currentShippingAmmount));
	}
	
	
	public function afterCreate($observer)
	{
		// confere se o modulo estah ativo
		if(!Mage::helper('Query_NovaPontoCom')->getConfigSetting('active'))
		{
			return;
		}
		
		$creditmemo = $observer->getEvent()->getCreditmemo();
		$order = Mage::getModel('sales/order')->load($creditmemo->getOrderId());
		
		if(!$order->getData('novaPontoCom_apiId'))
		{
			return;
		}
		
		$ticket = Mage::getModel('Query_NovaPontoCom/ticket')->loadByAttribute('order_id', $creditmemo->getOrderId());
		
		if(!$ticket || !$ticket->getId())
		{
			Mage::getSingleton('adminhtml/session')->addError($this->__('Could not find the Extra.com.br ticket for this order.'));
			return;
		}
	}
}