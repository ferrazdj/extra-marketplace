<?php

class Query_NovaPontoCom_Block_Tickets_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
	protected function _prepareForm()
	{
		$form = new Varien_Data_Form();
		$this->setForm($form);

		if(Mage::getSingleton('adminhtml/session')->getData('Query_NovaPontoCom_ticket_data'))
		{
			$values = Mage::getSingleton('adminhtml/session')->getData('Query_NovaPontoCom_ticket_data');
			Mage::getSingleton('adminhtml/session')->setData('Query_NovaPontoCom_ticket_data', null);
		}
		elseif(Mage::registry('Query_NovaPontoCom_ticket_data'))
		{
			$values = Mage::registry('Query_NovaPontoCom_ticket_data')->getData();
		}

		$fieldset = $form->addFieldset('novapontocom_tickets_form', array
			(
				'legend'	=> Mage::helper('Query_NovaPontoCom')->__('Protocol Data'),
				'class'		=> 'fieldset-wide'
			)
		);

		//$fieldset->addField('order_id', 'select', array
		$fieldset->addField('api_order_id', 'text', array
		(
			'label'     => Mage::helper('Query_NovaPontoCom')->__('Order'),
			'name'      => 'api_order_id',
			'disabled'	=> ((isset($values['ticket_id']) && intval($values['ticket_id']) != 0) ? true : false),
			'required'  => ((isset($values['ticket_id']) && intval($values['ticket_id']) != 0) ? '' : 'required-entry'),
			//'values'	=> Mage::getModel('Query_NovaPontoCom/adminhtml_ordersId')->toOptionArray(true)
		));
		
		$fieldset->addField('type', 'select', array
		(
			'label'     => Mage::helper('Query_NovaPontoCom')->__('Type'),
			'class'     => ((isset($values['ticket_id']) && intval($values['ticket_id']) != 0) ? false : true),
			'required'  => ((isset($values['ticket_id']) && intval($values['ticket_id']) != 0) ? '' : 'required-entry'),
			'disabled'	=> ((isset($values['ticket_id']) && intval($values['ticket_id']) != 0) ? true : false),
			'name'      => 'type',
			'values'	=> Mage::getModel('Query_NovaPontoCom/adminhtml_ticketType')->toOptionArray(true)
		));
		
		$fieldset->addField('subject', 'text', array
		(
			'label'     => Mage::helper('Query_NovaPontoCom')->__('Subject'),
			'class'     => 'required-entry',
			'required'  => true,
			'disabled'	=> ((isset($values['ticket_id']) && intval($values['ticket_id']) != 0) ? true : false),
			'name'      => 'subject',
		));

		$fieldset->addField('description', 'textarea', array
		(
			'label'     => Mage::helper('Query_NovaPontoCom')->__('Description'),
			'class'     => 'required-entry',
			'required'  => true,
			'disabled'	=> ((isset($values['ticket_id']) && intval($values['ticket_id']) != 0) ? true : false),
			'name'      => 'description',

		));
		
		$form->setValues($values);
		return parent::_prepareForm();
	}
}