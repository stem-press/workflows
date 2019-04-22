<?php

namespace Stem\Workflows\Models;

use Stem\Core\Context;
use Stem\Queue\Job;

class WorkflowJob extends Job {
	private $postId;
	private $workflowStateId;

	public function __construct($postId, $workflowStateId) {
		$this->postId = $postId;
		$this->workflowStateId = $workflowStateId;
	}

	public function maxIterations() {
		return 5;
	}

	/**
	 * Runs the job, returning a status code
	 * @return int
	 */
	public function run() {
		$post = Context::current()->modelForPostID($this->postId);

		/** @var WorkflowState $workflowState */
		$workflowState = WorkflowState::find($this->workflowStateId);

		/** @var Workflow $workflow */
		$workflow = new $workflowState->workflowClass($post, $workflowState);
		$result = $workflow->execute();

		if ($result == Workflow::EXECUTE_RESULT_ERROR) {
			return Job::STATUS_ERROR;
		} else if ($result == Workflow::EXECUTE_RESULT_FATAL_ERROR) {
			return Job::STATUS_FATAL_ERROR;
		}

		return Job::STATUS_OK;
	}
}