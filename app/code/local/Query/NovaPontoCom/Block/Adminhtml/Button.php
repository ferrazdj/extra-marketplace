<?php 
class Query_NovaPontoCom_Block_Adminhtml_Button extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        
        $url = Mage::helper('adminhtml')->getUrl('novapontocom/config/testDataForm/', array('id' => $element->getId()));

        $html = $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setId($element->getId())
                    ->setType('button')
                    ->setClass('scalable')
                    ->setLabel($this->__('Verify'))
                    ->setOnClick("openNovaPontoComConfigTestWindow('" . $element->getId() . "', '" . $url . "', '" . $this->__("Data Test Window") . "');")
                    ->toHtml();

        return $html;
    }
}