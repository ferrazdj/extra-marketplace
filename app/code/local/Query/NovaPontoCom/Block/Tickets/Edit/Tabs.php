<?php

class Query_NovaPontoCom_Block_Tickets_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

	public function __construct()
	{
		parent::__construct();
		$this->setId('novapontocom_ticket_tabs');
		$this->setDestElementId('edit_form');
		$this->setTitle(Mage::helper('Query_NovaPontoCom')->__('Protocol Data'));
	}

	protected function _beforeToHtml()
	{
		$this->addTab('form_section', array
		(
			'label'     => Mage::helper('Query_NovaPontoCom')->__('Protocol Data'),
			'title'     => Mage::helper('Query_NovaPontoCom')->__('Protocol Data'),
			'content'   => $this->getLayout()->createBlock('Query_NovaPontoCom/tickets_edit_tab_form')->toHtml(),
		));
		
		
		if($this->getRequest()->getParam('id'))
		{
			// logs
			$url = $this->getUrl('*/tickets_logs/new', array('ticket' => $this->getRequest()->getParam('id')));
			$content = 
				'<div class="a-right">
					<button class="scalable save" style="" onclick="setLocation(\'' . $url . '\')" type="button" title="' . $this->__('Add') . '">
						<span>
							<span>
								<span>' . $this->__('New Message') . '</span>
							</span>
						</span>
					</button>
				</div>
				<br />';
			$content .= $this->getLayout()->createBlock('Query_NovaPontoCom/tickets_edit_tab_logs')->toHtml();
			
			$this->addTab('logs_section', array
			(
				'label'     => Mage::helper('Query_NovaPontoCom')->__('Logs'),
				'title'     => Mage::helper('Query_NovaPontoCom')->__('Logs'),
				'content'   => $content,
			));
		}
		
		return parent::_beforeToHtml();
	}
}