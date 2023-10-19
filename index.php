<?php

@session_start();
@error_reporting(7);
@ini_set( 'display_errors',false );
@ini_set( 'html_errors',false );

header("Access-Control-Allow-Origin: *");

define("ROOT",dirname(__FILE__));
define("ROOT_DIR",dirname(__FILE__));
//date_default_timezone_set(TIMEZONE);

require_once 'config.php';
require_once 'engine.php';
require_once 'mysql.php';

if(file_exists(ROOT . "/classes/" . $_GET['module'] . ".php")) {

    require ROOT.'/classes/'.$_GET['module'].'.php';

    $Engine = new $_GET['module']();

    $result = $Engine->index();

    if ($result['data'] AND !$result['data']['error']) {
        $result['result'] = "success";
    } else {
        $result['result'] = "error";
        $result['error'] = $result['data']['error'];
    }

    print json_encode($result);

} else print "Error 404";

die();