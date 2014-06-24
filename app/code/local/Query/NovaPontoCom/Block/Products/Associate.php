<?php

class Query_NovaPontoCom_Block_Products_Associate extends Mage_Core_Block_Template
{
	private $_product;

	public function setProduct($product)
	{
		$this->_product = $product;
	}

	public function getProduct()
	{
		return $this->_product;
	}

	public function getProductImageUrl($product)
	{
		$imageUrl = $this->getSkinUrl('query/novapontocom/images/no-image.png');

		try
		{
			$imageUrl = (string) Mage::helper('catalog/image')->init($product, 'image')->resize(250);
		}
		catch(Exception $e)
		{

		}

		return $imageUrl;
	}

	public function getAssociatedProductsData($product)
	{
		$data = $product->getData('novaPontoCom_associatedProds');
		$json = json_decode($data, true);
		return ($json ? $json : array());
	}
}