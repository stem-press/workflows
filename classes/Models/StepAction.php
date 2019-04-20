<?php

namespace Stem\Workflows\Models;

abstract class StepAction {
	const RESULT_OK = 0;
	const RESULT_ERROR = -1;
	const RESULT_FATAL_ERROR = -666;

	abstract public function title();
	abstract public function description();

	abstract public function execute($post);
}