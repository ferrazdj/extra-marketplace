<?php

	class Query_NovaPontoCom_Model_Integration_Abstract
	{
		protected $_wsConnector;
		protected $_appToken;
		protected $_authToken;

		public function __construct()
		{
			$this->_appToken = Mage::helper('Query_NovaPontoCom')->getConfigSetting('app_token');
			$this->_authToken = Mage::helper('Query_NovaPontoCom')->getConfigSetting('auth_token');
			
			$this->_wsConnector = Mage::getModel('Query_NovaPontoCom/webServiceConnector');
		}
	}