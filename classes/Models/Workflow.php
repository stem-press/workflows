<?php

namespace Stem\Workflows\Models;

use Stem\Models\Post;
use Stem\Queue\Queue;

/**
 * Workflows are a sequential list of actionable steps.  For example, approving the member of your site might include the following steps:
 *
 * - Charge an initiation fee
 * - Update their account status
 * - Send a welcome email
 * - Send a membership email
 *
 * The Workflow class allows you to compose this process out of individual reusable steps or actions.
 *
 * Workflows can be executed immediately, or they can be run in a background queue.
 *
 * @package Stem\Workflows\Models
 */
abstract class Workflow {
	/** @var Action[]  */
	protected $steps = [];

	/** @var null|Post  */
	protected $post = null;

	/** @var WorkflowState|null  */
	protected $state = null;

	const EXECUTE_RESULT_FATAL_ERROR = -666;
	const EXECUTE_RESULT_ERROR = -1;
	const EXECUTE_RESULT_SUCCESS = 0;
	const EXECUTE_RESULT_COMPLETE = 1;

	const QUEUE_WORKFLOW = 'workflows';
	const QUEUE_EMAIL = 'email';

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
	public static function postTypes() {
		return [];
	}

	/**
	 * Identifier for this workflow
	 * @return string
	 */
	public static function id() {
		return null;
	}

	/**
	 * Title for this workflow
	 * @return string
	 */
	public static function title() {
		return null;
	}

	/**
	 * Type of workflow
	 * @return string
	 */
	public static function type() {
		return self::WORKFLOW_TYPE_ONCE;
	}

	/**
	 * Determines if this workflow can be run for the given post.
	 * @param Post $post
	 * @return bool
	 */
	public static function validForPost($post) {
		return true;
	}

	/**
	 * Workflow constructor.
	 *
	 * @param Post $post
	 * @param WorkflowState|null $state
	 */
	public function __construct($post, $state = null) {
		$this->post = $post;

		if (!empty($state)) {
			$this->state = $state;
			$this->steps = $this->state->steps;
		} else {
			$this->state = new WorkflowState();
			$this->state->post_id = $post->id;
			$this->state->workflow_id = static::id();

			$this->defineSteps();

			$this->state->current_step = 0;
			$this->state->total_steps = count($this->steps);
			if (count($this->steps) > 0) {
				$this->state->current_step_title = $this->steps[0]->title();
			}
			$this->state->steps = $this->steps;
		}
	}

	/**
	 * Defines the steps in the workflow
	 */
	protected function defineSteps() {

	}

	/**
	 * Steps in this workflow
	 * @return Action[]
	 */
	public function steps() {
		return $this->steps;
	}

	/**
	 * Executes the next step in the workflow
	 *
	 * @return int
	 */
	public function nextStep() {
		try {
			/** @var Action $step */
			$step = $this->steps[$this->state->current_step];
			$this->state->current_step_title = $step->title();

			$result = $step->execute($this->post);
			if ($result == Action::RESULT_FATAL_ERROR) {
				$this->state->status = WorkflowState::STATUS_CANCELLED;
				$this->state->save();

				return self::EXECUTE_RESULT_FATAL_ERROR;
			}

			if ($result == Action::RESULT_ERROR) {
				$this->state->status = WorkflowState::STATUS_ERROR;
				$this->state->save();
				return self::EXECUTE_RESULT_ERROR;
			}

			$this->state->current_step++;
			if ($this->state->current_step >= count($this->steps)) {
				$this->state->status = WorkflowState::STATUS_COMPLETE;
				$this->state->save();
				return self::EXECUTE_RESULT_COMPLETE;
			}

			$this->state->status = WorkflowState::STATUS_RUNNING;
			$this->state->current_step_title = $this->steps[$this->state->current_step]->title();
			$this->state->save();

			return self::EXECUTE_RESULT_SUCCESS;
		} catch (ActionException $ex) {
			if ($ex->getStopsWorkflow()) {
				$this->state->status = WorkflowState::STATUS_CANCELLED;
				$this->state->save();
				return self::EXECUTE_RESULT_FATAL_ERROR;
			} else {
				$this->state->status = WorkflowState::STATUS_ERROR;
				$this->state->save();
				return self::EXECUTE_RESULT_ERROR;
			}
		}
	}

	/**
	 * Executes all of the steps
	 *
	 * @return int
	 */
	public function execute() {
		while(true) {
			try {
				$result = $this->nextStep();
				if ($result != self::EXECUTE_RESULT_SUCCESS) {
					return $result;
				}
			} catch (\Exception $ex) {
				$this->cancel();
				return self::EXECUTE_RESULT_FATAL_ERROR;
			}
		}
	}

	/**
	 * Cancels the current workflow
	 */
	public function cancel() {
		$this->state->status = WorkflowState::STATUS_CANCELLED;
		$this->state->save();
	}

	/**
	 * Queues the workflow to run in the background queue
	 */
	public function queue() {
		$this->state->save();
		Queue::instance()->add(self::QUEUE_WORKFLOW, new WorkflowJob($this->post->id, $this->state->id));
	}
}