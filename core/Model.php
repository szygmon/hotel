<?php

namespace core;

class Model {

    private $models = array();

    function __construct() {
        // NONE
    }

    public function get($model) {
        if (isset($this->models[$model]))
            return $this->models[$model];
        $class = '\\model\\' .$model;
        return  $this->models[$model] = new $class;
    }

}
