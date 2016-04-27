<?php

namespace Twig;

use Model;
use Di;
use Conf;

class Twig {

	public function __construct() {
		Di::get('Template')->addTwigFunctions(array(
			'getAvatar' => array($this, 'fnGetAvatar'),
		));
                Di::get('Template')->addTwigFunctions(array(
			'getRoomImage' => array($this, 'fnGetRoomImage'),
		));
	}

	public function fnGetAvatar($id) {
		return $this->getFile('files/user/', $id);
	}
        
        public function fnGetRoomImage($id) {
		return $this->getFile('files/rooms/', $id);
	}

	public function getFile($path, $id) {
		$file = glob(ABSPATH . $path . md5($id) . '.*');
		if (count($file))
			$file = pathinfo(reset($file), PATHINFO_BASENAME);
		else
			$file = 'default.jpg';
		return Di::get('Router')->getUrl() . '/' . $path . $file;
	}

}
