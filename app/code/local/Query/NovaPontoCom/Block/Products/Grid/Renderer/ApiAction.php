<?php

class Query_NovaPontoCom_Block_Products_Grid_Renderer_ApiAction extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    protected $_defaultWidth = 150;
    
    public function render(Varien_Object $row)
    {
        // confere se o modulo estah ativo
        if(!Mage::helper('Query_NovaPontoCom')->getConfigSetting('active'))
        {
            return $this->__("Module not active");
        }

        $importerInfo = $row->getData('novaPontoCom_importerInfoId');
        $status = Mage::helper("adminhtml")->__($row->getData('novaPontoCom_status'));
        $statusCode = $row->getData('novaPontoCom_statusCode');
        $apiSku = $row->getData('novaPontoCom_apiSku');
        $associatedProducts = $row->getData('novaPontoCom_associatedProds');

        if(($statusCode == 900 || $statusCode == 902) && !$importerInfo)
        {
            return Mage::helper("adminhtml")->__('Wait');
        }
        // enviado, mas ainda sendo analisado
        else if(($statusCode == 901 || $statusCode == 902) && $importerInfo)
        {
            $url = Mage::helper("adminhtml")->getUrl("Query_NovaPontoCom/products/updateCandidateStatus/", array("product" => $row->getData('entity_id')));
            $urlDesync = Mage::helper("adminhtml")->getUrl("Query_NovaPontoCom/products/desyncProduct/", array("product" => $row->getData('entity_id')));
			$jsDesyncFunction = "confirmNovaPontoComAction(\"" . $this->__('Do You really want to dessynchronize this product?') . "\", \"" . $urlDesync . "\");";
			
			return "<a href='" . $url . "'>" . Mage::helper("adminhtml")->__('Consult load') . "</a><br /><br />" . 
				   "<a href='javascript: " . $jsDesyncFunction . "'>" . Mage::helper("adminhtml")->__('Desynchronize Product') . "</a>";
        }
        // candidato a ser enviado
        else if($statusCode == 901 && !$associatedProducts)
        {
            $url = Mage::helper("adminhtml")->getUrl("Query_NovaPontoCom/products/sendProductAsNew/", array("product" => $row->getData('entity_id')));
            $jsFunction = "confirmNovaPontoComAction(\"" . $this->__('Do You really want to do this action?') . "\", \"" . $url . "\");";
			
			return "<a href='javascript: " . $jsFunction . "'>" . Mage::helper("adminhtml")->__('Send product') . "</a>";
        }
        // candidato a ser enviado
        else if($statusCode == 901 && $associatedProducts)
        {
            $url = Mage::helper("adminhtml")->getUrl("Query_NovaPontoCom/products/getAssociatedProds/");
            $jsFunction = "openNovaPontoComAssociateProductWindow(" . $row->getData('entity_id') . ", \"" . $url . "\", \"" . $this->__('Associate Product') . "\")";
			
            return "<a href='javascript: " . $jsFunction . "'>" . Mage::helper("adminhtml")->__('Send product') . "</a>";
        }
        else if(($statusCode == 5 || $statusCode == 6) && !$apiSku)
        {
            $url = Mage::helper("adminhtml")->getUrl("Query_NovaPontoCom/products/getApiSku/", array("product" => $row->getData('entity_id')));
            $urlDesync = Mage::helper("adminhtml")->getUrl("Query_NovaPontoCom/products/desyncProduct/", array("product" => $row->getData('entity_id')));
			$jsDesyncFunction = "confirmNovaPontoComAction(\"" . $this->__('Do You really want to dessynchronize this product?') . "\", \"" . $urlDesync . "\");";
			
			return "<a href='" . $url . "'>" . Mage::helper("adminhtml")->__('Get API SKU') . "</a><br /><br />" . 
				   "<a href='javascript: " . $jsDesyncFunction . "'>" . Mage::helper("adminhtml")->__('Desynchronize Product') . "</a>";
        }
        else if(($statusCode == 5 || $statusCode == 6) && $apiSku)
        {
            $url = Mage::helper("adminhtml")->getUrl("Query_NovaPontoCom/products/syncProduct/", array("product" => $row->getData('entity_id')));
            $urlDesync = Mage::helper("adminhtml")->getUrl("Query_NovaPontoCom/products/desyncProduct/", array("product" => $row->getData('entity_id')));
			$jsDesyncFunction = "confirmNovaPontoComAction(\"" . $this->__('Do You really want to dessynchronize this product?') . "\", \"" . $urlDesync . "\");";
			
			return "<a href='" . $url . "'>" . Mage::helper("adminhtml")->__('Synchronize') . "</a><br /><br />" . 
				   "<a href='javascript: " . $jsDesyncFunction . "'>" . Mage::helper("adminhtml")->__('Desynchronize Product') . "</a>";
        }
        // caso o produtos tenha sido enviados, mas ainda nao foi aprovado
        else
        {
            $url = Mage::helper("adminhtml")->getUrl("Query_NovaPontoCom/products/updateCandidateStatus/", array("product" => $row->getData('entity_id')));
            $urlDesync = Mage::helper("adminhtml")->getUrl("Query_NovaPontoCom/products/desyncProduct/", array("product" => $row->getData('entity_id')));
			$jsDesyncFunction = "confirmNovaPontoComAction(\"" . $this->__('Do You really want to dessynchronize this product?') . "\", \"" . $urlDesync . "\");";
			
			return "<a href='" . $url . "'>" . Mage::helper("adminhtml")->__('Update Status') . "</a><br /><br />" . 
				   "<a href='javascript: " . $jsDesyncFunction . "'>" . Mage::helper("adminhtml")->__('Desynchronize Product') . "</a>";
        }

        return $this->getColumn()->getDefault();
    }
}