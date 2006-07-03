<?php

/**
 * $Id: index.php 123 2006-04-11 12:35:34Z goliath $
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

foreach ($config['graph']['list'] as $graphindex => $graph)
{
        if (isFiltered($graph, $filter))
                continue;
        ?>
        <h2><?php echo htmlspecialchars($graph['title']); ?></h2>
        <a href="javascript:loadgraphs('detail.php?graph=<?php echo htmlspecialchars($graphindex); ?>&amp;tree=<?php echo htmlspecialchars(currentTreePath($tree)); ?>&amp;filter=<?php echo htmlspecialchars(currentFilter($filter)); ?>');">
        <img src="../graph.php?graph=<?php echo htmlspecialchars($graphindex); ?>" alt="<?php echo htmlspecialchars($graph['title']); ?>" />
        </a>
        <?php
}


?>
