<?php

class PostController
{
    private CustomerGateway $customerGateway;
    private WidgetGateway $widgetGateway;

    public function __construct(CustomerGateway $customerGateway,WidgetGateway $widgetGateway)
    {
        $this->customerGateway=$customerGateway;
        $this->widgetGateway=$widgetGateway;
    }

    /*
     * Fonction qui permet d'effectuer les requêtes POST de notre API'
     * @param $action : le string qui permet d'appeler la bonne fonction
     */
    public function procesRequestPost(string $action)
    {

        switch ($action) {
            case "register":
                $this->register();
                break;

            case "login":
                $this->login();
                break;

            case "addWidget":
                $this->addWidget();
                break;

            case "getWidget":
                $this->getWidget();
                break;

            case "updateWidget":
                $this->updateWidget();
                break;

            case "checkHash":
                $this->checkhash();
                break;
        }
    }


    /////////////////////////////////////////////////// FONCTION GESTION ERREUR DE DONNEE///////////////////////////////////////////////////////////////////////////////
    /*
    * Renvoi un message d'erreur si des données sont manquantes
    * @param array data : Les données récupérées via $_POST
    */
    private function getValidationErrorsLogin(array $data){
        $errors=[];

        if(empty($data['email'])){
            $errors[]= 'Email required';
        }
        if(empty($data['password'])){
            $errors[]= 'Password required';
        }
        $this->afficherError($errors);
        return null;
    }

    /*
    * Renvoi un message d'erreur si des données sont manquantes
    * @param array data : Les données récupérées via $_POST
    */
    private function getValidationErrorsRegister(array $data){
        $errors=[];

        if(empty($data['name'])){
            $errors[]= 'Name required';
        }
        if(empty($data['email'])){
            $errors[]= 'Email required';
        }
        if(empty($data['firstname'])){
            $errors[]= 'Firstname required';
        }
        if(empty($data['password'])){
            $errors[]= 'Password required';
        }
        $this->afficherError($errors);
        return null;
    }

    /*
     * Renvoi un message d'erreur si des données sont manquantes
     * @param array data : Les données récupérées via $_POST
     */
    private function getValidationErrorsAddWidget(array $data){
        $errors=[];

        if(empty($data['id_user'])){
            $errors[]= 'Id user required';
        }
        if(empty($data['domain'])){
            $errors[]= 'Domain required';
        }
        $this->afficherError($errors);
        return null;
    }

    /*
    * Renvoi un message d'erreur si des données sont manquantes
    * @param array data : Les données récupérées via $_POST
    */
    private     function getValidationErrorsGetWidget(array $data){
        $errors=[];

        if(empty($data['id_user'])){
            $errors[]= 'Id user required';
        }

        $this->afficherError($errors);
        return null;
    }
    /*
    * Renvoi un message d'erreur si des données sont manquantes
    * @param array data : Les données récupérées via $_POST
    */
    private function getValidationErrorsUpdateWidget(array $data){
        $errors=[];

        if(empty($data['domain'])){
            $errors[]= 'Domain required';
        }
        if(empty($data['id_widget'])){
            $errors[]= 'ID widget required';
        }
        $this->afficherError($errors);
        return null;
    }

    /*
     * Renvoi un message d'erreur si des données sont manquantes
     * @param array data : Les données récupérées via $_POST
     */
    private function getValidationErrorsCheckHash(array $data){
        $errors=[];

        if(empty($data['hash'])){
            $errors[]= 'Hash required';
        }
        if(empty($data['domain'])){
            $errors[]= 'Domain required';
        }
        $this->afficherError($errors);
        return null;
    }


    /*
     * Affiche un message d'erreur si le  contenu  d'$errors n'est pas vide
     */
    private function afficherError($errors){
        if(!empty($errors)){
            http_response_code(422);
            echo json_encode([
                "error(s)"=>$errors
            ]);
            exit;
        }
    }



    /////////////////////////////////////////////////// FONCTION QUI REALISE LES ACTIONS///////////////////////////////////////////////////////////////////////////////

    /*
    * Effectue l'action register et vérifie au préalable que les données nécessaires sont présentes ou que l'email ne soit pas déjà utilisé.
    * Return l'id du customer crée.
    */
    private function register(){
        $data = $_POST;

        //J'affiche un message d'erreur si le nom,prénom,password ou email n'est pas présent dans $data.
        $this->getValidationErrorsRegister($data);

        //Je check si le nom d'utilisateur est disponible
        $this->customerGateway->checkEmailAvailable($data);

        //Je créer un customer dans la bdd avec les données récupérées dans $data.
        $data["password"]=md5($data['password']);
        $id=$this->customerGateway->registerCustomer($data);

        echo json_encode([
            "message"=>"Customer create ",
            "id"=>$id]);
        return $id;

    }
    /*
    * Effectue l'action login et vérifie au préalable que les données nécessaires sont présentes.
    * Return l'id du customer logged.
    */
    private function login(){
        $data = $_POST;
        $data["password"]=md5($data['password']);

        //J'affiche un message d'erreur si l'email ou le password n'est pas présent dans $data.
        $this->getValidationErrorsLogin($data);

        //Je teste si l'utilisateur est dans la base de données si oui je renvoie l'id sinon je renvoie false.
        $id=$this->customerGateway->loginCustomer($data);
        if($id){
            echo json_encode([
                "message"=>"Customer does exist",
                "idCustomer"=>$id
            ]);
        }else{
            echo json_encode([
                "message"=>"Customer does not exist"
            ]);
        }
        return $id;
    }

    /*
    * Effectue l'action addWidget et vérifie au préalable que les données nécessaires sont présentes ou que le domain ne soit pas déjà utilisé.
    * Return l'id et le hash du widget crée.
    */
    private function addWidget(){
        $data = $_POST;

        //J'affiche un message d'erreur si l'id_user ou le domain n'est pas présent dans $data.
        $this->getValidationErrorsAddWidget($data);

        //Je check si le domain est disponible
        $this->widgetGateway->checkDomainAvailable($data);

        //Je check si l'Id_user existe dans la base de données.
        $this->widgetGateway->checkIdUserExist($data);

        //Je génère une clé hash de 10caractères pour la création de mon widget.
        $data["hash"]=$this->widgetGateway->genererChaineAleatoire(10);

        //Je créer un widget et retourne l'id et le hash.
        $id_widget_added = $this->widgetGateway->addWidget($data);

        $hash = $this->widgetGateway->recupHash($data,$id_widget_added);
        $id_hash = ["id"=>$id_widget_added,"hash"=>$hash];

        if($id_hash){
            echo json_encode([
                "id"=>$id_hash["id"],
                "hash"=>$id_hash["hash"]
            ]);
            return $id_hash;
        }
    }


    /*
    * Effectue l'action getWidget et vérifie au préalable que les données nécessaires sont présentes.
    * Return la liste des widget du customer donnée en paramètre.
    */
    private function getWidget(){
        $data=$_POST;
        //J'affiche un message d'erreur si l'id_user n'est pas présent dans $data.
        $this->getValidationErrorsGetWidget($data);

        //Récupère tout les widget d'un client
        $all_widget_user= $this->widgetGateway->recupAllWidgetFromCustomer($data);
        var_dump($all_widget_user);
        return $all_widget_user;
    }

    /*
    * Effectue l'action updateWidget et vérifie au préalable que les données nécessaires sont présentes.
    * Return rien.
    */
    private function updateWidget(){
        $data =$_POST;
        //J'affiche un message d'erreur si le domain ou le id_widget ne sont pas présent dans $data.
        $this->getValidationErrorsUpdateWidget($data);

        //Je vérifie que le nouveau domain n'est pas deja utilisé
        $this->widgetGateway->checkDomainAvailable($data);

        //Je met a jour le domain du widget dans la bdd.
        $this->widgetGateway->updateWidget($data);
        echo json_encode([
            "id_widget"=>$data['id_widget'],
            "Widget domain updated to "=>$data["domain"]
        ]);

    }

    /*
    * Effectue l'action chechash et vérifie au préalable que les données nécessaires sont présentes.
    * Return True ou false.
    */
    private function checkhash(){
        $data=$_POST;

        //J'affiche un message d'erreur si le domain ou le hash n'est pas présent dans $data.
        $this->getValidationErrorsCheckHash($data);


        //Je teste la validité du hash reçu par le $_POST.
        $isValid=$this->widgetGateway->checkHash($data);
        echo json_encode([
            "Hash valide ?"=>$isValid
        ]);
        return $isValid;

    }
}
