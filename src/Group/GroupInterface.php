<?php
/**
 * @package    Fuel\Auth
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2014 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Auth\Group;

use Fuel\Auth\AuthInterface;

interface GroupInterface extends AuthInterface
{
	/**
	 * Create new group
	 *
	 * the use of the attributes array will depend on the driver. since drivers
	 * can be switched, the method must check the attributes for missing values
	 * and ignore values it doesn't need or use.
	 *
	 * @param  string  $group       name of the group to be created
	 * @param  array   $attributes  any attributes to be passed to the driver
	 *
	 * @throws  AuthException  if the group to be created already exists
	 *
	 * @return  mixed  the key of the group created, or false if it failed
	 *
	 * @since 2.0.0
	 */
	public function createGroup($group, Array $attributes = []);

	/**
	 * Update an existing group
	 *
	 * the use of the attributes array will depend on the driver. since drivers
	 * can be switched, the method must check the attributes for missing values
	 * and ignore values it doesn't need or use.
	 *
	 * @param  string  $group       id or name of the group to be checked
	 * @param  array   $attributes  any attributes to be passed to the driver
	 *
	 * @throws  AuthException  if the group to be updated does not exist
	 *
	 * @return  mixed  the id of the group updated, or false if it failed
	 *
	 * @since 2.0.0
	 */
	public function updateGroup($group, Array $attributes = []);

	/**
	 * Delete a group
	 *
	 * @param  string  $group  id or name of the group to be checked
	 *
	 * @throws  AuthException  if the group to be deleted does not exist
	 *
	 * @return  mixed  the id of the group deleted, or false if it failed
	 *
	 * @since 2.0.0
	 */
	public function deleteGroup($group);

	/**
	 * Assigns a given group to a user
	 *
	 * If no user is specified, the current logged-in user will be used.
	 *
	 * @param  string  $group  id or name of the group to assign. This group must exist
	 * @param  string  $user   user to assign to. if not given, the current logged-in user will be used
	 *
	 * @throws  AuthException  if the requested group does not exist
	 * @throws  AuthException  if there is no user
	 *
	 * @return  mixed  the id of the group assigned, or false if it failed
	 *
	 * @since 2.0.0
	 */
	public function assignUserToGroup($group, $user = null);

	/**
	 * Assigns a given group to another group (group nesting)
	 *
	 * @param  string  $group    id or name of the group to assign. This group must exist
	 * @param  string  $groupid  id of the group to assign to. This group must exist
	 *
	 * @throws  AuthException  if the requested group does not exist
	 * @throws  AuthException  if the group to assign to  does not exist
	 *
	 * @return  mixed  the id of the group assigned, or false if it failed
	 *
	 * @since 2.0.0
	 */
	public function assignGroupToGroup($group, $groupid);

	/**
	 * Return a list of all groups assigned to a user
	 *
	 * @param  string  $user   user to assign to. if not given, the current logged-in user will be used
	 *
	 * @return  array  assoc array with groupid => name
	 *
	 * @since 2.0.0
	 */
	public function getAssignedGroups($user = null);

	/**
	 * Return a list of all groups defined
	 *
	 * @return  array  assoc array with groupid => name
	 *
	 * @throws  AuthException  if there is no user
	 *
	 * @since 2.0.0
	 */
	public function getAllGroups();

	/**
	 * Return group information
	 *
	 * @param  string  $group    id or name of the group we need info of
	 * @param  string  $key      name of a group field to return
	 * @param  mixed   $default  value to return if no match could be found on key
	 *
	 * @throws  AuthException  if the requested group does not exist
	 *
	 * @return  mixed
	 *
	 * @since 2.0.0
	 */
	public function getGroup($group, $key = null, $default = null);

	/**
	 * Returns wether or not a user is member of the given group.
	 *
	 * If no user is specified, the current logged-in user will be checked.
	 *
	 * @param  string  $group  id or name of the group to be checked
	 * @param  string  $user   user to check. if not given, the current logged-in user will be checked
	 *
	 * @return  bool  true if a match is found, false if not
	 *
	 * @since 2.0.0
	 */
	public function isMemberOf($group, $user = null);

	/**
	 * Removes a given group from a user
	 *
	 * If no user is specified, the current logged-in user will be used.
	 *
	 * @param  string  $group  id or name of the group to remove. This group must be assigned
	 * @param  string  $user   user to check. if not given, the current logged-in user will be checked
	 *
	 * @throws  AuthException  if the requested group does not exist
	 * @throws  AuthException  if there is no user
	 *
	 * @return  mixed  the id of the group removed, or false if it failed
	 *
	 * @since 2.0.0
	 */
	public function removeUserFromGroup($group, $user = null);

	/**
	 * Removes a given group to another group (group nesting)
	 *
	 * @param  string  $group    id or name of the group to assign. This group must exist
	 * @param  string  $groupid  id of the group to assign to. This group must exist
	 *
	 * @throws  AuthException  if the requested group does not exist
	 * @throws  AuthException  if the group to assign to  does not exist
	 *
	 * @return  mixed  the id of the group removed, or false if it failed
	 *
	 * @since 2.0.0
	 */
	public function removeGroupFromGroup($group, $groupid);
}
