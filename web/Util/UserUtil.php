<?php

use \Model\School;

class UserUtil {

	/** @var \User\Me */
	protected $Me;

	/**
	 * @param \User\Me $Me
	 */
	function __construct() {
		$this->Me = Di::get('Me');
	}

	/**
	 * 
	 * @param \Model\User $user
	 * @param Array $post
	 * @return null
	 */
	public function edit($user, $post) {
		if ($post) {
			$user->setEmail($post['email']);

			if ($post['pass'] != '') {
				if ($post['pass'] != $post['pass2']) {
					Notify::error('Hasła sie nie zgadzają');
					return;
				}
			}
			$user->setPassword($post['pass']);
			Di::get('em')->flush();
		}
	}

}
