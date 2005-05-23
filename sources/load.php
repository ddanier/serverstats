<?php
/**
 * $Id$
 *
 * Author: David Danier, david.danier@team23.de
 * Project: Serverstats, http://www.webmasterpro.de/~ddanier/serverstats/
 * License: GPL v2 or later (http://www.gnu.org/copyleft/gpl.html)
 */

class load extends source
{
	private $loadavgfile;
	
	private $min1;
	private $min5;
	private $min15;
	private $running;
	private $tasks;
	
	public function __construct($loadavgfile = '/proc/loadavg')
	{
		$this->loadavgfile = $loadavgfile;
	}
	
	public function refreshData()
	{
		$temp = file_get_contents($this->loadavgfile);
		$temp = explode(' ', $temp);
		$this->min1 = $temp[0];
		$this->min5 = $temp[1];
		$this->min15 = $temp[2];
		$temp = explode('/', $temp[3]);
		$this->running = $temp[0];
		$this->tasks = $temp[1];
	}

	public function initRRD(rrd $rrd)
	{
		$rrd->addDatasource('1min');
		$rrd->addDatasource('5min');
		$rrd->addDatasource('15min');
		$rrd->addDatasource('running');
		$rrd->addDatasource('tasks');
	}

	public function updateRRD(rrd $rrd)
	{
		$rrd->setValue('1min', $this->min1);
		$rrd->setValue('5min', $this->min5);
		$rrd->setValue('15min', $this->min15);
		$rrd->setValue('running', $this->running);
		$rrd->setValue('tasks', $this->tasks);
	}
}

?>
