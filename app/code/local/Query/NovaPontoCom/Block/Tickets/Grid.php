<?php

class Query_NovaPontoCom_Block_Tickets_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct()
	{
		parent::__construct();
		$this->setId('novaPontoCom_ticket_grid');
		$this->setDefaultSort('created_at');
		$this->setDefaultFilter(array('status' => 'opened'));
		$this->setDefaultDir('ASC');
		$this->setSaveParametersInSession(true);
	}

	protected function _prepareCollection()
	{
		$collection = Mage::getModel('Query_NovaPontoCom/ticket')->getCollection();
		$this->setCollection($collection);
		
		return parent::_prepareCollection();
	}

	protected function _prepareColumns()
	{
		/*
		$this->addColumn('ticket_id', array
		(
			'header'    => Mage::helper('Query_NovaPontoCom')->__('ID'),
			'align'     =>'right',
			'width'     => '50px',
			'index'     => 'ticket_id',
		));
		*/
		
		$this->addColumn('api_code', array
		(
			'header'    => Mage::helper('Query_NovaPontoCom')->__('Protocol'),
			'align'     =>'right',
			'index'     => 'api_code',
		));

		$this->addColumn('customer_name', array
		(
			'header'    => Mage::helper('Query_NovaPontoCom')->__('Customer Name'),
			'align'     =>'left',
			'index'     => 'customer_name',
		));

		$this->addColumn('api_order_id', array
		(
			'header'    => Mage::helper('Query_NovaPontoCom')->__('Extra.com Order'),
			'align'     =>'left',
			'index'     => 'api_order_id',
			//'renderer'  => 'Query_NovaPontoCom/tickets_grid_renderer_order',
		));

		$this->addColumn('created_at', array
		(
			'header'    => Mage::helper('Query_NovaPontoCom')->__('Created At'),
			'align'     =>'left',
			'index'     => 'created_at',
			'type'  	=> 'datetime',
		));

		$this->addColumn('updated_at', array
		(
			'header'    => Mage::helper('Query_NovaPontoCom')->__('Updated At'),
			'align'     =>'left',
			'index'     => 'updated_at',
			'type'  	=> 'datetime',
		));

		$this->addColumn('subject', array
		(
			'header'    => Mage::helper('Query_NovaPontoCom')->__('Subject'),
			'align'     =>'left',
			'index'     => 'subject',
		));
		
		$this->addColumn('api_responsible', array
		(
			'header'    => Mage::helper('Query_NovaPontoCom')->__('Responsible'),
			'align'     =>'left',
			'index'     => 'api_responsible',
		));
		
		$this->addColumn('reason', array
		(
			'header'    => Mage::helper('Query_NovaPontoCom')->__('Reason'),
			'align'     =>'left',
			'index'     => 'reason',
		));
		
		$this->addColumn('status', array
		(
			'header'    => Mage::helper('Query_NovaPontoCom')->__('Status'),
			'align'     =>'right',
			'index'     => 'status',
		));

		/*
		$this->addColumn('state', array
		(
			'header'    => Mage::helper('transportation')->__('State'),
			'align'     =>'left',
			'index'     => 'state',
			'type'      => 'options',
			'options'   => Mage::getModel('transportation/states')->toArray(),
		));

		$this->addColumn('use_fixed_value', array
		(
			'header'    => Mage::helper('transportation')->__('Use Fixed Value'),
			'align'     =>'left',
			'index'     => 'use_fixed_value',
			'type'      => 'options',
			'options'   => Mage::getModel('adminhtml/system_config_source_yesno')->toArray(),
		));

		$this->addColumn('use_free_shipping', array
		(
			'header'    => Mage::helper('transportation')->__('Free Shipping'),
			'align'     =>'left',
			'index'     => 'use_free_shipping',
			'type'      => 'options',
			'options'   => Mage::getModel('adminhtml/system_config_source_yesno')->toArray(),
		));
		*/
		
		return parent::_prepareColumns();
	}

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('ticket_id');
        $this->getMassactionBlock()->setFormFieldName('tickets');

		/*
        $this->getMassactionBlock()->addItem('delete', array
		(
			'label'    => Mage::helper('Query_NovaPontoCom')->__('Delete'),
			'url'      => $this->getUrl('* / * /massDelete'),
			'confirm'  => Mage::helper('Query_NovaPontoCom')->__('Are you sure?')
        ));
		*/

		return $this;
    }

	public function getRowUrl($row)
	{
		return $this->getUrl('*/*/edit', array('id' => $row->getId()));
	}
}
