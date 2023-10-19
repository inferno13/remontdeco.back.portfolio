<?php

class Work extends ENGINE {

    public $module = __CLASS__;
    public $operation = "index";
    public $model = __CLASS__ . "Model";

    public $h1 = "Работа";

    public $content = Array();

    public $div_new = true;
    public $div_search = true;
    public $div_pagination = true;
    public $search_list = Array(
        'name'
    );

    public $data = Array();

    public function index() {

        $this->module = strtolower($this->module);

        switch($_GET['action']) {

            case "add" :

                $result['data'] = $this->add();
                break;

            case "view" :

                $result['data'] = $this->view();
                break;

            case "delete" :

                $result['data'] = $this->delete();
                break;

            default :

                $result['data'] = $this->get();
                break;
        }

        return $result;
    }

    ///////////////////////
    public function get() {

      $where = "";
        if ((int)$_GET['type'] < 3) {
         $where .= " AND type='".(int)$_GET['type']."' AND view='1' ";
       }

        if($this->validation($_POST['city'],"text",true)) {
            $where .= " AND (city='".$_POST['city']."' OR city='all') ";
        }

        $query = $this->db->query( "SELECT * 
        FROM ".PREFIX."_".$this->module." 
        WHERE id>0 
        ".$where." 
        ORDER BY date_add DESC 
        LIMIT 1000" );
        while($row = $this->db->get_row( $query )) {

            $this->content[$row["id"]] = $row;
        }

        return $this->content;
    }

    ///////////////////////
    public function add() {

        if($this->validation($_GET['type'],"int",true)) {
            $_type = $_GET['type'];
        } else {
            return ['error' => "Неверно указан Тип обьявления"];
        }

        if($this->validation($_POST['city'],"text",true)) {
            $_city = $_POST['city'];
        } else {
            return ['error' => "Неверно указано поле Город"];
        }

        if($this->validation($_POST['name'],"text",true)) {
            $_name = $_POST['name'];
        } else {
            return ['error' => "Неверно введено поле Название компании"];
        }
        if($this->validation($_POST['spec'],"text",true)) {
            $_spec = $_POST['spec'];
        } else {
            return ['error' => "Неверно введено поле Специализация"];
        }
        if($this->validation($_POST['col'],"int",true)) {
            $_col = $_POST['col'];
        } else {
            return ['error' => "Неверно введено поле Количество человек"];
        }
        if($this->validation($_POST['text'],"text",true)) {
            $_text = $_POST['text'];
        } else {
            return ['error' => "Неверно введено поле Текст сообщения"];
        }
        if($this->validation($_POST['email'],"email",true)) {
            $_email = $_POST['email'];
        } else {
            return ['error' => "Неверно введено поле E-mail"];
        }
        if($this->validation($_POST['phone'],"text",true)) {
            $_phone = $_POST['phone'];
        } else {
            return ['error' => "Неверно введено поле Телефон"];
        }
        $_phone_on  = $_POST['phoneOn'];
        if($_FILES['photo']['name'] == "") {
            return ['error' => "Вы не добавили фотографию"];
        } elseif ($_FILES['photo']['size'] > 3000000) {
            return ['error' => "Фотография должна быть не более 3Mb"];
        } else {

            $_photo = $this->upload($_FILES['photo']);

            if(!$_photo) {
                return ['error' => "Произошла ошибка загрузки файла, попробуйте позднее"];
            }
        }

        $this->db->query("INSERT INTO ".PREFIX."_".$this->module." (

        `type`,
        `city`,
        
        `name`,
        `spec`,
        `col`,
        `text`,
        `email`,
        `phone`,
        `phone_on`,
        
        `photo`

        ) VALUES (

        '".$_type."',
        '".$_city."',
        
        '".$_name."',
        '".$_spec."',
        '".$_col."',
        '".$_text."',
        '".$_email."',
        '".$_phone."',
        '".$_phone_on."',
        
        '".$_photo."'

        )");

        return true;
    }

    ////////////////////////
    public function view() {

        $id = (int)$_POST['e']['view']['elem']['id'];

        if($this->validation($id,"int",true)) {

            $row = $this->db->super_query( "SELECT view FROM ".PREFIX."_".$this->module." WHERE id='".$id."'");

            $this->db->query("UPDATE ".PREFIX."_".$this->module." 
            SET view='".!$row['view']."' 
            WHERE id='".$id."' ");

            return true;

        } else {

            return ['error' => "Ошбика данных!"];
        }
    }

    //////////////////////////
    public function delete() {

        $id = (int)$_POST['e']['del']['elem']['id'];

        if($this->validation($id,"int",true)) {

            $this->db->query("DELETE FROM ".PREFIX."_".$this->module." WHERE id='".$id."'");

            return true;

        } else {

            return ['error' => "Ошбика данных!"];
        }
    }
}