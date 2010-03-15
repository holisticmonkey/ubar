<?php
abstract class ViewAction extends Action {

	private $exception;

	public $title;
	public $page;
	public $section;
	public $subSection;

	// TODO: look into passing mapper into constructor so it can get template def itself?
	public function __construct($viewPath, $def) {
		parent :: __construct($viewPath, $def);

		// init view properties that may be in action
		// NOTE: we don't have template yet to get section, subsection, etc
		// NOTE: there is overlap between action defs and template defs

		$this->title = $def->getTitle();

		// if no value set, will get back an empty string from the def
		if ($this->title == '') {
			$key = $def->getTitleKey();
			if ($key != '') {
				$this->title = getTxt($key);
			}
		}

		$this->section = $def->getSection();
		$this->subSection = $def->getSubSection();

		function getTitle() {
			global $action;
			return $action->title;
		}

		function getPage() {
			global $action;
			return $action->page;
		}

		function getSubSection() {
			global $action;
			return $action->getSubSection();
		}

		function getSection() {
			global $action;
			return $action->getSection();
		}
	}

	public function initTemplateValues() {
		// only override values if not already set
		if (is_null($this->section) || $this->section == '') {
			$this->section = $this->templateDef->getSection();
		}
		if (is_null($this->subSection) || $this->subSection == '') {
			$this->subSection = $this->templateDef->getSubSection();
		}

	}

	protected function setException($exception) {
		$this->exception = $exception;
	}

	public function getTitle() {
		return $this->title;
	}

	public function getPage() {
		return $this->page;
	}

	public function getSection() {
		return $this->section;
	}

	public function getSubSection() {
		return $this->subSection;
	}
}
?>