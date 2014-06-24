<?php

class Query_NovaPontoCom_Block_Tickets_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'Query_NovaPontoCom';
        $this->_controller = 'tickets';
        
        $this->_updateButton('save', 'label', Mage::helper('Query_NovaPontoCom')->__('Save'));
        //$this->_updateButton('delete', 'label', Mage::helper('Query_NovaPontoCom')->__('Delete'));
		$this->_removeButton('delete');
		
        $this->_addButton('saveandcontinue', array
        (
            'label'     => Mage::helper('adminhtml')->__('Save and Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);
		
		if(Mage::registry('Query_NovaPontoCom_ticket_data') && Mage::registry('Query_NovaPontoCom_ticket_data')->getId())
		{
			$ticket = Mage::getModel('Query_NovaPontoCom/ticket')->load(Mage::registry('Query_NovaPontoCom_ticket_data')->getId());
			
			if($ticket->getStatus() == "opened")
			{
				$closeTicketMessage = $this->__('Do You really want to close this ticket?');
				$closeTicketUrl = $this->getUrl("*/tickets_logs/new", array
				(
					"ticket" => Mage::registry('Query_NovaPontoCom_ticket_data')->getId(),
					"closeAction" => "close"
				));
					
				$this->_addButton('closeticket', array
				(
					'label'     => Mage::helper('adminhtml')->__('Close Ticket'),
					'onclick'   => "confirmSetLocation('{$closeTicketMessage}', '{$closeTicketUrl}')",
					'class'     => 'save'
				), 0);
			}
			
			$ticketOrder = Mage::getModel('sales/order')->load($ticket->getOrderId());
			
			if($ticket->getStatus() == "opened" && $ticket->getReason() == "tradereturncancel" && $ticketOrder && $ticketOrder->getId())
			{
				$changeConfirmationMessage = $this->__('You need to create a creditmemo to realize that action. Do You want to proceed with the creditmemo creation?');
				$changeConfirmationtUrl = $this->getUrl("adminhtml/sales_order_creditmemo/new", array
				(
					"order_id" => $ticket->getOrderId()
				));
				
				$this->_addButton('changeconfirmation', array
				(
					'label'     => Mage::helper('adminhtml')->__('Confirm Change'),
					'onclick'   => "confirmSetLocation('{$changeConfirmationMessage}', '{$changeConfirmationtUrl}')",
					'class'     => 'save',
				), 0);
			}
		}

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('novapontocom_ticket_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'novapontocom_ticket_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'novapontocom_ticket_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if(Mage::registry('Query_NovaPontoCom_ticket_data') && Mage::registry('Query_NovaPontoCom_ticket_data')->getId())
        {
            $label = Mage::registry('Query_NovaPontoCom_ticket_data')->getId();
			
			return Mage::helper('Query_NovaPontoCom')->__("Edit");
        } 
        else 
        {
            return Mage::helper('Query_NovaPontoCom')->__('Add');
        }
    }
}