<?php
/**
 * @package    Fuel\Auth
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2014 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Auth\Storage;

use Fuel\Auth\Driver;

/**
 * Auth Storage driver base class
 *
 * @package  Fuel\Auth
 *
 * @since  2.0.0
 */
abstract class Base extends Driver implements StorageInterface
{
	/**
	 * @var  bool  These drivers do not support concurrency
	 */
	protected $hasConcurrency = false;
}
