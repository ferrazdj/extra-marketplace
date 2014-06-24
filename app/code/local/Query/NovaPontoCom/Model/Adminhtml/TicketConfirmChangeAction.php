<?php

class Query_NovaPontoCom_Model_Adminhtml_TicketConfirmChangeAction
{

	public function toOptionArray($emptyValue = false)
    {
		$values = array();
		
		if($emptyValue)
		{
			$values[] = array('value' => '', 'label' => Mage::helper('Query_NovaPontoCom')->__(''));
		}
		
		// $values[] = array('value' => 'changeproduct', 'label' => Mage::helper('Query_NovaPontoCom')->__('Change Product'));
		$values[] = array('value' => 'accountchargeback', 'label' => Mage::helper('Query_NovaPontoCom')->__('Account Chargeback'));
		$values[] = array('value' => 'creditcardchargeback', 'label' => Mage::helper('Query_NovaPontoCom')->__('Credit Card Chargeback'));
		
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
            // 'changeproduct' => Mage::helper('Query_NovaPontoCom')->__('Change Product'),
            'accountchargeback' => Mage::helper('Query_NovaPontoCom')->__('Account Chargeback'),
            'creditcardchargeback' => Mage::helper('Query_NovaPontoCom')->__('Credit Card Chargeback')
        );
    }

}