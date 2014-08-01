<?php

/**
 * class to be tested
 */
class Money{
	protected $amount;

	public function __construct($amount){
		$this->amount = $amount;
	}
	public function getAmount(){
		return $this->amount;
	}
	public function negate(){
		return $this->amount = $this->amount * -1;
	}
	public function add($plus){
		return $this->amount = $this->amount + $plus;
	}
}

/**
 * PHPUnit TestCase
 */
class SampleTest extends PHPUnit_Framework_TestCase{

	public function testNegateMethod(){
		$a = new Money( 10 );
		$a->negate();
		$this->assertEquals( -10, $a->getAmount() );
	}

	public function testAddMethod(){
		$a = new Money( 5 );
		$a->add(5);
		$this->assertEquals( 10, $a->getAmount() );
	}
}