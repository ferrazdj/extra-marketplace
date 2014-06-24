<?php

class Query_NovaPontoCom_Model_Adminhtml_Stores
{

    /**
     * Atributos ativos dos clientes
	 * (formato vetor de vetores)
	 * 
     * @return array
     */
    public function toOptionArray()
    {
		$stores = array();
		
		foreach(Mage::app()->getStores() as $store)
		{
			$stores[] = array
			(
				'value' => $store->getId(), 
				'label' => $store->getName()
			);
		}
		
		return $stores;
    }

    /**
     * Atributos ativos dos clientes
	 * (formato vetor: key => value)
	 * 
     * @return array
     */
    public function toArray()
    {
        $stores = array();
		
		foreach(Mage::app()->getStores() as $store)
		{
			$stores[] = array
			(
				$store->getId() => $store->getName()
			);
		}
		
		return $stores;
    }
}