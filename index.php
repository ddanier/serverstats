<?php
/**
 * $Id$
 *
 * Author: David Danier, david.danier@team23.de
 * Project: Serverstats, http://serverstats.berlios.de/
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

// Load all needed classes, function and everything else
require_once('init.php');

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de">  
<head>
	<title><?php echo lang::t('Statistics'); ?> - <?php echo lang::t('Summary'); ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<link rel="stylesheet" href="style.css" type="text/css" media="screen, projection" />
</head>
<body class="index">
<?php

// Show the menu
menu();

?>
<h1><?php echo lang::t('Statistics'); ?> - <?php echo lang::t('Summary'); ?></h1>
<?php

foreach ($config['graph']['list'] as $graphindex => $graph)
{
	?>
	<h2><?php echo htmlspecialchars($graph['title']); ?></h2>
	<a href="detail.php?graph=<?php echo $graphindex; ?>">
		<img src="graph.php?graph=<?php echo $graphindex; ?>" alt="<?php echo htmlspecialchars($graph['title']); ?>" />
	</a>
	<?php
}
?>
</body>
</html>
