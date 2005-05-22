<?php

class rrdgraph
{
	private $rrdtoolbin;
	private $title;
	private $period;
	private $width = 500;
	private $height = 150;
	private $format = 'PNG';
	
	private $content = array();
	private $defs = array();
	
	public function __construct($rrdtoolbin, $period, $title = null)
	{
		$this->rrdtoolbin = $rrdtoolbin;
		$this->period = $period;
		$this->title = $title;
	}
	
	public function addDEF($name, $ds, $rrdfile, $cf = 'AVERAGE')
	{
		if (in_array($name, $this->defs))
		{
			return;
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
	
	public function addLINE($name, $legend, $color = '000000', $width = 2)
	{
		if (!in_array($name, $this->defs))
		{
			return;
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
			return;
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
			return;
		}
		$this->content[] = array(
			'type' => 'stack',
			'name' => $name,
			'legend' => $legend,
			'color' => $color
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
		$params .= ' -s ' . escapeshellarg('-' . $this->period);
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
				case 'line':
					$optline = 'LINE' . $c['width'] . ':' . $c['name'] . '#' . $c['color'] . ':' . $c['legend'];
					break;
				case 'area':
					$optline = 'AREA:' . $c['name'] . '#' . $c['color'] . ':' . $c['legend'];
					break;
				case 'stack':
					$optline = 'STACK:' . $c['name'] . '#' . $c['color'] . ':' . $c['legend'];
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
