<?php

class Query_NovaPontoCom_Model_Adminhtml_CustomerAttributes
{
    /**
     * Atributos ativos dos clientes
	 * (formato vetor de vetores)
	 * 
     * @return array
     */

    public function toOptionArray()
    {
		// atributos de clientes
		$attributes = array();
		$collection = Mage::getModel('customer/attribute')->getCollection();
	
		foreach($collection as $attr)
		{
			// somente atributos visiveis
			if(!$attr->getIsVisible())
			{
				continue;
			}
			
			$attributes[] = array
			(
				'value' => "customer/" . $attr->getData('attribute_code'), 
				'label' => "[" . Mage::helper('Query_NovaPontoCom')->__("Customer") . "] " . Mage::helper('Query_NovaPontoCom')->__($attr->getData('frontend_label'))
			);
		}

		// atributos de enderecos de clientes
		$resource = Mage::getResourceSingleton('customer/address');
		$attrArray = Mage::getSingleton('eav/config')->getEntityAttributeCodes('customer_address', null);
		
		foreach($attrArray as $attrCode)
		{
			$attributes[] = array
			(
				'value' => "address/" . $attrCode, 
				'label' => "[" . Mage::helper('Query_NovaPontoCom')->__("Address") . "] " . Mage::helper('Query_NovaPontoCom')->__($resource->getAttribute($attrCode)->getStoreLabel())
			);
		}

		// atributos rua
		$attributes[] = array
		(
			'value' => 'address/street_1', 
			'label' => "[" . Mage::helper('Query_NovaPontoCom')->__("Address") . "] " . Mage::helper('Query_NovaPontoCom')->__('Street 1')
		);
		$attributes[] = array
		(
			'value' => 'address/street_2', 
			'label' => "[" . Mage::helper('Query_NovaPontoCom')->__("Address") . "] " . Mage::helper('Query_NovaPontoCom')->__('Street 2')
		);
		$attributes[] = array
		(
			'value' => 'address/street_3', 
			'label' => "[" . Mage::helper('Query_NovaPontoCom')->__("Address") . "] " . Mage::helper('Query_NovaPontoCom')->__('Street 3')
		);
		$attributes[] = array
		(
			'value' => 'address/street_4', 
			'label' => "[" . Mage::helper('Query_NovaPontoCom')->__("Address") . "] " . Mage::helper('Query_NovaPontoCom')->__('Street 4')
		);
		
		return $attributes;
    }


    /**
     * Atributos ativos dos clientes
	 * (formato vetor: key => value)
	 * 
     * @return array
     */

    public function toArray()
    {
        // atributos de clientes
        $attributes = array();
		$collection = Mage::getModel('customer/attribute')->getCollection();
	
		foreach($collection as $attr)
		{
			// somente atributos visiveis
			if(!$attr->getIsVisible())
			{
				continue;
			}
			
			$attributes[] = array
			(
				"customer/" . $attr->getData('attribute_code') => "[" . Mage::helper('Query_NovaPontoCom')->__("Customer") . "] " . Mage::helper('Query_NovaPontoCom')->__($attr->getData('frontend_label'))
			);
		}

		// atributos de enderecos de clientes
		$resource = Mage::getResourceSingleton('customer/address');
		$attrArray = Mage::getSingleton('eav/config')->getEntityAttributeCodes('customer_address', null);
		
		foreach($attrArray as $attrCode)
		{
			$attributes[] = array
			(
				"address/" . $attrCode => "[" . Mage::helper('Query_NovaPontoCom')->__("Address") . "] " . Mage::helper('Query_NovaPontoCom')->__($resource->getAttribute($attrCode)->getStoreLabel())
			);
		}

		// atributos rua
		$attributes[] = array
		(
			'address/street_1' => "[" . Mage::helper('Query_NovaPontoCom')->__("Address") . "] " . Mage::helper('Query_NovaPontoCom')->__('Street 1')
		);
		$attributes[] = array
		(
			'address/street_2' => "[" . Mage::helper('Query_NovaPontoCom')->__("Address") . "] " . Mage::helper('Query_NovaPontoCom')->__('Street 2')
		);
		$attributes[] = array
		(
			'address/street_3' => "[" . Mage::helper('Query_NovaPontoCom')->__("Address") . "] " . Mage::helper('Query_NovaPontoCom')->__('Street 3')
		);
		$attributes[] = array
		(
			'address/street_4' => "[" . Mage::helper('Query_NovaPontoCom')->__("Address") . "] " . Mage::helper('Query_NovaPontoCom')->__('Street 4')
		);
		
		return $attributes;
    }

}