<?php

class rrd
{
	private $rrdtoolbin;
	private $rrdfile;
	private $created = false;
	
	private $step = 60;
	private $datasources = array();
	private $archives = array();
	private $values = array();
	
	public function __construct($rrdtoolbin, $rrdfile)
	{
		$this->rrdtoolbin = $rrdtoolbin;
		$this->rrdfile = $rrdfile;
	}
	
	public function initOptions($temp)
	{
		$this->step = $temp['step'];
		$this->datasources = $temp['datasources'];
		$this->archives = $temp['archives'];
		foreach ($this->datasources as $ds)
		{
			$this->values[$ds['name']] = 'U';
		}
		if (!file_exists($this->rrdfile))
		{
			$this->create();
		}
		else
		{
			$this->created = true;
		}
	}
	
	public function getOptions()
	{
		$temp = array();
		$temp['step'] = $this->step;
		$temp['datasources'] = $this->datasources;
		$temp['archives'] = $this->archives;
		return $temp;
	}

	public function create()
	{
		$params = ' create ' . escapeshellarg($this->rrdfile);
		$params .= ' -s ' . escapeshellarg($this->step);
		foreach ($this->datasources as $ds)
		{
			$dsstring = 'DS:' . $ds['name'] . ':' . $ds['type'] . ':' . $ds['heartbeat'] . ':' . $ds['min'] . ':' . $ds['max'];
			$params .= ' ' . escapeshellarg($dsstring);
		}
		foreach ($this->archives as $rra)
		{
			$rrastring = 'RRA:' . $rra['cf'] . ':' . $rra['xff'] . ':' . $rra['steps'] . ':' . $rra['rows'];
			$params .= ' ' . escapeshellarg($rrastring);
		}
		system(escapeshellcmd($this->rrdtoolbin) . $params);
		$this->created = true;
	}

	public function addDatasource($name, $heartbeat = null, $min = 'U', $max = 'U', $type = 'GAUGE')
	{
		if ($this->created)
		{
			return;
		}
		if (!isset($heartbeat))
		{
			$heartbeat = $this->step * 4;
		}
		$this->datasources[] = array(
			'name' => $name,
			'min' => $min,
			'max' => $max,
			'type' => $type,
			'heartbeat' => $heartbeat
		);
		$this->values[$name] = 'U';
	}

	public function addArchive($steps, $rows, $cf = 'AVERAGE', $xff = '0.5')
	{
		if ($this->created)
		{
			return;
		}
		$this->archives[] = array(
			'steps' => $steps,
			'rows' => $rows,
			'cf' => $cf,
			'xff' => $xff
		);
	}

	public function setValue($dsname, $value)
	{
		if ($this->values[$dsname] != 'U')
		{
			return;
		}
		$this->values[$dsname] = $value;
	}

	public function update()
	{
		$params = ' update ' . escapeshellarg($this->rrdfile);
		$updatestr = 'N';
		foreach ($this->datasources as $ds)
		{
			$updatestr .= ':' . $this->values[$ds['name']];
		}
		$params .= ' ' . escapeshellarg($updatestr);
		system(escapeshellcmd($this->rrdtoolbin) . $params);

	}

	public function getDatasources()
	{
		return $this->datasources;
	}

	public function getArchives()
	{
		return $this->archives;
	}

	public function getRRDFile()
	{
		return $this->rrdfile;
	}
	
	public function getRRDToolBin()
	{
		return $this->rrdtoolbin;
	}
}

?>
