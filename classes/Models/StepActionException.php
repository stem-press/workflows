<?php

namespace Stem\Workflows\Models;

class StepActionException extends \Exception {
	protected $stopsWorkflow;

	public function __construct($message = "", $stopsWorkflow = false) {
		parent::__construct($message, 0, null);

		$this->stopsWorkflow = $stopsWorkflow;
	}

	public function getStopsWorkflow() {
		return $this->stopsWorkflow;
	}
}