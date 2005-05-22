<?php

class traffic extends source
{
	private $logdir;
	private $chain;
	private $cache;
	
	public function __construct($chain, $logdir = null)
	{
		if (!isset($logdir))
		{
			$logdir = SOURCEPATH . 'traffic';
		}
		$this->logdir = $logdir;
		$this->chain = $chain;
	}
	
	public function refreshData()
	{
		$this->cache['last'] = $this->cache['current'];
		$this->cache['current'] = array();
		if (!($traffic = @file_get_contents($this->logdir . DIRECTORY_SEPARATOR . $this->chain)))
		{
			$traffic = 0;
		}
		else
		{
			$traffic = trim($traffic);
		}
		$this->cache['current']['traffic'] = $traffic;
		$this->cache['current']['date'] = time();
	}

	public function initRRD(rrd $rrd)
	{
		$rrd->addDatasource('traffic');
		$rrd->addDatasource('bps');
	}

	public function updateRRD(rrd $rrd)
	{
		$traffic = $this->cache['current']['traffic'];
		$trafficD = $this->cache['current']['traffic'] - $this->cache['last']['traffic'];
		$dateD = $this->cache['current']['date'] - $this->cache['last']['date'];
		$bps = $trafficD / $dateD;
		if ($bps < 0 || $this->cache['last']['date'] == -1)
		{
			$bps = 0;
		}
		$rrd->setValue('traffic', $traffic);
		$rrd->setValue('bps', $bps);
	}
	
	public function useCache()
	{
		return true;
	}
	
	public function initCache()
	{
		$this->cache = array(
			'last' => array('traffic' => 0, 'date' => -1),
			'current' => array('traffic' => 0, 'date' => -1)
		);
	}
	
	public function loadCache($cachedata)
	{
		$this->cache = $cachedata;
	}
	
	public function getCache()
	{
		return $this->cache;
	}
}

?>
