<?php

class Query_NovaPontoCom_Block_Products_Grid_Renderer_ApiStatus extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    protected $_defaultWidth = 200;
    
    public function render(Varien_Object $row)
    {
        $importerInfo = $row->getData('novaPontoCom_importerInfoId');
        $status = $row->getData('novaPontoCom_status');
        $statusCode = $row->getData('novaPontoCom_statusCode');
        $apiSku = $row->getData('novaPontoCom_apiSku');


        if($statusCode == 0)
        {
            $errors = json_decode($row->getData('novaPontoCom_importError'));
            $errorHtml = "";

            if(is_array($errors))
            {
                foreach($errors as $err)
                {
                    $errorHtml .= "[" . ((string) $err->type) . "] " . ((string) $err->fieldName) . ": " . ((string) $err->message) . "<br />";
                }

                $status .= "<br /><br /><b>" . $this->__("Errors") . ":</b><br />" . $errorHtml;
            }
        }


        // candidato a ser enviado
        if($statusCode == 900 && !$importerInfo)
        {
            return "<b>" . $this->__("Searching for associated products") . "</b>";
        }
        else if($statusCode == 902 && !$importerInfo)
        {
            return "<b>" . $this->__("Waiting to be sent as new") . "</b>";
        }
        else if(($statusCode == 901 || $statusCode == 902) && $importerInfo)
        {
            return "<b>" . $this->__("Recently sent") . "</b><br />". $this->__("Importer Info ID") . ": " . $importerInfo;
        }
        else if($statusCode == 901)
        {
            return "<b>" . $this->__("Waiting dispatch") . "</b>";
        }
        // caso o produto ainda nao tenha sido enviado
        else if(!$statusCode && !$importerInfo)
        {
            return "<b>" . $this->__("Not loaded") . "</b>";
        }
        else if(($statusCode == 5 || $statusCode == 6) && !$apiSku)
        {
            return "<b>" . $this->__("Loaded") . "</b><br />". $this->__("Importer Info ID") . ": " . $importerInfo . "<br />". $this->__("Status") . ": " . $status;
        }
        else if(($statusCode == 5 || $statusCode == 6) && $apiSku)
        {
            return "<b>" . $this->__("Loaded") . "</b><br />". $this->__("API SKU") . ":" . $apiSku;
        }
        // caso o produtos tenha sido enviado, mas ainda nao foi aprovado
        else
        {
            return "<b>" . $this->__("Validating") . "</b><br />". $this->__("Importer Info ID") . ": " . $importerInfo . "<br />". $this->__("Status") . ": " . $status;
        }
        

        return $this->getColumn()->getDefault();
    }
}