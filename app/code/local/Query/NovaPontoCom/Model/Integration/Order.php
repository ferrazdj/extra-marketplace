<?php

class Query_NovaPontoCom_Model_Integration_Order extends Query_NovaPontoCom_Model_Integration_Abstract
{
	/**
	 *
	 * Realiza a busca de um pedido através de seu Id na API da NovaPontoCom
	 *
	 **/

	public function getOrderByApiId($apiId)
	{
		if(!$apiId)
		{
			throw new Exception("NovaPontoCom_apiId not defined.");
		}

		$this->_wsConnector->doGet($this->_authToken, $this->_appToken, 'orders/' . $apiId);
		$apiOrder = json_decode($this->_wsConnector->getResponseBody(),true);
		
		return $apiOrder;
	}


	/**
	 *
	 * Busca todos pedidos na API da NovaPontocom, dado o status
	 *
	 **/

	public function getApiOrdersByStatus($status)
	{
		$apiOrders = array();

		if(!$status)
		{
			return $apiOrders;
		}

		try
		{
			$apiOrders = $this->_search('/orders/status/' . $status);
		}
		catch(Exception $e)
		{
			Mage::helper('Query_NovaPontoCom')->saveLogInfo
			(
				'order', 
				'get-' . $status, 
				'sales/order', 
				0, 
				time(), 
				0, 
				$e->getCode(), 
				Mage::helper("Query_NovaPontoCom")->__($e->getMessage())
				
			);
		}

		return $apiOrders;
	}


	/**
	 *
	 * Faz a busca do pedido de acordo o status e retorna um vetor.
	 *
	 **/

	private function _search($location)
	{
		if(!$location)
		{
			throw new Exception("Search location was not defined on order");
		}

		$pageSize = 20;
		$offSet = 0;
		$limit = $pageSize;
		$result = array();
		do
		{
			try
			{
				if( $this->_wsConnector->doGet
					(
						$this->_authToken, 
						$this->_appToken, 
						$location, 
						'&_offset='. $offSet . '&_limit='. $limit
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
			}
			catch(Exception $e)
			{
				throw new Exception($e->getMessage());
			}
			
			$offSet += $pageSize;

		} while($pageResult && count($pageResult) == $pageSize);

		return $result;
	}



	/**
	 *
	 * Cria um pedido no magento, recebe o pedido vindo do NovaPontoCom e o status dele
	 *
	 **/

	public function createMageOrder($apiOrder, $apiOrderStatus)
	{
		if($this->_existsOrder($apiOrder['orderId']))
		{
			return;
		}

		$transaction 	= Mage::getModel('core/resource_transaction');
		$storeId 		= Mage::getStoreConfig('novapontocom/settings/default_store_id');

		if(!$storeId)
		{
			$storeId = Mage::app()->getStore()->getId();
		}

		// =============================================
		// dados do cliente
		// =============================================

		// nome do cliente
		$customerName = $apiOrder['customerName'];
		$separatorIndex = strpos($apiOrder['customerName'], " ");
		
		if($separatorIndex !== false)
		{
			$customerFirstName = substr($customerName, 0, $separatorIndex);
			$customerLastName = substr($customerName, $separatorIndex + 1);
		}
		else
		{
			$customerFirstName = $customerName;
			$customerLastName = "";
		}


		// dados personalizados configurados na administracao
		$cpfNumber = isset($apiOrder["documentNr"]) ? preg_replace('/\D/', '', $apiOrder["documentNr"]) : 0;

		// pessoa fisica
		if(strlen($cpfNumber) == 11)
		{
			$customerConfig = array();
			$customerConfig['name'] = array
			(
				"config" => Mage::helper('Query_NovaPontoCom')->getCustomerAttributeConfig('individual', 'name'),
				"value"  => $customerFirstName
			);
			$customerConfig['lastname'] = array
			(
				"config" => Mage::helper('Query_NovaPontoCom')->getCustomerAttributeConfig('individual', 'last_name'),
				"value"  => $customerLastName
			);
			$customerConfig['cpf'] = array
			(
				"config" => Mage::helper('Query_NovaPontoCom')->getCustomerAttributeConfig('individual', 'cpf'),
				"value"  => $cpfNumber
			);
			$customerConfig['celphone'] = array
			(
				"config" => Mage::helper('Query_NovaPontoCom')->getCustomerAttributeConfig('individual', 'cel_phone'),
				"value"  => (isset($apiOrder["celphone"]) ? $apiOrder["celphone"] : "")
			);
			
		}
		// pessoa juridica
		else if(strlen($cpfNumber) == 14)
		{
			$customerConfig['company'] = array
			(
				"config" => Mage::helper('Query_NovaPontoCom')->getCustomerAttributeConfig('company', 'company_name'),
				"value"  => $customerName
			);
			$customerConfig['ie'] = array
			(
				"config" => Mage::helper('Query_NovaPontoCom')->getCustomerAttributeConfig('company', 'ie'),
				"value"  => (isset($apiOrder["inscricaoEstadual"]) ? $apiOrder["inscricaoEstadual"] : "")
			);
			$customerConfig['cnpj'] = array
			(
				"config" => Mage::helper('Query_NovaPontoCom')->getCustomerAttributeConfig('company', 'cnpj'),
				"value"  => $cpfNumber
			);
		}
		else
		{
			throw new Exception("Invalid CPF/CNPJ number: '" . $cpfNumber . "'");
		}

		$addressConfig = array();
		$addressConfig['street'] 			= Mage::getStoreConfig('novapontocom_customer/address/street');
		$addressConfig['number'] 			= Mage::getStoreConfig('novapontocom_customer/address/number');
		$addressConfig['complement'] 		= Mage::getStoreConfig('novapontocom_customer/address/complement');
		$addressConfig['district'] 			= Mage::getStoreConfig('novapontocom_customer/address/district');

		
		// =============================================
		// cria instancia do pedido
		// =============================================
		$mageOrder = Mage::getModel('sales/order')
			->setStoreId($storeId)
			->setQuoteId(0)
			->setData('novaPontoCom_status', $apiOrderStatus)
			->setData('novaPontoCom_apiId', $apiOrder['orderId'])
			->setGlobal_currency_code('BRL')
			->setData('base_to_global_rate', 1.000)
			->setData('base_to_order_rate', 1.000)
			->setBase_currency_code('BRL')
			->setStore_currency_code('BRL')
			->setOrder_currency_code('BRL')
			->setState(Mage_Sales_Model_Order::STATE_NEW, 'pending', 'Order created from Extra.com API.', false);

		$mageOrder->setShippingAmount($apiOrder['freightChargedAmount']);
		
		
		$mageOrder->setCustomerEmail($apiOrder['customerEmail'])
			  ->setCustomerFirstname($customerFirstName)
			  ->setCustomerLastname($customerLastName)
			  ->setCustomerGroupId(1)
			  ->setCustomer_is_guest(1);

		
		// =============================================
		// cria instancia dos enderecos
		// =============================================
		$billingAddress = Mage::getModel('sales/order_address')
			->setStoreId($storeId)
			->setAddressType(Mage_Sales_Model_Quote_Address::TYPE_BILLING)
			->setFirstname($customerFirstName)
			->setMiddlename('')
			->setLastname($customerLastName)
			->setCity($apiOrder['billingInformations'][0]['city'])
			->setCountry_id($apiOrder['billingInformations'][0]['countryId'])
			->setRegion_id($apiOrder['billingInformations'][0]['state'])
			->setPostcode($apiOrder['billingInformations'][0]['postalCd'])
			->setTelephone($apiOrder['customerPhoneNumber']);
		
		$billingAddress->setData($addressConfig['street'], $apiOrder['billingInformations'][0]['address']);
		$billingAddress->setData($addressConfig['number'], $apiOrder['billingInformations'][0]['addressNr']);
		$billingAddress->setData($addressConfig['complement'], $apiOrder['billingInformations'][0]['additionalInfo']);
		$billingAddress->setData($addressConfig['district'], $apiOrder['billingInformations'][0]['quarter']);
		
		// trata se os campos utilizados sao partes do atributo street
		$street = array();
		$street = $this->_setAddressStreet($street, $addressConfig['street'], $apiOrder['billingInformations'][0]['address']);
		$street = $this->_setAddressStreet($street, $addressConfig['number'], $apiOrder['billingInformations'][0]['addressNr']);
		$street = $this->_setAddressStreet($street, $addressConfig['complement'], $apiOrder['billingInformations'][0]['additionalInfo']);
		$street = $this->_setAddressStreet($street, $addressConfig['district'], $apiOrder['billingInformations'][0]['quarter']);
		$billingAddress->setStreet($street);
		
		
		
		$shippingAddress = Mage::getModel('sales/order_address')
			->setStoreId($storeId)
			->setAddressType(Mage_Sales_Model_Quote_Address::TYPE_SHIPPING)
			->setFirstname($customerFirstName)
			->setMiddlename('')
			->setLastname($customerLastName)
			->setCity($apiOrder['billingInformations'][0]['city'])
			->setCountry_id($apiOrder['billingInformations'][0]['countryId'])
			->setRegion_id($apiOrder['billingInformations'][0]['state'])
			->setPostcode($apiOrder['billingInformations'][0]['postalCd'])
			->setTelephone($apiOrder['customerPhoneNumber']);
		
		
		$shippingAddress->setData($addressConfig['street'], $apiOrder['shippingInformationsList'][0]['address']);
		$shippingAddress->setData($addressConfig['number'], $apiOrder['shippingInformationsList'][0]['addressNr']);
		$shippingAddress->setData($addressConfig['complement'], $apiOrder['shippingInformationsList'][0]['additionalInfo']);
		$shippingAddress->setData($addressConfig['district'], $apiOrder['shippingInformationsList'][0]['quarter']);
		
		$street = array();
		$street = $this->_setAddressStreet($street, $addressConfig['street'], $apiOrder['shippingInformationsList'][0]['address']);
		$street = $this->_setAddressStreet($street, $addressConfig['number'], $apiOrder['shippingInformationsList'][0]['addressNr']);
		$street = $this->_setAddressStreet($street, $addressConfig['complement'], $apiOrder['shippingInformationsList'][0]['additionalInfo']);
		$street = $this->_setAddressStreet($street, $addressConfig['district'], $apiOrder['shippingInformationsList'][0]['quarter']);
		$shippingAddress->setStreet($street);


		// =============================================
		// preenche campos configurados personalizados
		// =============================================
		foreach($customerConfig as $key => $entry)
		{
			if($entry['config']['entity'] == "customer")
			{
				$mageOrder->setData($entry['config']['attribute'], $entry['value']);
				$mageOrder->setData("customer_" . $entry['config']['attribute'], $entry['value']);
			}
			else if($entry['config']['entity'] == "address")
			{
				$billingAddress->setData($entry['config']['attribute'], $entry['value']);
				$shippingAddress->setData($entry['config']['attribute'], $entry['value']);
			}
		}

		

		// =============================================
		// cria instancia do frete
		// =============================================
		$shippingMethodCode = $apiOrder['freightAdditionalInfo'];
		
		if(!$shippingMethodCode)
		{
			$shippingMethodCode = Mage::helper('Query_NovaPontoCom')->getConfigSetting('order_shipment_method');
		}

		$shippingMethodTitle = Mage::helper('Query_NovaPontoCom')->getShippingMethodTitle($shippingMethodCode);

		$mageOrder->setShippingAddress($shippingAddress);
		$mageOrder->setBillingAddress($billingAddress);
		$mageOrder->setShipping_method($shippingMethodCode);
		$mageOrder->setShipping_description($shippingMethodTitle);
		
		
		// =============================================
		// cria instancia do pagamento
		// =============================================
		$orderPayment = Mage::getModel('sales/order_payment')
			->setStoreId($storeId)
			->setCustomerPaymentId(0)
			->setMethod('Query_NovaPontoCom')
			->setAction('yes')
			->setPo_number(' – ')
			->setAdditionalInformation('api_type', $apiOrder['paymentTpId']);
		$mageOrder->setPayment($orderPayment);
		
		$subTotal 			= 0;
		$agroupedOrderItems = array();
		$orderItemQnty 		= 0;
		$numIt 				= 0;
		
		
		// =============================================
		// adiciona os produtos ao pedido
		// =============================================

		// transforma o vetor do novaPontoCom em um vetor formatado
		foreach($apiOrder["orderItems"] as $apiProduct)
		{
			$apiSku = $apiProduct["skuId"];
			
			// caso produto jah exista
			if(isset($agroupedOrderItems[$apiSku]))
			{
				$agroupedOrderItems[$apiSku]["qnty"]++;
			}
			// caso nao exista
			else
			{
				$agroupedOrderItems[$apiSku] = array();
				$agroupedOrderItems[$apiSku]["qnty"] = 1;
				$agroupedOrderItems[$apiSku]["price"] = $apiProduct["salePrice"];
			}
		}
		
		// realiza a adicao
		$mageProducts = array();

		foreach($agroupedOrderItems as $apiSku => $item)
		{
			// busca produto pelo sku do marketplace do Extra
			$mageProduct = Mage::getModel('catalog/product')->loadByAttribute('novaPontoCom_apiSku', $apiSku);
			
			if(!$mageProduct || $mageProduct->getId() == NULL)
			{
				throw new Exception(Mage::helper('Query_NovaPontoCom')->__("Could not load product with API ID #") . $apiSku);
			}
			
			// conferencia redundante, caso a funcao loadByAttribute tenha um comportamento inadequado
			$mageProduct = Mage::getModel('catalog/product')->load($mageProduct->getId());
			
			if(!$mageProduct->getData('novaPontoCom_apiSku') || $mageProduct->getData('novaPontoCom_apiSku') != $apiSku)
			{
				throw new Exception(Mage::helper('Query_NovaPontoCom')->__("Could not load product with API ID #") . $apiSku);
			}

			
			$mageProducts[] = $mageProduct;

			$rowTotal = $item['price'] * $item['qnty'];
			$orderItem = Mage::getModel('sales/order_item')
				->setStoreId($storeId)
				->setQuoteItemId(0)
				->setQuoteParentItemId(NULL)
				->setProductId($mageProduct->getId())
				->setProductType($mageProduct->getTypeId())
				->setQtyBackordered(NULL)
				->setTotalQtyOrdered($item['qnty'])
				->setQtyOrdered($item['qnty'])
				->setName($mageProduct->getName())
				->setSku($mageProduct->getSku())
				->setPrice($item['price'])
				->setBasePrice($item['price'])
				->setOriginalPrice($item['price'])
				->setRowTotal($rowTotal)
				->setBaseRowTotal($rowTotal);
			
			$subTotal += $rowTotal;
			$mageOrder->addItem($orderItem);
		}
		
		// =============================================
		// armazena os totais e cria o pedido
		// =============================================
		$mageOrder->setSubtotal($subTotal)
			->setBaseSubtotal($subTotal)
			->setGrandTotal($subTotal+(float)$apiOrder['freightChargedAmount'])
			->setBaseGrandTotal($subTotal + (float) $apiOrder['freightChargedAmount'])
			->setBaseShippingAmount((float) $apiOrder['freightChargedAmount'])
			->setShippingInclTax((float) $apiOrder['freightChargedAmount'])
			->setBaseShippingInclTax((float) $apiOrder['freightChargedAmount']);

		// reserva um incrementId para este pedido
		$mageOrderIncrementId = Mage::getSingleton('eav/config')->getEntityType('order')->fetchNewIncrementId($storeId);
		$mageOrder->setIncrementId($mageOrderIncrementId);
		$creationDatetime = new Zend_Date($apiOrder['purchaseDate'], Zend_Date::ISO_8601);
		$mageOrder->setCreatedAt($creationDatetime);
		
		$transaction->addObject($mageOrder);
		$transaction->addCommitCallback(array($mageOrder, 'place'));
		$transaction->addCommitCallback(array($mageOrder, 'save'));
		$transaction->save();
		
		// =============================================
		// atualiza o estoque dos produtos comprados
		// =============================================
		$currentStoreId = Mage::app()->getStore()->getId();
		Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

		foreach($mageProducts as $mageProduct)
		{
			$stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($mageProduct);
			
			if(!$stock || empty($stock))
			{
				throw new Exception("It was not possible to recover stock information from product " . $mageProduct->getId());
			}

			$stocklevel = (int) $stock->getQty();
			
			$newlevel = $stocklevel - $item['qnty'];
			if($newlevel > $stock->getMinQty()) 
			{ 
				$stock->setData('is_in_stock', 1);
			} 
			else
			{
				$stock->setData('is_in_stock', 0);
			}
			$stock->setData('qty', $newlevel);
			$stock->save();
			
			$mageProduct->setUpdatedAt(new Zend_Date());
			$mageProduct->save();
		}

		Mage::app()->setCurrentStore($currentStoreId);
		

		// registra log
		Mage::helper('Query_NovaPontoCom')->saveLogInfo
		(
			'order', 
			'create', 
			'sales/order', 
			$mageOrder->getId(), 
			time(), 
			0, 
			0, 
			'Order successfully created.',
			0,
			$apiOrder['orderId']
		);

		return $mageOrder;
	}


	/**
	 *
	 * Atualiza um pedido no magento
	 *
	 **/

	public function updateMageOrder($apiOrder, $apiOrderStatus)
	{
		if(!$this->_existsOrder($apiOrder['orderId']))
		{
			try
			{
				$mageOrder = $this->createMageOrder($apiOrder, $apiOrderStatus);
			}
			catch(Exception $e)
			{
				throw new Exception($e->getMessage());
			}
		}
		else
		{
			$mageOrder = Mage::getModel('sales/order')->loadByAttribute('novaPontoCom_apiId', $apiOrder['orderId']);
		}


		switch($apiOrderStatus)
		{
			// em caso de pedidos aprovados, cria fatura
			case 'Approved':

				if($mageOrder->canInvoice() && !$mageOrder->hasInvoices())
				{
					$invoiceId = Mage::getModel('sales/order_invoice_api')->create($mageOrder->getIncrementId(), array());

					if(!$invoiceId)
					{
						throw new Exception("It was not possible to get and invoiceId");
					}

					$invoice = Mage::getModel('sales/order_invoice')->loadByIncrementId($invoiceId);
					
					if(!$invoice)
					{
						throw new Exception("It was not possible to create invoice do order " . $mageOrder->getId());
					}

					$invoice->sendEmail(false);
					$invoice->setEmailSent(true);
					$invoice->save();


					Mage::helper('Query_NovaPontoCom')->saveLogInfo
					(
						'order', 
						'create-invoice', 
						'sales/order', 
						$mageOrder->getId(), 
						time(), 
						0, 
						0, 
						'Invoice successfully created.',
						0,
						$apiOrder['orderId']
					);
				}

				break;
		}

		// atualiza o status da api do pedido
		if($mageOrder->getData("novaPontoCom_status") != $apiOrderStatus)
		{
			/*
			$mageOrder->addStatusHistoryComment(Mage::helper('Query_NovaPontoCom')->__
				("Order status updated from Extra.com API : '%s' to '%s'.",
					$mageOrder->getData("novaPontoCom_status"), 
					$apiOrderStatus
				));
			*/

			$mageOrder->setData("novaPontoCom_status", $apiOrderStatus);
			$mageOrder->save();

			// registra log
			Mage::helper('Query_NovaPontoCom')->saveLogInfo
			(
				'order', 
				'update', 
				'sales/order', 
				$mageOrder->getId(), 
				time(), 
				0, 
				0, 
				'Order successfully updated.',
				0,
				$apiOrder['orderId']
			);
		}
	}


	/**
	 *
	 * Retorna se o pedido existe ou não no magento
	 *
	 **/

	private function _existsOrder($apiId)
	{
		$orderCollection = Mage::getModel('sales/order')->getCollection()->addAttributeToFilter
		(
			'novaPontoCom_apiId',
			array('eq' => $apiId)
		);
		
		if(count($orderCollection) > 0)
		{
			return true;
		}
		else
		{
			return false;
		}
	}


	/**
	 *
	 * Verifica consistencia entre os pedidos cancelados da loja no magento e na API da NovaPontoCom 
	 * 
	 **/

	public function verifyCanceledOrdersStatus()
	{
		$orderCollection = Mage::getModel('sales/order')->getCollection()->addFieldToFilter('status', array('in' => array('canceled')));

		foreach ($orderCollection as $order)
		{
		if($order->getData('novaPontoCom_status') != "Canceled")
			{
				try
				{
					$this->postCanceledOrder($order->getData('novaPontoCom_apiId'));
					
					$order->setData('novaPontoCom_apiId', 'Canceled');
					$order->save();
					
					Mage::helper('Query_NovaPontoCom')->saveLogInfo
					(
						'order', 
						'verify-canceled', 
						'sales/order', 
						$order->getId(), 
						time(), 
						0, 
						0, 
						'Canceled',
						0,
						$order->getData('novaPontoCom_apiId')
					);
				}
				catch(Exception $e)
				{
					throw new Exception($e->getMessage());
				}
			}
		}
	}


	/**
	 *
	 * Envia solicitaçao de cancelamento de pedido para a API da NovaPontoCom
	 *
	 **/

	public function postCanceledOrder($apiId)
	{
		if(!$apiId)
		{
			throw new Exception("API ID not defined.");
		}

		$location = "/orders/" . $apiId . "/status/canceled/";
		$params = array("reason" => "Canceled by Seller");
		
		$this->_wsConnector->doPost($this->_authToken, $this->_appToken, $location, json_encode($params), "json");
	}
		

	/**
	 *
	 * Envia informacao de tracking dos itens do pedido 
	 *
	 **/

	public function postTrackingItems($mainShipment, $controlPoint)
	{
		if(is_int($mainShipment))
		{
			$mainShipment = Mage::getModel('sales/order_shipment')->load($mainShipment);
		}

		// confere se veio o envio
		if(!$mainShipment || !$mainShipment->getId())
		{
			throw new Exception("Shipment not defined.");
		}

		// confere se o pedido pertence a API
		$order = $mainShipment->getOrder();

		if(!$order->getData('novaPontoCom_apiId'))
		{
			throw new Exception("Order API ID not defined.");
		}

		// pega todos os envios deste pedido e contabiliza 
		// numero de produtos jah enviados
		$shipmentCollection = $order->getShipmentsCollection();
		$qtyAlreadySent = array();
		
		foreach($shipmentCollection as $shipment)
		{
			// filtra para nao contabilizar envios posteriores
			if($shipment->getCreatedAt() >= $mainShipment->getCreatedAt())
			{
				continue;
			}

			$itemsCollection = $shipment->getItemsCollection();

			foreach($itemsCollection as $item)
			{
				if(!isset($qtyAlreadySent[$item->getProductId()]) || !$qtyAlreadySent[$item->getProductId()])
				{
					$qtyAlreadySent[$item->getProductId()] = 0;
				}
				
				$qtyAlreadySent[$item->getProductId()] += $item->getQty();
			}
		}

		// monta vetor com os ids no formato da API
		$itemsCollection = $mainShipment->getItemsCollection();
		$itemsIds = array();

		foreach ($itemsCollection as $item)
		{
			$product = Mage::getModel('catalog/product')->load($item->getProductId());

			if(!$product || !$product->getId())
			{
				throw new Exception("It was not possible to load product with ID " . $item->getProductId());
			}

			$beginIndex = isset($qtyAlreadySent[$product->getId()]) ? ($qtyAlreadySent[$product->getId()] + 1) : 1;
			$endIndex = $beginIndex + $item->getQty();

			for($i = $beginIndex; $i < $endIndex; $i++)
			{
				$itemsIds[] = $product->getData('novaPontoCom_apiSku') . "-" . $i;
			}
		}

		// prepara requisicao
		$creationDate = new Zend_Date($mainShipment->getCreatedAt());
		
		$params = array
		(
			"orderItemId" 		=> $itemsIds,
			"controlPoint"		=> $controlPoint,
			"extraDescription"	=> "",
			"occurenceDt"		=> $creationDate->toString(Zend_Date::ISO_8601),
			"carrierName"		=> $order->getShippingDescription(),
			"url" 				=> "",
			"objectId" 			=> "",
			"originDeliveryId" 	=> $mainShipment->getId(),
			"accessKeyNfe" 		=> $order->getIncrementId(),
			"linkNfe" 			=> "",
			"nfe" 				=> "",
			"serieNfe" 			=> ""
		);

		$location = "/orders/" . $order->getData('novaPontoCom_apiId') . "/ordersItems/trackings/";
		
		// realiza a requisicao
		$this->_wsConnector->doPost($this->_authToken, $this->_appToken, $location, json_encode($params), "json");
	}


	/**
	 *
	 * Registra erro no envio
	 *
	 **/

	public function registerShipmentError($shipment, $error)
	{
		if(!$shipment || !$shipment->getId() || !$error)
		{
			return;
		}

		$readConn = Mage::getSingleton('core/resource')->getConnection('core_read');
		$sql = "SELECT shipment_id FROM query_novapontocom_shipment_error WHERE shipment_id = " . $shipment->getId();
		$result = $readConn->fetchCol($sql);

		if($result && is_array($result) && count($result) > 0)
		{
			return;
		}

		$writeConn = Mage::getSingleton('core/resource')->getConnection('core_write');
		$sql = "INSERT INTO query_novapontocom_shipment_error (shipment_id, error) VALUES (" . $shipment->getId() . ", '" . $error . "')";
		$writeConn->query($sql);
	}
	
	/**
	 *
	 * Registra erro no envio
	 *
	 **/

	public function unregisterShipmentError($shipment, $error)
	{
		if(!$shipment || !$shipment->getId() || !$error)
		{
			return;
		}

		$writeConn = Mage::getSingleton('core/resource')->getConnection('core_write');
		$sql = "DELETE FROM query_novapontocom_shipment_error WHERE shipment_id = " . $shipment->getId() . " AND error = '" . $error . "'";
		$result = $readConn->query($sql);
	}

	/**
	 *
	 * Verifica consistencia entre os pedidos enviados e seu status no NovaPontoCom
	 * 
	 **/

	public function verifyShippedOrdersStatus()
	{
		$shipmentCollection  = Mage::getModel('sales/order_shipment')->getCollection();
		$shipmentCollection->getSelect()->joinInner
		(
			array('error_table' => 'query_novapontocom_shipment_error'), 
			"main_table.entity_id = error_table.shipment_id AND error_table.error = 'send-error'",
			null,
			null
		);

		foreach($shipmentCollection as $shipment)
		{
			try
			{
				$this->postTrackingItems($shipment, "EPR");

				// insere comentario no pedido
				$order = $shipment->getOrder();
				$order->addStatusHistoryComment(Mage::helper('Query_NovaPontoCom')->__("Shipment #%s sent as 'Sent' to the Extra.com API.", $shipment->getIncrementId()));
				$order->save();
				
				// remove dos erros
				$this->unregisterShipmentError($shipment, "send-error");
			}
			catch(Exception $e)
			{
				$this->registerShipmentError($shipment, "send-error");

				Mage::helper('Query_NovaPontoCom')->saveLogInfo
				(
					'order', 
					'send-late-shipment', 
					'sales/order_shipment', 
					$shipment->getId(), 
					time(), 
					0, 
					$e->getCode(), 
					Mage::helper("Query_NovaPontoCom")->__($e->getMessage())
				);	
			}
		}
	}

	
	/**
	 *
	 * Envia pedido para aprovacao
	 *
	 **/

	public function postApprovedOrder($novaPontoCom_apiId)
	{
		$location = "/orders/status/approved/" . $novaPontoCom_apiId;
		$this->_wsConnector->doPost($this->_authToken, $this->_appToken,$location,"","json");
	}

	
	
	private function _setAddressStreet($street, $index, $value)
	{
		if($index == "street" || $index == "street_1")
		{
			$street['1'] = $value;
		}
		else if($index == "street_2")
		{
			$street['2'] = $value;
		}
		else if($index == "street_3")
		{
			$street['3'] = $value;
		}
		else if($index == "street_4")
		{
			$street['4'] = $value;
		}
		
		return $street;
	}
}