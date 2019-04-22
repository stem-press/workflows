<?php

namespace Stem\Workflows\Models;

use Stem\Models\Post;

/**
 * An actionable step in a workflow
 *
 * @package Stem\Workflows\Models
 */
abstract class Action {
	const RESULT_OK = 0;
	const RESULT_ERROR = -1;
	const RESULT_FATAL_ERROR = -666;

	/**
	 * The title of the action
	 * @return string
	 */
	abstract public function title();

	/**
	 * Executes an action for a given post
	 *
	 * @param Post $post
	 *
	 * @return int
	 */
	abstract public function execute($post);
}