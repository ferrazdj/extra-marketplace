<?php

class Query_NovaPontoCom_Model_Adminhtml_ShippingMethods
{
    public function toOptionArray()
    {
        $carriers = Mage::getSingleton('shipping/config')->getActiveCarriers();
        $shippingMethods = array();

        foreach($carriers as $carriersCode => $carriersModel)
        {
            foreach($carriersModel->getAllowedMethods() as $methodCode => $methodTitle)
            {
                $shippingMethods[] = array 
                (
                    'value' =>  $carriersCode . "_" . $methodCode,
                    'label' => $methodTitle
                );
            }
        }
        
        return $shippingMethods;
    }

    public function toArray()
    {
        $carriers = Mage::getSingleton('shipping/config')->getActiveCarriers();
        $shippingMethods = array();

        foreach($carriers as $carriersCode => $carriersModel)
        {
            foreach($carriersModel->getAllowedMethods() as $methodCode => $methodTitle)
            {
                $shippingMethods[] = array 
                (
                    $carriersCode . "_" . $methodCode => $methodTitle
                );
            }
        }

        return $shippingMethods;  
    }
}