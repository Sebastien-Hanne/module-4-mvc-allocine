<?php
require_once(__DIR__."/../controller/FilmController.php");
require_once(__DIR__."/../controller/DiffusionController.php");
require_once(__DIR__."/../controller/HomeController.php");
require_once(__DIR__."/../controller/NotFoundController.php");
require_once(__DIR__."/../model/FilmModel.php");
require_once(__DIR__."/../model/DiffusionModel.php");

class Router{
    /**
     * Analyse l'URI et retourne le contrôleur, l'action et les paramètres.
     * @param string $uri L'URI demandée (ex: /film/3)
     * @return array Un tableau [Controller, action, params]
     */
    public static function getRoute(string $uri): array 
    {
        // 1. Nettoyer l'URI (supprimer le slash initial et les query strings)
        $uri = trim(parse_url($uri, PHP_URL_PATH) ?? '', '/');
        
        // 2. Séparer les segments (film, 3, etc.)
        $segments = explode('/', $uri);
        $controllerName = $segments[0] ?? ''; // "film" ou ""
        
        // --- LOGIQUE DE ROUTAGE ---
        switch ($controllerName) {
            
            case 'film':
                // Route : /film/{id} (ex: /film/3)
                if (isset($segments[1]) && is_numeric($segments[1])) {
                    $filmId = $segments[1];
                    return [new FilmController(), 'show', [$filmId]]; // Appel FilmController::show([3])
                }
                // Route : /film (sans ID)
                // Vous devriez créer une méthode 'index' dans FilmController pour lister tous les films
                return [new NotFoundController(), 'view', []]; // Pas d'ID, affiche 404 par défaut
                
            case 'diffusion':
                // Route : /diffusion/{id} (ex: /diffusion/5)
                if (isset($segments[1]) && is_numeric($segments[1])) {
                    $diffusionId = $segments[1];
                    return [new DiffusionController(), 'show', [$diffusionId]]; // Appel DiffusionController::show([5])
                }
                return [new NotFoundController(), 'view', []];
                
            case '':
                // Route : /
                return [new HomeController(), 'view', []]; // J'ai renommé en 'view' par convention
            
            default:
                // Si aucune route ne correspond
                return [new NotFoundController(), 'view', []];
        }
    }
}