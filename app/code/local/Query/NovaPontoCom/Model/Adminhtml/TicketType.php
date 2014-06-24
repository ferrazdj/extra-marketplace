<?php

class Query_NovaPontoCom_Model_Adminhtml_TicketType
{

	public function toOptionArray($emptyValue = false)
    {
		$values = array();
		
		if($emptyValue)
		{
			$values[] = array('value' => '', 'label' => Mage::helper('Query_NovaPontoCom')->__(''));
		}
		
		$values[] = array('value' => 'ccontact', 'label' => Mage::helper('Query_NovaPontoCom')->__('Contact'));
		$values[] = array('value' => 'cancel', 'label' => Mage::helper('Query_NovaPontoCom')->__('Cancellation'));
		
		return $values;
    }

    /**
     * Intervalo de 4 horas, variando de 4 a 24 horas
	 * (formato vetor: key => value)
	 * 
     * @return array
     */
    public function toArray()
    {
        return array
		(
            'contact' => Mage::helper('Query_NovaPontoCom')->__('Contact'),
            'cancel' => Mage::helper('Query_NovaPontoCom')->__('Cancellation')
        );
    }

}