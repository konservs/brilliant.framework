<?php
/**
 * SocialAuther
 *
 * @author: Andrii Biriev
 */

interface BSocialAdapterInterface{
	/**
	 * Authenticate and return bool result of authentication
	 *
	 * @return bool
	 */
	public function authenticate();
	}