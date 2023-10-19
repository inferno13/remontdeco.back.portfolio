<?php

class Users extends ENGINE
{
    public $module = __CLASS__;
    public $operation = "index";
    public $model = __CLASS__ . "Model";

    public $h1 = "Пользователи";

    public $data = Array();

    public function index() {

        $this->module = strtolower($this->module);

        switch ($_GET['action']) {

            //case "add" :
            //$result['data'] = $Engine->add();
            //break;

            default :
                $result['data'] = $this->get();
                break;
        }

        return $result;
    }

    //////////////////////
    public function all()
    {
        return false;
    }

    //////////////////////
    public function get()
    {
        return false;
    }

    //////////////////////
    public function add()
    {
        return false;
    }

    //////////////////////
    public function edit()
    {
        return false;
    }

    //////////////////////
    public function delete()
    {
        return false;
    }
}