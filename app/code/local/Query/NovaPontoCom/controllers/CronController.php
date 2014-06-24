<?php
class Query_NovaPontoCom_CronController extends Mage_Core_Controller_Front_Action
{
	public function indexAction()
	{
		$mageProduct = Mage::getModel('catalog/product')->load(3402);

// foreach ($collection as $item) {
// 	$mageProduct = $item;
// }
		$brandIndex = Mage::helper('Query_NovaPontoCom')->getProductFieldsConfig('brand');
		if(!Mage::helper('Query_NovaPontoCom')->getProductFieldsConfig('brand_type')) {
			$brandValue = $mageProduct->getData($brandIndex);
		} else {
			$brandValue = $mageProduct->getAttributeText($brandIndex);
		}

			//escapa quebras de linha na descricao 
			$description = str_replace("\r\n", "<br>", $mageProduct->getData('description'));
			$description = str_replace("\n", "<br>", $description);
			
			// monta vetor de dados a serem enviados
			$productData = array();
			$productData['skuIdOrigin'] = $mageProduct->getSku();
			$productData['sellingTitle'] = $mageProduct->getData('name');
			$productData['brand'] = $brandValue;
			$productData['defaultPrice'] = round($mageProduct->getPrice(), 2);
			$productData['salePrice'] = round(($mageProduct->getSpecialPrice() ? $mageProduct->getSpecialPrice() : $mageProduct->getPrice()), 2);
			$productData['Weight'] = round($mageProduct->getData('weight'), 2);
			$productData['availableQuantity'] = intval($mageProduct->getStockItem()->getQty());

			Zend_Debug::dump($productData);
	}
}
