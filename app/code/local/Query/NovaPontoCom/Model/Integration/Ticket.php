<?php

	class Query_NovaPontoCom_Model_Integration_Ticket extends Query_NovaPontoCom_Model_Integration_Abstract
	{	
		/* 
		 * 
		 * Envia ticket criado pelo lojista
		 * 
		 */

		public function send($mageTicket)
		{
			if(!$mageTicket || !$mageTicket->getId())
			{
				throw new Exception("missing Ticket Entity.");
			}

			if($mageTicket->getData('api_id'))
			{
				throw new Exception("ticket Entity already synchronized.");
			}

			
			// monta vetor de dados a serem enviados
			$ticketData = array();
			$ticketData['Type'] = $mageTicket->getType();
			$ticketData['orderId'] = $mageTicket->getApiOrderId() ? $mageTicket->getApiOrderId() : "";
			//$ticketData['orderItemId'] = $mageTicket->getOrderItemId();
			$ticketData['comment'] = $mageTicket->getDescription();
			
			// envia carga de dados
			$this->_wsConnector->doPost($this->_authToken, $this->_appToken, 'tickets', json_encode($ticketData), "json");
			
			if($this->_wsConnector->getResponseHeader("Location"))
			{
				$ticketApiId = str_replace("/tickets/", "", $this->_wsConnector->getResponseHeader("Location"));

				$mageTicket->setApiId($ticketApiId);
				$mageTicket->save();
			}
			else
			{
				throw new Exception("Could not find Location header on response.");
			}
		}
		

		/* 
		 * 
		 * Envia um log especifico
		 * 
		 */

		public function sendLog($log)
		{
			$ticket = Mage::getModel('Query_NovaPontoCom/ticket')->load($log->getTicketId());

			if(!$ticket || !$ticket->getId())
			{
				throw new Exception("Could not find the ticket for this log.");
			}

			$logData = array();
			$logData['comment'] = $log->getComment();
			$logData['sendMsg'] = $log->getNotifyCustomer() ? 'true' : 'false';
			
			$this->_wsConnector->doPost($this->_authToken, $this->_appToken, "/tickets/" . $ticket->getApiId() . "/ticketLogs", json_encode($logData), "json");
			
			if($this->_wsConnector->getResponseHeader("Location"))
			{
				$ticketLogApiId = str_replace("/tickets/" . $ticket->getApiId() . "/ticketlogs/", "", $this->_wsConnector->getResponseHeader("Location"));

				$log->setApiId($ticketLogApiId);
				$log->save();
			}
			else
			{
				throw new Exception("[Log] Could not find Location header on response.");
			}
		}

		
		/* 
		 * 
		 * Consome tickets na API, importando os novos
		 * e atualizando os jah existentes
		 * 
		 */

		public function get($startDate = null, $maxResults = 9999)
		{
			// realiza paginacao na busca
			$pageSize = ($maxResults < 20) ? $maxResults : 20;
			$offSet = 0;
			$limit = $pageSize;

			// confere filtro por data
			//$dateFilter = $startDate ? '&startDate=' . $startDate : '';
			$dateFilter = '';

			// armazena o resultado
			$result = array();
			
			try
			{
				do
				{
					if( $this->_wsConnector->doGet
						(
							$this->_authToken, 
							$this->_appToken, 
							'/tickets', 
							'_offset=' . $offSet . '&_limit=' . $limit . $dateFilter
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
					sleep(5);
					
				} while(count($pageResult) > 0 && $pageResult && count($pageResult) == $pageSize && count($result) < $maxResults);
			}
			catch(Exception $e)
			{
				Mage::helper('Query_NovaPontoCom')->saveLogInfo
				(
					'ticket', 
					'get', 
					'', 
					0, 
					time(), 
					0, 
					$e->getCode(), 
					Mage::helper("Query_NovaPontoCom")->__($e->getMessage())
				);
				
				$result = array();
			}
			
			return $result;
		}
		
		
		/* 
		 * 
		 * Cria novos tickets
		 * 
		 */

		public function createTicket($mageTicket, $apiTicket)
		{
			// busca o pedido
			$order = Mage::getModel('sales/order')->loadByAttribute('novaPontoCom_apiId', $apiTicket['order']);
			
			// armazena os dados dos tickets
			$mageTicket->setData('api_id', $apiTicket['idTicket']);
			$mageTicket->setData('description', $apiTicket['description']);
			$mageTicket->setData('customer_name', $apiTicket['nameCustomer']);
			$mageTicket->setData('api_opener_code', $apiTicket['codOpener']);
			$mageTicket->setData('reason', $apiTicket['reason']);
			$mageTicket->setData('action', $apiTicket['action']);
			$mageTicket->setData('api_responsible', $apiTicket['responsible']);
			$mageTicket->setData('api_code', $apiTicket['codTicket']);
			$mageTicket->setData('subject', $apiTicket['ticketSubject']);
			$mageTicket->setData('status', $apiTicket['status']);
			$mageTicket->setData('type', isset($apiTicket['type']) ? $apiTicket['type'] : "");
			$mageTicket->setData('api_order_id', $apiTicket['order']);
			$mageTicket->setData('order_id', $order->getId());
			$mageTicket->setCreatedAt(new Zend_Date($apiTicket['createDate'], Zend_Date::ISO_8601));
			$mageTicket->setUpdatedAt(new Zend_Date($apiTicket['datLastUpdate'], Zend_Date::ISO_8601));
			$mageTicket->save();
			
			
			// cria os logs
			if(isset($apiTicket['ticketLogs']))
			{
				foreach($apiTicket['ticketLogs'] as $apiLog)
				{
					$mageLog = Mage::getModel('Query_NovaPontoCom/ticket_log');
					$mageLog->setData('ticket_id', $mageTicket->getId());
					$mageLog->setData('api_id', $apiLog['idTicketLog']);
					$mageLog->setData('comment', $apiLog['txtDescription']);
					$mageLog->save();
				}
			}
		}
		
		
		/* 
		 * 
		 * Atualiza tickets existentes
		 * 
		 */

		public function updateTicket($mageTicket, $apiTicket)
		{
			// armazena os dados dos tickets
			$mageTicket->setData('status', $apiTicket['status']);
			$mageTicket->setUpdatedAt(new Zend_Date($apiTicket['datLastUpdate']));
			$mageTicket->save();
			
			// cria os logs
			if(isset($apiTicket['ticketLogs']))
			{
				foreach($apiTicket['ticketLogs'] as $apiLog)
				{
					$mageLog = Mage::getModel('Query_NovaPontoCom/ticket_log')->loadByAttribute('api_id', $apiLog['idTicketLog']);
					
					if($mageLog && $mageLog->getId())
					{
						$mageLog->setData('comment', $apiLog['txtDescription']);
					}
					else
					{
						$mageLog->setData('ticket_id', $mageTicket->getId());
						$mageLog->setData('api_id', $apiLog['idTicketLog']);
						$mageLog->setData('comment', $apiLog['txtDescription']);
					}
					
					$mageLog->save();
				}
			}
		}
		
		/* 
		 * 
		 * Fecha um ticket
		 * 
		 */

		public function closeTicket($mageTicket, $comment, $notifiyCustomer)
		{
			$actionData = array
			(
				"type" => "closeTicket",
				"sendMsg" => $notifiyCustomer,
				"comment" => $comment
			);
			
			if(!$this->_wsConnector->doPut
			(
			   $this->_authToken, 
			   $this->_appToken, "/tickets/" . $mageTicket->getApiId(), 
			   json_encode($actionData), 
			   "json"
			))
			{
				throw new Exception( $this->_wsConnector->getResponseBody());
			}
		}
		
		/* 
		 * 
		 * Fecha um ticket
		 * 
		 */

		public function confirmChange($mageTicket, $itemId, $action, $comment, $notifiyCustomer)
		{
			$actionData = array
			(
				"type" => "changeConfirmation",
				"sendMsg" => $notifiyCustomer,
				"orderId" => $mageTicket->getApiOrderId(),
				"orderItemId" => $itemId,
				"action" => $action,
				"comment" => $comment
			);
			
			if(!$this->_wsConnector->doPut
			   (
			   $this->_authToken, 
			   $this->_appToken, "/tickets/" . $mageTicket->getApiId(), 
			   json_encode($actionData),
			   "json"
			   )
			)
			{
				throw new Exception( $this->_wsConnector->getResponseBody());
			}
		}
	}