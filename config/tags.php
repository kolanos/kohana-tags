<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Tags Configuration
 */

// Whether to normalize tags at all (recommended, as raw tags are preserved anyway.)
$config['normalize_tags'] = TRUE;

// Use Kohana's url::title() helper for normalization
$config['use_kohana_normalization'] = TRUE;

// If 'use_kohana_normalization' is set to FALSE, you can define your own normalization here.
$config['custom_normalization'] = '-a-zA-Z0-9';

// Whether to prevent multiple users from tagging the same object. By default, set to block.
$config['block_multiuser_tag_on_object'] = TRUE;

// Will append this string to any integer tagsg. This is supposed to prevent PHP casting "string" integer tags as ints. Won't do anything to floats or non-numeric strings.
$config['append_to_integer'] = '';

// The maximum length of a tag.
$config['max_tag_length'] = 30;

// The tags table name, tags are stored. Must be plural to properly handle joins.
$config['tag_table'] = 'tags';

// The taggings table name, where taggings (when an object is tagged) are stored. Must be plural to properly handle joins.
$config['tagging_table'] = 'taggings';

// The foreign key name of the object being tagged (ie. blog_id, comment_id, etc.)
$config['object_foreign_key'] = 'object_id';
