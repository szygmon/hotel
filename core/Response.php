<?php

namespace Core;

class Response {

	protected $type = 'text/html';
	protected $data;
	protected $path;
	protected $template;

	public function __construct($data, $template = NULL, $path = NULL) {
		$this->data = $data;
		$this->template = $template;
		$this->path = $path;
	}

	public function getData() {
		return $this->data;
	}

	public function getTemplate() {
		return $this->template;
	}

	public function setType($type) {
		$this->type = $type;
		return $this;
	}

	public function getType() {
		return $this->type;
	}

	public function getPath() {
		return $this->path;
	}

}
