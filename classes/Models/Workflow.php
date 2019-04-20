<?php

namespace Stem\Workflows\Models;

use Stem\Models\Post;

abstract class Workflow {
	/** @var string Flag that indicates the workflow can only be run once for a given post  */
	const WORKFLOW_TYPE_ONCE = 'once';

	/** @var string Flag that indicates the workflow can only be run multiple times for a given post but only one can be running at any given time  */
	const WORKFLOW_TYPE_SERIAL = 'serial';

	/** @var string Flag that indicates the workflow can only be run multiple times for a given post and many can be running at any given time  */
	const WORKFLOW_TYPE_CONCURRENT = 'concurrent';

	/**
	 * Array of post types that work with this workflow
	 * @return string[]
	 */
	abstract public function postTypes();

	/**
	 * Identifier for this workflow
	 * @return string
	 */
	abstract public function id();

	/**
	 * Title for this workflow
	 * @return string
	 */
	abstract public function title();

	/**
	 * Type of workflow
	 * @return string
	 */
	abstract public function type();

	/**
	 * Total number of steps in this workflow
	 * @return int
	 */
	abstract public function totalSteps();

	/**
	 * Titles for the different steps
	 * @return string[]
	 */
	abstract public function stepTitles();

	/**
	 * Executes a step in the workflow
	 *
	 * @param Post $post
	 * @param int $step
	 * @param WorkflowState $state
	 *
	 * @return bool
	 */
	abstract public function execute($post, $step, $state);
}