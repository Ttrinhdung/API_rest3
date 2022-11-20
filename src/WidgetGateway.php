<?php

class WidgetGateway
{
    private PDO $conn;
    public function __construct(Database $database){
        $this->conn=$database->getConnection();
    }

    /*
     * Créer un widget dans la table widget.
     * @param array data
     * @param $id_user
     */
    public function addWidget(array $data,int $id_user){

        $sql="INSERT INTO widget(id_user,domain,hash) VALUES ($id_user,?,?)";
        $statement = $this->conn->prepare($sql);
        $statement->execute(array($data['domain'],$data['hash']));

        return $this->conn->lastInsertId();
    }

    /*
     * Renvoi le hash du widget donné en paramètre.
     * @param id_user
     * @param id_widget
     */
    public function recupHash(int $id_user,int $id_widget){
        $sql="SELECT hash FROM widget WHERE id_user = ? AND id=?";
        $statement = $this->conn->prepare($sql);
        $statement->execute(array($id_user,$id_widget));

        $hash = $statement->fetch(PDO::FETCH_ASSOC);
        return $hash;
    }

    /*
     * Renvoi un tableau de tout les widgets de l'utilisateur donné en paramètre.
     * @param id_user
     */
    public function recupAllWidgetFromCustomer(int $id_user){
        $sql="SELECT widget.id_user,id,domain,hash FROM widget INNER JOIN customer ON widget.id_user =customer.id_user WHERE widget.id_user=$id_user";
        $statement = $this->conn->prepare($sql);
        $statement->execute();
        $data = [];
        while ($row = $statement->fetch(PDO::FETCH_ASSOC)){
            $data[]=$row;
        }
        return $data;
    }

    /*
     * Met a jour le domain du widget donné en paramètre
     * @param array data
     * @param $id_widget
     */
    public function updateWidget(array $data,int $id_widget){
        $sql="UPDATE widget SET domain=? WHERE id=$id_widget";
        $statement = $this->conn->prepare($sql);
        $statement->execute(array($data["domain"]));
    }

    /*
     * Génère une clé hash de 10caractère
     * @param $longeur
     */
    function genererChaineAleatoire($longueur = 10)
    {
        $caracteres = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $longueurMax = strlen($caracteres);
        $chaineAleatoire = '';
        for ($i = 0; $i < $longueur; $i++)
        {
            $chaineAleatoire .= $caracteres[rand(0, $longueurMax - 1)];
        }
        return $chaineAleatoire;
    }

    /*
     * Vérifie la valdité du hash donné en paramètre
     * @param array data
     */
    public function checkHash(array $data){
        $isValid=False;
        $sql="SELECT * FROM widget WHERE hash=? AND domain=?";
        $statement = $this->conn->prepare($sql);
        $statement->execute(array($data['hash'],$data['domain']));

        $row = $statement->fetchAll(PDO::FETCH_ASSOC);
        if (!empty($row)){
            $isValid=True;
        }
        return $isValid;
    }

}