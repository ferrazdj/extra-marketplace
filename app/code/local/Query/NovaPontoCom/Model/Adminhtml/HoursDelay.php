<?php

class Query_NovaPontoCom_Model_Adminhtml_HoursDelay
{

    /**
     * Intervalo de 4 horas, variando de 4 a 24 horas
	 * (formato vetor de vetores)
	 * 
     * @return array
     */
    public function toOptionArray()
    {
		return array
		(
			array('value' => '04', 'label'=>Mage::helper('Query_NovaPontoCom')->__('%d hours', 4)),
			array('value' => '06', 'label'=>Mage::helper('Query_NovaPontoCom')->__('%d hours', 6)),
			array('value' => '08', 'label'=>Mage::helper('Query_NovaPontoCom')->__('%d hours', 8)),
			array('value' => '12', 'label'=>Mage::helper('Query_NovaPontoCom')->__('%d hours', 12)),
			array('value' => '24', 'label'=>Mage::helper('Query_NovaPontoCom')->__('%d hours', 24)),
		);
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
            '04' => Mage::helper('Query_NovaPontoCom')->__('%d hours', 4),
            '06' => Mage::helper('Query_NovaPontoCom')->__('%d hours', 6),
			'08' => Mage::helper('Query_NovaPontoCom')->__('%d hours', 8),
			'12' => Mage::helper('Query_NovaPontoCom')->__('%d hours', 12),
			'24' => Mage::helper('Query_NovaPontoCom')->__('%d hours', 24),
        );
    }

}