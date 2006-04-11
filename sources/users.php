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

class users extends source implements source_rrd
{
	private $usersbin;
	private $count;
	
	public function __construct($usersbin = '/usr/bin/users')
	{
		$this->usersbin = $usersbin;
	}
	
	public function refreshData()
	{
		$return = 0;
		$datarows = array();
		exec(escapeshellcmd($this->usersbin), $datarows, $return);
		if ($return !== 0)
		{
			throw new Exception('Could not execute "' . $this->usersbin . '"');
		}
		$cmdoutput = implode(' ', $datarows);
		$this->count = count(explode(' ', trim($cmdoutput . ' x'))) - 1;
	}
	
	public function initRRD(rrd $rrd)
	{
		$rrd->addDatasource('users', 'GAUGE', null, 0);
	}
	
	public function fetchValues()
	{
		$values = array();
		$values['users'] = $this->count;
		return $values;
	}
}

?>
