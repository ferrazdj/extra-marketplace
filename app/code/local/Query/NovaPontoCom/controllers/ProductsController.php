<?php

class Query_NovaPontoCom_ProductsController extends Mage_Adminhtml_Controller_Action
{
    /* 
	 * 
	 * Pagina com a lsitagem dos produtos enviados e a serem enviados a NovaPontoCom;
	 * Acessada por meio do menu NovaPontoCom -> Products
	 * 
	 */

    public function indexAction()
	{
		$this->loadLayout()->_setActiveMenu('novapontocom/products');
		$this->_title($this->__('NovaPontoCom Products'));
		$this->renderLayout();
	}

	public function gridAction()
    {
        $this->loadLayout(false);
        $this->renderLayout();
    }

	/* 
	 * 
	 * Consulta se existem produtos relacionados e coloca produto como
	 * candidato a ser enviado (lista na pagina de produtos NovaPontoCom);
	 * Chamada pela acao de massa 'Send to NovaPontoCom', na pagina de produtos padrao
	 * 
	 */

	public function firstSendProductAction()
	{
		$productIds = $this->getRequest()->getParam('product');
		$syncService = Mage::getModel('Query_NovaPontoCom/integration_product');
		
		foreach($productIds as $productId)
		{
			$product = Mage::getModel('catalog/product')->load($productId);
			
			// confere se produto foi carregado com sucesso
			if(!$product || !$product->getId())
			{
				Mage::getSingleton('adminhtml/session')->addError($this->__('Product %d could not be loaded.', $productId));
				continue;
			}

			// confere se produto jah eh candidato a envio
			if($product->getData('novaPontoCom_statusCode') == 901)
			{
				continue;
			}
			
			// confere se produto jah nao foi carregado na api
			if($product->getData('novaPontoCom_importerInfoId'))
			{
				Mage::getSingleton('adminhtml/session')->addError($this->__('Product %d already exists on NovaPontoCom API.', $productId));
				continue;
			}

			$product->setData('novaPontoCom_statusCode', 900);
			$product->save();
		}
			
		Mage::app()->getResponse()->setRedirect(Mage::helper('adminhtml')->getUrl("novapontocom/products/index"));
	}


	/* 
	 * 
	 * Busca pagina de produtos associados para abertura da janela modal;
	 * Chamada pelo link 'Associate'
	 * 
	 */

	public function getAssociatedProdsAction()
	{
		$productId = $this->getRequest()->getParam('product');
		// pega o produto e confere se ele foi carregado corretamente
		$product = Mage::getModel('catalog/product')->load($productId);
		if(!$product || !$product->getId())
		{
			return;
		}

		$this->loadLayout();
		$this->getLayout()->getBlock('products.associate')->setProduct($product);
		$this->renderLayout();
	}


	/* 
	 * 
	 * Envia o produto como associado de outro,
	 * chamada pelo link 'Send as new'
	 * 
	 */

	public function AssociateProductAction()
	{
		$productId = $this->getRequest()->getParam('product');
		$skuId = $this->getRequest()->getParam('skuId');
		$syncService = Mage::getModel('Query_NovaPontoCom/integration_product');
		
		// pega o produto e confere se ele foi carregado corretamente
		$product = Mage::getModel('catalog/product')->load($productId);
		if(!$product || !$product->getId())
		{
			Mage::getSingleton('adminhtml/session')->addError($this->__('Product not found.'));
			Mage::app()->getResponse()->setRedirect(Mage::helper('adminhtml')->getUrl("*/*/index"));
			return;
		}

		try
		{
			$syncService->associate($product, $skuId);

			// log
			Mage::helper('Query_NovaPontoCom')->saveLogInfo
			(
				'product', 
				'association', 
				'catalog/product', 
				$product->getId(), 
				time(), 
				0, 
				0, 
				'',
				Mage::getSingleton('admin/session')->getUser()->getId()
			);

			Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Product successfully associated.'));
		}
		catch(Exception $e)
		{
			// log
			Mage::helper('Query_NovaPontoCom')->saveLogInfo
			(
				'product', 
				'association', 
				'catalog/product', 
				$product->getId(), 
				time(), 
				0, 
				$e->getCode(), 
				$e->getMessage(),
				Mage::getSingleton('admin/session')->getUser()->getId()
			);

			Mage::getSingleton('adminhtml/session')->addError($this->__('Error trying to associate product: ') . $this->__($e->getMessage()));
		}
			
		Mage::app()->getResponse()->setRedirect(Mage::helper('adminhtml')->getUrl("novapontocom/products/index"));
	}



	/* 
	 * 
	 * Envia o produto como novo,
	 * chamada pelo link 'Send as new'
	 * 
	 */

	public function sendProductAsNewAction()
	{
		$productId = $this->getRequest()->getParam('product');
		$syncService = Mage::getModel('Query_NovaPontoCom/integration_product');

		// pega o produto e confere se ele foi carregado corretamente
		$product = Mage::getModel('catalog/product')->load($productId);
		if(!$product || !$product->getId())
		{
			Mage::getSingleton('adminhtml/session')->addError($this->__('Product not found.'));
			Mage::app()->getResponse()->setRedirect(Mage::helper('adminhtml')->getUrl("*/*/index"));
			return;
		}

		// pega os indices de dimensoes e fabricante
		$brandIndex = Mage::helper('Query_NovaPontoCom')->getProductFieldsConfig('brand');
		$hasBrand = Mage::helper('Query_NovaPontoCom')->getProductFieldsConfig('has_brand');

		if($hasBrand)
		{
			if(!$brandIndex)
			{
				if(!Mage::helper('Query_NovaPontoCom')->getProductFieldsConfig('default_brand'))
				{
					Mage::getSingleton('adminhtml/session')->addError
					(
						$this->__('Error trying to send product: ') . 
						$this->__("please configure the brand attribute in System -> Configuration -> Extra.com -> Product Configuration")
					);
				}
			}
		}

		// elege produto a ser enviado como novo
		$product->setData('novaPontoCom_statusCode', 902);
		$product->save();

		Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Product added to send queue.'));

		/*
		try
		{
			$syncService->load($product);

			// log
			Mage::helper('Query_NovaPontoCom')->saveLogInfo
			(
				'product', 
				'send-as-new', 
				'catalog/product', 
				$product->getId(), 
				time(), 
				0, 
				0, 
				'',
				Mage::getSingleton('admin/session')->getUser()->getId()
			);

			Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Product successfully sent.'));
		}
		catch(Exception $e)
		{
			// log
			Mage::helper('Query_NovaPontoCom')->saveLogInfo
			(
				'product', 
				'send-as-new', 
				'catalog/product', 
				$product->getId(), 
				time(), 
				0, 
				$e->getCode(), 
				$e->getMessage(),
				Mage::getSingleton('admin/session')->getUser()->getId()
			);

			Mage::getSingleton('adminhtml/session')->addError($this->__('Error trying to send product: ') . $this->__($e->getMessage()));
		}
		*/
			
		Mage::app()->getResponse()->setRedirect(Mage::helper('adminhtml')->getUrl("novapontocom/products/index"));
	}


	/* 
	 * 
	 * Consulta o status do produto enviado no upload e, caso seja aprovado,
	 * consulta sku do produto na API da NovaPontoCom;
	 * chamada pelo link 'Consult load'
	 * 
	 */
	
	public function updateCandidateStatusAction()
	{
		$productId = $this->getRequest()->getParam('product');
		$syncService = Mage::getModel('Query_NovaPontoCom/integration_product');

		// pega o produto e confere se ele foi carregado corretamente
		$product = Mage::getModel('catalog/product')->load($productId);
		if(!$product || !$product->getId())
		{
			Mage::getSingleton('adminhtml/session')->addError($this->__('Product not found.'));
			Mage::app()->getResponse()->setRedirect(Mage::helper('adminhtml')->getUrl("*/*/index"));
			return;
		}

		// confere se jah foi enviado, antes de atualiza-lo
		if(!$product->getData('novaPontoCom_importerInfoId'))
		{
			Mage::getSingleton('adminhtml/session')->addError($this->__('Product not sent to API yet.'));
			Mage::app()->getResponse()->setRedirect(Mage::helper('adminhtml')->getUrl("*/*/index"));
			return;
		}

		try
		{
			$syncService->consultLoadItem($product);

			// log
			Mage::helper('Query_NovaPontoCom')->saveLogInfo
			(
				'product', 
				'update-candidate-status', 
				'catalog/product', 
				$product->getId(), 
				time(), 
				0, 
				0, 
				'',
				Mage::getSingleton('admin/session')->getUser()->getId()
			);

			Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Product Status updated successfully.'));
		}
		catch(Exception $e)
		{
			// log
			Mage::helper('Query_NovaPontoCom')->saveLogInfo
			(
				'product', 
				'update-candidate-status', 
				'catalog/product', 
				$product->getId(), 
				time(), 
				0, 
				$e->getCode(), 
				$e->getMessage(),
				Mage::getSingleton('admin/session')->getUser()->getId()
			);

			Mage::getSingleton('adminhtml/session')->addError($this->__('Error trying to update product: ') . $this->__($e->getMessage()));
		}


		try
		{
			if($product->getData('novaPontoCom_statusCode') == 5 || $product->getData('novaPontoCom_statusCode') == 6)
			{
				$syncService->consultApiSku($product);
				Mage::getSingleton('adminhtml/session')->addSuccess($this->__('API SKU updated.'));
			}
		}
		catch(Exception $e)
		{
			// log
			Mage::helper('Query_NovaPontoCom')->saveLogInfo
			(
				'product', 
				'get-sku-api', 
				'catalog/product', 
				$product->getId(), 
				time(), 
				0, 
				$e->getCode(), 
				$e->getMessage(),
				Mage::getSingleton('admin/session')->getUser()->getId()
			);

			Mage::getSingleton('adminhtml/session')->addError($this->__('Error trying to get API SKU: ') . $this->__($e->getMessage()));
		}

		Mage::app()->getResponse()->setRedirect(Mage::helper('adminhtml')->getUrl("*/*/index"));
	}


	/* 
	 * 
	 * Consulta o sku do produto na API da NovaPontoCom;
	 * chamada pelo link 'Get API SKU'
	 * 
	 */

	public function getApiSkuAction()
	{
		$productId = $this->getRequest()->getParam('product');
		$syncService = Mage::getModel('Query_NovaPontoCom/integration_product');

		// pega o produto e confere se ele foi carregado corretamente
		$product = Mage::getModel('catalog/product')->load($productId);
		if(!$product || !$product->getId())
		{
			Mage::getSingleton('adminhtml/session')->addError($this->__('Product not found.'));
			Mage::app()->getResponse()->setRedirect(Mage::helper('adminhtml')->getUrl("*/*/index"));
			return;
		}

		// confere se jah foi enviado, antes de atualiza-lo
		if(!$product->getData('novaPontoCom_importerInfoId'))
		{
			Mage::getSingleton('adminhtml/session')->addError($this->__('Product not sent to API yet.'));
			Mage::app()->getResponse()->setRedirect(Mage::helper('adminhtml')->getUrl("*/*/index"));
			return;
		}

		try
		{
			$syncService->consultApiSku($product);

			// log
			Mage::helper('Query_NovaPontoCom')->saveLogInfo
			(
				'product', 
				'get-sku-api', 
				'catalog/product', 
				$product->getId(), 
				time(), 
				0, 
				0, 
				'',
				Mage::getSingleton('admin/session')->getUser()->getId()
			);

			Mage::getSingleton('adminhtml/session')->addSuccess($this->__('API SKU updated.'));
		}
		catch(Exception $e)
		{
			// log
			Mage::helper('Query_NovaPontoCom')->saveLogInfo
			(
				'product', 
				'get-sku-api', 
				'catalog/product', 
				$product->getId(), 
				time(), 
				0, 
				$e->getCode(), 
				$e->getMessage(),
				Mage::getSingleton('admin/session')->getUser()->getId()
			);

			Mage::getSingleton('adminhtml/session')->addError($this->__('Error trying to get API SKU: ') . $this->__($e->getMessage()));
		}

		Mage::app()->getResponse()->setRedirect(Mage::helper('adminhtml')->getUrl("*/*/index"));
	}


	/* 
	 * 
	 * Atualiza preco e estoque do produto na API da NovaPontoCom
	 * chamada pelo link 'Synchronize'
	 * 
	 */

	public function syncProductAction()
	{
		$productId = $this->getRequest()->getParam('product');
		$syncService = Mage::getModel('Query_NovaPontoCom/integration_product');

		// pega o produto e confere se ele foi carregado corretamente
		$product = Mage::getModel('catalog/product')->load($productId);
		if(!$product || !$product->getId())
		{
			Mage::getSingleton('adminhtml/session')->addError($this->__('Product not found.'));
			Mage::app()->getResponse()->setRedirect(Mage::helper('adminhtml')->getUrl("*/*/index"));
			return;
		}

		// confere se jah foi enviado, antes de atualiza-lo
		if(!$product->getData('novaPontoCom_apiSku'))
		{
			Mage::getSingleton('adminhtml/session')->addError($this->__('Product API SKU not found.'));
			Mage::app()->getResponse()->setRedirect(Mage::helper('adminhtml')->getUrl("*/*/index"));
			return;
		}
		
		try
		{
			// faz a requisicao de atualizacao 
			$syncService->updateStock($product);

			// log
			Mage::helper('Query_NovaPontoCom')->saveLogInfo
			(
				'product', 
				'update-stock', 
				'catalog/product', 
				$product->getId(), 
				time(), 
				0, 
				0, 
				'',
				Mage::getSingleton('admin/session')->getUser()->getId()
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
				time(), 
				0, 
				$e->getCode(), 
				$e->getMessage(),
				Mage::getSingleton('admin/session')->getUser()->getId()
			);

			Mage::getSingleton('adminhtml/session')->addError($this->__('Could not update product: ') . $this->__($e->getMessage()));
		}

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
				time(), 
				0, 
				0, 
				'',
				Mage::getSingleton('admin/session')->getUser()->getId()
			);

			Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Product Status updated successfully'));
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
				time(), 
				0, 
				$e->getCode(), 
				$e->getMessage(),
				Mage::getSingleton('admin/session')->getUser()->getId()
			);

			Mage::getSingleton('adminhtml/session')->addError($this->__('Could not update product: ') . $this->__($e->getMessage()));
		}
		
		Mage::app()->getResponse()->setRedirect(Mage::helper('adminhtml')->getUrl("*/*/index"));
	}
	
	
	/* 
	 * 
	 * Remove produto da sincronizacao com a API
	 * 
	 */

	public function desyncProductAction()
	{
		$productId = $this->getRequest()->getParam('product');
		$syncService = Mage::getModel('Query_NovaPontoCom/integration_product');

		// pega o produto e confere se ele foi carregado corretamente
		$product = Mage::getModel('catalog/product')->load($productId);
		if(!$product || !$product->getId())
		{
			Mage::getSingleton('adminhtml/session')->addError($this->__('Product not found.'));
			Mage::app()->getResponse()->setRedirect(Mage::helper('adminhtml')->getUrl("*/*/index"));
			return;
		}

		try
		{
			// faz a requisicao de atualizacao do estoque (zerando-o)
			if($product->getData('novaPontoCom_apiSku'))
			{
				$syncService->updateStock($product, 0);
			}

			// log
			Mage::helper('Query_NovaPontoCom')->saveLogInfo
			(
				'product', 
				'desync', 
				'catalog/product', 
				$product->getId(), 
				time(), 
				0, 
				0, 
				'',
				Mage::getSingleton('admin/session')->getUser()->getId()
			);
			
			$product->setData('novaPontoCom_importerInfoId', '');
			$product->setData('novaPontoCom_status', '');
			$product->setData('novaPontoCom_statusCode', -1);
			$product->setData('novaPontoCom_apiSku', '');
			$product->setData('novaPontoCom_associatedProds', '');
			$product->save();
			
			Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Product successfully desynchronized.'));
		}
		catch(Exception $e)
		{
			// log
			Mage::helper('Query_NovaPontoCom')->saveLogInfo
			(
				'product', 
				'desync', 
				'catalog/product', 
				$product->getId(), 
				time(), 
				0, 
				$e->getCode(), 
				$e->getMessage(),
				Mage::getSingleton('admin/session')->getUser()->getId()
			);

			Mage::getSingleton('adminhtml/session')->addError($this->__('Could not desynchronize product: ') . $this->__($e->getMessage()));
		}
		
		Mage::app()->getResponse()->setRedirect(Mage::helper('adminhtml')->getUrl("*/*/index"));
	}
}