<?php
/**
 * $Id$
 *
 * Author: David Danier, david.danier@team23.de
 * Project: Serverstats, http://www.webmasterpro.de/~ddanier/serverstats/
 * License: GPL v2 or later (http://www.gnu.org/copyleft/gpl.html)
 */

class external extends source
{
	private $command;
	private $datarow;
	private $data;
	
	public function __construct($command)
	{
		$this->command = $command;
	}
	
	private function getDatarow()
	{
		if (isset($this->datarow))
		{
			return;
		}
		$this->datarow = exec(escapeshellcmd($this->command));
		if ($this->datarow === false)
		{
			throw new Exception('Could not exec command (' . $this->command . ')');
		}
		// Some scripts have ugly output, so we have to delete all unneeded spaces
		$this->datarow = preg_replace('/([a-zA-Z0-9_]{1,19})[\s,]*:[\s,]*(.+)/', '\\1:\\2', $this->datarow);
	}
	
	private function getData()
	{
		if (isset($this->data))
		{
			return;
		}
		$this->getDatarow();
		$this->data = array();
		$elements = preg_split('/[\s,]+/', $this->datarow);
		foreach ($elements as $element)
		{
			if (preg_match('/^([a-zA-Z0-9_]{1,19}):(.+)$/', $element, $split))
			{
				$this->data[$split[1]] = $split[2];
			}
			else
			{
				$this->data[] = $element;
			}
		}
	}
	
	public function refreshData()
	{
		$this->getData();
	}
	
	public function initRRD(rrd $rrd)
	{
		$this->getData();
		foreach ($this->data as $key => $value)
		{
			$rrd->addDatasource($key);
		}
	}
	
	public function updateRRD(rrd $rrd)
	{
		foreach ($this->data as $key => $value)
		{
			$rrd->setValue($key, $value);
		}
	}
}

?>
