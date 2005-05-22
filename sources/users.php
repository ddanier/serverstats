<?php

class users extends source
{
	const USERS_EXE = '/usr/bin/users';
	private $count;
	
	public function refreshData()
	{
		$this->count = count(explode(' ', trim(exec(self::USERS_EXE) . ' x'))) - 1;
	}

	public function initRRD(rrd $rrd)
	{
		$rrd->addDatasource('users');
	}

	public function updateRRD(rrd $rrd)
	{
		$rrd->setValue('users', $this->count);
	}
}

?>
