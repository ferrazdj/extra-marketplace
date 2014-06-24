<?php

class Query_NovaPontoCom_Block_ImportOrderError_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct()
	{
		parent::__construct();
		$this->setId('novaPontoCom_importOrderError_grid');
		$this->setDefaultSort('error_id');
		$this->setDefaultDir('ASC');
		$this->setSaveParametersInSession(true);
	}

	protected function _prepareCollection()
	{
		$collection = Mage::getModel('Query_NovaPontoCom/importOrderError')->getCollection();
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}

	protected function _prepareColumns()
	{
		$this->addColumn('error_id', array
		(
			'header'    => Mage::helper('Query_NovaPontoCom')->__('ID'),
			'align'     =>'right',
			'width'     => '50px',
			'index'     => 'error_id',
		));

		$this->addColumn('order_api_id', array
		(
			'header'    => Mage::helper('Query_NovaPontoCom')->__('Order API ID'),
			'align'     =>'left',
			'index'     => 'order_api_id',
		));

		$this->addColumn('created_at', array
		(
			'header'    => Mage::helper('Query_NovaPontoCom')->__('Created At'),
			'align'     =>'left',
			'index'     => 'created_at',
			'type'  	=> 'datetime',
		));

		$this->addColumn('value', array
		(
			'header'    => Mage::helper('Query_NovaPontoCom')->__('Value'),
			'align'     =>'left',
			'index'     => 'value',
			'type'  => 'currency',
            'currency' => 'order_currency_code',
		));
		
		$this->addColumn('message', array
		(
			'header'    => Mage::helper('Query_NovaPontoCom')->__('Message'),
			'align'     =>'left',
			'index'     => 'message',
		));
		
		return parent::_prepareColumns();
	}

    protected function _prepareMassaction()
    {
        return false;
    }

	public function getRowUrl($row)
	{
		return false;
	}
}
