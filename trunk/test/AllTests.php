<?php
class AllTests
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Project');

        $suite->addTest(Package_AllTests::suite());

        return $suite;
    }
}
?>