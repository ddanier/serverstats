<?php
/**
 * $Id$
 *
 * Author: David Danier, david.danier@team23.de
 * Project: Serverstats, http://serverstats.berlios.de/
 * License: GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
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

class external extends source implements source_rrd
{
	private $command;
	private $datarows;
	private $data;
	
	private $dsdef = array();
	
	public function __construct($command)
	{
		$this->command = $command;
	}
	
	public function addDatasourceDefinition($name, $type = 'GAUGE', $heartbeat = null, $min = 'U', $max = 'U')
	{
		$this->dsdef[$name] = array(
			'type' => $type,
			'heartbeat' => $heartbeat,
			'min' => $min,
			'max' => $max
		);
	}
	
	private function getDatarow()
	{
		if (isset($this->datarows))
		{
			return;
		}
		$this->datarows = array();
		$return = 0;
		exec(escapeshellcmd($this->command), $this->datarows, $return);
		if ($return !== 0)
		{
			throw new Exception('Could not exec command (' . $this->command . ')');
		}
		// Some scripts have ugly output, so we have to delete all unneeded spaces
		$this->datarows = preg_replace('/([a-zA-Z0-9_]{1,19})[\s,]*:[\s,]*(.+)/', '\\1:\\2', $this->datarows);
	}
	
	private function getData()
	{
		if (isset($this->data))
		{
			return;
		}
		$this->getDatarow();
		$this->data = array();
		foreach ($this->datarows as $datarow)
		{
			$elements = preg_split('/[\s,]+/', $datarow);
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
			if (isset($this->dsdef[$key]))
			{
				$opt = $this->dsdef[$key];
				$rrd->addDatasource($key, $opt['type'], $opt['heartbeat'], $opt['min'], $opt['max']);
			}
			else
			{
				$rrd->addDatasource($key, 'GAUGE');
			}
		}
	}
	
	public function fetchValues()
	{
		return $this->data;
	}
}

?>
