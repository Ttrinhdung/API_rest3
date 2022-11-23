<?php

declare(strict_types=1);
spl_autoload_register(function ($class){
    require __DIR__ . "/src/$class.php";
});
header("Content-type: application/json; charset=UTF-8");

$uri =explode("/",$_SERVER["REQUEST_URI"]);

//Je setup mes variables $id_user et $id_widget a null si elles ne sont pas déclarés dans le $_POST sinon je récupère leurs valeurs.
$id_user = null;
if(isset($_POST['id_user'])){
    $id_user=(int)$_POST['id_user'];
}
$id_widget = null;
if(isset($_POST['id_widget'])){
    $id_widget=(int)$_POST['id_widget'];
}
$db = new Database("localhost","API rest","root","root");

//Selon l'URL j'effectue une action POST
$arrayPost=array("register","login","addWidget","getWidget","updateWidget","checkHash");
if($uri[1]=="API_rest" AND in_array($uri[2],$arrayPost)AND $_SERVER["REQUEST_METHOD"]=="POST" ){
    $action=$uri[2];
    $customerGateway = new CustomerGateway($db);
    $widgetGateway = new WidgetGateway($db);
    $controller = new PostController($customerGateway,$widgetGateway);
    $controller->procesRequestPost($action, $id_user,$id_widget);

}
//Sinon je renvoi une erreur 404 page not found
else{
    http_response_code(404);
    exit;
}
