<?php
class Query_NovaPontoCom_Model_Config_Source_Brand
{
    public function toOptionArray()
    {
        return array(
            array('value'=>0, 'label'=>Mage::helper('Query_NovaPontoCom')->__('Value Fixed')),
            array('value'=>1, 'label'=>Mage::helper('Query_NovaPontoCom')->__('Value Options')),
        );
    }
}
