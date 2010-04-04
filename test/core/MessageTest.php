<?php
require_once(__DIR__ . "/../UbarBaseTestCase.php");
class MessageTest extends UbarBaseTestCase {

	function testDefault() {
		$normal = new Message("Hi I'm a message");
		$field = new Message("hey", "contents");
		$bad = new Message(null);

		$this->assertEquals("Hi I'm a message", $normal->getMessage());
		$this->assertEquals("", $normal->getFieldName());

		$this->assertEquals("hey", $field->getMessage());
		$this->assertEquals("contents", $field->getFieldName());

		$this->assertEquals("", $bad->getMessage());
		$this->assertEquals("", $bad->getFieldName());
	}

	function testToString() {
		$normal = new Message("Hi I'm a message");
		$field = new Message("hey", "contents");

		$this->assertEquals("Hi I'm a message", "$normal");
		$this->assertEquals("(contents) hey", "$field");
	}

	function testEquals() {
		$a = new Message("Hi I'm a message");
		$b = new Message("Hi I'm a message");
		$c = new Message("wop");
		$d = new Message("Hi I'm a message", "wap");
		$e = new Message("Hi I'm a message", "weep");
		$this->assertTrue($a->equals($b));

		$this->assertFalse($a->equals("wee"));
		$this->assertFalse($a->equals($c));
		$this->assertFalse($a->equals($d));
		$this->assertFalse($d->equals($e));
	}
}
?>