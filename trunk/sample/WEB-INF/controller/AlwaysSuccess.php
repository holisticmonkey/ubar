<?php
class AlwaysSuccess extends BaseAction {

	public function executeInner() {
		return GlobalConstants::SUCCESS;
	}
}