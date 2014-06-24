<?php

class Query_NovaPontoCom_Block_Status extends Mage_Adminhtml_Block_Template
{
    private $_prodSeries;
    private $_orderSeries;
    private $_minutesConsidered = 100;

    public function getServiceStatus()
	{
		$active = Mage::helper('Query_NovaPontoCom')->getConfigSetting('active');
		$sessionStatus = Mage::helper('Query_NovaPontoCom')->getSessionConfig('status');

		$label = "";

		if($active)
		{
			$label .= "Active";

			if($sessionStatus == "running")
			{
				$label .= " (running)";
			}
			else if($sessionStatus == "stopped")
			{
				$label .= " (waiting)";
			}
		}
		else
		{
			$label .= "Inactive";
		}

		return $label;
	}

	public function getServiceLastProductInstance()
	{
		$lastSync = Mage::helper('Query_NovaPontoCom')->getSessionConfig('product_last_sync');
		
		
		if($lastSync)
		{
			return date("d/m/Y H:i:s", $lastSync);
		}
		else
		{
			return "---";
		}
	}


	public function getSeries()
	{
		$now = time();
		$timeLimit = $now - (60 * $this->_minutesConsidered);

		$this->_constructProdSeries($timeLimit);
		$this->_constructOrderSeries($timeLimit);

		// monta as sequencias
		$costProd = "";
		$costOrder = "";
		$minutes = "	";

		for($i = $timeLimit; $i <= $now; $i += 300)
		{
			$costProd .= "'" . $this->_getInstanceForMoment($i, 'prod') . "',";
			$costOrder .= "'" . $this->_getInstanceForMoment($i, 'order') . "',";
			$minutes .= "'" . date('H:i', $i) . "',";
		}

		if($costProd != "") 	$costProd = substr($costProd, 0, strlen($costProd) - 1);
		if($costOrder != "") 	$costOrder = substr($costOrder, 0, strlen($costOrder) - 1);
		if($minutes != "") 		$minutes = substr($minutes, 0, strlen($minutes) - 1);

		$html = "var costProd = [" . $costProd . "];\n";
		$html.= "var costOrder = [" . $costOrder . "];\n";
		$html.= "var minutes = [" . $minutes . "];\n";

		return $html;
	}

	private function _getInstanceForMoment($moment, $type)
	{
		if($type == 'prod')
		{
			$series = $this->_prodSeries;
		}
		else if($type == 'order')
		{
			$series = $this->_orderSeries;
		}
		else
		{
			return "";
		}


		foreach($series as $time => $number)
		{
			if($time >= $moment && $time < ($moment + 300))
			{
				return $number;
			}
		}

		return "";
	}

	private function _constructProdSeries($timeLimit)
	{
		$this->_prodSeries = array();

		$sql = "SELECT
					sync_start_time,
					count(*) as n
				FROM
					query_novapontocom_log
				WHERE
					type = 'product' AND
					user_id is null AND
					sync_start_time >= " . $timeLimit . "
				GROUP BY
					sync_start_time
				ORDER BY
					sync_start_time ASC";
		
		$readConn = Mage::getSingleton('core/resource')->getConnection('core_read');
		$result = $readConn->fetchAll($sql);
		
		foreach($result as $row)
		{
			$this->_prodSeries[$row['sync_start_time']] = $row['n'];
		}
	}

	private function _constructOrderSeries($timeLimit)
	{
		$this->_orderSeries = array();

		$sql = "SELECT
					sync_start_time,
					count(*) as n
				FROM
					query_novapontocom_log
				WHERE
					type = 'order' AND
					user_id is null AND
					sync_start_time >= " . $timeLimit . "
				GROUP BY
					sync_start_time
				ORDER BY
					sync_start_time ASC";
		
		$readConn = Mage::getSingleton('core/resource')->getConnection('core_read');
		$result = $readConn->fetchAll($sql);
		
		foreach($result as $row)
		{
			$this->_orderSeries[$row['sync_start_time']] = $row['n'];
		}
	}


	public function getServiceLastOrderInstance()
	{
		$lastSync = Mage::helper('Query_NovaPontoCom')->getSessionConfig('order_last_sync');
		
		if($lastSync)
		{
			return date("d/m/Y H:i:s", $lastSync);
		}
		else
		{
			return "---";
		}
	}
}


