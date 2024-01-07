<?php

class Routes
{
    /**
     * Méthode statique pour obtenir l'instance du routeur
     *
     * @return AltoRouter Retourne une instance d'AltoRouter contenant les routes définies
     */
    public static function getRouter(): AltoRouter
    {
        // Crée une nouvelle instance d'AltoRouter
        $router = new AltoRouter();

        // Définition des routes
        $router->map('GET', '/', 'c_accueilController#index', 'accueil');
        $router->map('GET|POST', '/test', 'c_testController#index', 'test');
        $router->map('GET', '/test/[i:id]', 'c_testController#index', 'test2');

        // Retourne l'instance du routeur avec les routes définies
        return $router;
    }
}
