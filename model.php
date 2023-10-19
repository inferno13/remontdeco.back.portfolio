<?php

class Model {

    public $db;
    public $table;

    public function __construct() {

        global $db, $user;
        $this->db = $db;

        $this->table = $this::CLASS;
        $this->table = str_replace('Model','',$this->table);
        $this->table = strtolower($this->table);

        return false;
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function alert($mas,$die=0) {

        echo "<pre>";
        print_r($mas);
        echo "</pre>";
        echo "<hr>";
        if($die) {
            die();
        }
    }
}