<?php

class Query_NovaPontoCom_Block_Tickets_Edit_Tab_Logs extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct()
	{
		parent::__construct();
		$this->setId('novaPontoCom_ticket_log_grid');
		$this->setDefaultSort('log_id');
		$this->setDefaultDir('ASC');
		$this->setSaveParametersInSession(true);
	}

	protected function _prepareCollection()
	{
		$collection = Mage::getModel('Query_NovaPontoCom/ticket_log')
						->getCollection()
						->addFieldToFilter('ticket_id', $this->getRequest()->getParam('id'));
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}

	protected function _prepareColumns()
	{
		$this->addColumn('log_id', array
		(
			'header'    => Mage::helper('Query_NovaPontoCom')->__('ID'),
			'align'     => 'right',
			'width'     => '50px',
			'index'     => 'log_id',
		));

		$this->addColumn('comment', array
		(
			'header'    => Mage::helper('Query_NovaPontoCom')->__('Comment'),
			'align'     => 'left',
			'index'     => 'comment',
			'renderer'	=> 'Query_NovaPontoCom/tickets_logs_grid_renderer_comment'
		));
		
		return parent::_prepareColumns();
	}

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('log_id');
        $this->getMassactionBlock()->setFormFieldName('tikets_logs');
		
		
//		$this->getMassactionBlock()->addItem('delete', array
//		(
//			'label'    => Mage::helper('Query_NovaPontoCom')->__('Delete'),
//			'url'      => $this->getUrl('*/tickets_logs/massDelete', array('ticket_id' => $this->getRequest()->getParam('id'))),
//			'confirm'  => Mage::helper('Query_NovaPontoCom')->__('Are you sure?')
//		));

		return $this;
    }

	public function getRowUrl($row)
	{
		//return $this->getUrl('*/tickets_logs/edit', array('id' => $row->getId()));
		return false;
	}
}
