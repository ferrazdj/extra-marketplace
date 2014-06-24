<?php

class Query_NovaPontoCom_Model_Adminhtml_MinutesDelay
{

    /**
     * Intervalo de 5 minutos, variando de 10 a 45 minutos
	 * (formato vetor de vetores)
	 * 
     * @return array
     */
    public function toOptionArray()
    {
        $minutes = array();
		
		for($i = 10; $i <= 45; $i += 5)
		{
			$minutes[] = array
			(
				'value' => $i, 
				'label' => Mage::helper('Query_NovaPontoCom')->__("%d minutes", $i)
			);
		}
		
		return $minutes;
    }

    /**
     * Intervalo de 5 minutos, variando de 10 a 45 minutos
	 * (formato vetor: key => value)
	 * 
     * @return array
     */
    public function toArray()
    {
        $minutes = array();
		
		for($i = 10; $i <= 45; $i += 5)
		{
			$minutes[] = array
			(
				$i => Mage::helper('Query_NovaPontoCom')->__('%d minutes', $i)
			);
		}
		
		return $minutes;
    }

}