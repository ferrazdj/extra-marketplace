<?php

class Query_NovaPontoCom_OrdersController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
	{
		$this->loadLayout()->_setActiveMenu('novapontocom/orders');
		$this->_title($this->__('NovaPontoCom Orders'));
		$this->renderLayout();
	}
	
	public function gridAction()
    {
        $this->loadLayout(false);
        $this->renderLayout();
    }
	
	/**
	*
	* Action responsavel por enviar um pedido com status 'Delivered' para o NovaPontoCom quando acionado
	* o botão 'NovaPontoCom Delivered' na administracao do pedido
	*/
	public function deliverAction()
	{
		$syncService = Mage::getModel('Query_NovaPontoCom/integration_order');
		$shipmentId  = $this->getRequest()->getParam('shipment');

		if(!$shipmentId)
		{
			Mage::helper('Query_NovaPontoCom')->saveLogInfo
			(
				'order',
				'deliver-shipment',
				'sales/order_shipment',
				$shipmentId,
				time(),
				0,
				0,
				Mage::helper('Query_NovaPontoCom')->__('Parameter shipment id was not receveid'),
				Mage::getSingleton('admin/session')->getUser()->getId()
			);
			
			Mage::getSingleton('adminhtml/session')->addError($this->__('Shipment could not be delivered.'));
		}
		else
		{
			$shipment  = Mage::getModel('sales/order_shipment')->load($shipmentId);
			$order = $shipment->getOrder();

			try
			{
				$syncService->postTrackingItems($shipment, "ENT");

				// insere comentario no pedido
				$order->addStatusHistoryComment($this->__("Shipment #%s sent as 'Delivered' to the Extra.com API.", $shipment->getIncrementId()));
				$order->save();

				Mage::helper('Query_NovaPontoCom')->saveLogInfo
				(
					'order', 
					'deliver-shipment',
					'sales/order_shipment',
					$shipmentId,
					time(), 
					0, 
					0, 
					'',
					Mage::getSingleton('admin/session')->getUser()->getId(),
					$shipmentId
				);
				
				Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Shipment ' . $shipmentId . ' was delivered.'));
				
			}
			catch(Exception $e)
			{
				// insere erro no envio
				$syncService->registerShipmentError($shipment, "send-error");

				Mage::helper('Query_NovaPontoCom')->saveLogInfo
				(
					'order', 
					'deliver-shipment',
					'sales/order_shipment',
					$shipmentId,
					time(), 
					0, 
					$e->getCode(), 
					$e->getMessage(),
					Mage::getSingleton('admin/session')->getUser()->getId(),
					$shipmentId
				);
				
				Mage::getSingleton('adminhtml/session')->addError($this->__('Order could not be delivered:') . $e->getMessage());
				
			}
		}
		Mage::app()->getResponse()->setRedirect(Mage::helper('adminhtml')->getUrl("adminhtml/sales_order_shipment/view", array('shipment_id' => $shipmentId)));
	}
}