<?php
class Database
{
    public function __construct(string $host, string $name,string $user,string $password){
        $this->host=$host;
        $this->name=$name;
        $this->user=$user;
        $this->password=$password;
    }

    public function getConnection(){
        try {
            $dsn = 'sqlite:'.dirname(__FILE__).'/bdd sqlite3/bdd.db';
            return new PDO($dsn);

            /*
            Data base Mysql :

            $dsn = "mysql:host=".$this->host.";dbname=".$this->name.";charset=utf8";
            return new PDO($dsn, $this->user, $this->password,
                [PDO::ATTR_EMULATE_PREPARES=>false,
                PDO::ATTR_STRINGIFY_FETCHES=>false]
            );
            */

        }catch(Exception $exception){
            echo "Impossible d'accéder à la base de données SQLite : ";

            die('Erreur : '.$exception->getMessage());
        }
    }

}
