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
	private $disk;
	private $withpartitions;
	private $sector_size;
	
	private $stats_disk;
	private $stats_part;
	
	private static $cache = array();
	
	public function __construct($disk, $withpartitions = false, $sector_size = 512, $path_stat = '/proc/diskstats')
	{
		$this->path_stat = $path_stat;
		$this->disk = $disk;
		$this->withpartitions = $withpartitions;
		$this->sector_size = $sector_size;
	}
	
	public function refreshData()
	{
		$this->getStats();
	}
	
	public function initRRD(rrd $rrd)
	{
		$this->getStats();
		$rrd->addDatasource('read', 'GAUGE', null, 0);
		$rrd->addDatasource('write', 'GAUGE', null, 0);
		$rrd->addDatasource('readps', 'DERIVE', null, 0);
		$rrd->addDatasource('writeps', 'DERIVE', null, 0);
		foreach ($this->stats_part as $part => $values)
		{
			$rrd->addDatasource('part' . $part . '_read', 'GAUGE', null, 0);
			$rrd->addDatasource('part' . $part . '_write', 'GAUGE', null, 0);
			$rrd->addDatasource('part' . $part . '_readps', 'DERIVE', null, 0);
			$rrd->addDatasource('part' . $part . '_writeps', 'DERIVE', null, 0);
		}
	}
	
	public static function deriveValue($value)
	{
		if (is_double($value))
		{
			$value = (int)fmod($value, 2147483647);
		}
		return $value;
	}
	
	public function updateRRD(rrd $rrd)
	{
		$rrd->setValue('read', $this->stats_disk['sectors_read'] * $this->sector_size);
		$rrd->setValue('write', $this->stats_disk['sectors_written'] * $this->sector_size);
		$rrd->setValue('readps', self::deriveValue($this->stats_disk['sectors_read'] * $this->sector_size));
		$rrd->setValue('writeps', self::deriveValue($this->stats_disk['sectors_written'] * $this->sector_size));
		foreach ($this->stats_part as $part => $values)
		{
			$rrd->setValue('part' . $part . '_read', $values['sectors_read'] * $this->sector_size);
			$rrd->setValue('part' . $part . '_write', $values['sectors_written'] * $this->sector_size);
			$rrd->setValue('part' . $part . '_readps', self::deriveValue($values['sectors_read'] * $this->sector_size));
			$rrd->setValue('part' . $part . '_writeps', self::deriveValue($values['sectors_written'] * $this->sector_size));
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
		}
		else
		{
			if (!($lines = file($this->path_stat)))
			{
				throw new Exception('Could not read "' . $this->path_stat . '"');
			}
			self::$cache[$this->path_stat] = $lines;
		}
		
		$this->stats_disk = array();
		$this->stats_part = array();
		foreach ($lines as $line)
		{
			if (preg_match('/^\s*[0-9]+\s+[0-9]+\s+' . quotemeta($this->disk) . '\s+([0-9]+)\s+([0-9]+)\s+([0-9]+)\s+([0-9]+)\s+([0-9]+)\s+([0-9]+)\s+([0-9]+)\s+([0-9]+)\s+([0-9]+)\s+([0-9]+)\s+([0-9]+)\s*$/i', $line, $parts))
			{
				$this->stats_disk = array(
					/*
					'num_reads' => $parts[1],
					'merged_reads' => $parts[2],*/
					'sectors_read' => $parts[3],/*
					'ms_read' => $parts[4],
					'num_writes' => $parts[5],
					'merged_writes' => $parts[6],*/
					'sectors_written' => $parts[7]/*,
					'ms_write' => $parts[8],
					'cur_io_procs' => $parts[9],
					'ms_io' => $parts[10],
					'ms_io_weighted' => $parts[11]*/
				);
			}
			elseif ($this->withpartitions && preg_match('/^\s*[0-9]+\s+[0-9]+\s+' . quotemeta($this->disk) . '([0-9]*)\s+([0-9]+)\s+([0-9]+)\s+([0-9]+)\s+([0-9]+)\s*$/i', $line, $parts))
			{
				$this->stats_part[$parts[1]] = array(
					/*
					'num_reads' => $parts[2],*/
					'sectors_read' => $parts[3],/*
					'num_writes' => $parts[4],*/
					'sectors_written' => $parts[5]
				);
			}
		}
	}
}

?>
