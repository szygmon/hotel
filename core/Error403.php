<?php

class Error403 extends Exception {

	function view() {
		$template = Di::get('Template');
		$template->render(new Core\Response(array(), 'error403'));
	}

}
