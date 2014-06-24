<?php

class Query_NovaPontoCom_Model_Adminhtml_CustomerAddressAttributes
{

    /**
     * Atributos ativos dos enderecos dos clientes
	 * (formato vetor de vetores)
	 * 
     * @return array
     */
    public function toOptionArray()
    {
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

		// adiciona variacao do campo street
		$attributes[] = array
		(
			'value' => 'street_1', 
			'label' => Mage::helper('Query_NovaPontoCom')->__('Street 1')
		);
		$attributes[] = array
		(
			'value' => 'street_2', 
			'label' => Mage::helper('Query_NovaPontoCom')->__('Street 2')
		);
		$attributes[] = array
		(
			'value' => 'street_3', 
			'label' => Mage::helper('Query_NovaPontoCom')->__('Street 3')
		);
		$attributes[] = array
		(
			'value' => 'street_4', 
			'label' => Mage::helper('Query_NovaPontoCom')->__('Street 4')
		);
		
		return $attributes;
    }

    /**
     * Atributos ativos dos enderecos dos clientes
	 * (formato vetor: key => value)
	 * 
     * @return array
     */
    public function toArray()
    {
        $resource = Mage::getResourceSingleton('customer/address');
		$attrArray = Mage::getSingleton('eav/config')->getEntityAttributeCodes('customer_address', null);
		
		foreach($attrArray as $attrCode)
		{
			$attributes[] = array
			(
				"address/" . $attrCode => "[" . Mage::helper('Query_NovaPontoCom')->__("Address") . "] " . Mage::helper('Query_NovaPontoCom')->__($resource->getAttribute($attrCode)->getStoreLabel())
			);
		}

		// adiciona variacao do campo street
		$attributes[] = array
		(
			'street_1' => Mage::helper('Query_NovaPontoCom')->__('Street 1')
		);
		$attributes[] = array
		(
			'street_2' => Mage::helper('Query_NovaPontoCom')->__('Street 2')
		);
		$attributes[] = array
		(
			'street_3' => Mage::helper('Query_NovaPontoCom')->__('Street 3')
		);
		$attributes[] = array
		(
			'street_4' => Mage::helper('Query_NovaPontoCom')->__('Street 4')
		);
		
		return $attributes;
    }

}