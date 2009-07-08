<?php defined('SYSPATH') OR die('No direct access allowed.');

class Tag_Model extends ORM {

	public function __construct($id = NULL)
	{
		// load database library into $this->db (can be omitted if not required)
		parent::__construct($id);
	}

	/*
	 * Overload __set() to format the tag
	 */
	public function __set($key, $value)
	{
		if ($key === 'tags')
		{
			// Set tag to url-safe format
			$value = url::title($value);
		}
		
		parent::__set($key, $value);
	}

} // End Tags Model
