<?php

$config = array(
	'cactisourcesample' => new external(SOURCEPATH . 'external/cacti/XYZ.pl'),
	'trafficCHAIN' => new traffic('CHAIN'),
	'mysql' => new mysql('user', 'password', 'localhost'),
	'load' => new load(),
	'users' => new users()
);

?>
