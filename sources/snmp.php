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

class snmp extends source implements source_rrd
{
	private $host;
	private $objects = array();
	private $community;
	
	private $data = array();
	
	private $dsdef = array();
	
	public function __construct($host, $objects, $community = 'public')
	{
		$this->host = $host;
		if (!is_array($objects))
		{
			$objects = array($objects);
		}
		$this->objects = $objects;
		$this->community = $community;
	}
	
	public function addDatasourceDefinition($object, $type = 'GAUGE', $heartbeat = null, $min = 'U', $max = 'U')
	{
		$this->dsdef[$object] = array(
			'type' => $type,
			'heartbeat' => $heartbeat,
			'min' => $min,
			'max' => $max
		);
	}
	
	public function refreshData()
	{
		snmp_set_valueretrieval(SNMP_VALUE_OBJECT);
		foreach ($this->objects as $objectName => $objectId)
		{
			$object = snmpget($this->host, $this->community, $objectId);
			if ($object !== false)
			{
				$this->data[$objectName] = $object->value;
			}
		}
	}
	
	public function initRRD(rrd $rrd)
	{
		foreach ($this->objects as $objectName => $objectId)
		{
			if (isset($this->dsdef[$objectName]))
			{
				$opt = $this->dsdef[$objectName];
				$rrd->addDatasource(
					rrd::escapeDsName($objectName),
					$opt['type'],
					$opt['heartbeat'],
					$opt['min'],
					$opt['max']
				);
			}
			else
			{
				$rrd->addDatasource(rrd::escapeDsName($objectName), 'GAUGE');
			}
		}
	}
	
	public function fetchValues()
	{
		$values = array();
		foreach ($this->data as $key => $value)
		{
			$values[rrd::escapeDsName($key)] = $value;
		}
		return $values;
	}
}

?>
