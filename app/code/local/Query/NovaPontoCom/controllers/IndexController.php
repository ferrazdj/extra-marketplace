<?php

class Query_NovaPontoCom_IndexController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->_title($this->__('NovaPontoCom Status'));
		$this->loadLayout()->_setActiveMenu('novapontocom/status');
        $this->renderLayout();
    }
    
	/*
    protected function _isAllowed()
    {
        $actionName = ($this->getRequest()->getActionName() == "index") ? "status" : $this->getRequest()->getActionName();
        
        return Mage::getSingleton('admin/session')->isAllowed('vpsa/' . $actionName);
    }
	*/




	/*
    public function indexAction()
    {
        $this->loadLayout()->_setActiveMenu('novapontocom/status');
		$productIds = $this->getRequest()->getParam('product');
		$syncService = Mage::getModel('Query_NovaPontoCom/integration_product');
		$html = "";
		
		foreach($productIds as $productId)
		{
			$product = Mage::getModel('catalog/product')->load($productId);
			$html .= "<b>Produto: " . $product->getName() . " (" . $product->getId() . ")</b><br /><br />";
			
			if($product->getData('novaPontoCom_apiSku'))
			{
				$html .= "Produto presente no Market Place. Buscando dados...<br />";
				$html .= "Resultado: " . $result = $syncService->getInfo($product) . "<br />";
			}
			else if($product->getData('novaPontoCom_importerInfoId'))
			{
				$html .= "Produto jah enviado como candidato ao Market Place. Atualizando status...<br />";
				if($syncService->consultLoadItem($product))
				{
					$html .= "Atualizado com sucesso.<br />";
					
					if($product->getData('novaPontoCom_status') == "Aprovado")
					{
						$html .= "Produto aprovado. Consultando API Sku...<br />";
						
						if($syncService->consultApiSku($product))
						{
							$html .= "API Sku do produto: " . $product->getData('novaPontoCom_apiSku') . "<br />";
						}
						else
						{
							$html .= "Nao foi possivel buscar sku.";
						}
					}
					else
					{
						$html .= "Status: " . $product->getData('novaPontoCom_status') . "<br />";
					}
				}
				else
				{
					$html .= "Nao foi possivel atualizar.<br />";
				}
			}
			else
			{
				$html .= "Enviando como produto novo...<br />";
				if($syncService->load($product))
				{
					$html .= "Carregado com sucesso.<br />";
					$html .= "Status: " . $product->getData('novaPontoCom_status') . "<br />";
				}
				else
				{
					$html .= "Erro: por favor consulte o log.<br />";
				}
			}
		}
		
		$block = $this->getLayout()->createBlock('core/text', 'test-novapontocom')->setText($html);
        $this->_addContent($block);
		
        $this->renderLayout();
    }
	
	public function statusAction()
    {
        $this->loadLayout();
		
		// pega horario da ultima sincronizacao
		date_default_timezone_set('America/Sao_Paulo');
		$html = "Interface temporaria de acompanhamento<br /><br /><b>Ultima sincronizacao</b>: " . date('d/m/Y h:i:s', Mage::helper('Query_NovaPontoCom')->getSessionConfig('product_last_sync'));
		
		// pega os produtos que foram atualizados
		$prodIds = explode(",", Mage::helper('Query_NovaPontoCom')->getSessionConfig('product_last_sync_updated'));
		$html .= "<br /><br /><b>Produtos atualizados</b>:<br />";
		foreach($prodIds as $prodId)
		{
			$product = Mage::getModel('catalog/product')->load($prodId);
			$html .= $product->getName() . "<br />";
		}
		
		
		$block = $this->getLayout()->createBlock('core/text', 'novapontocom-status')->setText($html);
        $this->_addContent($block);
		
        $this->renderLayout();
    }

    public function productsAction()
	{
		$this->loadLayout()->_setActiveMenu('novapontocom/status');
		$this->_title($this->__('NovaPontoCom Products'));
		$this->renderLayout();
	}
	
	*/

}