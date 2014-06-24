<?php

class Query_NovaPontoCom_ShippingController extends Mage_Core_Controller_Front_Action
{
	public function freightAction()
	{
		// decodifica a requisicao
		$prods = $this->getRequest()->getParam('skuId');
		$zipCode = $this->getRequest()->getParam('zipCode');

		if(!$prods || !$zipCode)
		{
			echo "Missing parameters.";
			return;
		}

		// busca e organiza os produtos
		$items = array();

		$prodArray = explode("|", $prods);
		$prodCollection = array();

		foreach($prodArray as $prodData)
		{
			$prodData = explode(",", $prodData);
			$prodId = $prodData[0];
			$prodQty = $prodData[1];
			
			
			$product = Mage::getModel('catalog/product')->load($prodId);
			$qty = intval($prodQty);
			$prodCollection[$prodId] = $product;

			if(!$product || !$product->getId())
			{
				echo "Could not find product " . $prodId;
				return;
			}

			if($qty < 0)
			{
				continue;
			}

			$item = Mage::getModel('sales/quote_address_item');
			$item->setProduct($product);
			$item->setQty($qty);

			$items[] = $item;
		}

		// calcula o valor do frete
		$rate = $this->_calcShippingRate($items, $zipCode);

		// confere se ha formas de envio disponiveis e pega a mais barata
		if($rate === false)
		{
			$this->getResponse()->setBody(json_encode(array()));
			return;
		}
		
		// monta o retorno
		$result = array();
		$result['freights'] = array();
		$result['freightAdditionalInfo'] = $rate->getCarrier() . "/" . $rate->getMethod();
		$result['sellerMpToken'] = Mage::helper('Query_NovaPontoCom')->getConfigSetting('app_token');
		
		foreach($items as $item)
		{
			$productRate = $this->_calcShippingRate(array($item), $zipCode);

			$productResult = array();
			$productResult['skuIdOrigin'] = $item->getProduct()->getId();
			$productResult['quantity'] = $item->getQty();
			$productResult['freightAmount'] = $productRate->getPrice();
			$productResult['deliveryTime'] = 5;
			$productResult['freightType'] = "NORMAL";

			$result['freights'][] = $productResult;
		}
		
		$this->getResponse()->setHeader('Content-type', 'application/json');
		$this->getResponse()->setBody(json_encode($result));
	}



	private function _calcShippingRate($items, $zipCode)
	{
		// calcula valores totais
		$totalWeight = 0;
		$totalValue = 0.0;
		$totalQty = 0;

		foreach($items as $productId => $item)
		{
			$totalWeight += $item->getQty() * $item->getProduct()->getWeight();
			$totalValue += $item->getQty() * $item->getProduct()->getPrice();
			$totalQty += $item->getQty();
		}

		$request = Mage::getModel('shipping/rate_request');
        $request->setAllItems($items);
        $request->setDestCountryId("BR");
        $request->setDestRegionId("");
        $request->setDestRegionCode("");

        $request->setDestStreet("");
        $request->setDestCity("");
        $request->setDestPostcode($zipCode);

        $request->setPackageValue($totalValue);
        //$request->setPackageValueWithDiscount($product->getPrice());
        $request->setPackageWeight($totalWeight);
        $request->setPackageQty($totalQty);

        $rates = Mage::getModel('shipping/shipping')->collectRates($request)->getResult();

        // confere se ha formas de envio disponiveis e pega a mais barata
		if(count($rates->asArray()) <= 0)
		{
			return false;
		}

		return $rates->getCheapestRate();
	}
}