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
     * Fonction qui permet d'effectuer les réquêtes POST de notre API'
     * @param array data
     * @param $id_user
     * @param $id_widget
     */
    public function procesRequestPost(string $action ,  $id_user ,  $id_widget)
    {

        switch ($action) {
            case "register":
                $data = $_POST;
                $data["password"]=md5($data['password']);

                //J'affiche un message d'erreur si le nom,prenom,password ou email n'est pas présent dans $data.
                $errors = $this->getValidationErrorsRegister($data);
                if(!empty($errors)){
                    http_response_code(422);
                    echo json_encode([
                        "errors"=>$errors
                    ]);
                    break;
                }
                //Je check si le nom d'utilisateur est disponible
                $errors = $this->customerGateway->checkEmailAvailable($data);
                if(!empty($errors)){
                    http_response_code(422);
                    echo json_encode([
                        "errors"=>"Email is already taken"
                    ]);
                    break;
                }

                //Je créer un customer dans la bdd avec les données récupere dans $data.
                $id=$this->customerGateway->registerCustomer($data);

                echo json_encode([
                    "message"=>"Customer create ",
                    "id"=>$id]);
                return $id;

            case "login":
                $data = $_POST;
                $data["password"]=md5($data['password']);

                //J'affiche un message d'erreur si l'email ou le password n'est pas présent dans $data.
                $errors = $this->getValidationErrorsLogin($data);
                if(!empty($errors)){
                    http_response_code(422);
                    echo json_encode([
                        "errors"=>$errors
                    ]);
                    break;
                }

                //Je test si l'utilisateur est dans la base de donnée si oui je renvoi l'id sinon je renvoi false.
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

            case "addWidget":
                $data = $_POST;

                //J'affiche un message d'erreur si l'id_user ou le domain n'est pas présent dans $data.
                $errors = $this->getValidationErrorsAddWidget($data,$id_user);

                if(!empty($errors)){
                    http_response_code(422);
                    echo json_encode([
                        "errors"=>$errors
                    ]);
                    break;
                }

                //Je génère une clé hash de 10caractères pour la création de mon widget.
                $data["hash"]=$this->widgetGateway->genererChaineAleatoire(10);

                //Je créer un widget et retourne l'id et le hash.
                $id = $this->widgetGateway->addWidget($data,$id_user);

                $hash = $this->widgetGateway->recupHash($id_user,$id);
                $id_hash = ["id"=>$id,"hash"=>$hash];

                if($id_hash){
                    echo json_encode([
                        "id"=>$id_hash["id"],
                        "hash"=>$id_hash["hash"]
                    ]);
                    return $id_hash;

                }
                break;

            case "getWidget":

                //Recupère tout les widget d'un client
                $all_widget_user= $this->widgetGateway->recupAllWidgetFromCustomer($id_user);
                var_dump($all_widget_user);
                return $all_widget_user;


            case "updateWidget":
                $data =$_POST;
                //J'affiche un message d'erreur si le domain ou le id_widget ne sont pas présent dans $data.
                $errors = $this->getValidationErrorsUpdateWidget($data,$id_widget);
                if(!empty($errors)){
                    http_response_code(422);
                    echo json_encode([
                        "errors"=>$errors
                    ]);
                    break;
                }

                //Je met a jour le domain du widget dans la bdd.
                $this->widgetGateway->updateWidget($data,$id_widget);
                echo json_encode([
                    "id_widget"=>$id_widget,
                    "Widget domain updated to "=>$data["domain"]
                ]);

                break;

            case "checkHash":
                $data=$_POST;

                //J'affiche un message d'erreur si le domain ou le hash n'est pas présent dans $data.
                $errors = $this->getValidationErrorsCheckHash($data);
                if(!empty($errors)){
                    http_response_code(422);
                    echo json_encode([
                        "errors"=>$errors
                    ]);
                    break;
                }

                //Je test la validité du hash récu par le $_POST.
                $isValid=$this->widgetGateway->checkHash($data);
                echo json_encode([
                    "Hash valide ?"=>$isValid
                ]);
                return $isValid;


        }
    }

    /*
    * Renvoi un message d'érreur si des données sont manquantes
    * @param array data
    */
    private function getValidationErrorsLogin(array $data){
        $errors=[];

        if(empty($data['email'])){
            $errors[]= 'Email required';
        }
        if(empty($data['password'])){
            $errors[]= 'Password required';
        }

        return $errors;
    }

    /*
     * Renvoi un message d'érreur si des données sont manquantes
     * @param array data
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

        return $errors;
    }

    /*
     * Renvoi un message d'érreur si des données sont manquantes
     * @param array data
     * @param $id_user
     */
    private function getValidationErrorsAddWidget(array $data,$id_user){
        $errors=[];

        if(empty($id_user)){
            $errors[]= 'Id user required';
        }
        if(empty($data['domain'])){
            $errors[]= 'Domain required';
        }
        return $errors;
    }
    /*
     * Renvoi un message d'érreur si des données sont manquantes
     * @param array data
     * @param id_widget
     */
    private function getValidationErrorsUpdateWidget(array $data,$id_widget){
        $errors=[];

        if(empty($data['domain'])){
            $errors[]= 'Domain required';
        }
        if(empty($id_widget)){
            $errors[]= 'ID widget required';
        }

        return $errors;
    }

    /*
     * Renvoi un message d'érreur si des données sont manquantes
     * @param array data
     */
    private function getValidationErrorsCheckHash(array $data){
        $errors=[];

        if(empty($data['hash'])){
            $errors[]= 'Hash required';
        }
        if(empty($data['domain'])){
            $errors[]= 'Domain required';
        }
        return $errors;
    }
}
