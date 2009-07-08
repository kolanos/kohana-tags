<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Tags v0.1
 *
 * An adaptation of Freetag
 */

class Tags_Core 
{
	// Configuration
	protected $config;

	/**
	 * Create an instance of Tags.
	 *
	 * @return  object
	 */
	public static function factory($config = array())
	{
		return new Tags($config);
	}

	/**
	 * Return a static instance of Tags.
	 *
	 * @return  object
	 */
	public static function instance($config = array())
	{
		static $instance;

		// Load the Tags instance
		empty($instance) and $instance = new Tags($config);

		return $instance;
	}

	/**
	 * Loads configuration options.
	 *
	 * @return  void
	 */
	public function __construct($config = array())
	{
		// Append default tags configuration
		$config += Kohana::config('tags');

		// Save the config in the object
		$this->config = $config;

		Kohana::log('debug', 'Tags Library loaded');
	}

	/**
	* Objects with Tag
	*
	* Use this function to build a page of results that have been tagged with the specified tag.
	* Pass along a user_id to collect only a certain user's tagged objects, and pass along
	* none in order to get back all user-tagged objects.
	*
	* @param	string	tag		The normalized tag.
	* @param	int		offset	The numerical offset to begin displaying results. Defaults to 0. Optional.
	* @param	int		limit	The number of results per page to show. Defaults to 100. Optional.
	* @param	int		user_id	The unique user_id who tagged the object. Optional.
	* @return	object	An object of object_id numbers that reference your original objects.
	*/
	public function objects_with_tag($tag = '', $offset = 0, $limit = 100, $user_id = 0) 
	{
		if (empty($tag)) 
		{
			return FALSE;
		}
		
		$result = ORM::factory(inflector::singular($this->config['tagging_table']))
			->select('DISTINCT '.$this->config['object_foreign_key'])
			->join($this->config['tag_table'], $this->config['tagging_table'].'.tag_id', $this->config['tag_table'].'.id', 'INNER')
			->where('tag', $tag)
			->orderby($this->config['object_foreign_key'], 'ASC')
			->offset($offset)
			->limit($limit);

		if ($user_id = intval($user_id)) 
		{
			$result->where('user_id', $user_id);
		}
		
		return $result->find_all();
	}

	/*
	 * All Objects with Tag
	 *
	 * Use this function to build a page of results that have been tagged with the specified tag.
	 * This function acts the same as objects_with_tag, except that it returns an unlimited
	 * number of results. Therefore, it's more useful for internal displays, not for API's.
	 * Pass along a user_id to collect only a certain user's tagged objects, and pass along
	 * none in order to get back all user-tagged objects.
	 *
	 * @param	string	tag		The normalized tag.
	 * @param	int		user_id	The unique user_id who tagged the object.
	 * @return	object	An object of object_id numbers that reference your original objects.
	 */
	public function objects_with_tag_all($tag = '', $user_id = 0) 
	{
		if (empty($tag)) 
		{
			return FALSE;
		}
		
		$result = ORM::factory(inflector::singular($this->config['tagging_table']))
			->select('DISTINCT '.$this->config['object_foreign_key'])
			->join($this->config['tag_table'], $this->config['tagging_table'].'.tag_id', $this->config['tag_table'].'.id', 'INNER')
			->where('tag', $tag)
			->orderby($this->config['object_foreign_key'], 'ASC');
		
		if ($user_id = intval($user_id)) 
		{
			$result->where('user_id', $user_id);
		}
		
		return $result->find_all();
	}

	/*
	 * Objects with Tag Combination
	 *
	 * Returns an object of object_id's that have all the tags passed in the
	 * tags parameter. Use this to provide tag combo services to your users.
	 *
	 * @param	array	tags	An array of normalized tags.
	 * @param	int 	offset	The numerical offset to begin displaying results. Defaults to 0. Optional.
	 * @param	int		limit	The number of results per page to show. Defaults to 100.
	 * @param	int		user_id	Restrict the result to objects tagged by a particular user.
	 * @return	object	An array of Object ID numbers that reference your original objects.
	 */
	public function objects_with_tag_combo($tags = '', $offset = 0, $limit = 100, $user_id = 0)
	{
		if (empty($tags) and ! is_array($tags)) 
		{
			return FALSE;
		}
		
		if ( ! count($tags)) 
		{
			return FALSE;
		}

		foreach ($tags as $key => $value) 
		{
			$tags[$key] = $value;
		}

		$tags = array_unique($tags);

		$result = ORM::factory(inflector::singular($this->config['tagging_table']))
			->select($this->config['tagging_table'].'.'.$this->config['object_foreign_key'].', '.$this->config['tag_table'].'.tag, COUNT(DISTINCT '.$this->config['tag_table'].'.tag) as uniques')
			->join($this->config['tag_table'], $this->config['tagging_table'].'.tag_id', $this->config['tag_table'].'.id', 'INNER')
			->in($this->config['tag_table'].'.tag', $tags)
			->groupby($this->config['tagging_table'].'.'.$this->config['object_foreign_key'])
			->having('uniques', count($tags))
			->offset($offset)
			->limit($limit);

		if ($user_id = intval($user_id)) 
		{
			$result->where('user_id', $user_id);
		}

		return $result->find_all();
	}

	/**
	* Objects with Tag ID
	*
	* Use this function to build a page of results that have been tagged with the specified tag.
	* This function acts the same as objects_with_tag, except that it accepts a numerical
	* tag_id instead of a text tag.
	* Pass along a user_id to collect only a certain user's tagged objects, and pass along
	* none in order to get back all user-tagged objects.
	*
	* @param	int	tag_id	The ID number of the tag.
	* @param	int	offset- The numerical offset to begin displaying results. Defaults to 0. Optional.
	* @param	int limit	The number of results per page to show. Defaults to 100. Optional.
	* @param	int user_id The unique user_id who tagged the object.
	* @return	object	An array of Object ID numbers that reference your original objects.
	*/
	public function objects_with_tag_id($tag_id = 0, $offset = 0, $limit = 100, $user_id = 0) 
	{
		if ( ! $tag_id = intval($tag_id)) 
		{
			return FALSE;
		}

		$result = ORM::factory(inflector::singular($this->config['tagging_table']))
			->select('DISTINCT '.$this->config['object_foreign_key'])
			->join($this->config['tag_table'], $this->config['tagging_table'].'.tag_id', $this->config['tag_table'].'.id', 'INNER')
			->where($this->config['tag_table'].'.id', $tag_id)
			->offset($offset)
			->limit($limit);

		if ($user_id = intval($user_id)) 
		{
			$result->where('user_id', $user_id);
		}

		return $result->find_all();
	}


	/**
	* Tags on Object
	*
	* You can use this function to show the tags on an object. Since it supports both user-specific
	* and general modes with the $user_id parameter, you can use it twice on a page to make it work
	* similar to upcoming.org and flickr, where the page displays your own tags differently than
	* other users' tags.
	*
	* @param	int	object_id	The unique ID of the object in question.
	* @param	int	offset		The offset of tags to return.
	* @param	int limit		The size of the tagset to return. Use a zero size to get all tags.
	* @param	int user_id		The unique ID of the person who tagged the object, if user-level tags only are preferred.
	*
	* @return array Returns a PHP array with object elements ordered by object ID. Each element is an associative
	* array with the following elements:
	*   - 'tag' => Normalized-form tag
	*	 - 'raw_tag' => The raw-form tag
	*	 - 'tagger_id' => The unique ID of the person who tagged the object with this tag.
	*/
	public function tags_on_object($object_id = 0, $offset = 0, $limit = 10, $user_id = 0) 
	{
		if ( ! $object_id = intval($object_id)) 
		{
			return FALSE;
		}

		$result = ORM::factory(inflector::singular($this->config['tagging_table']))
			->select('DISTINCT tag, raw_tag, user_id')
			->join($this->config['tag_table'], $this->config['tagging_table'].'.tag_id', $this->config['tag_table'].'.id', 'INNER')
			->where($this->config['object_foreign_key'], $object_id)
			->orderby($this->config['tag_table'].'.id', 'ASC');

		if ($user_id = intval($user_id)) 
		{
			$result->where('user_id', $user_id);
		}

		if ($limit = intval($limit)) 
		{
			$result->offset($offset)->limit($limit);
		}

		return $result->find_all();
	}

	/**
	* Safe Tag
	*
	* Pass individual tag phrases along with object and object ID's in order to
	* set a tag on an object. If the tag in its raw form does not yet exist,
	* this function will create it.
	* Fails transparently on duplicates, and checks for dupes based on the
	* block_multiuser_tag_on_object constructor param.
	*
	* @param	int	user_id		The	user_id	unique ID of the person who tagged the object with this tag.
	* @param	int object_id	The unique ID of the object in question.
	* @param	string	tag		A raw string from a web form containing tags.
	* @return	boolean	Returns true if successful, false otherwise. Does not operate as a transaction.
	*/
	public function safe_tag($user_id = 0, $object_id = 0, $tag = '') 
	{
		if ( ! $user_id = intval($user_id) or ! $object_id = intval($object_id) or empty($tag)) 
		{
			return FALSE;
		}

		if ( ! empty($this->config['append_to_integer']) and is_numeric($tag) and intval($tag) == $tag) 
		{
			// Converts numeric tag "123" to "123_" to facilitate
			// alphanumeric sorting (otherwise, PHP converts string to
			// true integer).
			$tag = preg_replace('/^([0-9]+)$/', "$1".$this->config['append_to_integer'], $tag);
		}

		$normalized_tag = $this->normalize_tag($tag);
		
		$result = ORM::factory(inflector::singular($this->config['tagging_table']))
			->join($this->config['tag_table'], $this->config['tagging_table'].'.tag_id', $this->config['tag_table'].'.id', 'INNER')
			->where(array($this->config['object_foreign_key'] => $object_id, 'tag' => $normalized_tag));

		// First, check for duplicate of the normalized form of the tag on this object.
		// Dynamically switch between allowing duplication between users on the constructor param 'block_multiuser_tag_on_object'.
		// If it's set not to block multiuser tags, then modify the existence
		// check to look for a tag by this particular user. Otherwise, the following
		// query will reveal whether that tag exists on that object for ANY user.
		if ( ! $this->config['block_multiuser_tag_on_object']) 
		{
			$result->where('user_id', $user_id);
		}

		if ($result->count_all() > 0) 
		{
			return TRUE;
		}
		
		// Then see if a raw tag in this form exists.
		$result = ORM::factory(inflector::singular($this->config['tag_table']))
			->select('id')
			->where('raw_tag', $tag);

		//"SELECT id FROM ".$this->_table_prefix.$this->config['tag_table']." WHERE raw_tag = $tag"
		
		if ($result->count_all() > 0) 
		{
			$result = $result->find();
			$tag_id = $result->id;
		}
		else 
		{
			// Add new tag!
			$new_tag = ORM::factory(inflector::singular($this->config['tag_table']));
			$new_tag->tag = $normalized_tag;
			$new_tag->raw_tag = $tag;
			$new_tag->save();
			
			//"INSERT INTO ".$this->_table_prefix.$this->config['tag_table']." (tag, raw_tag) VALUES ($normalized_tag, $tag)";
			
			$tag_id = $new_tag->id;
		}
		
		if ( ! ($tag_id > 0)) 
		{
			return FALSE;
		}
		
		$new_tagging = ORM::factory(inflector::singular($this->config['tagging_table']));
		$new_tagging->tag_id = $tag_id;
		$new_tagging->user_id = $user_id;
		$new_tagging->{$this->config['object_foreign_key']} = $object_id;
		$new_tagging->save();
		
		return TRUE;
	}

	/**
	* Normalize Tag
	*
	* This is a utility function used to take a raw tag and convert it to normalized form.
	* Normalized form is essentially lowercased alphanumeric characters only,
	* with no spaces or special characters.
	*
	* Customize the normalized valid chars with your own set of special characters
	* in regex format within the option 'custom_normalization'. It acts as a filter
	* to let a customized set of characters through.
	*
	* After the filter is applied, the function also lowercases the characters using strtolower
	* in the current locale.
	*
	* The default for normalized_valid_chars is a-zA-Z0-9, or english alphanumeric.
	*
	* @param	string	tag	An individual tag in raw form that should be normalized.
	* @return	string	Returns the tag in normalized form.
	*/
	public function normalize_tag($tag) 
	{
		if ($this->config['normalize_tags']) 
		{
			if ($this->config['use_kohana_normalization'])
			{
				$tag = url::title($tag);
			}
			else
			{
				$normalized_valid_chars = $this->config['custom_normalization'];
				$tag = preg_replace("/[^$normalized_valid_chars]/", "", $tag);
			}
			
			return strtolower($tag);
		}
		else 
		{
			return $tag;
		}
	}

	/**
	* Delete Object Tag
	*
	* Removes a tag from an object. This does not delete the tag itself from
	* the database. Since most applications will only allow a user to delete
	* their own tags, it supports raw-form tags as its tag parameter, because
	* that's what is usually shown to a user for their own tags.
	*
	* @param	integer	user_id		The unique user_id of the person who tagged the object with this tag.
	* @param	integer object_id	The object_id of the object in question.
	* @param	string	tag			The raw string form of the tag to delete. See above for notes.
	* @return	string	Returns the tag in normalized form.
	*/
	public function delete_object_tag($user_id = 0, $object_id = 0, $tag = '') 
	{
		if ( ! $user_id = intval($user_id) or ! $object_id = intval($object_id) or empty($tag))
		{
			return FALSE;
		}
		
		if ( ! $tag_id = $this->raw_tag_id($tag)) 
		{
			return FALSE;
		}
		
		$delete_tag = ORM::factory(inflector::singular($this->config['tagging_table']))
			->where('user_id', $user_id)
			->where($this->config['object_foreign_key'], $object_id)
			->where('tag_id', $tag_id);
			
		if ($delete_tag->delete_all())
			return TRUE;
		else
			return FALSE;
	}

	/**
	* Delete All Object Tags
	*
	* Removes all tags from an object. This does not delete the tag itself 
	* from the database. This is most useful for cleanup, where an item is 
	* deleted and all its tags should be wiped out as well.
	*
	* @param	integer	object_id	The ID of the object in question.
	* @return	boolean	Returns TRUE if successful, FALSE otherwise.
	*/
	public function delete_all_object_tags($object_id = 0) 
	{
		if ( ! $object_id = intval($object_id)) 
		{
			return FALSE;
		}
		
		$delete_tags = ORM::factory(inflector::singular($this->config['tagging_table']))
			->where($this->config['object_foreign_key'], $object_id);
				
		if ($delete_tags->delete_all())
			return TRUE;
		else
			return FALSE;
	}

	/**
	* Delete All Object Tags for User
	*
	* Removes all tag from an object for a particular user. This does not
	* delete the tag itself from the database. This is most useful for
	* implementations similar to del.icio.us, where a user is allowed to retag
	* an object from a text box. That way, it becomes a two step operation of
	* deleting all the tags, then retagging with whatever's left in the input.
	*
	* @param	integer	user_id		The unique user_id of the person who tagged the object with this tag.
	* @param	integer	object_id	The object_id of the object in question.
	* @return	boolean	Returns TRUE if successful, FALSE otherwise.
	*/
	public function delete_all_object_tags_for_user($user_id = 0, $object_id = 0) 
	{
		if ( ! $user_id = intval($user_id) or ! $object_id = intval($object_id)) 
		{
			return FALSE;
		}

		$delete_tag = ORM::factory(inflector::singular($this->config['tagging_table']))
			->where('user_id', $user_id)
			->where($this->config['object_foreign_key'], $object_id);
			
		if ($delete_tag->delete_all())
			return TRUE;
		else
			return FALSE;
	}

	/**
	* Tag ID
	*
	* Retrieves the unique ID number of a tag based upon its normal form. Actually,
	* using this function is dangerous, because multiple tags can exist with the same
	* normal form, so be careful, because this will only return one, assuming that
	* if you're going by normal form, then the individual tags are interchangeable.
	*
	* @param	string	tag	The normal form of the tag to fetch.
	* @return	integer	Returns the tag ID.
	*/
	public function tag_id($tag = '') 
	{
		if (empty($tag)) 
		{
			return FALSE;
		}

		$result = ORM::factory(inflector::singular($this->config['tag_table']))
			->where('tag', $tag)
			->limit(1)
			->find();
			
		return $result->id;
	}

	/**
	* Raw Tag ID
	*
	* Retrieves the unique ID number of a tag based upon its raw form. If a single
	* unique record is needed, then use this function instead of tag_id(),
	* because raw_tags are unique.
	*
	* @param	string	tag	The raw string form of the tag to fetch.
	* @return	integer	Returns the tag ID.
	*/
	public function raw_tag_id($tag = '') 
	{
		if (empty($tag)) 
		{
			return FALSE;
		}
		
		$result = ORM::factory(inflector::singular($this->config['tag_table']))
			->where('raw_tag', $tag)
			->limit(1)
			->find();
		
		return $result->id;
	}

	/**
	* Tag Object
	*
	* This function allows you to pass in a string directly from a form, which is then
	* parsed for quoted phrases and special characters, normalized and converted into tags.
	* The tag phrases are then individually sent through the safe_tag() method for processing
	* and the object referenced is set with that tag.
	*
	* This method has been refactored to automatically look for existing tags and run
	* adds/updates/deletes as appropriate. It also has been refactored to accept comma-separated lists
	* of user_id's and objecct_id's to create either duplicate taggings from multiple users or
	* apply the tags to multiple objects. However, a singular user_id and object_id still produces
	* the same behavior.
	*
	* @param	string|array	user_id		A comma-separated string or array of unique user_id's of the taggers.
	* @param	string|array	object_id	A comma-separated list of unique id's of the object(s) in question.
	* @param	string			tags		The raw string form of the tag to delete. See above for notes.
	* @param	boolean	Whether to skip the update portion for objects that haven't been tagged. (Default: TRUE)
	* @return	boolean	Returns TRUE if successful, FALSE otherwise.
	*/
	public function tag_object($user_id = '', $object_id = '', $tags = '', $skip_updates = TRUE) 
	{
		if (empty($tags)) 
		{
			return FALSE;
		}

		if ( ! is_array($user_id) and ! empty($user_id))
		{
			// Break up CSL's for tagger id's and object id's
			$user_id = split(',', $user_id);
		}
		
		$valid_user_id = array();
		
		foreach ($user_id as $id) 
		{
			if (intval($id) > 0) 
			{
				$valid_user_id[] = intval($id);
			}
		}

		if ( ! count($valid_user_id)) 
		{
			return FALSE;
		}

		if ( ! is_array($object_id) and ! empty($object_id))
		{
			$object_id = split(',', $object_id);
		}
		
		$valid_object_id = array();
		
		foreach ($object_id as $id) 
		{
			if (intval($id) > 0) 
			{
				$valid_object_id[] = intval($id);
			}
		}
		
		if ( ! count($valid_object_id)) 
		{
			return FALSE;
		}

		$tags = $this->_parse_tags($tags);

		foreach ($valid_user_id as $user_id) 
		{
			foreach ($valid_object_id as $object_id) 
			{
				$old_tags = $this->tags_on_object($object_id, 0, 0, $user_id);

				$preserve_tags = array();

				if ( ! $skip_updates and count($old_tags)) 
				{
					foreach ($old_tags as $tag_item)
					{
						if ( ! in_array($tag_item->raw_tag, $tags)) 
						{
							// We need to delete old tags that don't appear in the new parsed string.
							$this->delete_object_tag($user_id, $object_id, $tag_item->raw_tag);
						}
						else 
						{
							// We need to preserve old tags that appear (to save timestamps)
							$preserve_tags[] = $tag_item->raw_tag;
						}
					}
				}
				
				$new_tags = array_diff($tags, $preserve_tags);

				$this->_tag_object_array($user_id, $object_id, $new_tags);
			}
		}

		return TRUE;
	}

	/**
	* Tag Object Array
	*
	* Private method to add tags to an object from an array.
	*
	* @param	integer	user_id		Unique ID of user.
	* @param	integer	object_id	Unique ID of object.
	* @param	array	tags		Array of tags to be add.
	* @return	boolean	TRUE if successful, FALSE otherwise.
	*/
	private function _tag_object_array($user_id, $object_id, $tags) 
	{
		foreach($tags as $tag) 
		{
			$tag = trim($tag);
			
			if ( ! empty($tag) and (strlen($tag) <= $this->config['max_tag_length'])) 
			{
				if (get_magic_quotes_gpc()) 
				{
					$tag = addslashes($tag);
				}
				
				$this->safe_tag($user_id, $object_id, $tag);
			}
		}
		
		return TRUE;
	}

	/**
	 * Parse Tags
	 *
	 * Private method to parse tags out of a string and into an array.
	 *
	 * @param	string	tags	String to parse.
	 * @return	array	Returns an array of the raw "tags" parsed.
	 */
	private function _parse_tags($tags = '')
	{
		if (empty($tags)) 
		{
			// If the tag string is empty, return the empty set.
			return array();
		}
		
		// Perform tag parsing
		if (get_magic_quotes_gpc()) 
		{
			$query = stripslashes(trim($tags));
		}
		else 
		{
			$query = trim($tags);
		}
		
		$words = preg_split('/(")/', $query, -1, PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE);
		$delim = 0;
		
		foreach ($words as $key => $word)
		{
			if ($word == '"')
			{
				$delim++;
				continue;
			}
			
			if (($delim % 2 == 1) and $words[$key - 1] == '"') 
			{
				$new_words[] = $word;
			}
			else 
			{
				$new_words = array_merge($new_words, preg_split('/\s+/', $word, -1, PREG_SPLIT_NO_EMPTY));
			}
		}
		
		return $new_words;
	}

	/**
	 * Most Popular Tags
	 *
	 * This function returns the most popular tags, with offset and limit support 
	 * for pagination. It also supports restricting to an individual user. Call it 
	 * with no parameters for a list of 25 most popular tags.
	 *
	 * @param	integer	user_id	The unique ID of the person to restrict results.
	 * @param	integer	offset	The offset of the tag to start at.
	 * @param	integer	limit	The number of tags to return in the result set.
	 * @return	object	Returns an object tags ordered by popularity descending.
	 */
	public function most_popular_tags($user_id = 0, $offset = 0, $limit = 25) 
	{
		$result = ORM::factory(inflector::singular($this->config['tag_table']))
			->select('tag, COUNT(*) as count')
			->join($this->config['tagging_table'], $this->config['tag_table'].'.id', $this->config['tagging_table'].'.tag_id', 'INNER')
			->groupby('tag')
			->orderby(array('count' => 'DESC', 'tag' => 'ASC'))
			->offset($offset)
			->limit($limit);
		
		if ($user_id = intval($user_id)) 
		{
			$result->where('user_id', $user_id);
		}

		return $result->find_all();
	}

	/**
	 * Most Recent Objects
	 *
	 * This function returns the most recent object IDs, with offset and limit support 
	 * for pagination. It also supports restricting to an individual user. Call it with 
	 * no parameters for a list of 25 most recent tags.
	 *
	 * @param	integer	user_id	The unique ID of the person to restrict results to.
	 * @param	string	tag		Tag to filter by.
	 * @param	integer	offset	The offset of the object to start at.
	 * @param	integer	limit	The number of object ids to return in the result set.
	 * @return	object	Returns an object with object ids ordered by timestamp descending.
	 */
	public function most_recent_objects($user_id = 0, $tag = '', $offset = 0, $limit = 25) 
	{
		$result = ORM::factory(inflector::singular($this->config['tagging_table']));

		if (empty($tag)) 
		{
			$result->select('DISTINCT '.$this->config['object_foreign_key'].', created')
				->orderby('created', 'DESC')
				->offset($offset)
				->limit($limit);		
		}
		else 
		{
			$result->select('DISTINCT '.$this->config['object_foreign_key'].', created')
				->join($this->config['tagging_table'], $this->config['tag_table'].'.id', $this->config['tagging_table'].'.tag_id', 'INNER')
				->where('tag', $tag)
				->orderby('created', 'DESC')
				->offset($offset)
				->limit($limit);
		}
		
		if ($user_id = intval($user_id)) 
		{
			$result->where('user_id', $user_id);
		}

		return $result->find_all();
	}

	/**
	 * Count Tags
	 *
	 * Returns the total number of tag->object links in the system.
	 * It might be useful for pagination at times, but I'm not sure if I actually use
	 * this anywhere. Restrict to a person's tagging by using the $user_id parameter.
	 * It does NOT include any tags in the system that aren't directly linked
	 * to an object.
	 *
	 * @param	integer	user_id	The unique ID of the person to restrict results to.
	 * @return	integer	Returns the count
	 */
	public function count_tags($user_id = 0, $normalized_version = FALSE) 
	{
		if ($normalized_version) 
		{
			$distinct_column = 'tag';
		}
		else 
		{
			$distinct_column = 'tag_id';
		}

		$result = ORM::factory(inflector::singular($this->config['tag_table']))
			->select('DISTINCT '.$distinct_column)
			->join($this->config['tagging_table'], $this->config['tag_table'].'.id', $this->config['tagging_table'].'.tag_id', 'INNER');
								
		if ($user_id = intval($user_id)) 
		{
			$result->where('user_id', $user_id);
		}
		
		return ($count = $result->count_all()) ? $count : FALSE;
	}

	/**
	 * Tag Cloud (HTML)
	 *
	 * This is a pretty straightforward, flexible method that automatically
	 * generates some html that can be dropped in as a tag cloud.
	 * It uses explicit font sizes inside of the style attribute of SPAN
	 * elements to accomplish the differently sized objects.
	 *
	 * It will also link every tag to $tag_page_url, appended with the
	 * normalized form of the tag. You should adapt this value to your own
	 * tag detail page's URL.
	 *
	 * @param	integer	num_tags		The number of tags to return. (default: 100)
	 * @param	integer	min_font_size	The minimum font size in the cloud. (default: 10)
	 * @param	integer	max_font_size	The maximum font size in the cloud. (default: 20)
	 * @param	string	font_units		The "units" for the font size (i.e. 'px', 'pt', 'em') (default: px)
	 * @param	string	span_class		The class to use for all spans in the cloud. (default: cloud_tag)
	 * @param	string	tag_page_url	The tag page URL (default: /tag/)
	 * @param	integer	user_id			Specify starting record (default: 0)
	 * @param	integer	offset			Offset of the tags result (default: 0)
	 * @return	string	Returns an HTML snippet that can be used directly as a tag cloud.
	 */
	public function tag_cloud_html($num_tags = 100, $min_font_size = 10, $max_font_size = 20, $font_units = 'px', $span_class = 'cloud_tag', $tag_page_url = '/tag/', $user_id = NULL, $offset = 0) 
	{
		$tag_list = $this->tag_cloud_tags($num_tags, $user_id, $offset);

		if (count($tag_list)) 
		{
			// Get the maximum qty of tagged objects in the set
			$max_qty = max(array_values($tag_list));
			
			// Get the min qty of tagged objects in the set
			$min_qty = min(array_values($tag_list));
		}
		else 
		{
			return '';
		}

		// For ever additional tagged object from min to max, we add
		// $step to the font size.
		$spread = $max_qty - $min_qty;
		
		if ($spread == 0) 
		{
			// Divide by zero
			$spread = 1;
		}
		
		$step = ($max_font_size - $min_font_size) / ($spread);

		// Since the original tag_list is alphabetically ordered,
		// we can now create the tag cloud by just putting a span
		// on each element, multiplying the diff between min and qty
		// by $step.
		$cloud_html = '';
		$cloud_spans = array();
		
		foreach ($tag_list as $tag => $qty) 
		{
			$size = $min_font_size + ($qty - $min_qty) * $step;
			$cloud_span[] = '<span class="' . $span_class . '" style="font-size: '. $size . $font_units . '; line-height: '. $size . $font_units . '"><a href="'.$tag_page_url . $tag . '">' . $tag . '</a></span>';

		}
		
		$cloud_html = join("\n ", $cloud_span);

		return $cloud_html;
	}

	/**
	 * Tag Cloud (Tags)
	 *
	 * This is a function built explicitly to set up a page with most popular tags
	 * that contains an alphabetically sorted list of tags, which can then be sized
	 * or colored by popularity.
	 *
	 * Also known more popularly as Tag Clouds!
	 *
	 * Here's the example case: http://upcoming.org/tag/
	 *
	 * @param	integer	limit	The maximum number of tags to return.
	 * @param	integer offset	The unique ID of the user to restrict to (Optional, default: 0)
	 * @param	integer user_id	Specify starting record (default: 0)
	 * @return	object	Returns an object of normalized tags and quantity of taggins.
	 */
	public function tag_cloud_tags($limit = 100, $offset = 0, $user_id = 0) 
	{
		$result = ORM::factory(inflector::singular($this->config['tag_table']))
			->select('tag, COUNT('.$this->config['object_foreign_key'].') as quantity')
			->join($this->config['tagging_table'], $this->config['tag_table'].'.id', $this->config['tagging_table'].'.tag_id', 'INNER')
			->groupby('tag')
			->orderby('quantity', 'DESC')
			->offset($offset)
			->limit($limit);

		if ($user_id = intval($user_id)) 
		{
			$result->where('user_id', $user_id);
		}

		return $result->find_all();
	}

	/**
	 * Similar Tags
	 *
	 * Finds tags that are "similar" or related to the given tag.
	 * It does this by looking at the other tags on objects tagged with the tag specified.
	 * Confusing? Think of it like e-commerce's "Other users who bought this also bought,"
	 * as that's exactly how this works.
	 *
	 * Returns FALSE if no tag is passed, or if no related tags are found.
	 *
	 * It's important to note that the quantity passed back along with each tag
	 * is a measure of the *strength of the relation* between the original tag
	 * and the related tag. It measures the number of objects tagged with both
	 * the original tag and its related tag.
	 *
	 * Thanks to Myles Grant for contributing this function!
	 *
	 * @param	string	tag		The raw normalized form of the tag to fetch.
	 * @param	integer	limit	The maximum number of tags to return.
	 * @param	integer	user_id	The unique id of a user to restrict the search to. Optional.
	 * @return	object	Returns an object of normalized tags and the quantity of related tags, 
	 * sorted by number of occurences of that tag (high to low).
	 */
	public function similar_tags($tag = '', $limit = 100, $user_id = 0) 
	{
		if (empty($tag)) 
		{
			return FALSE;
		}

		// This query was written using a double join for PHP. If you're trying to eke
		// additional performance and are running MySQL 4.X, you might want to try a subselect
		// and compare perf numbers.

		//$result = ORM::factory(inflector::singular($this->config['tagging_table']))
		$db = new Database();
		$result = $db->select('t1.tag, COUNT(o1.'.$this->config['object_foreign_key'].') AS quantity')
			->from($this->config['tagging_table'].' AS o1')
			->join($this->config['tag_table'].' AS t1', 't1.id', 'o1.tag_id', 'INNER')
			->join($this->config['tagging_table'].' AS o2', 'o1.'.$this->config['object_foreign_key'], 'o2.'.$this->config['object_foreign_key'], 'INNER')
			->join($this->config['tag_table'].' AS t2', 't2.id', 'o2.tag_id', 'INNER')
			->where('t2.tag', $tag)
			->where('t1.tag !=', $tag)
			->groupby('o1.tag_id')
			->orderby('quantity', 'DESC')
			->limit($limit);
	
		if ($user_id = intval($user_id))
		{
			$result->where('o1.user_id', $user_id)
				->where('o2.user_id', $user_id);
		}
		
		echo Kohana::debug($result->get());
		
		return $result->get();
	}

	/**
	 * Similar Objects
	 *
	 * This method implements a simple ability to find some objects in the database
	 * that might be similar to an existing object. It determines this by trying
	 * to match other objects that share the same tags.
	 *
	 * The user of the method has to use a threshold (by default, 1) which specifies
	 * how many tags other objects must have in common to match. If the original object
	 * has no tags, then it won't match anything. Matched objects are returned in order
	 * of most similar to least similar.
	 *
	 * The more tags set on a database, the better this method works. Since this
	 * is such an expensive operation, it requires a limit to be set via max_objects.
	 *
	 * @param	integer	object_id	The unique ID of the object to find similar objects for.
	 * @param	integer	threshold	The Threshold of tags that must be found in common (default: 1)
	 * @param	integer	max_objects	The maximum number of similar objects to return (default: 5).
	 * @param	integer	user_id		Optionally pass a tagger id to restrict similarity to a tagger's view.
	 * @return	object	Returns an object with matched objects ordered by strength of match descending.
	 */
	public function similar_objects($object_id = 0, $threshold = 1, $max_objects = 5, $user_id = 0)
	{
		if ( ! $object_id = intval($object_id) or ! $threshold = intval($threshold) or ! $max_objects = intval($max_objects))
		{
			return FALSE;
		}

		// Pass in a zero-limit to get all tags.
		$tag_items = $this->tags_on_object($object_id, 0, 0);

		$tags = array();
		
		foreach ($tag_items as $tag_item) 
		{
			$tags[] = $tag_item->tag;
		}
		
		$tags = array_unique($tags);
		
		if ( ! $num_tags = count($tags)) 
		{
			return FALSE;
		}
		
		$result = ORM::factory(inflector::singular($this->config['tagging_table']))
			->select($this->config['object_foreign_key'].', COUNT('.$this->config['object_foreign_key'].') as num_common_tags')
			->join($this->config['tag_table'], $this->config['tag_table'].'.id', $this->config['tagging_table'].'.tag_id', 'INNER')
			->in($this->config['tag_table'].'.tag', $tags)
			->groupby($this->config['tagging_table'].'.'.$this->config['object_foreign_key'])
			->having('num_common_tags >=', $threshold)
			->orderby('num_common_tags', 'DESC')
			->offset(0)
			->limit($max_objects);

		if ($user_id = intval($user_id)) 
		{
			$result->where('user_id', $user_id);
		}

		return $result->find_all();
	}

} // End Tags Library
