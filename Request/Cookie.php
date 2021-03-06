<?php

class PPI_Request_Cookie extends PPI_Request_Abstract {
	/**
	 * Constructor
	 *
	 * Stores the given cookies or tries to fetch
	 * cookies if the given array is empty or not
	 * given
	 *
	 * @param array $cookies
	 */
	function __construct(array $cookies = array() ) {
		if($cookies !== null) {
			$this->_array       = $cookies;
			$this->_isCollected = false;
		} else {
			$this->_array = $_COOKIE;
		}
	}

	/**
	 * Set an offset
	 *
	 * Required by ArrayAccess interface
	 *
	 * Note: PPI_Request should be smart enough to set an array
	 *
	 * @param string $offset
	 * @param string $value
	 *
	 * @return void
	 */

	function offsetSet($offset, $value) {
		if ($value === null) {
			return $this->offsetUnset($offset);
		}

		// Handle cookie parameters - TODO
		list($name, $content, $expire, $path, $domain, $secure, $httponly) = $value;

		$this->_array[$offset] = $content;

		if ($this->_isCollected) {
			setcookie($name, $content, $expire, $path, $domain, $secure, $httponly);
		}
	}

	/**
	 * Unset an offset
	 *
	 * Required by ArrayAccess interface
	 *
	 * @param string $offset
	 *
	 * @return void
	 */

	function offsetUnset($offset) {
		$this->_array[$offset] = null;

		if ($this->_isCollected) {
			setcookie($offset, null, time() - 3600);
		}
	}

}