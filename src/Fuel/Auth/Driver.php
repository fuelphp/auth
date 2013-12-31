<?php
/**
 * @package    Fuel\Auth
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2013 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Auth;

use Fuel\Auth\AuthException;

/**
 * Auth base driver class.
 *
 * It is extended by all driver base classes, and provides common methods and
 * prototypes for all Auth drivers
 *
 * @package  Fuel\Auth
 *
 * @since  2.0.0
 */
abstract class Driver
{
	/**
	 * @var  Manager  this drivers manager instance
	 */
	protected $manager;

	/**
	 * @var  bool  Whether or not this driver allows updates
	 */
	protected $readOnly = false;

	/**
	 * @var  array  supported global methods, with driver and return type
	 */
	protected $methods = array();

	/**
	 * @var  string  driver type
	 */
	protected $type = 'undefined';

	/**
	 * Set this drivers manager instance
	 *
	 * @since 2.0.0
	 */
	public function setManager(Manager $manager)
	{
		$this->manager = $manager;
	}

	/**
	 * Return the list of global methods this driver supports.
	 *
	 * This list is an array with elements:
	 * 'methodname' => array('drivertype', 'returnvalue'),
	 *
	 * and should be defined in the drivers base class, since it MUST be the
	 * same list for all drivers of the given type!
	 *
	 * @return  array
	 *
	 * @since 2.0.0
	 */
	public function getMethods()
	{
		return $this->methods;
	}

	/**
	 * Return the driver type.
	 *
	 * This is to ensure that nobody is trying to add a driver of type A as
	 * type B, which would make a mess. The type should be defined in the
	 * base class for the given type.
	 *
	 * @return  array
	 *
	 * @since 2.0.0
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * get a configuration item
	 *
	 * @param  string  $key      the config key to retrieve
	 * @param  string  $default  the value to return if not found
	 *
	 * @return  mixed
	 *
	 * @since 2.0.0
	 */
	public function getConfig($key = null, $default = null)
	{
		return func_num_args() ? \Arr::get($this->config, $key, $default) : $this->config;
	}

	/**
	 * get the readonly status of this driver
	 *
	 * @return  bool
	 *
	 * @since 2.0.0
	 */
	public function isReadOnly()
	{
		return $this->readOnly;
	}
}
