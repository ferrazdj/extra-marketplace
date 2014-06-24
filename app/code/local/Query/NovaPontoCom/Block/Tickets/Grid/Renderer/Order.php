<?php

class Query_NovaPontoCom_Block_Tickets_Grid_Renderer_Order extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $order = Mage::getModel('sales/order')->load($row->getData('order_id'));
        
        if($order->getId())
        {
            //$url = Mage::helper("adminhtml")->getUrl("adminhtml/sales_order/view/", array("order_id" => $order->getId()));
            //return "<a href='" . $url . "'>" . $order->getIncrementId() . "</a>";

            return $order->getIncrementId();
        }

        return $this->getColumn()->getDefault();
    }
}