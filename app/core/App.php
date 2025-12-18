<?php
    require_once(__DIR__."/Router.php");
    require_once(__DIR__."/../model/FilmModel.php");
    require_once(__DIR__."/../model/DiffusionModel.php");

const ROOT_APP_PATH = "first_mvc"; // Cette constante est là pour être utilisée

class App{
    public static function start(){
        
        // 1. **Correction essentielle : Nettoyer l'URI**
        // Supprime le chemin de base (ex: /first_mvc) de l'URI.
        // str_replace supprime le premier 'first_mvc' trouvé dans l'URI
        $uri = str_replace(ROOT_APP_PATH,"",$_SERVER["REQUEST_URI"]);
        
        // 2. Appel du routeur avec l'URI nettoyée
        [$controller, $method, $params] = Router::getRoute($uri);
        
        // On exécute l'action sur le contrôleur
        if (method_exists($controller, 'view')) {
            $controller->view($method, $params);
        } else {
            // Pour les contrôleurs simples comme NotFoundController
            call_user_func([$controller, $method], $params); 
        }
    }
}