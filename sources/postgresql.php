<?php
/**
 * $Id$
 *
 * Author: Piotr Michalczyk, piotr@michalczyk.pro
 * Project: Serverstats, http://serverstats.berlios.de/
 * License: GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
 *
 * Copyright (C) 2010 Piotr Michalczyk
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

class postgresql extends source implements source_rrd
{
	private $host;
	private $port;
	private $user;
	private $password;
	private $dbname;
	
	private $backendworking;
	private $backendwaiting;
	private $backendidle;
	private $backendidleintrans;
	
	private $xact_commit;
	private $xact_rollback;
	private $blks_read ;
	private $blks_hit;
	private $tup_returned;
	private $tup_fetched;
	private $tup_inserted;
	private $tup_updated;
	private $tup_deleted;
	
	public function __construct($host = 'localhost', $port = '5432', $user = 'postgres', $password = '', $dbname = 'template1')
	{
		$this->host = $host;
		$this->port = $port;
		$this->user = $user;
		$this->password = $password;
		$this->dbname = $dbname;
	}
	
	public function init()
	{	
		$connect_string = "host='".$this->host."' port='".$this->port."' user='".$this->user.
				"' password='".$this->password."' dbname='".$this->dbname."'";
		if (($this->db = @pg_connect($connect_string)) === false)
		{
			throw new Exception('Could not connect to database.');
		}
	}
	
	public function refreshData()
	{
		// Backends status
		$sql = "WITH backends AS (SELECT current_query, waiting from pg_stat_activity) ".
			"SELECT 'idle' AS type, COUNT(*) AS number FROM backends ".
			    "WHERE current_query='<IDLE>' ".
			"UNION SELECT 'idleintrans' AS type, COUNT(*) AS number FROM backends ".
			    "WHERE current_query='<IDLE> in transaction' ".
			"UNION SELECT 'working' AS type, COUNT(*) AS number FROM backends ".
			    "WHERE waiting='f' AND current_query NOT LIKE '<IDLE>%' ".
			"UNION SELECT 'waiting' AS type, COUNT(*) AS number FROM backends ".
			    "WHERE waiting='t' AND current_query NOT LIKE '<IDLE>%' ".
			"UNION SELECT 'all' AS type , COUNT(*) AS number FROM backends;";
		$result = pg_query($this->db, $sql);
		while ($data = pg_fetch_row($result)) {
		    switch ($data[0]) {
			case 'working':
			    $this->backendworking = $data[1];
			    break;
			case 'waiting':
			    $this->backendwaiting = $data[1];
			    break;
			case 'idle':
			    $this->backendidle = $data[1];
			    break;
			case 'idleintrans':
			    $this->backendidleintrans = $data[1];
			    break;
		    }
		}

		// Transaction/blocks/tuples count
		$sql = "SELECT SUM(xact_commit) AS xact_commit, SUM(xact_rollback) AS xact_rollback, SUM(blks_read) AS blks_read, ".
			"SUM(blks_hit) AS blks_hit, SUM(tup_returned) AS tup_returned, SUM(tup_fetched) AS tup_fetched, ".
			"SUM(tup_inserted) AS tup_inserted, SUM(tup_updated) AS tup_updated, SUM(tup_deleted) AS tup_deleted ".
			"FROM pg_stat_database;";
		$result = pg_query($this->db, $sql);
		$data = pg_fetch_assoc($result);
		$this->xact_commit = $data['xact_commit'];
		$this->xact_rollback = $data['xact_rollback'];
		$this->blks_read = $data['blks_read'];
		$this->blks_hit = $data['blks_hit'];
		$this->tup_returned = $data['tup_returned'];
		$this->tup_fetched = $data['tup_fetched'];
		$this->tup_inserted = $data['tup_inserted'];
		$this->tup_updated = $data['tup_updated'];
		$this->tup_deleted = $data['tup_deleted'];
	}
	
	public function initRRD(rrd $rrd)
	{
		$rrd->addDatasource('backendworking', 'GAUGE', null, 0);
		$rrd->addDatasource('backendwaiting', 'GAUGE', null, 0);
		$rrd->addDatasource('backendidle', 'GAUGE', null, 0);
		$rrd->addDatasource('backendidleintrans', 'GAUGE', null, 0);
		
		$rrd->addDatasource('xact_commitps', 'DERIVE', null, 0);
		$rrd->addDatasource('xact_rollbackps', 'DERIVE', null, 0);
		$rrd->addDatasource('blks_readps', 'DERIVE', null, 0);
		$rrd->addDatasource('blks_hitps', 'DERIVE', null, 0);
		$rrd->addDatasource('tup_returnedps', 'DERIVE', null, 0);
		$rrd->addDatasource('tup_fetchedps', 'DERIVE', null, 0);
		$rrd->addDatasource('tup_insertedps', 'DERIVE', null, 0);
		$rrd->addDatasource('tup_updatedps', 'DERIVE', null, 0);
		$rrd->addDatasource('tup_deletedps', 'DERIVE', null, 0);
	}
	
	public function fetchValues()
	{
		$values = array();
		$values['backendworking'] = $this->backendworking;
		$values['backendwaiting'] = $this->backendwaiting;
		$values['backendidle'] = $this->backendidle;
		$values['backendidleintrans'] = $this->backendidleintrans;
		
		$values['xact_commitps'] = $this->xact_commit;
		$values['xact_rollbackps'] = $this->xact_rollback;
		$values['blks_readps'] = $this->blks_read;
		$values['blks_hitps'] = $this->blks_hit;
		$values['tup_returnedps'] = $this->tup_returned;
		$values['tup_fetchedps'] = $this->tup_fetched;
		$values['tup_insertedps'] = $this->tup_inserted;
		$values['tup_updatedps'] = $this->tup_updated;
		$values['tup_deletedps'] = $this->tup_deleted;
		
		return $values;
	}
}

?>
