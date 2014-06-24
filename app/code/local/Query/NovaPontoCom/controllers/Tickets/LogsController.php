<?php

class Query_NovaPontoCom_Tickets_LogsController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
	{
		$this->loadLayout()->_setActiveMenu('novapontocom/tickets');
		$this->_title($this->__('NovaPontoCom Logs Tickets'));
		$this->renderLayout();
	}

	public function newAction()
	{
		$this->_forward('edit');
	}
	
	public function viewAction()
	{
		$ticketId = $this->getRequest()->getParam('id');
		
		$ticket = Mage::getModel('Query_NovaPontoCom/ticket_log')->load($ticketId);
		if(!$ticket || !$ticket->getId())
		{
			$this->getResponse()->setBody("Could not find log #" . $ticketId);
		}
		else
		{
			$this->getResponse()->setBody($ticket->getComment());
		}
	}

	public function editAction()
	{
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('Query_NovaPontoCom/ticket_log')->load($id);

		if ($model->getId() || $id == 0)
		{
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			
			if (!empty($data))
			{
				$model->setData($data);
			}

			Mage::register('Query_NovaPontoCom_ticket_log_data', $model);

			$this->loadLayout();
			$this->_setActiveMenu('novapontocom/tickets');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));
			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			$this->_addContent($this->getLayout()->createBlock('Query_NovaPontoCom/tickets_logs_edit'))
				 ->_addLeft($this->getLayout()->createBlock('Query_NovaPontoCom/tickets_logs_edit_tabs'));;

			$this->renderLayout();
		}
		else
		{
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('Query_NovaPontoCom')->__('Item does not exist'));
			$this->_redirect('*/*/');
		}
	}

	public function saveAction()
	{
		if ($data = $this->getRequest()->getPost())
		{
			if(isset($_FILES['filename']['name']) && $_FILES['filename']['name'] != '')
			{
				try
				{
					/* Starting upload */	
					$uploader = new Varien_File_Uploader('filename');
					
					// Any extention would work
	           		$uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
					$uploader->setAllowRenameFiles(false);
					
					// Set the file upload mode 
					// false -> get the file directly in the specified folder
					// true -> get the file in the product like folders 
					//	(file.jpg will go in something like /media/f/i/file.jpg)
					$uploader->setFilesDispersion(false);
							
					// We set media as the upload dir
					$path = Mage::getBaseDir('media') . DS ;
					$uploader->save($path, $_FILES['filename']['name'] );
					
				}
				catch (Exception $e)
				{
		      
		        }
	        
		        //this way the name is saved in DB
	  			$data['filename'] = $_FILES['filename']['name'];
			}
	  		
			$ticket = Mage::getModel('Query_NovaPontoCom/ticket')->load($this->getRequest()->getParam('ticket_id'));
			
			$model = Mage::getModel('Query_NovaPontoCom/ticket_log');
			$model->setData($data)->setId($this->getRequest()->getParam('id'));
			$notifyCustomer = isset($data['notify_customer']) ? "true" : "false";
			
			// confere se deve fechar o ticket
			if(isset($data['close_action']))
			{
				try
				{
					$syncService = Mage::getModel('Query_NovaPontoCom/integration_ticket');
					
					if($data['close_action'] == "close")
					{
						$syncService->closeTicket($ticket, $data['comment'], $notifyCustomer);
						
						// log
						Mage::helper('Query_NovaPontoCom')->saveLogInfo
						(
							'ticket', 
							'close', 
							'Query_NovaPontoCom/ticket', 
							$model->getId() ? $model->getId() : 'null', 
							time(), 
							0, 
							0, 
							'',
							Mage::getSingleton('admin/session')->getUser()->getId()
						);
						
						$ticket->setStatus("closed");
						$ticket->save();
						
						Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Ticket successfully closed.'));
					}
					else if($data['close_action'] == "confirm")
					{
						$syncService->confirmChange($ticket, $data['order_item_id'], $data['confirm_change_action'], $data['comment'], $notifyCustomer);
						
						// log
						Mage::helper('Query_NovaPontoCom')->saveLogInfo
						(
							'ticket', 
							'confirm-change', 
							'Query_NovaPontoCom/ticket', 
							$model->getId() ? $model->getId() : 'null', 
							time(), 
							0, 
							0, 
							'',
							Mage::getSingleton('admin/session')->getUser()->getId()
						);

						Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Ticket successfully closed.'));
					}
				}
				catch(Exception $e)
				{
					// log
					Mage::helper('Query_NovaPontoCom')->saveLogInfo
					(
						'ticket', 
						($data['close_action'] == "confirm") ? 'confirm-change' : 'close', 
						'Query_NovaPontoCom/ticket', 
						$model->getId() ? $model->getId() : 'null', 
						time(), 
						0, 
						$e->getCode(), 
						$e->getMessage(),
						Mage::getSingleton('admin/session')->getUser()->getId()
					);
					
					if($data['close_action'] == "close")
					{
						Mage::getSingleton('adminhtml/session')->addError($this->__('Could not close the ticket: ') . $e->getMessage());
					}
					else if($data['close_action'] == "confirm")
					{
						Mage::getSingleton('adminhtml/session')->addError($this->__('Could not confirm the change: ') . $e->getMessage());
					}
					
					$this->_redirect('*/tickets/edit', array('id' => $this->getRequest()->getParam('ticket_id')));
					return;
				}
			}
			
			try
			{
				if ($model->getCreatedAt() == NULL || $model->getUpdatedAt() == NULL)
				{
					$model->setCreatedAt(now());
					$model->setUpdatedAt(now());
				}
				else
				{
					$model->setUpdatedAt(now());
				}
				
				$model->save();
				
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('Query_NovaPontoCom')->__('Item was successfully saved'));
				Mage::getSingleton('adminhtml/session')->setFormData(false);

				if ($this->getRequest()->getParam('back'))
				{
					$this->_redirect('*/*/edit', array('id' => $model->getId()));
					return;
				}
				
				$this->_redirect('*/tickets/edit', array('id' => $this->getRequest()->getParam('ticket_id')));
				return;
            }
            catch (Exception $e)
            {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/tickets/edit', array('id' => $this->getRequest()->getParam('ticket_id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('Query_NovaPontoCom')->__('Unable to find item to save'));
        $this->_redirect('*/*/');
	}

	public function deleteAction()
	{
		if($this->getRequest()->getParam('id') > 0)
		{
			try
			{
				$model = Mage::getModel('Query_NovaPontoCom/ticket_log');
				$model->load($this->getRequest()->getParam('id'));
				$ticketId = $model->getTicketId();
				$model->delete();
				
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Item was successfully deleted'));
				$this->_redirect('*/tickets/edit', array('id' => $ticketId));
			}
			catch (Exception $e)
			{
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
		}
		else
		{
			$this->_redirect('*/*/');	
		}
	}

	public function massDeleteAction()
    {
        $ticketLogIds = $this->getRequest()->getParam('tikets_logs');
        
        if(!is_array($ticketLogIds))
        {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        }
        else
        {
            try
            {
                foreach($ticketLogIds as $ticketLogId)
                {
                    $ticket = Mage::getModel('Query_NovaPontoCom/ticket_log')->load($ticketLogId);
                    $ticket->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess
                (
                    Mage::helper('adminhtml')->__('Total of %d record(s) were successfully deleted', count($ticketLogIds))
                );
            }
            catch (Exception $e)
            {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }

        $this->_redirect('*/tickets/edit', array('id' => $this->getRequest()->getParam('ticket_id')));
    }
}