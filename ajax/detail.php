<?php
/**
 * $Id: detail.php 141 2006-07-03 11:14:33Z goliath $
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

// Load all needed classes, function and everything else
require_once('../init.php');
if (!isset($_GET['graph']))
{
	die('$_GET["graph"] missing');
}

// Init Vars used in this script
$graphindex = $_GET['graph'];
$graph = $config['graph']['list'][$graphindex];

?>
<h1><?php echo htmlspecialchars(lang::t('Statistics')); ?> - <?php echo htmlspecialchars($graph['title']); ?></h1>
<?php

// List all configured graphs
foreach ($config['graph']['types'] as $graphtype)
{
	?>
	<h2><?php echo $graphtype['title']; ?></h2>
	<img src="../graph.php?graph=<?php echo $graphindex; ?>&start=<?php echo -$graphtype['period']; ?>&title=<?php echo htmlspecialchars($graphtype['title']); ?>" alt="<?php echo htmlspecialchars($graphtype['title']); ?>" />
	<?php
}
?>
