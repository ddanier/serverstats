<?php
/**
 * rrdgraph.class.php $Id$
 *
 * Author: David Danier, david.danier@team23.de
 * Project: Serverstats, http://www.webmasterpro.de/~ddanier/serverstats/
 * License: GPL v2 or later (http://www.gnu.org/copyleft/gpl.html)
 */

class rrdgraph
{
	private $rrdtoolbin;
	private $title;
	private $start;
	private $end;
	private $width = 500;
	private $height = 150;
	private $format = 'PNG';
	
	private $content = array();
	private $defs = array();
	
	public function __construct($rrdtoolbin, $start, $end = null)
	{
		$this->rrdtoolbin = $rrdtoolbin;
		$this->start = $start;
		$this->end = $end;
	}
	
	public function setTitle($title)
	{
		$this->title = $title;
	}
	
	public function setWidth($width)
	{
		$this->width = $width;
	}
	
	public function setHeight($height)
	{
		$this->height = $height;
	}
	
	public function addDEF($name, $ds, $rrdfile, $cf = 'AVERAGE')
	{
		if (in_array($name, $this->defs))
		{
			throw new Exception('Name already in use');
		}
		$this->content[] = array(
			'type' => 'def',
			'cf' => $cf,
			'name' => $name,
			'ds' => $ds,
			'rrdfile' => $rrdfile
		);
		$this->defs[] = $name;
	}
	
	public function addCDEF($name, $expression)
	{
		if (in_array($name, $this->defs))
		{
			throw new Exception('Name already in use');
		}
		$this->content[] = array(
			'type' => 'cdef',
			'name' => $name,
			'expression' => $expression
		);
		$this->defs[] = $name;
	}
	
	public function addLINE($name, $legend, $color = '000000', $width = 2)
	{
		if (!in_array($name, $this->defs))
		{
			throw new Exception('Unknown name');
		}
		$this->content[] = array(
			'type' => 'line',
			'name' => $name,
			'legend' => $legend,
			'color' => $color,
			'width' => $width
		);
	}

	public function addAREA($name, $legend, $color = '000000')
	{
		if (!in_array($name, $this->defs))
		{
			throw new Exception('Unknown name');
		}
		$this->content[] = array(
			'type' => 'area',
			'name' => $name,
			'legend' => $legend,
			'color' => $color
		);
	}
	
	public function addSTACK($name, $legend, $color = '000000')
	{
		if (!in_array($name, $this->defs))
		{
			throw new Exception('Unknown name');
		}
		$this->content[] = array(
			'type' => 'stack',
			'name' => $name,
			'legend' => $legend,
			'color' => $color
		);
	}
	
	public function addGPRINT($name, $format, $cf = 'AVERAGE')
	{
		if (!in_array($name, $this->defs))
		{
			throw new Exception('Unknown name');
		}
		$this->content[] = array(
			'type' => 'gprint',
			'name' => $name,
			'format' => $format,
			'cf' => $cf
		);
	}
	
	public function addHRULE($value, $legend, $color = '000000')
	{
		$this->content[] = array(
			'type' => 'hrule',
			'value' => $value,
			'legend' => $legend,
			'color' => $color
		);
	}
	
	public function addVRULE($time, $legend, $color = '000000')
	{
		$this->content[] = array(
			'type' => 'vrule',
			'time' => $time,
			'legend' => $legend,
			'color' => $color
		);
	}
	
	public function addCOMMENT($text)
	{
		$this->content[] = array(
			'type' => 'comment',
			'text' => $text
		);
	}
	
	private function command($file = '-')
	{
		$params = ' graph ' . escapeshellarg($file);
		if (!empty($this->title))
		{
			$params .= ' -t ' . escapeshellarg($this->title);
		}
		$params .= ' -s ' . escapeshellarg($this->start);
		if (isset($this->end))
		{
			$params .= ' -e ' . escapeshellarg($this->end);
		}
		$params .= ' -a ' . escapeshellarg($this->format);
		$params .= ' -w ' . escapeshellarg($this->width);
		$params .= ' -h ' . escapeshellarg($this->height);
		$params .= ' -M -z -l 0 ';
		foreach ($this->content as $c)
		{
			$optline = '';
			switch ($c['type'])
			{
				case 'def':
					$optline = 'DEF:' . $c['name'] . '=' . $c['rrdfile'] . ':' . $c['ds'] . ':' . $c['cf'];
					break;
				case 'cdef':
					$optline = 'CDEF:' . $c['name'] . '=' . $c['expression'];
					break;
				case 'line':
					$optline = 'LINE' . $c['width'] . ':' . $c['name'] . '#' . $c['color'] . ':' . $c['legend'];
					break;
				case 'area':
					$optline = 'AREA:' . $c['name'] . '#' . $c['color'] . ':' . $c['legend'];
					break;
				case 'stack':
					$optline = 'STACK:' . $c['name'] . '#' . $c['color'] . ':' . $c['legend'];
					break;
				case 'gprint':
					$optline = 'GPRINT:' . $c['name'] . ':' . $c['cf'] . ':' . $c['format'];
					break;
				case 'hrule':
					$optline = 'HRULE:' . $c['value'] . '#' . $c['color'] . ':' . $c['legend'];
					break;
				case 'vrule':
					$optline = 'VRULE:' . $c['time'] . '#' . $c['color'] . ':' . $c['legend'];
					break;
				case 'comment':
					$optline = 'COMMENT:' . $c['text'];
					break;
			}
			$params .= ' ' . escapeshellarg($optline);
		}
		return (escapeshellcmd($this->rrdtoolbin) . $params);
	}
	
	public function save($file)
	{
		system($this->command($file) . ' > /dev/null 2>&1');
	}
	
	public function output()
	{
		passthru($this->command());
	}
}

?>
