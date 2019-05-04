<?php

namespace Stem\Workflows\Models;

use Stem\Models\Post;

final class WorkflowManager {
	/** @var array  */
	private static $workflows = [];

	/** @var array  */
	private static $workflowsForPostTypes = [];

	/**
	 * Returns workflow classes for a given post type
	 * @param $postType
	 *
	 * @return Workflow[]
	 */
	public static function workflowsForPostType($postType) {
		if (!isset(static::$workflowsForPostTypes[$postType])) {
			return [];
		}

		return static::$workflowsForPostTypes[$postType];
	}

	/**
	 * Returns workflow classes and workflow states for a given post
	 *
	 * @param Post $post
	 * @return array
	 */
	public static function workflowsForPost($post) {
		$states = WorkflowState::query()->where('post_id', $post->id)->orderBy('updated_at', 'desc')->get();

		$runningStates = [];
		$availableFlows = [];
		$unavailableFlows = [];
		$potentialFlows = [];

		$postTypeFlows = static::workflowsForPostType($post::postType());
		/** @var WorkflowState $state */
		foreach($states as $state) {
			if (empty($state->workflowClass)) {
				continue;
			}

			if ($state->status < WorkflowState::STATUS_COMPLETE) {
				$runningStates[] = $state;

				if ($state->workflowClass::type() == Workflow::WORKFLOW_TYPE_CONCURRENT) {
					if ($state->workflowClass::validForPost($post)) {
						if (!in_array($state->workflowClass, $availableFlows)) {
							$availableFlows[] = $state->workflowClass;
						}
					}
				} else {
					$unavailableFlows[] = $state->workflowClass;
				}
			} else if ($state->status == WorkflowState::STATUS_COMPLETE) {
				if ($state->workflowClass::type() == Workflow::WORKFLOW_TYPE_ONCE) {
					$unavailableFlows[] = $state->workflowClass;
				} else {
					if ($state->workflowClass::validForPost($post)) {
						if (!in_array($state->workflowClass, $availableFlows) && !in_array($state->workflowClass, $unavailableFlows)) {
							$availableFlows[] = $state->workflowClass;
						}
					}
				}
			} else {
				if ((($state->status < WorkflowState::STATUS_CANCELLED) || ($state->status < WorkflowState::STATUS_ERROR)) && ($state->workflowClass::validForPost($post))) {
					if (!in_array($state->workflowClass, $availableFlows) && !in_array($state->workflowClass, $unavailableFlows)) {
						$potentialFlows[] = $state->workflowClass;
					}
				}
			}
		}

		foreach($potentialFlows as $flow) {
			if (!in_array($flow, $unavailableFlows) && !in_array($flow, $potentialFlows)) {
				if ($flow::validForPost($post)) {
					if (!in_array($flow, $availableFlows) && !in_array($flow, $unavailableFlows)) {
						$availableFlows[] = $flow;
					}
				}
			}
		}

		/** @var Workflow $flow */
		foreach($postTypeFlows as $flow) {
			if (!in_array($flow, $availableFlows) && !in_array($flow, $unavailableFlows)) {
				if ($flow::validForPost($post)) {
					if (!in_array($flow, $availableFlows) && !in_array($flow, $unavailableFlows)) {
						$availableFlows[] = $flow;
					}
				}
			}
		}

		return [
			'running' => $runningStates,
			'available' => $availableFlows
		];

	}

	/**
	 * Returns a workflow class with a given id
	 *
	 * @param $id
	 *
	 * @return string|null
	 */
	public static function workflow($id) {
		if (!isset(static::$workflows[$id])) {
			return null;
		}

		return static::$workflows[$id];
	}

	/**
	 * Registers a workflow class
	 *
	 * @param string $workflowClass
	 */
	public static function registerWorkflow($workflowClass) {
		static::$workflows[$workflowClass::id()] = $workflowClass;

		$postTypes = $workflowClass::postTypes();
		foreach($postTypes as $postType) {
			if (empty(static::$workflowsForPostTypes[$postType])) {
				static::$workflowsForPostTypes[$postType] = [];
			}

			static::$workflowsForPostTypes[$postType][] = $workflowClass;
		}
	}
}