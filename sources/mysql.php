<?php
/**
 * $Id$
 *
 * Author: David Danier, david.danier@team23.de
 * Project: Serverstats, http://www.webmasterpro.de/~ddanier/serverstats/
 * License: GPL v2 or later (http://www.gnu.org/copyleft/gpl.html)
 */

class mysql extends source
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
		if (!($this->db = mysql_connect($this->host, $this->user, $this->password)))
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

	public function updateRRD(rrd $rrd)
	{
		$rrd->setValue('questions', $this->questions);
		$rrd->setValue('questionsps', $this->questions);
		$rrd->setValue('processcount', $this->processcount);
	}
}

?>
