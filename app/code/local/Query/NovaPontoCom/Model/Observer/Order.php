<?php

class Query_NovaPontoCom_Model_Observer_Order
{
	/**
	*
	* Observador responsavel pelo cancelamento de pedido
	*
	**/
	public function cancel($observer)
	{
		// confere se o modulo estah ativo
		if(!Mage::helper('Query_NovaPontoCom')->getConfigSetting('active'))
		{
			return;
		}

		$syncService = Mage::getModel('Query_NovaPontoCom/integration_order');
		$order = $observer->getEvent()->getOrder();

		if(!$order)
		{
			Mage::helper('Query_NovaPontoCom')->saveLogInfo
			(
				'order', 
				'cancel', 
				'sales/order', 
				$order->getId(), 
				time(), 
				0, 
				0, 
				Mage::helper('Query_NovaPontoCom')->__("Order was not recovered by observer"),
				Mage::getSingleton('admin/session')->getUser()->getId(),
				$order->getData('novaPontoCom_apiId')
			);
		}
		else
		{
			if($order->getData('novaPontoCom_status') == NULL)
			{
				return;
			}
		}

		try
		{
			$syncService->postCanceledOrder($order->getData('novaPontoCom_apiId'));
			Mage::helper('Query_NovaPontoCom')->saveLogInfo
			(
				'order', 
				'cancel', 
				'sales/order', 
				$order->getId(), 
				time(), 
				0, 
				0, 
				'',
				Mage::getSingleton('admin/session')->getUser()->getId(),
				$order->getData('novaPontoCom_apiId')
			);
			Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('Query_NovaPontoCom')->__('Order canceled on novaPontoCom.'));	

		}
		catch(Exception $e)
		{
			Mage::helper('Query_NovaPontoCom')->saveLogInfo
			(
				'order', 
				'cancel', 
				'sales/order', 
				$order->getId(), 
				time(), 
				0, 
				$e->getCode(), 
				$e->getMessage(),
				Mage::getSingleton('admin/session')->getUser()->getId(),
				$order->getData('novaPontoCom_apiId')
			);	
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('Query_NovaPontoCom')->__('Order could not be canceled on novaPontoCom.'));
		}		
	}
	
	
	/**
	*
	* Observador responsavel pela sincronizacao de estoque
	* de produtos que sejam comprados na loja
	*
	**/
	public function updateBoughtProductsInventory($observer)
	{
		if(Mage::helper('Query_NovaPontoCom')->getConfigSetting('active'))
		{
			$order = $observer->getEvent()->getOrder();
			
			$currentStoreId = Mage::app()->getStore()->getId();
			Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
			
			foreach($order->getItemsCollection() as $item)
			{
				$product = Mage::getModel('catalog/product')->load($item->getProductId());
				
				$product->setUpdatedAt(new Zend_Date());
				$product->save();
			}
			
			Mage::app()->setCurrentStore($currentStoreId);
		}
	}
}