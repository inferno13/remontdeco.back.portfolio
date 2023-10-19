<?PHP

    if($_SERVER["SERVER_NAME"] == "local.ru") { // Local

        //define(DBHOST,"172.25.158.47");
        //define(DBNAME,"Auriga");
        //define(DBUSER,"root");
        //define(DBPASS,"");
        //define(PREFIX,"hfdb");
        //define(COLLATE,"windows-1251");
        //define(TEST,true);

    } else { // Proda 
        define("DBSERV","mysqli");
        define("DBPORT","3306");
        define("DBHOST","localhost");
       define("DBNAME","u1885306_default");
        define("DBUSER","u1885306_default");
       define("DBPASS","SFdMdjX5L227eQT3");
        define("PREFIX","rd");
        define("COLLATE","utf8");
    }

    define("ROOT",__DIR__);
    define("UPLOAD",ROOT."/upload/");
    define("INTR",481);
    define("SITE",ROOT);
    define("EMAIL","inferno.13@mail.ru");
    define("ERROR","");
    define("PAGINATION",50);

?>
