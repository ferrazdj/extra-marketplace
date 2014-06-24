<?php

class Query_NovaPontoCom_Model_Main
{
	public function run()
	{
		// Log de execucao da cron
		// Mage::log('Cron Passando', null, __METHOD__, true);
		// if(!$this->_canRun())
		// {
		// 	return;
		// }

		// roda o servico
		$serviceStartTime = time();
		Mage::helper('Query_NovaPontoCom')->setSessionConfig('status', 'running');
		
		$this->_processQueuedProducts($serviceStartTime);
		$this->_syncProducts($serviceStartTime);
		$this->_syncOrders($serviceStartTime);
		$this->_syncTickets($serviceStartTime);
		
		Mage::helper('Query_NovaPontoCom')->setSessionConfig('status', 'stopped');
		$serviceEndTime = time();
	}


	/* 
	 * 
	 * Confere a sincronizacao deve rodar, considerando:
	 * - se o modulo estah ativado;
	 * - se nao ha outra instancia executando.
	 * Caso haja erros nao tratados anteriores, ressuscita o servico
	 * 
	 */

	private function _canRun()
	{
		// confere se o modulo estah ativo
		if(!Mage::helper('Query_NovaPontoCom')->getConfigSetting('active'))
		{
			return false;
		}

		// confere se o servico jah nao estah em execucao
		if(Mage::helper('Query_NovaPontoCom')->getSessionConfig('status') == 'running')
		{
			// confere se alguma excecao deixou o servico parado;
			// caso sim, ressuscita-o
			if(!Mage::helper('Query_NovaPontoCom')->hasRecentLogRegister(5))
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			return true;
		}
	}
	
	

	/* 
	 * 
	 * Produtos colocados em fila de processamento:
	 * consulta de possiveis associados;
	 * envio em lote;
	 * 
	 */

	private function _processQueuedProducts($serviceStartTime)
	{
		$syncService = Mage::getModel('Query_NovaPontoCom/integration_product');
		$currentStoreId = Mage::app()->getStore()->getId();
        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);


        // =============================================
		// tratamento de produtos que estao aguardando 
		// para serem pesquisados
		// =============================================
		$collection = Mage::getResourceModel('catalog/product_collection');
		$collection->addAttributeToFilter
		(
			'novaPontoCom_statusCode',
			array
			(
				'eq' => 900
			)
		);
		
		foreach($collection as $prod)
		{
			$product = Mage::getModel('catalog/product')->load($prod->getId());
			
			// realiza busca de associados na api
			try
			{
				// busca produtos que possam estar associados
				$searchResult = $syncService->search($product->getName(), 15);

				if($searchResult && count($searchResult) > 0)
				{
					$product->setData('novaPontoCom_statusCode', 901);
					$product->setData('novaPontoCom_associatedProds', json_encode($searchResult));
					$product->save();
				}
				else
				{
					$product->setData('novaPontoCom_statusCode', 901);
					$product->setData('novaPontoCom_associatedProds', '');
					$product->save();
				}

				// log
				Mage::helper('Query_NovaPontoCom')->saveLogInfo
				(
					'product', 
					'search', 
					'catalog/product', 
					$product->getId(), 
					time(), 
					0, 
					0, 
					''
				);
			}
			catch(Exception $e)
			{
				// log
				Mage::helper('Query_NovaPontoCom')->saveLogInfo
				(
					'product', 
					'search', 
					'catalog/product', 
					$product->getId(), 
					time(), 
					0, 
					$e->getCode(), 
					$e->getMessage()
				);

				Mage::getSingleton('adminhtml/session')->addError(Mage::helper('Query_NovaPontoCom')->__
				(
					'Could not consult product %d: ', $product->getId()) . Mage::helper('Query_NovaPontoCom')->__($e->getMessage())
				);
			}
		}


		// =============================================
		// tratamento de produtos que estao aguardando
		// para serem enviados
		// =============================================
		$collection = Mage::getResourceModel('catalog/product_collection');
		$collection->addAttributeToSelect('novaPontoCom_importerInfoId');
		$collection->addAttributeToFilter
		(
			'novaPontoCom_statusCode',
			array
			(
				'eq' => 902
			)
		);
		
		$mageProductIds = array();

		foreach($collection as $prod)
		{
			if($prod->getData('novaPontoCom_importerInfoId'))
			{
				continue;
			}
			
			$mageProductIds[] = $prod->getId();
		}

		if(count($mageProductIds) > 0)
		{
			try
			{
				$result = $syncService->loadMultiple($mageProductIds);

				Mage::helper('Query_NovaPontoCom')->saveLogInfo
				(
					'product', 
					'load-multiple', 
					'catalog/product', 
					0, 
					$serviceStartTime, 
					0, 
					0, 
					''
				);
			}
			catch(Exception $e)
			{
				Mage::helper('Query_NovaPontoCom')->saveLogInfo
				(
					'product', 
					'load-multiple', 
					'catalog/product', 
					0, 
					$serviceStartTime, 
					0, 
					0, 
					''
				);
			}
		}

		// armazena referencia e consulta resultado dos envios
		if(count($mageProductIds) > 0 && $result && $result["location"])
		{
			$errors = $result["errors"];
			
			foreach($mageProductIds as $productId)
			{
				if(isset($errors[$productId]))
				{
					Mage::helper('Query_NovaPontoCom')->saveLogInfo
					(
						'product', 
						'consult-load', 
						'catalog/product', 
						$productId, 
						$serviceStartTime, 
						0, 
						0, 
						$errors[$productId]
					);
				}
				else
				{
					$mageProduct = Mage::getModel('catalog/product')->load($productId);

					try
					{
						$syncService->consultLoad($mageProduct, $result["location"]);

						Mage::helper('Query_NovaPontoCom')->saveLogInfo
						(
							'product', 
							'consult-load', 
							'catalog/product', 
							$productId, 
							$serviceStartTime, 
							0, 
							0, 
							''
						);
					}
					catch(Exception $e)
					{
						Mage::helper('Query_NovaPontoCom')->saveLogInfo
						(
							'product', 
							'consult-load', 
							'catalog/product', 
							$productId, 
							$serviceStartTime, 
							0, 
							0, 
							$e->getMessage()
						);
					}
				}

				sleep(5);
			}
		}

		Mage::app()->setCurrentStore($currentStoreId);
	}

	/* 
	 * 
	 * Sincronizacao de produtos:
	 * primeiro os que estao em aprovacao;
	 * em seguida os que estao jah cadastrados;
	 * 
	 */

	private function _syncProducts($serviceStartTime)
	{
		// confere se o tempo de intervalo minimo foi atingido
		$lastSyncTime = Mage::helper('Query_NovaPontoCom')->getSessionConfig('product_last_sync');
		$configuredDelay = Mage::helper('Query_NovaPontoCom')->getConfigSetting('product_service_delay');

		if(($serviceStartTime - $lastSyncTime) < ($configuredDelay * 60))
		{
			return;
		}

		$syncService = Mage::getModel('Query_NovaPontoCom/integration_product');
		$currentStoreId = Mage::app()->getStore()->getId();
        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);


		// =============================================
		// tratamento de produtos que estao em validacao
		// =============================================
		$collection = Mage::getResourceModel('catalog/product_collection');
		$collection->addAttributeToFilter
		(
			'novaPontoCom_importerInfoId',
			array
			(
				'notnull' => true
			)
		);
		$collection->addAttributeToFilter
		(
			'novaPontoCom_apiSku',
			array
			(
				'eq' => ''
			)
		);
		
		$updatedProducts = array();
		
		foreach($collection as $prod)
		{
			$product = Mage::getModel('catalog/product')->load($prod->getId());
			
			// atualiza o status do produto
			try
			{
				$syncService->consultLoadItem($product);
				
				// log
				Mage::helper('Query_NovaPontoCom')->saveLogInfo
				(
					'product', 
					'update-loading', 
					'catalog/product', 
					$product->getId(), 
					$serviceStartTime, 
					0, 
					0, 
					''
				);
			}
			catch(Exception $e)
			{
				// log
				Mage::helper('Query_NovaPontoCom')->saveLogInfo
				(
					'product', 
					'update-loading', 
					'catalog/product', 
					$product->getId(), 
					$serviceStartTime, 
					0, 
					$e->getCode(), 
					$e->getMessage()
				);
			}


			// aprovado
			if($product->getData('novaPontoCom_statusCode') == 5 || $product->getData('novaPontoCom_statusCode') == 6)
			{
				$syncService->consultApiSku($product);
			}
		}

		// =============================================
		// tratamento de produtos jah cadastrados
		// =============================================
		$collection = Mage::getResourceModel('catalog/product_collection');
		$collection->addAttributeToFilter
		(
			'novaPontoCom_apiSku',
			array
			(
				'notnull' => true
			)
		);
		$collection->addAttributeToFilter
		(
			'novaPontoCom_apiSku',
			array
			(
				'neq' => ''
			)
		);
		$lastChangesPeriod = new Zend_Date();
		$lastChangesPeriod->sub($configuredDelay, Zend_Date::MINUTE);
		$collection->addAttributeToFilter
		(
			'updated_at',
			array
			(
				'from' => $lastChangesPeriod,
				'datetime' => true
			)
		);

		$updatedProducts = array();
		
		foreach($collection as $prod)
		{
			$product = Mage::getModel('catalog/product')->load($prod->getId());
			
			// atualiza o estoque do produto
			try
			{
				$syncService->updateStock($product);
				
				// log
				Mage::helper('Query_NovaPontoCom')->saveLogInfo
				(
					'product', 
					'update-stock', 
					'catalog/product', 
					$product->getId(), 
					$serviceStartTime, 
					0, 
					0, 
					''
				);
			}
			catch(Exception $e)
			{
				// log
				Mage::helper('Query_NovaPontoCom')->saveLogInfo
				(
					'product', 
					'update-stock', 
					'catalog/product', 
					$product->getId(), 
					$serviceStartTime, 
					0, 
					$e->getCode(), 
					$e->getMessage()
				);
			}

			// atualiza o preco do produto
			try
			{
				$syncService->updatePrice($product);
				
				// log
				Mage::helper('Query_NovaPontoCom')->saveLogInfo
				(
					'product', 
					'update-price', 
					'catalog/product', 
					$product->getId(), 
					$serviceStartTime, 
					0, 
					0, 
					''
				);
			}
			catch(Exception $e)
			{
				// log
				Mage::helper('Query_NovaPontoCom')->saveLogInfo
				(
					'product', 
					'update-price', 
					'catalog/product', 
					$product->getId(), 
					$serviceStartTime, 
					0, 
					$e->getCode(), 
					$e->getMessage()
				);
			}
		}
		
		// atualiza referencia da ultima instancia
		Mage::app()->setCurrentStore($currentStoreId);
		Mage::helper('Query_NovaPontoCom')->setSessionConfig('product_last_sync', time());
	}
	
	
	/* 
	 * 
	 * Sincronizacao de pedidos:
	 * 
	 * 
	 * 
	 */
	 
	private function _syncOrders($serviceStartTime)
	{
		$lastSyncTime = Mage::helper('Query_NovaPontoCom')->getSessionConfig('order_last_sync');
		$configuredDelay = Mage::helper('Query_NovaPontoCom')->getConfigSetting('order_service_delay');
		
		$syncOrders = array();
		if(($serviceStartTime - $lastSyncTime) < ($configuredDelay * 60))
		{
			return;
		}
		

		$syncService = Mage::getModel('Query_NovaPontoCom/integration_order');
		$apiOrders = array();
		$orderStatuses = array
		(
			'new' 				=> 'New',
			'approved' 				=> 'Approved',
			'sentPartially' 		=> 'Partially Sent',
			'partiallyDelivered' 	=> 'Partially Delivered',
			'sent' 					=> 'Sent',
			'delivered' 			=> 'Delivered',
			'canceled' 				=> 'Canceled'
		);

		// =============================================
		// busca pelos pedidos da api e os processa
		// =============================================
		foreach($orderStatuses as $status => $statusLabel)
		{
			$apiOrders[$status] = $syncService->getApiOrdersByStatus($status);
			
			// cria pedidos novos
			if($status == "new")
			{
				foreach($apiOrders[$status] as $apiOrder)
				{
					try
					{
						$syncService->createMageOrder($apiOrder, $statusLabel);

						// caso haja instancia de erro, a remove
						$orderImportError = Mage::getModel('Query_NovaPontoCom/importOrderError')->loadByOrderApiId($apiOrder['orderId']);

						if($orderImportError->getId())
						{
							$orderImportError->delete();
						}
					}
					catch(Exception $e)
					{
						$orderImportError = Mage::getModel('Query_NovaPontoCom/importOrderError')->loadByOrderApiId($apiOrder['orderId']);

						if(!$orderImportError->getId())
						{
							$orderImportError->setOrderApiId($apiOrder['orderId']);
							$orderImportError->setValue($apiOrder['totalAmount']);
							$orderImportError->setMessage(Mage::helper("Query_NovaPontoCom")->__($e->getMessage()));
							$orderImportError->setCreatedAt(new Zend_Date());
							$orderImportError->save();
						}

						Mage::helper('Query_NovaPontoCom')->saveLogInfo
						(
							'order', 
							'create', 
							'sales/order', 
							0, 
							time(), 
							0, 
							$e->getCode(), 
							Mage::helper("Query_NovaPontoCom")->__($e->getMessage()),
							'null',
							$apiOrder['orderId']
						);
					}
				}
			}

			// atualiza pedidos
			if($status != "new")
			{
				foreach($apiOrders[$status] as $apiOrder)
				{
					try
					{
						$syncService->updateMageOrder($apiOrder, $statusLabel);

						// caso haja instancia de erro, a remove
						$orderImportError = Mage::getModel('Query_NovaPontoCom/importOrderError')->loadByOrderApiId($apiOrder['orderId']);

						if($orderImportError->getId())
						{
							$orderImportError->delete();
						}
					}
					catch(Exception $e)
					{
						$orderImportError = Mage::getModel('Query_NovaPontoCom/importOrderError')->loadByOrderApiId($apiOrder['orderId']);

						if(!$orderImportError->getId())
						{
							$orderImportError->setOrderApiId($apiOrder['orderId']);
							$orderImportError->setValue($apiOrder['totalAmount']);
							$orderImportError->setMessage(Mage::helper("Query_NovaPontoCom")->__($e->getMessage()));
							$orderImportError->setCreatedAt(new Zend_Date());
							$orderImportError->save();
						}

						Mage::helper('Query_NovaPontoCom')->saveLogInfo
						(
							'order', 
							'update', 
							'sales/order', 
							0, 
							time(), 
							0, 
							$e->getCode(), 
							Mage::helper("Query_NovaPontoCom")->__($e->getMessage()),
							'null',
							$apiOrder['orderId']
						);	
					}
				}
			}

			// aguarda para evitar que a api recuse a requisicao
			sleep(5);
		}
		

		// =============================================
		// procura incoerencia em produtos cancelados
		// =============================================
		try
		{
			$syncService->verifyCanceledOrdersStatus();
		}
		catch(Exception $e)
		{
			Mage::helper('Query_NovaPontoCom')->saveLogInfo
			(
				'order', 
				'verify-canceled', 
				'sales/order', 
				0, 
				time(), 
				0, 
				$e->getCode(), 
				Mage::helper("Query_NovaPontoCom")->__($e->getMessage())
			);	
		}


		// =============================================
		// procura incoerencia em produtos enviados
		// =============================================
		try
		{
			$syncService->verifyShippedOrdersStatus();
		}
		catch(Exception $e)
		{
			Mage::helper('Query_NovaPontoCom')->saveLogInfo
			(
				'order', 
				'verify-shipped', 
				'sales/order', 
				0, 
				time(), 
				0, 
				$e->getCode(), 
				Mage::helper("Query_NovaPontoCom")->__($e->getMessage())
			);	
		}

		// atualiza referencia da ultima instancia
		Mage::helper('Query_NovaPontoCom')->setSessionConfig('order_last_sync', time());
	}
	
	
	/* 
	 * 
	 * Sincronizacao de chamados:
	 * primeiro recupera dados da api;
	 * em seguida envia os novos que foram criados no magento;
	 * 
	 */
	
	private function _syncTickets($serviceStartTime)
	{
		// confere se o tempo de intervalo minimo foi atingido
		$lastSyncTime = Mage::helper('Query_NovaPontoCom')->getSessionConfig('ticket_last_sync');
		$configuredDelay = Mage::helper('Query_NovaPontoCom')->getConfigSetting('ticket_service_delay');

		if(($serviceStartTime - $lastSyncTime) < ($configuredDelay * 60))
		{
			return;
		}
		
		$syncService = Mage::getModel('Query_NovaPontoCom/integration_ticket');
		$startDate = Mage::helper("Query_NovaPontoCom")->getSessionConfig("ticket_last_update");
		$error = false;
		
		// processa cada um dos tickets trazidos
		foreach($syncService->get($startDate) as $apiTicket)
		{
			$mageTicket = Mage::getModel('Query_NovaPontoCom/ticket')->loadByAttribute('api_id', $apiTicket['idTicket']);
			
			try
			{
				if($mageTicket && $mageTicket->getId())
				{
					$syncService->updateTicket($mageTicket, $apiTicket);
				}
				else
				{
					$syncService->createTicket($mageTicket, $apiTicket);
				}
			}
			catch(Exception $e)
			{
				Mage::helper('Query_NovaPontoCom')->saveLogInfo
				(
					'ticket', 
					'create-update', 
					'Query_NovaPontoCom/ticket', 
					0, 
					time(), 
					0, 
					$e->getCode(), 
					Mage::helper("Query_NovaPontoCom")->__($e->getMessage())
				);

				$error = true;
			}
		}

		if(!$error)
		{
			$now = new Zend_Date();
			Mage::helper("Query_NovaPontoCom")->setSessionConfig("ticket_last_update", $now->toString("yyyy-MM-dd"));
		}
		
		// envia chamados que ainda nao foram enviados
		$collection = Mage::getModel('Query_NovaPontoCom/ticket')
			->getCollection()
			->addFieldToFilter("api_id", array("null" => true));
		
		foreach($collection as $ticket)
		{
			try
			{
				$syncService->send($ticket);
			}
			catch(Exception $e)
			{
				Mage::helper('Query_NovaPontoCom')->saveLogInfo
				(
					'ticket', 
					'send', 
					'Query_NovaPontoCom/ticket', 
					0, 
					time(), 
					0, 
					$e->getCode(), 
					Mage::helper("Query_NovaPontoCom")->__($e->getMessage())
				);
			}
		}
		
		// envia logs dos pedidos existentes
		$collection = Mage::getModel('Query_NovaPontoCom/ticket_log')
						  ->getCollection()
						  ->addFieldToFilter("api_id", array("null" => false));
		
		foreach($collection as $log)
		{
			try
			{
				$syncService->sendLog($log);
			}
			catch(Exception $e)
			{
				Mage::helper('Query_NovaPontoCom')->saveLogInfo
				(
					'ticket-log', 
					'send', 
					'Query_NovaPontoCom/ticket_log', 
					0, 
					time(), 
					0, 
					$e->getCode(), 
					Mage::helper("Query_NovaPontoCom")->__($e->getMessage())
				);
			}
		}
		
		Mage::helper('Query_NovaPontoCom')->setSessionConfig('ticket_last_sync', time());
	}
}