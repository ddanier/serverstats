<?php
/**
 * mysql.php $Id$
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
		$this->cache['last'] = $this->cache['current'];
		$this->cache['current'] = array();
		
		// Questions
		$sql = "SHOW STATUS LIKE 'QUESTIONS';";
		$result = mysql_query($sql, $this->db);
		$data = mysql_fetch_row($result);
		$questions = $data[1];
		// Processcount
		$sql = "SHOW PROCESSLIST";
		$result = mysql_query($sql, $this->db);
		$processcount = mysql_num_rows($result) - 1;
		
		$this->cache['current']['questions'] = $questions;
		$this->cache['current']['processcount'] = $processcount;
		$this->cache['current']['date'] = time();
	}

	public function initRRD(rrd $rrd)
	{
		$rrd->addDatasource('questions');
		$rrd->addDatasource('questionsps');
		$rrd->addDatasource('processcount');
	}

	public function updateRRD(rrd $rrd)
	{
		$questions = $this->cache['current']['questions'];
		$questionsD = $this->cache['current']['questions'] - $this->cache['last']['questions'];
		$dateD = $this->cache['current']['date'] - $this->cache['last']['date'];
		$questionsps = $questionsD / $dateD;
		if ($questionsps < 0 || $this->cache['last']['date'] == -1)
		{
			$questionsps = 0;
		}
		$processcount = $this->cache['current']['processcount'];
		$rrd->setValue('questions', $questions);
		$rrd->setValue('questionsps', $questionsps);
		$rrd->setValue('processcount', $processcount);
	}
	
	public function useCache()
	{
		return true;
	}
	
	public function initCache()
	{
		$this->cache = array(
			'last' => array('questions' => 0, 'processcount' => 0, 'date' => -1),
			'current' => array('questions' => 0, 'processcount' => 0, 'date' => -1)
		);
	}
	
	public function loadCache($cachedata)
	{
		$this->cache = $cachedata;
	}
	
	public function getCache()
	{
		return $this->cache;
	}
}

?>
