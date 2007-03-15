<?php
/**
 * $Id$
 *
 * Author: Andreas Korthaus, akorthaus@web.de
 * Enhancements/Bugfixes: David Danier, david.danier@team23.de
 * Project: Serverstats, http://serverstats.berlios.de/
 * License: GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
 *
 * Copyright (C) 2005 Andreas Korthaus
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

class cpu extends source implements source_cached, source_rrd
{
	private $path_stat;
	
	private $cpuFieldList = array(
		'user',
		'nice',
		'system',
		'idle',
		'iowait',
		'irq',
		'softirq'
	);
	private $sysFieldList = array(
		'intr',
		'ctxt',
		'processes',
		'procs_running',
		'procs_blocked'
	);
	private $sysFieldListPs = array(
		'intr',
		'ctxt',
		'processes',
	);
	private $uptimeField = 'btime';
	
	private $cpuStats;
	private $sysStats;
	private $uptime;
	
	private $oldCpuStats;
	
	public function __construct($path_stat = '/proc/stat')
	{
		$this->path_stat = $path_stat;
	}
	
	public function refreshData()
	{
		$this->getStats();
	}
	
	public function initRRD(rrd $rrd)
	{
		$this->getStats();
		foreach ($this->cpuStats as $cpu => $values)
		{
			foreach ($values as $key => $value)
			{
				$rrd->addDatasource($cpu . '_' . $key, 'GAUGE', null, 0, 100);
			}
		}
		foreach ($this->sysFieldList as $key)
		{
			$rrd->addDatasource($key, 'GAUGE', null, 0);
			if (in_array($key, $this->sysFieldListPs))
			{
				$rrd->addDatasource($key . '_ps', 'DERIVE', null, 0);
			}
		}
		$rrd->addDatasource('uptime', 'GAUGE', null, 0);
	}
	
	public function fetchValues()
	{
		$returnValues = array();
		foreach ($this->cpuStats as $cpu => $values)
		{
			$cpuUsage = array();
			foreach ($values as $key => $value)
			{
				if (isset($this->oldCpuStats[$cpu][$key]))
				{
					$cpuUsage[$key] = $value - $this->oldCpuStats[$cpu][$key];
				}
				else
				{
					$cpuUsage[$key] = 0;
				}
			}
			$sumUsage = array_sum($cpuUsage);
			if ($sumUsage > 0)
			{
				$factor = (100 / $sumUsage);
				foreach ($cpuUsage as $key => $value)
				{
					$returnValues[$cpu . '_' . $key] = $value * $factor;
				}
			}
		}
		foreach ($this->sysStats as $key => $value)
		{
			$returnValues[$key] = $value;
			if (in_array($key, $this->sysFieldListPs))
			{
				$returnValues[$key . '_ps'] = $value;
			}
		}
		$returnValues['uptime'] = $this->uptime;
		return $returnValues;
	}
	
	private function getStats()
	{
		if (isset($this->cpuStats) && isset($this->sysStats) && isset($this->uptime))
		{
			return;
		}
		
		if (($lines = @file($this->path_stat)) === false)
		{
			throw new Exception('Could not read "' . $this->path_stat . '"');
		}
		
		$this->cpuStats = array();
		$this->sysStats = array();
		foreach ($lines as $line)
		{
			if (preg_match('/^([a-zA-Z0-9]*)((\s+([0-9]+))+)\s*$/i', $line, $parts))
			{
				$key = strtolower($parts[1]);
				$value = trim($parts[2]);
				if (preg_match('/^cpu[0-9]*$/i', $key))
				{
					$this->cpuStats[$key] = array();
					$line_cpustats = preg_split('/\s+/', $value);
					$i = 0;
					foreach ($this->cpuFieldList as $fieldName)
					{
						if (!isset($line_cpustats[$i]))
						{
							break;
						}
						$this->cpuStats[$key][$fieldName] = $line_cpustats[$i];
						$i++;
					}
				}
				elseif (in_array($key, $this->sysFieldList))
				{
					// intval() converts "123 456" to int(123), so we don't have
					// to do anything special for "intr"
					$this->sysStats[$key] = intval($value);
				}
				elseif ($key == $this->uptimeField)
				{
					$this->uptime = time() - intval($value);
				}
			}
		}
	}
	
	public function initCache()
	{
		$this->getStats();
		$this->oldCpuStats = $this->cpuStats;
	}
	
	public function loadCache($cachedata)
	{
		$this->oldCpuStats = $cachedata['cpuStats'];
	}
	
	public function getCache()
	{
		return array(
			'cpuStats' => $this->cpuStats,
		);
	}
}

?>
