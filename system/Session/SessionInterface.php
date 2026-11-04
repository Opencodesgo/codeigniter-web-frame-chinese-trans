<?php
/**
 * CodeIgniter，会话，会话接口
 */

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Session;

/**
 * Expected behavior of a session container used with CodeIgniter.
 * 使用CodeIgniter的会话容器的预期行为。
 */
interface SessionInterface
{
	/**
	 * Regenerates the session ID.
	 * 重新生成会话ID
	 *
	 * @param boolean $destroy Should old session data be destroyed?
	 */
	public function regenerate(bool $destroy = false);

	//--------------------------------------------------------------------

	/**
	 * Destroys the current session.
	 * 销毁当前会话
	 */
	public function destroy();

	//--------------------------------------------------------------------

	/**
	 * Sets user data into the session.
	 * 将用户数据设置到会话中。
	 *
	 * If $data is a string, then it is interpreted as a session property
	 * key, and  $value is expected to be non-null.
	 * 如果 $data 是字符串，则将其解释为会话属性键，且 $value 预期不为空。
	 *
	 * If $data is an array, it is expected to be an array of key/value pairs
	 * to be set as session properties.
	 * 如果 $data 是一个数组，则期望它是一个键值对的数组，用于设置会话属性。
	 *
	 * @param string|array $data  Property name or associative array of properties
	 * @param mixed        $value Property value if single key provided
	 */
	public function set($data, $value = null);

	//--------------------------------------------------------------------

	/**
	 * Get user data that has been set in the session.
	 * 获取已在会话中设置的用户数据。
	 *
	 * If the property exists as "normal", returns it.
	 * Otherwise, returns an array of any temp or flash data values with the
	 * property key.
	 * 如果属性存在且为“正常”状态，则返回该属性；否则，返回一个包含该属性键的临时或闪存数据值的数组。
	 *
	 * Replaces the legacy method $session->userdata();
	 *
	 * @param string $key Identifier of the session property to retrieve
	 *
	 * @return mixed The property value(s)
	 */
	public function get(string $key = null);

	//--------------------------------------------------------------------

	/**
	 * Returns whether an index exists in the session array.
	 * 返回索引是否存在于会话数组中
	 *
	 * @param string $key Identifier of the session property we are interested in.
	 *
	 * @return boolean
	 */
	public function has(string $key): bool;

	//--------------------------------------------------------------------

	/**
	 * Remove one or more session properties.
	 * 删除一个或多个会话属性。
	 *
	 * If $key is an array, it is interpreted as an array of string property
	 * identifiers to remove. Otherwise, it is interpreted as the identifier
	 * of a specific session property to remove.
	 * 如果 $key 是一个数组，则将其解释为要删除的字符串属性标识符的数组；
	 * 否则，将其解释为要删除的特定会话属性的标识符。
	 *
	 * @param string|array $key Identifier of the session property or properties to remove.
	 */
	public function remove($key);

	//--------------------------------------------------------------------

	/**
	 * Sets data into the session that will only last for a single request.
	 * Perfect for use with single-use status update messages.
	 * 将数据放入会话中，该会话仅在单个请求期间有效。非常适合用于一次性状态更新消息。
	 *
	 * If $data is an array, it is interpreted as an associative array of
	 * key/value pairs for flashdata properties.
	 * Otherwise, it is interpreted as the identifier of a specific
	 * flashdata property, with $value containing the property value.
	 *
	 * @param string|array $data  Property identifier or associative array of properties
	 * @param string|array $value Property value if $data is a scalar
	 */
	public function setFlashdata($data, $value = null);

	//--------------------------------------------------------------------

	/**
	 * Retrieve one or more items of flash data from the session.
	 * 从会话中检索一个或多个flash数据项。
	 *
	 * If the item key is null, return all flashdata.
	 * 如果项键为空，则返回所有flashdata。
	 *
	 * @param  string $key Property identifier
	 * @return array|null	The requested property value, or an associative
	 *     array  of them
	 */
	public function getFlashdata(string $key = null);

	//--------------------------------------------------------------------

	/**
	 * Keeps a single piece of flash data alive for one more request.
	 * 为另一个请求保留一块flash数据
	 *
	 * @param array|string $key Property identifier or array of them
	 */
	public function keepFlashdata($key);

	//--------------------------------------------------------------------

	/**
	 * Mark a session property or properties as flashdata.
	 * 将一个或多个会话属性标记为flashdata
	 *
	 * @param string|array $key Property identifier or array of them
	 *
	 * @return False if any of the properties are not already set
	 */
	public function markAsFlashdata($key);

	//--------------------------------------------------------------------

	/**
	 * Unmark data in the session as flashdata.
	 * 取消将会话中的数据标记为flashdata
	 *
	 * @param string|array $key	Property identifier or array of them
	 */
	public function unmarkFlashdata($key);

	//--------------------------------------------------------------------

	/**
	 * Retrieve all of the keys for session data marked as flashdata.
	 * 检索标记为flashdata的会话数据的所有键
	 *
	 * @return array	The property names of all flashdata
	 */
	public function getFlashKeys(): array;

	//--------------------------------------------------------------------

	/**
	 * Sets new data into the session, and marks it as temporary data
	 * with a set lifespan.
	 * 将新数据放入会话中，并将其标记为具有设定生命周期的临时数据。
	 *
	 * @param string|array $data  Session data key or associative array of items
	 * @param mixed        $value Value to store
	 * @param integer      $ttl   Time-to-live in seconds
	 */
	public function setTempdata($data, $value = null, int $ttl = 300);

	//--------------------------------------------------------------------

	/**
	 * Returns either a single piece of tempdata, or all temp data currently
	 * in the session.
	 * 返回单个临时数据项，或当前会话中的所有临时数据。
	 *
	 * @param  string $key Session data key
	 * @return mixed        Session data value or null if not found.
	 */
	public function getTempdata(string $key = null);

	//--------------------------------------------------------------------

	/**
	 * Removes a single piece of temporary data from the session.
	 * 从会话中删除单个临时数据
	 *
	 * @param string $key Session data key
	 */
	public function removeTempdata(string $key);

	//--------------------------------------------------------------------

	/**
	 * Mark one of more pieces of data as being temporary, meaning that
	 * it has a set lifespan within the session.
	 * 将多个数据项标记为临时，表示它们在会话期间具有固定的生命周期。
	 *
	 * @param string|array $key Property identifier or array of them
	 * @param integer      $ttl Time to live, in seconds
	 *
	 * @return boolean    False if any of the properties were not set
	 */
	public function markAsTempdata($key, int $ttl = 300);

	//--------------------------------------------------------------------

	/**
	 * Unmarks temporary data in the session, effectively removing its
	 * lifespan and allowing it to live as long as the session does.
	 * 在会话中清除临时数据，使其生命周期被有效终止，并能与会话持续存在。
	 *
	 * @param string|array $key	Property identifier or array of them
	 */
	public function unmarkTempdata($key);

	//--------------------------------------------------------------------

	/**
	 * Retrieve the keys of all session data that have been marked as temporary data.
	 * 检索已标记为临时数据的所有会话数据的键
	 *
	 * @return array
	 */
	public function getTempKeys(): array;

	//--------------------------------------------------------------------
}
