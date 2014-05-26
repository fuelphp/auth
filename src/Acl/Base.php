<?php
/**
 * @package    Fuel\Auth
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2013 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Auth\Acl;

use Fuel\Auth\Driver;

/**
 * Auth Acl driver base class
 *
 * @package  Fuel\Auth
 *
 * @since  2.0.0
 */
abstract class Base extends Driver
{
	/**
	 * @var  string  type for drivers extending this base class
	 */
	protected $type = 'acl';

	/**
	 * @var  array  exported methods, must be supported by all user drivers
	 *
	 * for every method listed, there MUST be an method definition
	 * in this base class, to ensure the driver implements it!
	 */
	protected $methods = array(
		'hasAccess',
	);

	/**
	 * Base constructor. Prepare all things common for all acl drivers
	 */
	public function __construct(array $config = array())
	{
		parent::__construct($config);
	}
}