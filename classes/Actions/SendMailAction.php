<?php

namespace Stem\Workflows\Actions;

use Stem\MailTemplates\Jobs\SendMailTemplateJob;
use Stem\Queue\Queue;
use Stem\Workflows\Models\Action;

class SendMailAction extends Action {
	private $title = null;
	private $mailTemplate = null;
	private $email = null;
	private $data = null;
	private $inlineImages = null;

	public function __construct($title, $email, $mailTemplate, $data = [], $inlineImages = []) {
		$this->title = $title;
		$this->mailTemplate = $mailTemplate;
		$this->email = $email;
		$this->data = $data ?: [];
		$this->inlineImages = $inlineImages ?: [];
	}

	public function title() {
		return $this->title;
	}

	public function execute($post) {
		$job = new SendMailTemplateJob($this->mailTemplate, $this->email, $this->data, $this->inlineImages);
		Queue::instance()->add('email', $job);
	}
}