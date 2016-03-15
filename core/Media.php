<?php

namespace core;

// Bład brak inplementacji !!!
class Media {

    private static $media;
    private $key;

    function __construct() {
        if (!self::$media)
            ; // $this=self::$media;
        else {

            $image_id = 1;
            while (file_exists($uploaddir . $image_id . '.jpg')) {
                ++$image_id;
            }
            $file = $uploaddir . $image_id . '.jpg';
        }
    }

}
