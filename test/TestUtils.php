<?php
//Mimics a MgStringCollection
class FakeStringCollection
{
	private $values;
	
	public function __construct($vals) {
		$this->values = $vals;
	}
	
	public function IndexOf($val) {
		for ($i = 0; $i < count($this->values); $i++) {
			if ($val === $this->values[$i])
				return $i;
		}
		return -1;
	}
}
	
class TestUtils
{	
	public static function mockByteReader($testCase, $xml) {
		$stub = $testCase->getMockBuilder("MgByteReader")->getMock();
		$stub->method("GetMimeType")
			->will($testCase->returnValue("text/xml"));
		$stub->method("ToString")
			->will($testCase->returnValue($xml));
		return $stub;
	}
}	
?>