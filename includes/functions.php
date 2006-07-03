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

// All sources should be loaded on demand
function __autoload($class)
{
	if (file_exists(SOURCEPATH . $class . '.php'))
	{
		require_once(SOURCEPATH . $class . '.php');
	}
	elseif (file_exists(INCLUDEPATH . $class . '.class.php'))
	{
		require_once(INCLUDEPATH . $class . '.class.php');
	}
}

function menuTree($tree, $path = '')
{
	?>
	<ul>
	<?php
	foreach ($tree as $index => $item)
	{
		$itempath = $path . ($path === "" ? $index : (',' . $index));
		?>
		<li class="treeitem">
			<a href="index.php?tree=<?php echo htmlspecialchars($itempath); ?>"><?php echo htmlspecialchars($item['title']); ?></a>
			<?php if (isset($item['sub'])) menuTree($item['sub'], $itempath); ?>
		</li>
		<?php
	}
	?>
	</ul>
	<?php
}

// Function to  draw the menu
function menu()
{
	global $config, $filter, $tree;
	if (!isset($config) || !isset($config['graph']['list']) || !isset($config['graph']['tree']))
	{
		return;
	}
	?><div id="menu">
	<h2><?php echo htmlspecialchars(lang::t('Tree')); ?></h2>
	<ul>
	<li class="index">
		<a href="index.php"><?php echo htmlspecialchars($config['graph']['tree']['title']); ?></a>
		<?php if (isset($config['graph']['tree']['sub'])) menuTree($config['graph']['tree']['sub']); ?>
	</li>
	</ul>
	<h2><?php echo htmlspecialchars(lang::t('Graphs')); ?></h2>
	<ul>
	<li class="index"><a href="index.php?tree=<?php echo htmlspecialchars(currentTreePath($tree)); ?>&amp;filter=<?php echo htmlspecialchars(currentFilter($filter)); ?>"><?php echo htmlspecialchars(lang::t('Summary')); ?></a></li>
	<?php
	foreach ($config['graph']['list'] as $graphindex => $graph)
	{
		if (isFiltered($graph, $filter))
			continue;
		?>
		<li class="detail">
			<a href="detail.php?graph=<?php echo htmlspecialchars($graphindex); ?>&amp;tree=<?php echo htmlspecialchars(currentTreePath($tree)); ?>&amp;filter=<?php echo htmlspecialchars(currentFilter($filter)); ?>"><?php echo htmlspecialchars($graph['title']); ?></a>
		</li>
		<?php
	}
	?>
	</ul>
	</div>
	<?php
}

// Function to test if some values are set in an array
function array_check($array, $values)
{
	if (!is_array($values))
	{
		$values = array($values);
	}
	foreach ($values as $value)
	{
		if (!isset($array[$value]))
		{
			return false;
		}
	}
	return true;
}

// Function to get a value out of an array and return a defaultvalue if value is not set in array
function array_get($array, $value, $default = null)
{
	if (isset($array[$value]))
	{
		return $array[$value];
	}
	else
	{
		return $default;
	}
}

function value_compare($value1, $value2, $operator)
{
	switch ($operator)
	{
		case '>=':
			return ($value1 >= $value2);
		case '>':
			return ($value1 > $value2);
		case '<=':
			return ($value1 <= $value2);
		case '<':
			return ($value1 < $value2);
		case '=':
		case '==':
			return ($value1 == $value2);
		case '!=':
			return ($value1 != $value2);
		default:
			throw new Exception('Invalid operator');
	}
}

function extractFilter($filterString = '')
{
	$filter = array();
	$filterString = trim($filterString);
	if (strtolower($filterString) == 'none')
	{
		return array('none');
	}
	$filterParts = preg_split('/\s*,\s*/', $filterString);
	foreach ($filterParts as $filterPart)
	{
		if (preg_match('/^([a-zA-Z0-9]+):(.+)$/', $filterPart, $matches))
		{
			$filter[$matches[1]] = $matches[2];
		}
	}
	return $filter;
}

function extractFilterFromTree($tree)
{
	$last = array_pop($tree);
	return extractFilter($last['filter']);
}

function extractTree($treeString = '')
{
	global $config;
	$tree = array();
	$treeString = trim($treeString);
	if ($treeString === "")
		$treeParts = array();
	else
		$treeParts = preg_split('/\s*,\s*/', $treeString);
	$path = '';
	$tree[] = array(
		'title' => $config['graph']['tree']['title'],
		'filter' => $config['graph']['tree']['filter'],
		'path' => $path
	);
	$subTree = $config['graph']['tree']['sub'];
	foreach ($treeParts as $part)
	{
		if (isset($subTree[$part]))
		{
			$path .= $path === "" ? $part : (',' . $part);
			$tree[] = array(
				'title' => $subTree[$part]['title'],
				'filter' => $subTree[$part]['filter'],
				'path' => $path
			);
			if (isset($subTree[$part]['sub']))
				$subTree = $subTree[$part]['sub'];
			else
				break;
		}
		else
			break;
	}
	return $tree;
}

function isFiltered($graph, $filter)
{
	foreach ($filter as $category => $condition)
	{
		if ($condition == 'none')
		{
			return true;
		}
		if ($condition{0} == "!")
		{
			$condition = substr($condition, 1);
			$negate = true;
		}
		else
		{
			$negate = false;
		}
		if (!isset($graph['categories']) || !isset($graph['categories'][$category]))
			return !$negate;
		if (is_array($graph['categories'][$category]) ||
			$graph['categories'][$category] instanceof Traversable)
		{
			$filtered = true;
			foreach ($graph['categories'][$category] as $value)
			{
				if ((!$negate && $condition == $value) ||
					($negate && $condition != $value))
				{
					$filtered = false;
					break;
				}
			}
			if ($filtered)
				return true;
		}
		else
		{
			if ($negate)
			{
				if ($condition == $graph['categories'][$category])
					return true;
			}
			else
			{
				if ($condition != $graph['categories'][$category])
					return true;
			}
		}
	}
}

function printBreadcrumps($tree)
{
	$needSep = false;
	foreach ($tree as $crump)
	{
		?>
		<?php if ($needSep) echo ' &gt; '; ?><a href="index.php?tree=<?php echo htmlspecialchars($crump['path']); ?>"><?php echo htmlspecialchars($crump['title']); ?></a>
		<?php
		$needSep = true;
	}
}

function currentTreePath($tree)
{
	$last = array_pop($tree);
	return $last['path'];
}

function currentFilter($filter)
{
	$filterParts = array();
	foreach ($filter as $category => $condition)
	{
		$filterParts[] = $category . ':' . $condition;
	}
	return implode(',', $filterParts);
}

?>
