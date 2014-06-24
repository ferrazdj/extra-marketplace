<?php
	class Query_NovaPontoCom_Model_Observer_Shipment
	{
		public function updateItemsTracking($observer)
		{
			// confere se o modulo estah ativo
			if(!Mage::helper('Query_NovaPontoCom')->getConfigSetting('active'))
			{
				return;
			}
			
			$syncService = Mage::getModel('Query_NovaPontoCom/integration_order');
			$shipment = $observer->getEvent()->getShipment();
			$order = $shipment->getOrder();
			
			if(!$order->getData('novaPontoCom_apiId'))
			{
				return;
			}

			try
			{
				$syncService->postTrackingItems($shipment, "EPR");
				
				// insere comentario no pedido
				$order->addStatusHistoryComment("Shipment #%s sent as 'Sent' to the Extra.com API", $shipment->getIncrementId());
				$order->save();

				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('Query_NovaPontoCom')->__('Items tracking created at novaPontoCom'));
			}
			catch(Exception $e)
			{
				$syncService->registerShipmentError($shipment, "send-error");

				Mage::helper('Query_NovaPontoCom')->saveLogInfo
				(
					'order', 
					'update-items-tracking', 
					'sales/order', 
					$order->getId(), 
					time(), 
					0, 
					$e->getCode(), 
					$e->getMessage(),
					Mage::getSingleton('admin/session')->getUser()->getId(),
					$order->getData('novaPontoCom_apiId')
				);

				Mage::getSingleton('adminhtml/session')->addError(Mage::helper('Query_NovaPontoCom')->__('Items tracking could not be created:' . $e->getMessage()));
			}
		}
	}