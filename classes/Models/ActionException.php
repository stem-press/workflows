<?php

namespace Stem\Workflows\Models;

/**
 * Exception thrown by actions
 *
 * @package Stem\Workflows\Models
 */
class ActionException extends \Exception {
	protected $stopsWorkflow;

	public function __construct($message = "", $stopsWorkflow = false, $previous = null) {
		parent::__construct($message, 0, $previous);

		$this->stopsWorkflow = $stopsWorkflow;
	}

	public function getStopsWorkflow() {
		return $this->stopsWorkflow;
	}

	public function getExceptionOfClass($exceptionClass) {
		$previous = $this->getPrevious();
		while($previous != null) {
			if (is_a($previous, $exceptionClass)) {
				return $previous;
			}

			$previous = $previous->getPrevious();
		}

		return null;
	}
}