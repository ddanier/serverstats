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

class mysql extends source implements source_rrd
{
	private $db;
	private $host;
	private $user;
	private $password;
	
	private $questions;
	private $processcount;
	
	public function __construct($user = 'status', $password = '', $host = 'localhost')
	{
		$this->user = $user;
		$this->password = $password;
		$this->host = $host;
	}
	
	public function init()
	{
		if (($this->db = @mysql_connect($this->host, $this->user, $this->password)) === false)
		{
			throw new Exception('Could not connect to database');
		}
	}
	
	public function refreshData()
	{
		// Questions
		$sql = "SHOW STATUS LIKE 'QUESTIONS';";
		$result = mysql_query($sql, $this->db);
		$data = mysql_fetch_row($result);
		$questions = $data[1];
		// Processcount
		$sql = "SHOW PROCESSLIST";
		$result = mysql_query($sql, $this->db);
		$processcount = mysql_num_rows($result) - 1;
		
		$this->questions = $questions;
		$this->processcount = $processcount;
	}
	
	public function initRRD(rrd $rrd)
	{
		$rrd->addDatasource('questions', 'GAUGE', null, 0);
		$rrd->addDatasource('questionsps', 'DERIVE', null, 0);
		$rrd->addDatasource('processcount', 'GAUGE', null, 0);
	}
	
	public function fetchValues()
	{
		$values = array();
		$values['questions'] = $this->questions;
		$values['questionsps'] = $this->questions;
		$values['processcount'] = $this->processcount;
		return $values;
	}
}

?>
