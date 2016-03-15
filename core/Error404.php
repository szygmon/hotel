<?php

class Error404 extends Exception {

    function view() {
        $template = Di::get('Template');
        if(!$template)
             $template = new Core\Template();
        $template->render(new Core\Response(array(), 'error404'));
    }

}
