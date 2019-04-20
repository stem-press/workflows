<?php

namespace Stem\Workflows\Models;

use Stem\Core\Context;
use Stem\Queue\Job;
use Stem\Queue\Queue;

class WorkflowJob extends Job {
	private $postId;
	private $workflowStateId;
	private $currentStep;

	public function __construct($postId, $currentStep, $workflowStateId) {
		$this->postId = $postId;
		$this->currentStep = $currentStep;
		$this->workflowStateId = $workflowStateId;
	}

	/**
	 * Runs the job, returning a status code
	 * @return int
	 */
	public function run() {
		$post = Context::current()->modelForPostID($this->postId);

		/** @var WorkflowState $workflowState */
		$workflowState = WorkflowState::find($this->workflowStateId);

		if ($workflowState->workflow->execute($post, $this->currentStep, $workflowState)) {
			Queue::instance()->add('workflows', new WorkflowJob($this->postId, $this->currentStep+1, $workflowState->id));
		}

		if ($workflowState->status == WorkflowState::STATUS_ERROR) {
			return self::STATUS_ERROR;
		}

		return self::STATUS_OK;
	}
}