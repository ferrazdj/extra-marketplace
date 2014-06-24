<?php

class Query_NovaPontoCom_Block_Tickets_Logs_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
        
        if($this->getRequest()->getParam('id')) 
        {
            $ticketId = Mage::getModel('Query_NovaPontoCom/ticket_log')->load($this->getRequest()->getParam('id'))->getTicketId();
        } 
        else 
        {
            $ticketId = $this->getRequest()->getParam('ticket');
        }

        $this->_objectId = 'id';
        $this->_blockGroup = 'Query_NovaPontoCom';
        $this->_controller = 'tickets';
        
        $this->_updateButton('save', 'label', Mage::helper('Query_NovaPontoCom')->__('Save'));
        $this->_updateButton('delete', 'label', Mage::helper('Query_NovaPontoCom')->__('Delete'));
		
        $this->_updateButton
        (
            'back',
            'onclick',
            'setLocation(\'' . $this->getUrl
            (
                '*/tickets/edit', 
                array
                (
                    'id' => $ticketId
                )
            ) . '\')'
        );

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('novapontocom_ticket_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'novapontocom_ticket_log_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'novapontocom_ticket_log_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if($this->getRequest()->getParam('id')) 
        {
            $ticketId = Mage::getModel('Query_NovaPontoCom/ticket_log')->load($this->getRequest()->getParam('id'))->getTicketId();
        } 
        else 
        {
            $ticketId = $this->getRequest()->getParam('ticket');
        }


        if($this->getRequest()->getParam('id'))
        {
            $label = Mage::getModel('Query_NovaPontoCom/ticket_log')->load($this->getRequest()->getParam('id'))->getId();
			
			return Mage::helper('Query_NovaPontoCom')->__("Edit %s", $this->htmlEscape($label));
        } 
        else 
        {
            if($this->getRequest()->getParam('closeAction'))
			{
				if($this->getRequest()->getParam('closeAction') == "close")
				{
					return Mage::helper('Query_NovaPontoCom')->__('Close Ticket');
				}
				else if($this->getRequest()->getParam('closeAction') == "confirm")
				{				
					return Mage::helper('Query_NovaPontoCom')->__('Confirm Change');
				}
			}
			else
			{
				return Mage::helper('Query_NovaPontoCom')->__('Add');
			}
        }
    }
}