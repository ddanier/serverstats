<?php
/**
 * $Id$
 *
 * Author: David Danier, david.danier@team23.de
 * Project: Serverstats, http://www.webmasterpro.de/~ddanier/serverstats/
 * License: GPL v2 or later (http://www.gnu.org/copyleft/gpl.html)
 *
 * Copyright (C) 2005 David Danier
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

class disk extends source
{
	private $path_stat;
	private $filter_disk;
	private $filter_part;
	
	private $stats_disk;
	private $stats_part;
	private $time;
	
	private static $cachetime = array();
	private static $cache = array();
	
	public function __construct($filter_disk = null, $filter_part = null, $path_stat = '/proc/diskstats')
	{
		$this->path_stat = $path_stat;
		if (isset($filter_disk))
		{
			if (is_array($filter_disk))
			{
				$this->filter_disk = $filter_disk;
			}
			else
			{
				$this->filter_disk = array($filter_disk);
			}
		}
		else
		{
			$this->filter_disk = null;
		}
		if (isset($filter_part))
		{
			if (is_array($filter_part))
			{
				$this->filter_part = $filter_part;
			}
			else
			{
				$this->filter_part = array($filter_part);
			}
		}
		else
		{
			$this->filter_part = null;
		}
	}
	
	public function refreshData()
	{
		$this->getStats();
	}
	
	public function initRRD(rrd $rrd)
	{
		$this->getStats();
		foreach ($this->stats_disk as $disk => $values)
		{
			$rrd->addDatasource($disk . '_rps', 'DERIVE', null, 0);
			$rrd->addDatasource($disk . '_wps', 'DERIVE', null, 0);
		}
		foreach ($this->stats_part as $part => $values)
		{
			$rrd->addDatasource($part . '_rps', 'DERIVE', null, 0);
			$rrd->addDatasource($part . '_wps', 'DERIVE', null, 0);
		}
	}
	
	public function updateRRD(rrd $rrd)
	{
		foreach ($this->stats_disk as $disk => $values)
		{
			$rrd->setValue($disk . '_rps', $values['sectors_read'] * $values['sector_size']);
			$rrd->setValue($disk . '_wps', $values['sectors_written'] * $values['sector_size']);
		}
		foreach ($this->stats_part as $part => $values)
		{
			$rrd->setValue($part . '_rps', $values['sectors_read'] * $this->stats_disk[$values['disk']]['sector_size']);
			$rrd->setValue($part . '_wps', $values['sectors_written'] * $this->stats_disk[$values['disk']]['sector_size']);
		}
	}
	
	private function getStats()
	{
		if (isset($this->stats_disk) && isset($this->filter_part))
		{
			return;
		}
		
		if (isset(self::$cache[$this->path_stat]))
		{
			$lines = self::$cache[$this->path_stat];
			$this->time = self::$cachetime[$this->path_stat];
		}
		else
		{
			if (!($lines = file($this->path_stat)))
			{
				throw new Exception('Could not read "' . $this->path_stat . '"');
			}
			$this->time = microtime(true);
			self::$cache[$this->path_stat] = $lines;
			self::$cachetime[$this->path_stat] = $this->time;
		}
		
		$lastdisk = null;
		$this->stats_disk = array();
		$this->stats_part = array();
		foreach ($lines as $line)
		{
			if (preg_match('/^\s*[0-9]+\s+[0-9]+\s+([a-z]+[0-9]*)\s+([0-9]+)\s+([0-9]+)\s+([0-9]+)\s+([0-9]+)\s+([0-9]+)\s+([0-9]+)\s+([0-9]+)\s+([0-9]+)\s+([0-9]+)\s+([0-9]+)\s+([0-9]+)\s*$/i', $line, $parts))
			{
				$lastdisk = $parts[1];
				$sector_size = 512; /*todo: fix this!!*/
				if (isset($this->filter_disk))
				{
					$use = false;
					foreach ($this->filter_disk as $test)
					{
						if (preg_match($test, $parts[1]))
						{
							$use = true;
							break;
						}
					}
					if (!$use)
					{
						continue;
					}
				}
				$this->stats_disk[$parts[1]] = array(
					'num_reads' => $parts[2],
					'merged_reads' => $parts[3],
					'sectors_read' => $parts[4],
					'ms_read' => $parts[5],
					'num_writes' => $parts[6],
					'merged_writes' => $parts[7],
					'sectors_written' => $parts[8],
					'ms_write' => $parts[9],
					'cur_io_procs' => $parts[10],
					'ms_io' => $parts[11],
					'idle' => $parts[12],
					'sector_size' => $sector_size
				);
			}
			elseif (preg_match('/^\s*[0-9]+\s+[0-9]+\s+([a-z]+[0-9]*)\s+([0-9]+)\s+([0-9]+)\s+([0-9]+)\s+([0-9]+)\s*$/i', $line, $parts))
			{
				if (isset($this->filter_part))
				{
					$use = false;
					foreach ($this->filter_part as $test)
					{
						if (preg_match($test, $parts[1]))
						{
							$use = true;
							break;
						}
					}
					if (!$use)
					{
						continue;
					}
					if (!isset($lastdisk))
					{
						throw new Exception('/*todo*/');
					}
					$this->stats_part[$parts[1]] = array(
						'num_reads' => $parts[2],
						'sectors_read' => $parts[3],
						'num_writes' => $parts[4],
						'sectors_written' => $parts[5],
						'disk' => $lastdisk
					);
				}
			}
		}
	}
}

?>
