<?php
/**
 * $Id$
 *
 * Author: David Danier, david.danier@team23.de
 * Project: Serverstats, http://www.webmasterpro.de/~ddanier/serverstats/
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

// All sources should be loaded on demand
function __autoload($class)
{
	require_once(SOURCEPATH . $class . '.php');
}

// Function to  draw the menu
function menu()
{
	global $config;
	if (!isset($config) || !isset($config['graphlist']))
	{
		return;
	}
	?><div id="menu">
	<ul>
	<li class="index"><a href="index.php"><?php echo lang::t('Summary'); ?></a></li>
	<?php
	foreach ($config['graphlist'] as $graphindex => $graph)
	{
		?>
		<li class="detail">
			<a href="detail.php?graph=<?php echo $graphindex; ?>"><?php echo $graph['title']; ?></a>
		</li>
		<?php
	}
	?>
	</ul>
	</div>
	<?php
}

// Validate Graph
function validateGraph($graphindex)
{
	global $config;
	if (!isset($config) || !isset($config['graphlist']) || !isset($config['graphlist'][$graphindex]))
	{
		throw new Exception('Unknown graph');
	}
	$graph = $config['graphlist'][$graphindex];
	if (!isset($graph['title']))
	{
		throw new Exception('Title missing');
	}
	if (!is_array($graph['content']))
	{
		throw new Exception('Title missing');
	}
	foreach ($graph['content'] as $c)
	{
		$needed = array();
		if (!isset($c['type']))
		{
			throw new Exception('Graphcontent has no type');
		}
		switch ($c['type'])
		{
			case 'def':
				$needed = array('source', 'name', 'ds', 'cf');
				break;
			case 'cdef':
				$needed = array('name', 'expression');
				break;
			case 'line':
				$needed = array('color', 'legend', 'width');
				if (isset($c['name']))
				{
					array_push($needed, 'name');
				}
				else
				{
					array_push($needed, 'source', 'ds', 'cf');
				}
				break;
			case 'area':
				$needed = array('color', 'legend');
				if (isset($c['name']))
				{
					array_push($needed, 'name');
				}
				else
				{
					array_push($needed, 'source', 'ds', 'cf');
				}
				break;
			case 'stack':
				$needed = array('color', 'legend');
				if (isset($c['name']))
				{
					array_push($needed, 'name');
				}
				else
				{
					array_push($needed, 'source', 'ds', 'cf');
				}
				break;
			case 'gprint':
				$needed = array('format', 'cf');
				if (isset($c['name']))
				{
					array_push($needed, 'name');
				}
				else
				{
					array_push($needed, 'source', 'ds');
				}
				break;
			case 'hrule':
				$needed = array('value', 'legend', 'color');
				break;
			case 'vrule':
				$needed = array('time', 'legend', 'color');
				break;
			case 'comment':
				$needed = array('text');
				break;
			default:
				throw new Exception('Unknown Contenttype');
				break;
		}
		validateArray($c, $needed, 'Graphcontent of type "' . $c['type'] . '" misses index "%s"');
	}
}

// Validate Config
function validateConfig($withgraphlist = false)
{
	global $config;
	if (!isset($config))
	{
		throw new Exception('No configuration found');
	}
	$needed = array('language', 'rrdtool', 'sources', 'archives', 'graphtypes', 'defaultperiod', 'graph', 'graphlist');
	validateArray($config, $needed, '"%s" missing in configuration');
	if ($withgraphlist)
	{
		foreach ($config['graphlist'] as $graphindex => $graph)
		{
			validateGraph($graphindex);
		}
	}
	foreach ($config['sources'] as $sourcename => $source)
	{
		if (!($source instanceof source))
		{
			throw new Exception('Source "' . $sourcename . '" not instanceof source');
		}
	}
	$needed = array('steps', 'rows', 'cf', 'xff');
	foreach ($config['archives'] as $a)
	{
		validateArray($a, $needed, '"%s" missing in archive configuration');
	}
	$needed = array('title', 'period');
	foreach ($config['graphtypes'] as $g)
	{
		validateArray($g, $needed, '"%s" missing in graphtype configuration');
	}
	$needed = array('height', 'width', 'usecache');
	validateArray($config['graph'], $needed, '"%s" missing in graph configuration');
}

// Validate an array (test if some indizes are set)
function validateArray($array, $needed, $error = 'Index %s not found')
{
	foreach ($needed as $key)
	{
		if (!isset($array[$key]))
		{
			throw new Exception(sprintf($error, $key));
		}
	}
}

?>
