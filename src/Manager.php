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

/**
 * Auth manager class
 *
 * @package  Fuel\Auth
 *
 * @since  2.0.0
 */
class Manager
{
	/**
	 * @var  array  default auth configuration
	 */
	protected $config = array(
		'use_all_drivers' => false,
		'always_return_arrays' => true,
	);

	/**
	 * @var  array  loaded auth drivers
	 */
	protected $drivers = array();

	/**
	 * @var  array  supported global methods, with driver and return type
	 */
	protected $methods = array();

	/**
	 * @var  array  errors picked up in the last driver call
	 */
	protected $lastErrors = array();

	/**
	 * @var  int  When logged in, the linked id of the current user
	 */
	protected $linkedUserId;

	/**
	 * Class constructor.
	 *
	 * @since 2.0.0
	 */
	public function __construct(array $config = array())
	{
		// update the default config with whatever was passed
		$this->config = \Arr::merge($this->config, $config);
	}

	/**
	 * Capture calls to driver methods, and distribute them after checking...
	 *
	 * @param  string  $method      method name that was called
	 * @param  array   $args        array of arguments for the method
	 *
	 * @return  mixed
	 *
	 * @since 2.0.0
	 */
	public function __call($method, $args)
	{
		if (isset($this->methods[$method]))
		{
			// reset the last error array
			$this->lastErrors = array();

			// get the driver type
			$type = $this->methods[$method];

			// and process the call
			$result = array();

			// loop over the defined drivers
			foreach ($this->drivers[$type] as $name => $driver)
			{
				// call the driver method
				try
				{
					if ($result[$name] = call_user_func_array(array($driver, $method), $args))
					{
						// if we don't have to try all, bail out now
						if ($this->getConfig('use_all_drivers', false) === false)
						{
							break;
						}
					}
				}
				catch (AuthException $e)
				{
					// store the exception
					$this->lastErrors[$name] = $e;

					// and (re)set the result
					$result[$name] = false;
				}
			}

			if ($this->getConfig('always_return_arrays', true) === false and count($result) === 1)
			{
				return reset($result);
			}
			else
			{
				return $result;
			}
		}

		// we don't know or support this method
		throw new \ErrorException('Method "Fuel\Auth\Manager::'.$method.'()" does not exist.');
	}

	/**
	 * add a new driver
	 *
	 * @param  string  $type    the type of driver added
	 * @param  string  $name    the name of the driver added
	 * @param  string  $driver  the driver instance
	 *
	 * @since 2.0.0
	 */
	public function addDriver($type, $name, Driver $driver)
	{
		// make sure it's the correct driver for this type
		if (($driverType = $driver->getType()) != $type)
		{
			throw new AuthException('Auth driver error: "'.$name.'" is a "'.$driverType.'" instead of a "'.$type.'" driver.');
		}

		// link this driver to it's manager
		$driver->setManager($this);

		// is this the first driver loaded for this type?
		if ( ! isset($this->drivers[$type]))
		{
			// import all methods exported by the base class of this driver type
			$this->methods = \Arr::merge($this->methods, array_fill_keys($driver->getMethods(), $type));
		}

		// store the driver
		$this->drivers[$type][$name] = $driver;
	}

	/**
	 * get a specific driver instance
	 *
	 * @param  string  $type    the type of driver to get
	 * @param  string  $name    the name of the driver to get
	 *
	 * @return  mixed  driver instance, or null if not found
	 *
	 * @since 2.0.0
	 */
	public function getDriver($type = null, $name = null)
	{
		// if it exists, return the driver
		if (isset($this->drivers[$type][$name]))
		{
			return $this->drivers[$type][$name];
		}

		// do we need the entire list of drivers
		elseif (func_num_args() == 1)
		{
			return isset($this->drivers[$type]) ? $this->drivers[$type] : null;
		}

		elseif (func_num_args() == 0)
		{
			return $this->drivers;
		}

		// no hit
		return null;
	}

	/**
	 * remove an existing driver
	 *
	 * @param  string  $type    the type of driver to be removed
	 * @param  string  $name    the name of the driver to be removed
	 *
	 * @return  boolean  whether or not the driver was removed
	 *
	 * @since 2.0.0
	 */
	public function removeDriver($type, $name)
	{
		// if it exists, remove the driver
		if (isset($this->drivers[$type][$name]))
		{
			unset($this->drivers[$type][$name]);
			return true;
		}

		return false;
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
	 * Return the errors detected in the last driver call
	 *
	 * @return  array
	 *
	 * @since 2.0.0
	 */
	public function lastErrors()
	{
		return $this->lastErrors;
	}

	/*--------------------------------------------------------------------------
	 * Custom user driver methods
	 *------------------------------------------------------------------------*/

	/**
	 * Return the current linked user id
	 *
	 * @return  mixed  user id, or null if not logged in
	 *
	 * @since 2.0.0
	 */
	public function getUserId()
	{
		return $this->linkedUserId;
	}

	/**
	 * Login user
	 *
	 * @param   string  $user      user identification (name, email, etc...)
	 * @param   string  $password  the password for this user
	 *
	 * @throws  AuthException  if no storage driver is defined
	 *
	 * @return  array  results of all user drivers
	 *
	 * @since 2.0.0
	 */
	public function login($user = null, $password = null)
	{
		// make sure we have a storage driver loaded
		if ( ! $storage = $this->getDriver('storage'))
		{
			throw new AuthException('no storage driver is defined, can not store user information');
		}

		$orgSetting = $this->config['always_return_arrays'];
		$this->config['always_return_arrays'] = true;

		// call the login method on all loaded drivers
		$result = $this->__call('login', array($user, $password));

		// if we have a successful login
		if ( ! empty(array_filter($result)))
		{
			// attempt a shadow login for all login drivers that failed
			foreach ($result as $driver => $id)
			{
				if ($id === false and $this->getDriver('user', $driver)->hasShadowSupport())
				{
					// call the driver method
					try
					{
						$result[$driver] = $this->getDriver('user', $driver)->shadowLogin();
					}
					catch (\Exception $e)
					{
						// store the exception
						$this->lastErrors[$driver] = $e;

						// and (re)set the result
						$result[$driver] = false;
					}
				}
			}
		}

		$this->config['always_return_arrays'] = $orgSetting;

		// determine the linked user id
		if ( ! $this->linkedUserId = $storage->findLinkedUser($result))
		{
			// no hit, all logins must have failed
			$this->linkedUserId = null;
		}

		if ($this->getConfig('always_return_arrays', true) === false and count($result) === 1)
		{
			return reset($result);
		}

		return $result;
	}

	/**
	 * Login user using a (linked) user id (and no password!)
	 *
	 * This method may not be supported by all user drivers, as some backends
	 * don't allow a forced login without a password.
	 *
	 * @param   string  $id  id of the user for which we need to force a login
	 *
	 * @throws  AuthException  if no storage driver is defined
	 *
	 * @return  array  results of all user drivers
	 *
	 * @since 2.0.0
	 */
	public function forceLogin($id)
	{
		// make sure we have a storage driver loaded
		if ( ! $storage = $this->getDriver('storage'))
		{
			throw new AuthException('no storage driver is defined, can not store user information');
		}

		// fetch the driver => userid mappings for this linked id
		$drivers = $storage->getLinkedUsers($id);

		// storage for the results
		$result = array();

		// loop over the defined user drivers
		foreach ($this->drivers['user'] as $name => $driver)
		{
			// if we have a match for this driver, attempt to login
			if (isset($drivers[$name]) and $id = $drivers[$name])
			{
				// call the driver method
				try
				{
					if ($result[$name] = call_user_func_array(array($driver, 'forceLogin'), array($id)))
					{
						// if we don't have to try all, bail out now
						if ($this->getConfig('use_all_drivers', false) === false)
						{
							break;
						}
					}
				}
				catch (AuthException $e)
				{
					// store the exception
					$this->lastErrors[$name] = $e;

					// and reset the result
					$result[$name] = false;
				}
			}
		}

		if ($this->getConfig('always_return_arrays', true) === false and count($result) === 1)
		{
			return reset($result);
		}

		return $result;
	}

	/**
	 * Check if this driver is logged in or not
	 *
	 * @return  array  results of all user drivers
	 *
	 * @since 2.0.0
	 */
	public function isLoggedIn()
	{
		return $this->linkedUserId !== null;
	}

	/**
	 * Logout user
	 *
	 * @return  array  results of all user drivers
	 *
	 * @since 2.0.0
	 */
	public function logout()
	{
		$orgSetting = $this->config['always_return_arrays'];
		$this->config['always_return_arrays'] = true;

		// call the logout method on all loaded drivers
		$result = $this->__call('logout', array());

		$this->config['always_return_arrays'] = $orgSetting;

		// check for a success for at least one driver
		if (in_array(true, $result))
		{
			// reset the linked user id
			$this->linkedUserId = null;
		}

		return $result;
	}

	/**
	 * Delete a user
	 *
	 * if you delete the current logged-in user, a logout will be forced.
	 *
	 * @param  string  $username         name of the user to be deleted
	 *
	 * @throws  AuthException  if the user to be deleted does not exist
	 *
	 * @return  bool  true if the delete succeeded, or false if it failed
	 *
	 * @since 2.0.0
	 */
	public function delete($username)
	{
		// make sure we have a storage driver loaded
		if ( ! $storage = $this->getDriver('storage'))
		{
			throw new AuthException('no storage driver is defined, can not store user information');
		}

		$orgSetting = $this->config['always_return_arrays'];
		$this->config['always_return_arrays'] = true;

		// call the delete method on all loaded drivers
		$result = $this->__call('delete', array($username));

		$this->config['always_return_arrays'] = $orgSetting;

		// delete the linked user id information
		$id =  $storage->deleteLinkedUser($result);
		if ($this->linkedUserId === $id)
		{
			// delete of the logged-in user, force a logout
			$this->linkedUserId = null;
		}

		if ($this->getConfig('always_return_arrays', true) === false and count($result) === 1)
		{
			return reset($result);
		}

		return $result;
	}

	/*--------------------------------------------------------------------------
	 * Custom group driver methods
	 *------------------------------------------------------------------------*/

	/*--------------------------------------------------------------------------
	 * Custom ACL driver methods
	 *------------------------------------------------------------------------*/
}