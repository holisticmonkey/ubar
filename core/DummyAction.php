<?php
/* This Dummy Action class is for use in cases where no action is required to render a page.
 */
class DummyAction extends Action {

	public function executeInner() {
		return GlobalConstants::SUCCESS;
	}
}
?>