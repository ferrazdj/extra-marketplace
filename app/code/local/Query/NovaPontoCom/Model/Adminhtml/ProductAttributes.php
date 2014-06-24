<?php

class Query_NovaPontoCom_Model_Adminhtml_ProductAttributes
{

    /**
     * Atributos ativos dos clientes
	 * (formato vetor de vetores)
	 * 
     * @return array
     */
    public function toOptionArray()
    {
		$attrModel = Mage::getModel('eav/config');
		$attributeCodes = $attrModel->getEntityAttributeCodes('catalog_product');
		$attributes = array();

		// adiciona opcao padrao
		$attributes[] = array
		(
			'value' => '', 
			'label' => Mage::helper('Query_NovaPontoCom')->__('Use Default')
		);

		foreach($attributeCodes as $code)
		{
			$attr = $attrModel->getAttribute('catalog_product', $code);
			$label = $attr->getData('frontend_label');

			if(!$label)
			{
				continue;
			}

			$attributes[] = array
			(
				'value' => $code, 
				'label' => Mage::helper('Query_NovaPontoCom')->__($label)
			);
		}
		
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
        $attrModel = Mage::getModel('eav/config');
		$attributeCodes = $attrModel->getEntityAttributeCodes('catalog_product');
		$attributes = array();

		// adiciona opcao padrao
		$attributes[] = array
		(
			'value' => Mage::helper('Query_NovaPontoCom')->__('Use Default')
		);

		foreach($attributeCodes as $code)
		{
			$attr = $attrModel->getAttribute('catalog_product', $code);
			$label = $attr->getData('frontend_label');

			if(!$label)
			{
				continue;
			}
			$attributes[] = array
			(
				$code => Mage::helper('Query_NovaPontoCom')->__($label)
			);
		}
		
		return $attributes;
    }

}