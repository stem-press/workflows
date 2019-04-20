<?php

namespace Stem\Workflows\Models;

use Stem\Models\Post;

final class WorkflowManager {
	/** @var array  */
	private static $workflows = [];

	/** @var array  */
	private static $workflowsForPostTypes = [];

	/**
	 * Returns workflows for a given post type
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
	 * Returns workflows and workflow states for a given post
	 *
	 * @param Post $post
	 * @return array
	 */
	public static function workflowsForPost($post) {
		$states = WorkflowState::query()->where('post_id', $post->id)->orderBy('updated_at', 'desc')->get();

		$runningStates = [];
		$availableFlows = [];
		$unavailableFlows = [];

		$postTypeFlows = static::workflowsForPostType($post::postType());
		/** @var WorkflowState $state */
		foreach($states as $state) {
			if ($state->status < WorkflowState::STATUS_COMPLETE) {
				$runningStates[] = $state;

				if ($state->workflow->type() == Workflow::WORKFLOW_TYPE_CONCURRENT) {
					$availableFlows[] = $state->workflow;
				} else {
					$unavailableFlows[] = $state->workflow;
				}
			} else if ($state->status == WorkflowState::STATUS_COMPLETE) {
				if ($state->workflow->type() == Workflow::WORKFLOW_TYPE_ONCE) {
					$unavailableFlows[] = $state->workflow;
				} else {
					$availableFlows[] = $state->workflow;
				}
			} else {
				if ($state->status != WorkflowState::STATUS_CANCELLED) {
					$runningStates[] = $state;
				}

				$availableFlows[] = $state->workflow;
			}
		}

		/** @var Workflow $flow */
		foreach($postTypeFlows as $flow) {
			if (!in_array($flow, $availableFlows)) {
				if (!in_array($flow, $unavailableFlows)) {
					$availableFlows[] = $flow;
				}
			}
		}

		return [
			'running' => $states,
			'available' => $availableFlows
		];

	}

	/**
	 * Returns a workflow with a given id
	 *
	 * @param $id
	 *
	 * @return Workflow|null
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
		/** @var Workflow $workflow */
		$workflow = new $workflowClass();

		static::$workflows[$workflow->id()] = $workflow;

		$postTypes = $workflow->postTypes();
		foreach($postTypes as $postType) {
			if (empty(static::$workflowsForPostTypes[$postType])) {
				static::$workflowsForPostTypes[$postType] = [];
			}

			static::$workflowsForPostTypes[$postType][] = $workflow;
		}
	}
}