<?php

class Query_NovaPontoCom_Block_Products_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct()
    {
        parent::__construct();
        $this->setId('novaPontoCom_product_grid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        // $this->setVarNameFilter('product_filter');
    }

    protected function _getStore()
    {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

    protected function _prepareCollection()
    {
        $store = $this->_getStore();
        $collection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('attribute_set_id')
            ->addAttributeToSelect('type_id');

        if(Mage::helper('catalog')->isModuleEnabled('Mage_CatalogInventory'))
        {
            $collection->joinField('qty',
                'cataloginventory/stock_item',
                'qty',
                'product_id=entity_id',
                '{{table}}.stock_id=1',
                'left');
        }
        if($store->getId())
        {
            //$collection->setStoreId($store->getId());
            $adminStore = Mage_Core_Model_App::ADMIN_STORE_ID;
            $collection->addStoreFilter($store);
            $collection->joinAttribute
            (
                'name',
                'catalog_product/name',
                'entity_id',
                null,
                'inner',
                $adminStore
            );
            $collection->joinAttribute
            (
                'custom_name',
                'catalog_product/name',
                'entity_id',
                null,
                'inner',
                $store->getId()
            );
            $collection->joinAttribute
            (
                'status',
                'catalog_product/status',
                'entity_id',
                null,
                'inner',
                $store->getId()
            );
            $collection->joinAttribute
            (
                'novaPontoCom_importerInfoId',
                'catalog_product/novaPontoCom_importerInfoId',
                'entity_id',
                null,
                'inner',
                $store->getId()
            );
            $collection->joinAttribute
            (
                'novaPontoCom_importError',
                'catalog_product/novaPontoCom_importError',
                'entity_id',
                null,
                'inner',
                $store->getId()
            );
            $collection->joinAttribute
            (
                'novaPontoCom_status',
                'catalog_product/novaPontoCom_status',
                'entity_id',
                null,
                'inner',
                $store->getId()
            );
            $collection->joinAttribute
            (
                'novaPontoCom_statusCode',
                'catalog_product/novaPontoCom_statusCode',
                'entity_id',
                null,
                'inner',
                $store->getId()
            );
            $collection->joinAttribute
            (
                'novaPontoCom_apiSku',
                'catalog_product/novaPontoCom_apiSku',
                'entity_id',
                null,
                'inner',
                $store->getId()
            );
            $collection->joinAttribute
            (
                'novaPontoCom_associatedProds',
                'catalog_product/novaPontoCom_associatedProds',
                'entity_id',
                null,
                'inner',
                $store->getId()
            );
            $collection->joinAttribute
            (
                'visibility',
                'catalog_product/visibility',
                'entity_id',
                null,
                'inner',
                $store->getId()
            );
            $collection->joinAttribute
            (
                'price',
                'catalog_product/price',
                'entity_id',
                null,
                'left',
                $store->getId()
            );
        }
        else
        {
            $collection->addAttributeToSelect('price');
            $collection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner');
            $collection->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner');
            $collection->joinAttribute('novaPontoCom_importerInfoId', 'catalog_product/novaPontoCom_importerInfoId', 'entity_id', null, 'left');
            $collection->joinAttribute('novaPontoCom_importError', 'catalog_product/novaPontoCom_importError', 'entity_id', null, 'left');
            $collection->joinAttribute('novaPontoCom_status', 'catalog_product/novaPontoCom_status', 'entity_id', null, 'left');
            $collection->joinAttribute('novaPontoCom_statusCode', 'catalog_product/novaPontoCom_statusCode', 'entity_id', null, 'inner');
            $collection->joinAttribute('novaPontoCom_apiSku', 'catalog_product/novaPontoCom_apiSku', 'entity_id', null, 'left');
            $collection->joinAttribute('novaPontoCom_associatedProds', 'catalog_product/novaPontoCom_associatedProds', 'entity_id', null, 'left');
        }

        $collection->addAttributeToFilter('novaPontoCom_statusCode', array('gt' => '-1'));

        $this->setCollection($collection);

        parent::_prepareCollection();
        $this->getCollection()->addWebsiteNamesToResult();
        return $this;
    }

    protected function _addColumnFilterToCollection($column)
    {
        if ($this->getCollection())
        {
            if ($column->getId() == 'websites')
            {
                $this->getCollection()->joinField('websites',
                    'catalog/product_website',
                    'website_id',
                    'product_id=entity_id',
                    null,
                    'left');
            }
        }
        return parent::_addColumnFilterToCollection($column);
    }

    protected function _prepareColumns()
    {
        $this->addColumn('entity_id',
            array
            (
                'header'=> Mage::helper('catalog')->__('ID'),
                'width' => '50px',
                'type'  => 'number',
                'index' => 'entity_id',
        	)
        );
        $this->addColumn('name',
            array
            (
                'header'=> Mage::helper('catalog')->__('Name'),
                'index' => 'name',
        	)
        );

        $store = $this->_getStore();
        if ($store->getId())
        {
            $this->addColumn('custom_name',
                array
                (
                    'header'=> Mage::helper('catalog')->__('Name in %s', $store->getName()),
                    'index' => 'custom_name',
            	)
            );
        }

       $this->addColumn('sku',
            array
            (
                'header'=> Mage::helper('catalog')->__('SKU'),
                'width' => '80px',
                'index' => 'sku',
        	)
        );

        $store = $this->_getStore();
        $this->addColumn('price',
            array
            (
                'header'=> Mage::helper('catalog')->__('Price'),
                'type'  => 'price',
                'currency_code' => $store->getBaseCurrency()->getCode(),
                'index' => 'price',
        	)
        );

        if (Mage::helper('catalog')->isModuleEnabled('Mage_CatalogInventory'))
        {
            $this->addColumn('qty',
                array
                (
                    'header'=> Mage::helper('catalog')->__('Qty'),
                    'width' => '100px',
                    'type'  => 'number',
                    'index' => 'qty',
            	)
            );
        }

        if (!Mage::app()->isSingleStoreMode())
        {
            $this->addColumn('websites',
                array
                (
                    'header'=> Mage::helper('catalog')->__('Websites'),
                    'width' => '100px',
                    'sortable'  => false,
                    'index'     => 'websites',
                    'type'      => 'options',
                    'options'   => Mage::getModel('core/website')->getCollection()->toOptionHash(),
            	)
            );
        }
		
		$this->addColumn('novaStatus',
            array
            (
                'header'    => Mage::helper('catalog')->__('Extra.com Status'),
                'filter'    => false,
                'sortable'  => false,
                'renderer'  => 'Query_NovaPontoCom/products_grid_renderer_apiStatus',
            )
        );
		
        $this->addColumn('action',
            array
            (
                'header'    => Mage::helper('catalog')->__('Action'),
                'filter'    => false,
                'sortable'  => false,
                'renderer'  => 'Query_NovaPontoCom/products_grid_renderer_apiAction',
            )
        );
		
		return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        /*
		$this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('product');

        $this->getMassactionBlock()->addItem('delete', 
        	array
        	(
            	'label'=> Mage::helper('catalog')->__('Delete'),
            	'url'  => $this->getUrl('* / * /massDelete'),
            	'confirm' => Mage::helper('catalog')->__('Are you sure?')
        	)
        );

        $statuses = Mage::getSingleton('catalog/product_status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', 
        	array
        	(
            	'label'=> Mage::helper('catalog')->__('Change status'),
            	'url'  => $this->getUrl('* / * /massStatus', array('_current'=>true)),
            	'additional' => array
            	(
                	'visibility' => array
                	(
                        'name' => 'status',
                        'type' => 'select',
                        'class' => 'required-entry',
                        'label' => Mage::helper('catalog')->__('Status'),
                        'values' => $statuses
                    )
             	)
        	)
        );

        if (Mage::getSingleton('admin/session')->isAllowed('catalog/update_attributes'))
        {
            $this->getMassactionBlock()->addItem('attributes', 
            	array
            	(
                	'label' => Mage::helper('catalog')->__('Update Attributes'),
                	'url'   => $this->getUrl('* /catalog_product_action_attribute/edit', array('_current'=>true))
            	)
            );
        }

        Mage::dispatchEvent('adminhtml_catalog_product_grid_prepare_massaction', array('block' => $this));
		*/
        return $this;
    }

    public function getRowUrl($row)
    {
        return false;
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }
}