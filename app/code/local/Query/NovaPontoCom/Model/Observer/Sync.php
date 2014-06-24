<?php
	
	class Query_NovaPontoCom_Model_Observer_Sync
	{
		public function addSyncMassAction($observer)
		{
			// confere se o modulo estah ativo
			if(!Mage::helper('Query_NovaPontoCom')->getConfigSetting('active'))
			{
				return;
			}

			$block = $observer->getEvent()->getBlock();
			
			if($block && 
			   $block->getType() == 'adminhtml/widget_grid_massaction' &&
			   $block->getRequest()->getControllerName() == 'sales_order')
			{
				$block->addItem('novapontocom_order_sync', array
				(
					'label' => 'Synchronize with Extra.com',
					'url' => Mage::app()->getStore()->getUrl('novapontocom/sync/start'),
				));
			}
			else if($block && 
					$block->getType() == 'adminhtml/widget_grid_massaction' &&
					$block->getRequest()->getControllerName() == 'catalog_product')
			{
				$block->addItem('novapontocom_product_send', array
				(
					'label' => Mage::helper('Query_NovaPontoCom')->__('Sent to Extra.com API'),
					'url' => Mage::app()->getStore()->getUrl('novapontocom/products/firstSendProduct'),
				));
			}
			else if ($block && 
					$block->getType() == 'adminhtml/sales_order_shipment_view' )
			{
				$shipment = $block->getShipment();
				$order = $shipment->getOrder();

				// nao mostra, caso o pedido jah esteja entregue
				if($order->getData('novaPontoCom_status') == "Delivered")
				{
					return;
				}
				
				$url = Mage::helper('adminhtml')->getUrl('novapontocom/orders/deliver/', array("shipment" => $shipment->getId()));
				$message = Mage::helper('Query_NovaPontoCom')->__('Do you want to mark this shipment as delivered?');
				$block->addButton('shipment_novapontocom_delivery', array
				(
					'label'     => Mage::helper('Query_NovaPontoCom')->__("Mark as delivered (Extra.com)"),
					'onclick'   => "if(confirm('" . $message . "')){ postDeliveredShipment('" . $url . "'); }",
					'class'     => 'go'
				));
			}
			else if ($block && 
					$block->getType() == 'adminhtml/sales_order_creditmemo_view' )
			{
				$creditmemo = $block->getCreditmemo();
				$order = $creditmemo->getOrder();

				// nao mostra, caso o pedido nao seja da novapontocom
				if(!$order->getData('novaPontoCom_apiId'))
				{
					return;
				}
				
				// nao mostra, caso nao seja encontrado ticket correspondente
				$ticket = Mage::getModel('Query_NovaPontoCom/ticket')->loadByAttribute('order_id', $creditmemo->getOrderId());
		
				if(!$ticket || !$ticket->getId())
				{
					return;
				}
				
				$url = Mage::helper('adminhtml')->getUrl('novapontocom/tickets_logs/new/', array
				(
					"creditmemo" => $creditmemo->getId(),
					"ticket" => $ticket->getId(),
					"closeAction" => "confirm"
				));
				$message = Mage::helper('Query_NovaPontoCom')->__('Send change confirmation to Extra.com');
				
				$block->addButton('creditmemo_novapontocom_confirmChange', array
				(
					'label'     => Mage::helper('Query_NovaPontoCom')->__("Send change confirmation to Extra.com"),
					'onclick'   => "setLocation('" . $url . "');",
					'class'     => 'go'
				));
			}
		}
	}
	