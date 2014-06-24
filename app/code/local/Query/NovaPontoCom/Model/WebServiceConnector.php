<?php

class Query_NovaPontoCom_Model_WebServiceConnector
{
	private $_curlSession;
	private $_baseUrl;
	private $_requestHeaders;
	private $_postParams;
	private $_responseCode;
	private $_responseHeaders;
	private $_responseBody;
	private $_errors;
	
	
	public function __construct()
	{
		$this->_requestHeaders = array();
		$this->_errors = array();
		//$this->_baseUrl = "http://sandbox.extra.com.br/api/v1/";
		$this->_baseUrl = "https://api.extra.com.br/api/v1/";
	}
	
	/*
	 * Inicializa o objeto curl
	 */
	protected function _initCurl()
	{
		$this->_curlSession = curl_init();
		curl_setopt($this->_curlSession, CURLOPT_VERBOSE, 0); 
		curl_setopt($this->_curlSession, CURLOPT_SSLVERSION, 3); 
		curl_setopt($this->_curlSession, CURLOPT_SSL_VERIFYHOST, 2); 
		curl_setopt($this->_curlSession, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($this->_curlSession, CURLOPT_TIMEOUT, 40);
		curl_setopt($this->_curlSession, CURLOPT_HEADER, true);
		curl_setopt($this->_curlSession, CURLOPT_RETURNTRANSFER, true);
		$this->_errors = array();
		$this->_requestHeaders = array();
		$this->_responseHeaders = "";
		$this->_responseBody = "";
	}
	
	/*
	 * Altera a url a ser utilizada
	 */
	protected function _appendRequestUrl($str)
	{
		$this->_baseUrl .= $str;
	}
	
	/*
	 * Adiciona linhas ao cabecalho da requisicao
	 */
	protected function _addRequestHeader($content)
	{
		$this->_requestHeaders[] = $content;
	}

	/*
	 * Realiza a requisicao para o servidor
	 */
	protected function _doRequest($uri)
	{
		curl_setopt($this->_curlSession, CURLOPT_HTTPHEADER, $this->_requestHeaders);
		curl_setopt($this->_curlSession, CURLOPT_URL, $this->_baseUrl . $uri);
		$response = curl_exec($this->_curlSession);
		
		$this->_responseCode = curl_getinfo($this->_curlSession, CURLINFO_HTTP_CODE);
//		Zend_Debug::dump(curl_getinfo($this->_curlSession));
//		Mage::log(curl_getinfo($this->_curlSession));

		if($response === false)
		{
//			Zend_Debug::dump("Erro: " . curl_error($this->_curlSession));
//			Mage::log("Erro: " . curl_error($this->_curlSession));
		}
		else
		{
			// separa cabecalho do corpo
			$headerSize = curl_getinfo($this->_curlSession, CURLINFO_HEADER_SIZE);
			$headers = explode("\r\n", substr($response, 0, $headerSize));
			foreach($headers as $header)
			{
				@list($key, $value) = explode(":", $header);

				if($key)
				{
					$this->_responseHeaders[$key] = trim($value);
				}
			}

			$this->_responseBody = substr($response, $headerSize);
			
//			Zend_Debug::dump($this->_responseHeaders);
//			Mage::log($this->_responseHeaders);
//			Zend_Debug::dump($this->_responseBody);
//			Mage::log($this->_responseBody);
		}
	}
	
	/*
	 * Realiza um GET de alguma funcao do servico REST
	 */
	public function doGet($authToken, $appToken, $location, $params = "")
	{
		$this->_initCurl();
		$this->_addRequestHeader('nova-auth-token: ' . $authToken);
		$this->_addRequestHeader('nova-app-token: ' . $appToken);
		$this->_addRequestHeader('content-type: application/json');
		$this->_doRequest($location . "?" . $params);
		
		if($this->_responseCode == "200")
		{
			return true;
		}
		else
		{
			throw new Exception($this->formatError(), $this->_responseCode);
		}
	}

	/*
	 * Realiza um POST de alguma funcao do servico REST
	 */
	public function doPost($authToken, $appToken, $location, $params, $headerContentType = null)
	{
		$this->_initCurl();
		$this->_addRequestHeader('nova-auth-token: ' . $authToken);
		$this->_addRequestHeader('nova-app-token: ' . $appToken);
		if(!$headerContentType)
		{
			$this->_addRequestHeader('content-type: application/gzip');
		}
		else
		{
			$this->_addRequestHeader('content-type: application/' . $headerContentType);
		}

		curl_setopt($this->_curlSession, CURLOPT_POST, true);
		curl_setopt($this->_curlSession, CURLOPT_POSTFIELDS, $params);
		$this->_doRequest($location);

		if($this->_responseCode != "201" && $this->_responseCode != "202")
		{
			throw new Exception($this->formatError(), $this->_responseCode);
		}
	}
	
	/*
	 * Realiza um PUT de alguma funcao do servico REST
	 */
	public function doPut($authToken, $appToken, $location, $params = '')
	{
		$this->_initCurl();
		$this->_addRequestHeader('nova-auth-token: ' . $authToken);
		$this->_addRequestHeader('nova-app-token: ' . $appToken);
		$this->_addRequestHeader('content-type: application/json');
		curl_setopt($this->_curlSession, CURLOPT_CUSTOMREQUEST, "PUT");
		curl_setopt($this->_curlSession, CURLOPT_POSTFIELDS, $params);
		$this->_doRequest($location);

		if($this->_responseCode != "202" && $this->_responseCode != "204")
		{
			throw new Exception($this->formatError(), $this->_responseCode);
		}
		
		return true;
	}

	/*
	 * retorna codigo de resposta da requisicao
	 */
	protected function getResponseCode()
	{
		return $this->_responseCode;
	}

	/*
	 * retorna um cabecalho especifico da resposta
	 */
	public function getResponseHeader($header)
	{
		return (isset($this->_responseHeaders[$header]) ? $this->_responseHeaders[$header] : "");
	}

	/*
	 * retorna conteudo de resposta da requisicao
	 */
	public function getResponseBody()
	{
		return $this->_responseBody;
	}

	/*
	 * tenta formatar a string de erro de acordo com o retorno do servidor
	 */
	public function formatError()
	{
		$json = json_decode($this->_responseBody);

		// descricao vinda do servidor
		if($json && isset($json->errorDesc))
		{
			$errorDesc = $json->errorDesc . " (code " . $this->_responseCode . ").";
		}
		else if($this->_responseBody)
		{
			$errorDesc = $this->_responseBody . " (code " . $this->_responseCode . ").";
		}
		else
		{
			$errorDesc = "";
		}

		// tipo do erro
		if($this->_responseCode == 400)
		{
			return "badly formatted request: " . $errorDesc;
		}
		else if($this->_responseCode == 401)
		{
			return "request needs authentication: " . $errorDesc;
		}
		else if($this->_responseCode == 403)
		{
			return "denied request: " . $errorDesc;
		}
		else if($this->_responseCode == 404)
		{
			return "resource not found: " . $errorDesc;
		}
		else if($this->_responseCode == 405)
		{
			return "not permited action: " . $errorDesc;
		}
		else if($this->_responseCode == 406)
		{
			return "not permited action: " . $errorDesc;
		}
		else if($this->_responseCode == 415)
		{
			return "invalid media type: " . $errorDesc;
		}
		else if($this->_responseCode == 422)
		{
			return "logic exception: " . $errorDesc;
		}
		else if($this->_responseCode == 500)
		{
			return "server error.";
		}
		else
		{
			return "unrecognized exception: " . $this->_responseCode;
		}
	}
}