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
 * @property int $total_steps
 * @property string $current_step_title
 * @property string $last_error
 * @property-read  float $progress
 * @property Action[] $steps
 * @property-read string $workflowClass
 */
class WorkflowState extends Model {
	const STATUS_NEW = 0;
	const STATUS_RUNNING = 100;
	const STATUS_COMPLETE = 200;
	const STATUS_CANCELLED = 300;
	const STATUS_ERROR = 600;

	/** @var null|string */
	private $_workflowClass = null;

	/**
	 * {@inheritDoc}
	 */
	protected $table = 'stem_workflow_states';

	/**
	 * {@inheritDoc}
	 */
	protected $casts = [
		'state' => 'array',
	];

	private function insureState() {
		if (empty($this->state)) {
			$this->state = [
				'step' => 0,
				'totalSteps' => 0,
				'steps' => null,
				'currentStep' => null
			];
		}
	}

	public function getCurrentStepAttribute() {
		$this->insureState();

		if (isset($this->state['step'])) {
			return $this->state['step'];
		}

		return 0;
	}

	public function setCurrentStepAttribute($val) {
		$this->insureState();

		$state = $this->state;
		$state['step'] = $val;
		$this->state = $state;
	}

	public function getTotalStepsAttribute() {
		$this->insureState();

		if (isset($this->state['totalSteps'])) {
			return $this->state['totalSteps'];
		}

		return 0;
	}

	public function setTotalStepsAttribute($val) {
		$this->insureState();

		$state = $this->state;
		$state['totalSteps'] = $val;
		$this->state = $state;
	}

	public function getCurrentStepTitleAttribute() {
		$this->insureState();

		if (isset($this->state['currentStep'])) {
			return $this->state['currentStep'];
		}

		return null;
	}

	public function setCurrentStepTitleAttribute($val) {
		$this->insureState();

		$state = $this->state;
		$state['currentStep'] = $val;
		$this->state = $state;
	}

	public function getStepsAttribute() {
		$this->insureState();

		if (isset($this->state['steps'])) {
			return unserialize($this->state['steps']);
		}

		return [];
	}

	public function setStepsAttribute($val) {
		$this->insureState();

		$state = $this->state;
		$state['steps'] = serialize($val);
		$this->state = $state;
	}

	public function getProgressAttribute() {
		$this->insureState();

		if ($this->total_steps == 0) {
			return 0;
		}

		return $this->current_step / $this->total_steps;
	}

	public function getWorkflowClassAttribute() {
		if (empty($this->_workflowClass)) {
			$this->_workflowClass = WorkflowManager::workflow($this->workflow_id);
		}

		return $this->_workflowClass;
	}
}