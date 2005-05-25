<?php
/**
 * $Id$
 *
 * Author: David Danier, david.danier@team23.de
 * Project: Serverstats, http://www.webmasterpro.de/~ddanier/serverstats/
 * License: GPL v2 or later (http://www.gnu.org/copyleft/gpl.html)
 */

class traffic extends source
{
	private $logdir;
	private $chain;
	private $cache;

	private $traffic;
	
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
		if (!($traffic = @file_get_contents($this->logdir . DIRECTORY_SEPARATOR . $this->chain)))
		{
			throw new Exception('Could not get current data');
		}
		else
		{
			$traffic = trim($traffic);
		}
		$this->traffic = $traffic;
	}

	public function initRRD(rrd $rrd)
	{
		$rrd->addDatasource('traffic', 'GAUGE', null, 0);
		$rrd->addDatasource('bps', 'DERIVE', null, 0);
	}

	public function updateRRD(rrd $rrd)
	{
		$rrd->setValue('traffic', $this->traffic);
		$rrd->setValue('bps', $this->traffic);
	}
}

?>
