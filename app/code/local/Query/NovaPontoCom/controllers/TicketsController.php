<?php

class Query_NovaPontoCom_TicketsController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
	{
		$this->loadLayout()->_setActiveMenu('novapontocom/tickets');
		$this->_title($this->__('NovaPontoCom Tickets'));
		$this->renderLayout();
	}

	public function newAction()
	{
		$this->_forward('edit');
	}

	public function editAction()
	{
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('Query_NovaPontoCom/ticket')->load($id);

		if ($model->getId() || $id == 0)
		{
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			
			if (!empty($data))
			{
				$model->setData($data);
			}

			Mage::register('Query_NovaPontoCom_ticket_data', $model);

			$this->loadLayout();
			$this->_setActiveMenu('novapontocom/tickets');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));
			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			$this->_addContent($this->getLayout()->createBlock('Query_NovaPontoCom/tickets_edit'))
				->_addLeft($this->getLayout()->createBlock('Query_NovaPontoCom/tickets_edit_tabs'));

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
	  			
	  			
			$model = Mage::getModel('Query_NovaPontoCom/ticket');		
			$model->setData($data)->setId($this->getRequest()->getParam('id'));
			
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
				
				$this->_redirect('*/*/');
				return;
            }
            catch (Exception $e)
            {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('Query_NovaPontoCom')->__('Unable to find item to save'));
        $this->_redirect('*/*/');
	}

	public function deleteAction()
	{
		if( $this->getRequest()->getParam('id') > 0 )
		{
			try
			{
				$model = Mage::getModel('Query_NovaPontoCom/ticket');
				$model->setId($this->getRequest()->getParam('id'))->delete();
				
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Item was successfully deleted'));
				$this->_redirect('*/*/');
			}
			catch (Exception $e)
			{
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
		}
		$this->_redirect('*/*/');
	}

	public function massDeleteAction()
    {
        $ticketIds = $this->getRequest()->getParam('tickets');
        
        if(!is_array($ticketIds))
        {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        }
        else
        {
            try
            {
                foreach ($ticketIds as $ticketId)
                {
                    $ticket = Mage::getModel('Query_NovaPontoCom/ticket')->load($ticketId);
                    $ticket->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess
                (
                    Mage::helper('adminhtml')->__('Total of %d record(s) were successfully deleted', count($ticketIds))
                );
            }
            catch (Exception $e)
            {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
		
        $this->_redirect('*/*/index');
    }
	
	public function confirmChangeAction()
    {
        $creditmemo = Mage::getModel('sales/order_creditmemo')->load($this->getRequest()->getParam('creditmemo'));
        
        if(!$creditmemo || !$creditmemo->getId())
        {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Could not load creditmemo object'));
			$this->_redirect('adminhtml/sales_order_creditmemo/view', array('creditmemo_id' => $this->getRequest()->getParam('creditmemo')));
        }
		
		
		if(!$creditmemo || !$creditmemo->getId())
        {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Could not load creditmemo object'));
			$this->_redirect('adminhtml/sales_order_creditmemo/view', array('creditmemo_id' => $this->getRequest()->getParam('creditmemo')));
			return;
        }
		
		$ticket = Mage::getModel('Query_NovaPontoCom/ticket')->loadByAttribute('order_id', $creditmemo->getOrderId());
		
		if(!$ticket || !$ticket->getId())
		{
			Mage::getSingleton('adminhtml/session')->addError($this->__('Could not find the Extra.com ticket for this order.'));
			$this->_redirect('adminhtml/sales_order_creditmemo/view', array('creditmemo_id' => $this->getRequest()->getParam('creditmemo')));
			return;
		}
		
		// envia a troca para cada um dos produtos
		$syncService = Mage::getModel('Query_NovaPontoCom/integration_ticket');
		
		foreach($creditmemo->getItemsCollection() as $item)
		{
			try
			{
				$syncService->confirmChange($ticket, $item->getId(), "Devolucao confirmada", "false");
				
				Mage::getSingleton('adminhtml/session')->addSuccess
				(
					Mage::helper('adminhtml')->__('Product %s change was confirmed.', $item->getId())
				);
			}
			catch (Exception $e)
			{
				$errorMsg = Mage::helper('adminhtml')->__('Product %s change could not be confirmed: %s', $item->getId(), $e->getMessage());
				Mage::getSingleton('adminhtml/session')->addError($errorMsg);
			}
		}
		
        $this->_redirect('adminhtml/sales_order_creditmemo/view', array('creditmemo_id' => $this->getRequest()->getParam('creditmemo')));
    }
}