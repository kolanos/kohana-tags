<?php defined('SYSPATH') OR die('No direct access allowed.');

class Tagging_Model extends ORM {

	protected $belongs_to = array('tags', 'users');

	protected $now;

	public function __construct($id)
	{
		// load database library into $this->db (can be omitted if not required)
		parent::__construct($id);
	
		// Set the UNIX timestamp for use with the created field
		$this->now = time();
	}

	/**
	 * Overload saving to set the created time when the object is saved.
	 */
	public function save()
	{
		if ($this->loaded === FALSE)
		{
			// Set the created time
			$this->created = $this->now;
		}

		return parent::save();
	}

} // End Tagging Model
