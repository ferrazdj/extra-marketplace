<?php

class Query_NovaPontoCom_Helper_Data extends Mage_Core_Helper_Abstract
{
    /*
	 * Possiveis status dos produtos:
	 * 
	 * 1 - Pendente de aprovacao (NovaPontoCom)
	 * 2 - Pendente de categorizacao ou deduplicacao (NovaPontoCom)
	 * 3 - Produto enviado para integracao (NovaPontoCom)
	 * 4 - Produto pendente de leitura na integracao (NovaPontoCom)
	 * 5 - Produto disponivel no admin (NovaPontoCom)
	 * 6 - Produto indexado (NovaPontoCom)
	 * 7 - Produto recusado (NovaPontoCom)
	 * 8 - Importacao cancelada (NovaPontoCom)
	 * 
	 * 900 - Aguardando ser pesquisado (Query Commerce)
	 * 901 - Candidato a ser associado (Query Commerce)
	 * 902 - Aguardando envio como novo (Query Commerce)
	 * 
	 */
	
	
	/*
	 * Busca uma configuracao padrao do modulo
	 */

	public function getConfigSetting($configName)
	{
		return Mage::getStoreConfig('novapontocom/settings/' . $configName);
	}


	/*
	 * Busca uma configuracao de produtos do modulo
	 */

	public function getProductFieldsConfig($configName)
	{
		return Mage::getStoreConfig('novapontocom_product/field_config/' . $configName);
	}


	/*
	 * Retorna configuracao da sessao do modulo
	 */

	public function getSessionConfig($code)
	{
		if(!$code)
		{
			return "";
		}

		$readConn = Mage::getSingleton('core/resource')->getConnection('core_read');
		$sql = "SELECT value FROM query_novapontocom_session_config WHERE code = '" . $code . "'";
		$result = $readConn->fetchCol($sql);

		if(!$result || !is_array($result) || count($result) != 1)
		{
			return "";
		}
		else
		{
			return $result[0];
		}
	}
	

	/*
	 * Salva valor de uma configuracao da sessao do modulo
	 */

	public function setSessionConfig($code, $value)
	{
		if(!$code)
		{
			return;
		}
	
		$writeConn = Mage::getSingleton('core/resource')->getConnection('core_write');
		$sql = "SELECT value FROM query_novapontocom_session_config WHERE code = '" . $code . "'";
		$result = $writeConn->fetchAll($sql);
		
		if(count($result) > 0)
		{
			$sql = "UPDATE query_novapontocom_session_config SET value = '" . $value . "' WHERE code = '" . $code . "'";
			$writeConn->query($sql);
		}
		else
		{
			$sql = "INSERT INTO query_novapontocom_session_config (code, value) VALUES ('" . $code . "', '" . $value . "')";
			$writeConn->query($sql);
		}
	}


	/*
	 * Retorna entidade e nome do atributo configurados
	 */

	public function getCustomerAttributeConfig($type, $attrName, $savedIndex = null)
	{
		if(!$savedIndex)
		{
			$savedIndex = Mage::getStoreConfig('novapontocom_customer/' . $type . '/' . $attrName);
		}

		// separa a entidade e o atributo
		$separatorIndex = strpos($savedIndex, "/");

		if($separatorIndex === false)
		{
			$entity = "customer";
			$attribute = $savedIndex;
		}
		else
		{
			$entity = substr($savedIndex, 0, $separatorIndex);
			$attribute = substr($savedIndex, $separatorIndex + 1);
		}

		return array("entity" => $entity, "attribute" => $attribute);
	}
	
	
	/*
	 * Possiveis tipos de pagamentos:
	 * 
	 * 1 - Cartao de Credito
	 * 2 - Boleto
	 * 4 - Cupom
	 * 5 - Transferencia
	 * 
	 */
	
	public function getPaymentLabel($code)
	{
		if($code == 1)
		{
			return $this->__("Credit Card");
		}
		else if($code == 2)
		{
			return $this->__("Bank Slip");
		}
		else if($code == 4)
		{
			return $this->__("Cupon");
		}
		else if($code == 5)
		{
			return $this->__("Transfer");
		}
		else
		{
			return "";
		}
	}
	

	/*
	 * Salva valor de uma configuracao da sessao do modulo
	 */

	public function saveLogInfo($type, $action, $entity, $entityId, $syncTime, $timeSpent, $responseCode, $message, $adminUserId = 'null', $novaPontoCom_apiId = 'null')
	{
		$writeConn = Mage::getSingleton('core/resource')->getConnection('core_write');
		$sql = "INSERT INTO query_novapontocom_log " .
			   "( " . 
			   "	type, " . 
			   "	action, " . 
			   "	entity, " . 
			   "	entity_id, " . 
			   "	sync_start_time, " . 
			   "	time_spent, " . 
			   "	response_code, " . 
			   "	message, " . 
			   "	user_id, " .
			   "	novapontocom_apiid" .
			   ") " . 
			   "VALUES " . 
			   "( " . 
			   "	'" . $type . "', " . 
			   "	'" . $action . "', " . 
			   "	'" . $entity . "', " . 
			   "	" . $entityId . ", " . 
			   "	" . $syncTime . ", " . 
			   "	" . $timeSpent . ", " . 
			   "	" . $responseCode . ", " . 
			   "	" . $writeConn->quote($message) . ", " . 
			   "	" . $adminUserId . ", " .
			   "	" . $novaPontoCom_apiId . " " .
			   ") ";
		
		$writeConn->query($sql);
	}


	/*
	 * Confere se ha registro no log dentro do tempo passado como parametro (minutos)
	 */

	public function hasRecentLogRegister($minutes)
	{
		if(!$minutes)
		{
			$minutes = 5;
		}

		$readConn = Mage::getSingleton('core/resource')->getConnection('core_read');
		$sql = "SELECT COUNT(*) as n FROM query_novapontocom_log WHERE sync_start_time > " . (time() - ($minutes * 60));
		$result = $readConn->fetchCol($sql);

		if(!$result || !is_array($result) || count($result) != 1)
		{
			return false;
		}
		else
		{
			return ($result[0] > 0);
		}
	}



	public function getShippingMethodTitle($shippingMethod)
	{
		list($carrierCode, $methodCode) = explode("_", $shippingMethod);

		$carriers = Mage::getSingleton('shipping/config')->getAllCarriers();
        
        foreach($carriers as $carCod => $carriersModel)
        {
            if($carCod != $carrierCode)
            {
            	continue;
            }

            foreach($carriersModel->getAllowedMethods() as $metCod => $title)
            {
                if($metCod == $methodCode)
                {
                	return $title;
                }
            }
        }

        return "";
	}
}
