<?php

class config implements ArrayAccess
{
	public function offsetExists($offset)
	{
		return false;
	}
	
	public function offsetGet($offset)
	{
		return null;
	}
	
	public function offsetSet($offset, $value)
	{
		throw new Exception('Config may not be changed');
	}

	public function offsetUnset($offset)
	{
		throw new Exception('Config may not be changed');
	}
}

?>
