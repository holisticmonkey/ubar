<?php
/* This Dummy Action class is for use in cases where no action is required to render a page.
 */
class DummyAction extends ViewAction {

	public function execute() {
		return GlobalConstants::SUCCESS;
	}
}
?>