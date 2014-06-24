<?php

class Query_NovaPontoCom_Block_Tickets_Logs_Grid_Renderer_Comment extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $url = $this->getUrl("*/tickets_logs/view", array("id" => $row->getData('log_id')));
		
		return "<iframe frameborder=\"0\" src=\"" . $url . "\" width=\"100%\" height=\"400\">Your browser has no support to iframe.</iframe>";
    }
}