<?php defined('SYSPATH') OR die('No direct access allowed.');

class Tagging_Model extends ORM {

	protected $belongs_to = array('tags', 'users');

	protected $now;

	public function __construct($id)
	{
		// load database library into $this->db (can be omitted if not required)
		parent::__construct($id);
	
		// Set the DATETIME for use with created and modified fields
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
	
	// Find similar tags
	public function similar($tags_table, $tagged_table, $tag, $user_id, $limit)
	{
		if (isset($user_id) and intval($user_id) > 0) 
		{
			$user_id = intval($user_id);
			$where_sql .= " AND o1.tagger_id = $user_id AND o2.tagger_id = $user_id ";
		}
	
		$result = $this->db->query("SELECT t1.tag, COUNT( o1.object_id ) AS quantity FROM ? o1 INNER JOIN ? t1 ON ( t1.id = o1.tag_id ) INNER JOIN ? o2 ON ( o1.object_id = o2.object_id ) INNER JOIN ? t2 ON ( t2.id = o2.tag_id ) WHERE t2.tag = ? AND t1.tag != ? ? GROUP BY o1.tag_id ORDER BY quantity DESC LIMIT 0, ?", $tagged_table, $tags_table, $tagged_table, $tags_table, $tag, $tag, $where_sql, $limit);
	

		return $result->result();
	}

} // End Tagged Model
