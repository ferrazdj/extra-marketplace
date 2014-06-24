<?php

	class Query_NovaPontoCom_Model_Integration_Product extends Query_NovaPontoCom_Model_Integration_Abstract
	{
		/* 
		 * 
		 * Realiza busca de produtos associados, pelo nome do produto;
		 * Chamada por products/firstSendProduct
		 * 
		 */

		public function search($searchString, $maxResults = 9999)
		{
			if(!$searchString)
			{
				throw new Exception("missing 'search string' parameter.");
			}

			// realiza paginacao na busca
			$pageSize = ($maxResults < 20) ? $maxResults : 20;
			$offSet = 0;
			$limit = $pageSize;

			// armazena o resultado
			$result = array();
			
			do
			{
				if( $this->_wsConnector->doGet
					(
						$this->_authToken, 
						$this->_appToken, 
						'/products', 
						'searchText='. $searchString . '&_offset='. $offSet . '&_limit='. $limit
					) )
				{
					$pageResult = json_decode($this->_wsConnector->getResponseBody(), true);
					
					if($pageResult)
					{
						$result = array_merge($result, $pageResult);
					}
				}
				else
				{
					$pageResult = null;
				}

				$offSet += $pageSize;
				$limit += $pageSize;

			} while($pageResult && count($pageResult) == $pageSize && count($result) < $maxResults);

			return $result;
		}


		/* 
		 * 
		 * Envia o produto como novo;
		 * Chamada por products/sendProductAsNewAction
		 * 
		 */

		public function associate($mageProduct, $skuId)
		{
			if(!$mageProduct || !$mageProduct->getId())
			{
				throw new Exception("missing Product Entity.");
			}

			if(!$skuId)
			{
				throw new Exception("missing SKU ID.");
			}

			// monta vetor de dados a serem enviados
			$productData = array();
			$productData['skuOrigin'] = $mageProduct->getSku();
			$productData['skuId'] = $skuId;
			$productData['defaultPrice'] = round($mageProduct->getPrice(), 2);
			$productData['salePrice'] = round(($mageProduct->getSpecialPrice() ? $mageProduct->getSpecialPrice() : $mageProduct->getPrice()), 2);
			$productData['installmentId'] = "";
			$productData['crossDockingTime'] = "0";
			$productData['availableQuantity'] = intval($mageProduct->getStockItem()->getQty() - $mageProduct->getStockItem()->getMinQty());
			$productData['totalQuantity'] = intval($mageProduct->getStockItem()->getQty());

			// envia carga de dados
			try
			{
				$this->_wsConnector->doPost($this->_authToken, $this->_appToken, 'sellerItems', json_encode($productData), 'json');
				$location = $this->_wsConnector->getResponseHeader("Content-Location");
				$responseJson = json_decode($this->_wsConnector->getResponseBody());

				// verifica url de consulta de estado da transacao
				if(!isset($responseJson->status) || $responseJson->status != 1)
				{
					if(isset($responseJson->message))
					{
						throw new Exception($responseJson->message);
					}
					else
					{
						throw new Exception("unrecognized error");
					}
				}
				else if(!$location)
				{
					throw new Exception("location header not found in the response.");
				}

				$apiSku = intval(str_replace("/sellerItems/", "", $location));

				if(!$apiSku)
				{
					throw new Exception(Mage::helper('Query_NovaPontoCom')->__("location header mal formated: '%s'", $location));
				}

				$mageProduct->setData('novaPontoCom_apiSku', $apiSku);
				$mageProduct->setData('novaPontoCom_statusCode', 5);
				$mageProduct->save();
			}
			catch(Exception $e)
			{
				throw new Exception($e->getMessage());
			}
		}


		/* 
		 * 
		 * Envia o produto como novo;
		 * Chamada por products/sendProductAsNewAction
		 * 
		 */

		public function load($mageProduct)
		{
			if(!$mageProduct || !$mageProduct->getId())
			{
				throw new Exception("missing Product Entity.");
			}

			if($mageProduct->getData('novaPontoCom_importerInfoId'))
			{
				throw new Exception("product Entity already synchronized.");
			}

			// pega as imagens
			$mediaApi = Mage::getModel('catalog/product_attribute_media_api');
			$images = $mediaApi->items($mageProduct->getId());
			$imagesUrl = array();

			foreach($images as $img)
			{
				$imagesUrl[] = $img['url'];
			}

			// categorias
			// $categoriesNames  = array('Teste>API');
			
			$categoriesNames  = array();
			foreach($mageProduct->getCategoryCollection() as $cat)
			{
				$cat = Mage::getModel('catalog/category')->load($cat->getId());
				$categoriesNames[] = $cat->getName();
			}
			
			// pega os indices de dimensoes e fabricante
			$widthIndex = Mage::helper('Query_NovaPontoCom')->getProductFieldsConfig('width');
			$heightIndex = Mage::helper('Query_NovaPontoCom')->getProductFieldsConfig('height');
			$lengthIndex = Mage::helper('Query_NovaPontoCom')->getProductFieldsConfig('length');
			$brandIndex = Mage::helper('Query_NovaPontoCom')->getProductFieldsConfig('brand');
			$hasBrand = Mage::helper('Query_NovaPontoCom')->getProductFieldsConfig('has_brand');

			// confere se deve utilizar valores padroes
			$widthValue = $widthIndex ? $mageProduct->getData($widthIndex) : Mage::helper('Query_NovaPontoCom')->getProductFieldsConfig('default_width');
			$heightValue = $heightIndex ? $mageProduct->getData($heightIndex) : Mage::helper('Query_NovaPontoCom')->getProductFieldsConfig('default_height');
			$lengthValue = $lengthIndex ? $mageProduct->getData($lengthIndex) : Mage::helper('Query_NovaPontoCom')->getProductFieldsConfig('default_length');
			
			if($hasBrand)
			{
				if(!$brandIndex)
				{
					if(Mage::helper('Query_NovaPontoCom')->getProductFieldsConfig('default_brand'))
					{
						$brandValue = Mage::helper('Query_NovaPontoCom')->getProductFieldsConfig('default_brand');
					}
					else
					{
						throw new Exception("please configure the brand attribute in System -> Configuration -> Extra.com -> Product Configuration");
					}
				}
				else
				{
					if(!Mage::helper('Query_NovaPontoCom')->getProductFieldsConfig('brand_type')) {
						$brandValue = $mageProduct->getData($brandIndex);
					} else {
						$brandValue = $mageProduct->getAttributeText($brandIndex);
					}
				}
			}
			else
			{
				$brandValue = Mage::helper('Query_NovaPontoCom')->__("Unavailable");
			}

			//escapa quebras de linha na descricao 
			$description = str_replace("\r\n", "<br>", $mageProduct->getData('description'));
			$description = str_replace("\n", "<br>", $description);
			
			// monta vetor de dados a serem enviados
			$productData = array();
			$productData['skuIdOrigin'] = $mageProduct->getSku();
			$productData['sellingTitle'] = $mageProduct->getData('name');
			$productData['description'] = $description;
			$productData['brand'] = $brandValue;
			$productData['defaultPrice'] = round($mageProduct->getPrice(), 2);
			$productData['salePrice'] = round(($mageProduct->getSpecialPrice() ? $mageProduct->getSpecialPrice() : $mageProduct->getPrice()), 2);
			$productData['categoryList'] = $categoriesNames;
			$productData['Weight'] = round($mageProduct->getData('weight'), 2);
			$productData['Length'] = round($lengthValue, 2);
			$productData['Width'] = round($widthValue, 2);
			$productData['Height'] = round($heightValue, 2);
			$productData['availableQuantity'] = intval($mageProduct->getStockItem()->getQty());
			$productData['images'] = $imagesUrl;

			if (Mage::helper('Query_NovaPontoCom')->getProductFieldsConfig('log_product')) {
				Mage::log($productData, null, $mageProduct->getId().'.log', true);
			}

			// confere campos obrigatorios
			if(!$productData['brand'])
			{
				throw new Exception("field 'Brand' is empty.");
			}
			if($productData['Weight'] == 0)
			{
				throw new Exception("field 'Weight' cannot be 0.");
			}
			if(!$productData['Width'])
			{
				throw new Exception("field 'Width' is empty.");
			}
			if(!$productData['Length'])
			{
				throw new Exception("field 'Length' is empty.");
			}
			if(!$productData['Height'])
			{
				throw new Exception("field 'Height' is empty.");
			}

			Mage::log($productData, null, $mageProduct->getId().'.log', true);

			// envia carga de dados
			try
			{
				$this->_wsConnector->doPost($this->_authToken, $this->_appToken, 'loads/products', gzencode(json_encode(array($productData)), 9));
				$location = $this->_wsConnector->getResponseHeader("Location");
				
				// verifica url de consulta de estado da transacao
				if(!$location)
				{
					throw new Exception("location header not found in the response.");
				}

				// consulta estado da transacao e atualiza status
				$this->consultLoad($mageProduct, $location);
			}
			catch(Exception $e)
			{
				throw new Exception($e->getMessage());
			}
		}



		/* 
		 * 
		 * Envia varios produtos como novos;
		 * Chamada pelo servico do cron
		 * 
		 */

		public function loadMultiple($mageProductIds)
		{
			if(!is_array($mageProductIds) || count($mageProductIds) <= 0)
			{
				return;
			}

			// configuracoes
			// pega os indices de dimensoes e fabricante
			$widthIndex = Mage::helper('Query_NovaPontoCom')->getProductFieldsConfig('width');
			$heightIndex = Mage::helper('Query_NovaPontoCom')->getProductFieldsConfig('height');
			$lengthIndex = Mage::helper('Query_NovaPontoCom')->getProductFieldsConfig('length');
			$brandIndex = Mage::helper('Query_NovaPontoCom')->getProductFieldsConfig('brand');
			$hasBrand = Mage::helper('Query_NovaPontoCom')->getProductFieldsConfig('has_brand');

			$defaultWidth = Mage::helper('Query_NovaPontoCom')->getProductFieldsConfig('default_width');
			$defaultHeight = Mage::helper('Query_NovaPontoCom')->getProductFieldsConfig('default_height');
			$defaultLength = Mage::helper('Query_NovaPontoCom')->getProductFieldsConfig('default_length');
			
			$useProductBrandAttribute = false;
			if($hasBrand)
			{
				if(!$brandIndex)
				{
					if(Mage::helper('Query_NovaPontoCom')->getProductFieldsConfig('default_brand'))
					{
						$brandValue = Mage::helper('Query_NovaPontoCom')->getProductFieldsConfig('default_brand');
					}
					else
					{
						return;
					}
				}
				else
				{
					// deve pegar marca do produto
					$useProductBrandAttribute = true;
				}
			}
			else
			{
				$brandValue = Mage::helper('Query_NovaPontoCom')->__("Unavailable");
			}


			// monta dados a serem enviados
			// percorre cada um dos produtos e coleta os dados necessarios
			$productsToSend = array();
			$productsErrors = array();

			foreach($mageProductIds as $productId)
			{
				try
				{
					$mageProduct = Mage::getModel('catalog/product')->load($productId);

					if(!$mageProduct || !$mageProduct->getId())
					{
						throw new Exception("missing Product Entity.");
					}

					if($mageProduct->getData('novaPontoCom_importerInfoId'))
					{
						throw new Exception("product Entity already synchronized.");
					}

					// confere se deve utilizar valores padroes
					$widthValue = $widthIndex ? $mageProduct->getData($widthIndex) : $defaultWidth;
					$heightValue = $heightIndex ? $mageProduct->getData($heightIndex) : $defaultHeight;
					$lengthValue = $lengthIndex ? $mageProduct->getData($lengthIndex) : $defaultLength;

					// pega as imagens
					$mediaApi = Mage::getModel('catalog/product_attribute_media_api');
					$images = $mediaApi->items($mageProduct->getId());
					$imagesUrl = array();

					foreach($images as $img)
					{
						$imagesUrl[] = $img['url'];
					}

					// categorias
					//$categoriesNames  = array('Teste>API');
					$categoriesNames  = array();
					foreach($mageProduct->getCategoryCollection() as $cat)
					{
						$cat = Mage::getModel('catalog/category')->load($cat->getId());
						$categoriesNames[] = $cat->getName();
					}

					//escapa quebras de linha na descricao 
					$description = str_replace("\r\n", "<br>", $mageProduct->getData('description'));
					$description = str_replace("\n", "<br>", $description);
					
					// monta vetor de dados a serem enviados
					$productData = array();
					$productData['skuIdOrigin'] = $mageProduct->getSku();
					$productData['sellingTitle'] = $mageProduct->getData('name');
					$productData['description'] = $description;
					$productData['brand'] = $useProductBrandAttribute ? $mageProduct->getData($brandIndex) : $brandValue;
					$productData['defaultPrice'] = round($mageProduct->getPrice(), 2);
					$productData['salePrice'] = round(($mageProduct->getSpecialPrice() ? $mageProduct->getSpecialPrice() : $mageProduct->getPrice()), 2);
					$productData['categoryList'] = $categoriesNames;
					$productData['Weight'] = round($mageProduct->getData('weight'), 2);
					$productData['Length'] = round($lengthValue, 2);
					$productData['Width'] = round($widthValue, 2);
					$productData['Height'] = round($heightValue, 2);
					$productData['availableQuantity'] = intval($mageProduct->getStockItem()->getQty());
					$productData['images'] = $imagesUrl;

					// confere campos obrigatorios
					if(!$productData['brand'])
					{
						throw new Exception("field 'Brand' is empty.");
					}
					if($productData['Weight'] == 0)
					{
						throw new Exception("field 'Weight' cannot be 0.");
					}
					if(!$productData['Width'])
					{
						throw new Exception("field 'Width' is empty.");
					}
					if(!$productData['Length'])
					{
						throw new Exception("field 'Length' is empty.");
					}
					if(!$productData['Height'])
					{
						throw new Exception("field 'Height' is empty.");
					}

					$productsToSend[] = $productData;
				}
				catch(Exception $e)
				{
					$productsErrors[$productId] = $e->getMessage();
				}
			}

			// envia carga de dados
			try
			{
				$location = false;
				
				if($productsToSend && count($productsToSend) > 0)
				{
					$this->_wsConnector->doPost($this->_authToken, $this->_appToken, 'loads/products', gzencode(json_encode($productsToSend), 9));
					$location = $this->_wsConnector->getResponseHeader("Location");
				}

				return array("location" => $location, "errors" => $productsErrors);
			}
			catch(Exception $e)
			{
				throw new Exception($e->getMessage());
			}
		}


		/* 
		 * 
		 * Busca a primeira informacao do produto enviado
		 * chamado por products/updateCandidateStatus
		 * 
		 */

		public function consultLoad($mageProduct, $location = null)
		{
			if(!$mageProduct || !$mageProduct->getId())
			{
				throw new Exception("missing Product Entity.");
			}

			if(!$location)
			{
				if(!$mageProduct->getData('novaPontoCom_importerInfoId'))
				{
					throw new Exception("missing 'Location' parameter.");
				}

				$location = "/loads/products/" . $mageProduct->getData('novaPontoCom_importerInfoId');
			}


			$this->_wsConnector->doGet($this->_authToken, $this->_appToken, $location);
			$responseJson = json_decode($this->_wsConnector->getResponseBody());

			// confere dados da transacao
			if(!isset($responseJson->importerInfoId) || !$responseJson->importerInfoId)
			{
				throw new Exception("information 'importerInfoId' not found.");
			}

			// confere dados da transacao
			if(!isset($responseJson->status) || !$responseJson->status)
			{
				throw new Exception("information 'status' not found.");
			}

			// armazena os valores
			$mageProduct->setData('novaPontoCom_importerInfoId', $responseJson->importerInfoId);
			$mageProduct->setData('novaPontoCom_status', $responseJson->status);
			$mageProduct->save();

			return true;
		}


		/* 
		 * 
		 * Consulta status do produto enviado a NovaPontoCom
		 * chamado por products/updateCandidateStatus
		 * 
		 */

		public function consultLoadItem($mageProduct)
		{
			if(!$mageProduct || !$mageProduct->getId())
			{
				throw new Exception("missing Product Entity.");
			}

			if(!$mageProduct->getData('novaPontoCom_importerInfoId'))
			{
				throw new Exception("missing 'Location' parameter.");
			}

			$location = "/loads/products/" . $mageProduct->getData('novaPontoCom_importerInfoId') . "/" . $mageProduct->getSku();
			
			$this->_wsConnector->doGet($this->_authToken, $this->_appToken, $location);
			$responseJson = json_decode($this->_wsConnector->getResponseBody());

			// confere dados da transacao
			if(!isset($responseJson->status) || !$responseJson->status)
			{
				throw new Exception("information 'status' not found.");
			}
			else if(!isset($responseJson->statusCode))
			{
				throw new Exception("information 'statusCode' not found.");
			}

			// confere se ha erros
			if(isset($responseJson->problems))
			{
				$mageProduct->setData('novaPontoCom_importError', json_encode($responseJson->problems));
			}

			// armazena os valores
			$mageProduct->setData('novaPontoCom_statusCode', $responseJson->statusCode);
			$mageProduct->setData('novaPontoCom_status', $responseJson->status);
			$mageProduct->save();

			return true;
		}


		/* 
		 * 
		 * Consulta sku do produto na API da NovaPontoCom
		 * chamado por products/updateCandidateStatus
		 * 
		 */

		public function consultApiSku($mageProduct)
		{
			if(!$mageProduct || !$mageProduct->getId())
			{
				throw new Exception("missing Product Entity.");
			}

			if(!$mageProduct->getData('novaPontoCom_importerInfoId'))
			{
				throw new Exception("missing 'Location' parameter.");
			}

			$location = "/sellerItems/skuOrigin/" . $mageProduct->getSku();

			$this->_wsConnector->doGet($this->_authToken, $this->_appToken, $location);
			$responseJson = json_decode($this->_wsConnector->getResponseBody());
			
			// armazena os valores
			$mageProduct->setData('novaPontoCom_apiSku', $responseJson->skuId);
			$mageProduct->save();
			
			return true;
		}
		

		public function getInfo($mageProduct)
		{
			if(!$mageProduct || !$mageProduct->getId())
			{
				Mage::log("Missing Product Entity.");
				return false;
			}

			if(!$mageProduct->getData('novaPontoCom_apiSku'))
			{
				Mage::log("Product without 'API SKU' data.");
				return false;
			}

			$location = "/sellerItems/" . $mageProduct->getData('novaPontoCom_apiSku');
			$this->_wsConnector->doGet($this->_authToken, $this->_appToken, $location);
			
			return $this->_wsConnector->getResponseBody();
		}
		

		/* 
		 * 
		 * Atualiza estoque do produto na API da NovaPontoCom
		 * chamado por products/syncProduct
		 * 
		 */

		public function updateStock($mageProduct, $forcedQty = null)
		{
			if(!$mageProduct || !$mageProduct->getId())
			{
				throw new Exception("missing Product Entity.");
			}

			if(!$mageProduct->getData('novaPontoCom_apiSku'))
			{
				throw new Exception("product without 'API SKU' data.");
			}

			$location = "/sellerItems/" . $mageProduct->getData('novaPontoCom_apiSku') . "/stock/";
			$stockData = array();
			$stockData['availableQuantity'] = intval($mageProduct->getStockItem()->getQty() - $mageProduct->getStockItem()->getMinQty());
			$stockData['totalQuantity'] = intval($mageProduct->getStockItem()->getQty());
			
			if($forcedQty)
			{
				$stockData['availableQuantity'] = intval($forcedQty - $mageProduct->getStockItem()->getMinQty());
				$stockData['totalQuantity'] = intval($forcedQty);
			}
			
			$this->_wsConnector->doPut($this->_authToken, $this->_appToken, $location, json_encode($stockData));
			
			return true;
		}
		

		/* 
		 * 
		 * Atualiza preco do produto na API da NovaPontoCom
		 * chamado por products/syncProduct
		 * 
		 */

		public function updatePrice($mageProduct)
		{
			if(!$mageProduct || !$mageProduct->getId())
			{
				throw new Exception("missing Product Entity.");
			}

			if(!$mageProduct->getData('novaPontoCom_apiSku'))
			{
				throw new Exception("product without 'API SKU' data.");
			}

			$location = "/sellerItems/" . $mageProduct->getData('novaPontoCom_apiSku') . "/prices/";
			$priceData = array();
			$priceData['defaultPrice'] = $mageProduct->getPrice();
			$priceData['salePrice'] = ($mageProduct->getSpecialPrice() ? $mageProduct->getSpecialPrice() : $mageProduct->getPrice());
			$priceData['installmentId'] = "";
			
			$this->_wsConnector->doPut($this->_authToken, $this->_appToken, $location, json_encode($priceData));
			
			return true;
		}
	}