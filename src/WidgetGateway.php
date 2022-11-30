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
     */
    public function addWidget(array $data){

        $sql="INSERT INTO widget(id_user,domain,hash) VALUES (?,?,?)";
        $statement = $this->conn->prepare($sql);
        $statement->execute(array($data['id_user'],$data['domain'],$data['hash']));

        return $this->conn->lastInsertId();
    }
    /*
     * Vérifie si le domain n'est pas deja utilisé dans la base de données
     * Return un message d'erreur si le domain existe déjà  sinon return true
     * @param array data
     */
    public function checkDomainAvailable(array $data){

        $sql = "SELECT domain FROM widget WHERE domain=?";
        $statement = $this->conn->prepare($sql);
        $statement->execute(array($data['domain']));

        $errors=$statement->fetch(PDO::FETCH_ASSOC);
        if(!empty($errors)){
            http_response_code(422);
            echo json_encode([
                "error(s)"=>"Domain is already taken"
            ]);
            exit;
        }
        return true;
    }

    /*
     * Renvoi le hash du widget donné en paramètre.
     * @param $data
     * @param $id_widget_added
     */
    public function recupHash(array $data,int $id_widget_added){
        $sql="SELECT hash FROM widget WHERE id_user = ? AND id=$id_widget_added";
        $statement = $this->conn->prepare($sql);
        $statement->execute(array($data['id_user'],$id_widget_added));

        $hash = $statement->fetch(PDO::FETCH_ASSOC);
        return $hash;
    }

    /*
     * Renvoi un tableau de tout les widgets de l'utilisateur donné en paramètre.
     * @param $data
     */
    public function recupAllWidgetFromCustomer(array $data){
        $sql="SELECT widget.id_user,id,domain,hash FROM widget INNER JOIN customer ON widget.id_user =customer.id_user WHERE widget.id_user=?";
        $statement = $this->conn->prepare($sql);
        $statement->execute(array($data['id_user']));
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
    public function updateWidget(array $data){
        $sql="UPDATE widget SET domain=? WHERE id=?";
        $statement = $this->conn->prepare($sql);
        $statement->execute(array($data["domain"],$data['id_widget']));
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

    /*
    * Vérifie si le user_id est présent dans la base de données
    * Return un message d'erreur si l'id_widget n'existe pas sinon return true
    * @param array data
    */
    public function checkIdWidgetExist(array $data){

        $sql="SELECT id FROM widget WHERE id=?";

        $statement = $this->conn->prepare($sql);
        $statement->execute(array($data['id_widget']));

        $errors=$statement->fetch(PDO::FETCH_ASSOC);
        if(empty($errors)){
            http_response_code(422);
            echo json_encode([
                "error(s)"=>"Id_widget does not exist"
            ]);
            exit;
        }
        return true;
    }

}