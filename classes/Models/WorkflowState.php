<?php

namespace Stem\Workflows\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class WorkflowState
 * @package TheWonder\Models\Workflow
 *
 * @property int $workflow_id
 * @property int $post_id
 * @property int $status
 * @property array $state
 * @property int $current_step
 * @property float $progress
 * @property Workflow $workflow
 */
class WorkflowState extends Model {
	const STATUS_NEW = 0;
	const STATUS_RUNNING = 100;
	const STATUS_COMPLETE = 200;
	const STATUS_CANCELLED = 300;
	const STATUS_ERROR = 600;

	/** @var null|Workflow  */
	private $workflow = null;

	/**
	 * {@inheritDoc}
	 */
	protected $table = 'ilab_workflows';

	/**
	 * {@inheritDoc}
	 */
	protected $casts = [
		'state' => 'array',
	];

	public function getCurrentStepAttribute() {
		if (isset($this->state['step'])) {
			return $this->state['step'];
		}

		return 0;
	}

	public function getProgressAttribute() {
		return 0;
	}

	public function getWorkflowAttribute() {
		if (empty($this->workflow)) {
			$this->workflow = WorkflowManager::workflow($this->workflow_id);
		}

		return $this->workflow;
	}
}