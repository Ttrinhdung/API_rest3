<?php

class CustomerGateway
{
    private PDO $conn;
    public function __construct(Database $database){
    $this->conn=$database->getConnection();
    }

    public function getAllcustomer(){
            $sql="SELECT * FROM customer";

            $statement = $this->conn->query($sql);

            $data = [];
            while ($row = $statement->fetch(PDO::FETCH_ASSOC)){
                $data[]=$row;
            }
            return $data;
    }

    /*
     * Créer un nouveau client dans la table customer a partir des données récupérer dans le $_POST.
     * Return l'id du nouveau client.
     * @param array data
     */
    public function registerCustomer(array $data){
            $sql="INSERT INTO customer(name,firstname,email,password) 
                  VALUES (?,?,?,?)";
            $statement = $this->conn->prepare($sql);
            $statement->execute(array($data['name'],$data['firstname'],$data['email'],$data['password']));
            return $this->conn->lastInsertId();
    }

    /*
     * Vérifier si l'adresse mail n'est pas deja utilisé dans la base de donnée
     * Return True ou False
     * @param array data
     */
    public function checkEmailAvailable(array $data){

        $sql = "SELECT email FROM customer WHERE email=?";
        $statement = $this->conn->prepare($sql);
        $statement->execute(array($data['email']));

        return $statement->fetch(PDO::FETCH_ASSOC);
    }


    /*
     * Vérifie dans la bdd si l'email et le password récupérer dans le $_POST corresponde a un customer.
     * Si oui return l'id de l'utilisateur, Sinon false.
     * @param array data
     */
    public function loginCustomer(array $data){
        $sql="SELECT id_user FROM customer WHERE email=? AND password=?";

        $statement = $this->conn->prepare($sql);
        $statement->execute(array($data['email'],$data['password']));

        $id = $statement->fetch(PDO::FETCH_ASSOC);
        return $id;

    }
}