<?php
require_once (__DIR__ . "/../UbarBaseTestCase.php");
class MessageFormatTest extends UbarBaseTestCase {

	function testSimple() {
		$this->assertEquals("How many? '9'", MessageFormat::get("How many? '{0}'", array(9)));
		$this->assertEquals("How many? '9'", MessageFormat::get("How many? '{dogs}'", array("dogs" => 9)));
	}

	function testExtraArgs() {
		$this->assertEquals("How many? '9'", MessageFormat::get("How many? '{0}'", array(9,0,101)));
	}

	function testMissingArgs() {
		$this->assertEquals("How many? '{0}'", MessageFormat::get("How many? '{0}'"));
	}

	function testNoEvals() {
		$this->assertEquals("test no args", MessageFormat::get("test no args"));
	}

	function testNumber() {
		$this->assertEquals("1,234 dogs.", MessageFormat::get("{dogs,number,integer} dogs.", array("dogs" => 1234)));
		$this->assertEquals("1 dogs.", MessageFormat::get("{dogs,number,integer} dogs.", array("dogs" => 1.4)));
		$this->assertEquals("1.4 dogs.", MessageFormat::get("{dogs,number,double} dogs.", array("dogs" => 1.4)));
	}

	function testChoice() {
		$this->assertEquals("-1 dogs", MessageFormat::get("{0,number,integer} {0,choice,0>dogs|0#dogs|1#dog|1<dogs}", array(-1)));
		$this->assertEquals("0 dogs", MessageFormat::get("{0,number,integer} {0,choice,0>dogs|0#dogs|1#dog|1<dogs}", array(0)));
		$this->assertEquals("1 dog", MessageFormat::get("{0,number,integer} {0,choice,0>dogs|0#dogs|1#dog|1<dogs}", array(1)));
		$this->assertEquals("100,000 dogs", MessageFormat::get("{0,number,integer} {0,choice,0>dogs|0#dogs|1#dog|1<dogs}", array(100000)));
		// not in choice, punt and html format
		$this->assertEquals("-1 {0,choice,0#dogs|1#dog|1&lt;dogs}", MessageFormat::get("{0,number,integer} {0,choice,0#dogs|1#dog|1<dogs}", array(-1)));
	}

	function testDate() {
		$this->assertEquals("2/22/1978", MessageFormat::get("{0,date}", array("February 22, 1978")));
		$this->assertEquals("2/22/1978", MessageFormat::get("{0,date,normal}", array("February 22, 1978")));
		$this->assertEquals("February 22, 1978", MessageFormat::get("{0,date,verbose}", array("February 22, 1978")));
		$this->assertEquals("2/22/1978 12:00:00 AM", MessageFormat::get("{0,date,datetime}", array("February 22, 1978")));
		try {
			MessageFormat::get("{0,date}", array("woops"));
			$this->fail("Since dev mode is on, a bad date string should throw an error.");
		} catch (Exception $e) {
			//noop
		}
		try {
			MessageFormat::get("{0,date}", array("111111111"));
			$this->fail("Since dev mode is on, a bad date string should throw an error.");
		} catch (Exception $e) {
			//noop
		}
	}

	function testDuration() {

	}

	function testMath() {
		$this->assertEquals("15", MessageFormat::get("{0,math,*{1}}", array(3, 5)));
		$this->assertEquals("6", MessageFormat::get("{0,math,*2}", array(3)));
		$this->assertEquals("1", MessageFormat::get("{0,math,%2}", array(5)));
		$this->assertEquals("1.5", MessageFormat::get("{0,math,/2}", array(3)));
		$this->assertEquals("1", MessageFormat::get("{0,math,-2}", array(3)));
		//$this->assertEquals("5", MessageFormat::get("{0,math,+floor(2)}", array(3)));
		//$this->assertEquals("6", MessageFormat::get("{0,math,*2}", array("asdf")));
	}

	function testBadMath() {
		try {
			MessageFormat::get("{0,math,*2}", array("asdf"));
			$this->fail("Since dev mode is on, a bad math string should throw an error.");
		} catch (Exception $e) {
			//noop
		}
	}

	function testNesting() {
		$this->assertEquals(" 9 foosas", MessageFormat::get("{0, choice, 0# no foosa|1# {0} foosa|2< {0, number, integer} foosas}", array(9)));
	}

	function testUnknownType() {
		try {
			MessageFormat::get("{0,wopple}", array("111111111"));
			$this->fail("Since dev mode is on, an unknown formatter should throw an error.");
		} catch (Exception $e) {
			//noop
		}
	}

}