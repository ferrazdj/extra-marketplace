<?php

class Query_NovaPontoCom_Block_Tickets_Logs_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
	protected function _prepareForm()
	{
		$form = new Varien_Data_Form();
		$this->setForm($form);

		if(Mage::getSingleton('adminhtml/session')->getData('Query_NovaPontoCom_ticket_log_data'))
		{
			$values = Mage::getSingleton('adminhtml/session')->getData('Query_NovaPontoCom_ticket_log_data');
			Mage::getSingleton('adminhtml/session')->setData('Query_NovaPontoCom_ticket_log_data', null);
		}
		elseif(Mage::registry('Query_NovaPontoCom_ticket_log_data'))
		{
			$values = Mage::registry('Query_NovaPontoCom_ticket_log_data')->getData();
		}

		$fieldset = $form->addFieldset('novapontocom_tickets_logs_form', array
			(
				'legend'	=> Mage::helper('Query_NovaPontoCom')->__('Message'),
				'class'		=> 'fieldset-wide'
			)
		);
		
		$configSettings = Mage::getSingleton('cms/wysiwyg_config')->getConfig(array
		(
			'add_widgets' => false,
			'add_variables' => false,
			'add_images' => false,
			'files_browser_window_url' => $this->getBaseUrl() . 'admin/cms_wysiwyg_images/index/',
		));

		$fieldset->addField('comment', 'editor', array
		(
			'label'     => Mage::helper('Query_NovaPontoCom')->__('Comment'),
			'class'     => 'required-entry',
			'required'  => true,
			'name'      => 'comment',
			'wysiwyg'   => true,
			'config' 	=> $configSettings
		));
		
		$fieldset->addField('notify_customer', 'checkbox', array
		(
			'label'     => Mage::helper('Query_NovaPontoCom')->__('Notify Customer by Email'),
			'name'      => 'notify_customer',
		));
		
		$fieldset->addField('close_action', 'hidden', array
		(
			'name'      => 'close_action',
		));
		
		$fieldset->addField('ticket_id', 'hidden', array
		(
			'name'      => 'ticket_id',
		));
		
		
		if($this->getRequest()->getParam('closeAction') == "confirm")
		{
			$fieldset->addField('order_id', 'hidden', array
			(
				'label'     => Mage::helper('Query_NovaPontoCom')->__('Order'),
				'name'      => 'order_id',
				'disabled'	=> true,
				'required'  => true
			));
			
			$ticket = Mage::getModel('Query_NovaPontoCom/ticket')->load($this->getRequest()->getParam('ticket'));
			$creditmemo = Mage::getModel('sales/order_creditmemo')->load($this->getRequest()->getParam('creditmemo'));
			$orderItems = array();
			
			foreach($creditmemo->getItemsCollection() as $item)
			{
				$orderItems[] = array
				(
					"value" => $item->getId(),
					"label" => $item->getName()
				);
			}
			
			$fieldset->addField('order_item_id', 'select', array
			(
				'label'     => Mage::helper('Query_NovaPontoCom')->__('Order Item'),
				'name'      => 'order_item_id',
				'disabled'	=> ((isset($values['ticket_id']) && intval($values['ticket_id']) != 0) ? true : false),
				'required'  => ((isset($values['ticket_id']) && intval($values['ticket_id']) != 0) ? '' : 'required-entry'),
				'values'	=> $orderItems
			));
			
			$fieldset->addField('confirm_change_action', 'select', array
			(
				'label'     => Mage::helper('Query_NovaPontoCom')->__('Action Type'),
				'name'      => 'confirm_change_action',
				'required'  => true,
				'values'	=> Mage::getModel('Query_NovaPontoCom/adminhtml_ticketConfirmChangeAction')->toOptionArray(true)
			));
			
			$values['order_id'] = $ticket->getOrderId();
		}
		
		if($this->getRequest()->getParam('closeAction'))
		{
			$values['close_action'] = $this->getRequest()->getParam('closeAction');
		}
		
		if($this->getRequest()->getParam('ticket'))
		{
			$values['ticket_id'] = $this->getRequest()->getParam('ticket');
		}

		$form->setValues($values);
		return parent::_prepareForm();
	}
}