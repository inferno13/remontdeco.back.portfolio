<?php

/*
CREATE TABLE `tm_users` (
  `id` int(11) NOT NULL,
  `date_create` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `role` varchar(255) NOT NULL DEFAULT 'user',
  `name` varchar(255) NOT NULL DEFAULT '',
  `email` varchar(255) NOT NULL DEFAULT '',
  `password` varchar(255) DEFAULT NULL,
  `reg_ip` varchar(255) NOT NULL DEFAULT '',
  `log_ip` varchar(255) NOT NULL DEFAULT '',
  `ip` varchar(255) NOT NULL DEFAULT '',
  `token` varchar(255) NOT NULL DEFAULT '' COMMENT 'online',
  `recovery` varchar(255) NOT NULL DEFAULT '',
  `confirm` varchar(255) NOT NULL DEFAULT '',
  `active` int(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
*/

class UsersModel extends Model {

    public function all() {
        return false;
    }

    public function get() {
        return false;
    }

    public function add($data) {

        $this->db->query("INSERT INTO ".PREFIX."_".$this->table." 
        
                (
                
                `name`,
                `email`,
                `password`,
                `reg_ip`
                
                ) VALUES (

                '".$data['name']."',
                '".$data['email']."',
                '".$data['password']."',
                '".$_SERVER["REMOTE_ADDR"]."'

                )");

        return $this->db->insert_id();
    }

    public function edit() {
        return false;
    }

    public function delete() {
        return false;
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    public function enter($data) {

        return $this->db->super_query( "SELECT 

        id,
        role,
        name,
        email

        FROM ".PREFIX."_users
        
        WHERE email = '".$data['email']."'
        AND password = '".$data['password']."' 
        AND confirm = 'confirmed' 
        AND active = '1'" );
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    public function out($data) {

        return $this->db->query( "UPDATE ".PREFIX."_users 
       
        SET token='' 
        
        WHERE token = '".$data['token']."'" );
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    public function setToken($data) {

        return $this->db->query( "UPDATE ".PREFIX."_users 
       
        SET 
        token='".md5($data['user_token'])."', 
        log_ip='".$_SERVER["REMOTE_ADDR"]."' 
        
        WHERE id = '".$data['user_id']."'" );
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    public function getRecovery($data) {

        $row = $this->db->super_query( "SELECT id 

        FROM ".PREFIX."_users 
        
        WHERE recovery='".$data['recovery']."'" );

        if($row['id'] > 0) {
            return true;
        }

        return false;
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    public function setRecovery($data) {

        return $this->db->query( "UPDATE ".PREFIX."_users
       
        SET recovery='".$data['recovery']."'
        
        WHERE email = '".$data['email']."'" );
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    public function getConfirm($data) {

        $row = $this->db->super_query( "SELECT id 

        FROM ".PREFIX."_users 
        
        WHERE confirm='".$data['confirm']."'" );

        if($row['id'] > 0) {
            return true;
        }

        return false;
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    public function getMailByConfirm($data) {

        $row = $this->db->super_query( "SELECT email  

        FROM ".PREFIX."_users 
        
        WHERE confirm='".$data['confirm']."'" );

        return $row['email'];
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    public function setConfirm($data) {

        return $this->db->query( "UPDATE ".PREFIX."_users 
       
        SET confirm='".$data['confirm']."' 
        
        WHERE email='".$data['email']."' 
        AND confirm=''" );
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    public function changeConfirm($data) {

        $this->db->query("DELETE 

        FROM ".PREFIX."_users 
        
        WHERE email='".$data['email']."' 
        AND confirm!='".$data['confirm']."'");

        return $this->db->query("UPDATE ".PREFIX."_users 
       
        SET confirm='confirmed', 
        active='1' 
        
        WHERE confirm='".$data['confirm']."'" );
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    public function confirmed($data) {

        $row = $this->db->super_query( "SELECT confirm 

        FROM ".PREFIX."_users
         
        WHERE email = '".$data['email']."'" );

        if($row['confirm'] == "confirmed") {
            return true;
        }

        return false;
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    public function getEmail($email) {

        return $this->db->super_query( "SELECT id 
        
        FROM ".PREFIX."_users 
        
        WHERE email='".$email."' 
        AND confirm='confirmed' 
        
        " );
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    public function getUserByToken($token) {

        $this->db->query( "UPDATE ".PREFIX."_users
       
        SET ip='".$_SERVER["REMOTE_ADDR"]."'
        
        WHERE token = '".$token."'" );

        return $this->db->super_query( "SELECT 

        id,
        role,
        name,
        email

        FROM ".PREFIX."_users
        
        WHERE token = '".md5($token)."'
        AND active = '1'" );
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    public function takeUserNameByEmail($data) {

        return $this->db->super_query( "SELECT name
        
        FROM ".PREFIX."_users 
        
        WHERE email='".$data['email']."'" );
    }


    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    public function changePassword($data) {

        return $this->db->query("UPDATE ".PREFIX."_users 
       
        SET 
        password = '" . $data['password'] . "', 
        recovery='' 
        
        WHERE recovery='" . $data['recovery'] . "'");
    }

}

$UsersModel = new UsersModel;