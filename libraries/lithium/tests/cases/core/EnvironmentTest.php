<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2010, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace lithium\tests\cases\core;

use \lithium\core\Environment;
use \lithium\tests\mocks\core\MockRequest;

class EnvironmentTest extends \lithium\test\Unit {

	public function setUp() {
		Environment::reset();
	}

	/**
	 * Tests setting and getting current environment, and that invalid environments cannot be
	 * selected.
	 *
	 * @return void
	 */
	public function testSetAndGetCurrentEnvironment() {
		Environment::set('production',  array('foo' => 'bar'));
		Environment::set('staging',     array('foo' => 'baz'));
		Environment::set('development', array('foo' => 'dib'));

		Environment::set('development');

		$this->assertEqual('development', Environment::get());
		$this->assertTrue(Environment::is('development'));
		$this->assertNull(Environment::get('doesNotExist'));

		$expected = array('foo' => 'dib');
		$config = Environment::get('development');
		$this->assertEqual($expected, $config);

		$foo = Environment::get('foo'); // returns 'dib', since the current env. is 'development'
		$expected = 'dib';
		$this->assertEqual($expected, $foo);
	}

	/**
	 * Tests modifying environment configuration.
	 *
	 * @return void
	 */
	public function testModifyEnvironmentConfiguration() {
		$expected = array('foo' => 'bar');
		Environment::set('test', $expected);
		$this->assertEqual($expected, Environment::get('test'));

		$expected += array('baz' => 'qux');
		Environment::set('test', array('baz' => 'qux'));
		$this->assertEqual($expected, Environment::get('test'));
	}

	/**
	 * Tests auto-detecting environment settings through a series of mock request classes.
	 *
	 * @return void
	 */
	public function testEnvironmentDetection() {
		Environment::set(new MockRequest(array('SERVER_ADDR' => '::1')));
		$this->assertTrue(Environment::is('development'));

		$request = new MockRequest(array('SERVER_ADDR' => '1.1.1.1', 'HTTP_HOST' => 'test.local'));
		Environment::set($request);
		$this->assertTrue(Environment::is('test'));

		$request = new MockRequest(array('SERVER_ADDR' => '1.1.1.1', 'HTTP_HOST' => 'www.com'));
		Environment::set($request);
		$this->assertTrue(Environment::is('production'));
	}

	/**
	 * Tests resetting the `Environment` class to its default state.
	 *
	 * @return void
	 */
	public function testReset() {
		Environment::set('test', array('foo' => 'bar'));
		Environment::set('test');
		$this->assertEqual('test', Environment::get());
		$this->assertEqual('bar', Environment::get('foo'));

		Environment::reset();
		$this->assertEqual('', Environment::get());
		$this->assertNull(Environment::get('foo'));
	}

	/**
	 * Tests using a custom detector to get the current environment.
	 *
	 * @return void
	 */
	public function testCustomDetector() {
		Environment::is(function($request) {
			if ($request->env('HTTP_HOST') == 'localhost') {
				return 'development';
			}
			if ($request->env('HTTP_HOST') == 'staging.server') {
				return 'test';
			}
			return 'production';
		});

		$request = new MockRequest(array('HTTP_HOST' => 'localhost'));
		Environment::set($request);
		$this->assertTrue(Environment::is('development'));

		$request = new MockRequest(array('HTTP_HOST' => 'lappy.local'));
		Environment::set($request);
		$this->assertTrue(Environment::is('production'));

		$request = new MockRequest(array('HTTP_HOST' => 'test.local'));
		Environment::set($request);
		$this->assertTrue(Environment::is('production'));
	}
}

?>