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
        $router->map('GET|POST', '/login', 'c_securityController#index', 'login');
        $router->map('GET', '/deconnexion', 'c_securityController#destroySession', 'deconnexion');
        $router->map('GET|POST', '/inscription', 'c_securityController#inscription', 'inscription');
        $router->map('GET|POST', '/verification[.:format]?', 'c_securityController#verification', 'verification');
        $router->map('GET|POST', '/verification-email', 'c_securityController#reverification', 'reverification');
        $router->map('GET|POST', '/demande-reset-password', 'c_securityController#demandeReset', 'demandeReset');
        $router->map('GET|POST', '/reset-password[.:format]?', 'c_securityController#resetPassword', 'resetPassword');
        $router->map('GET|POST', '/profil', 'c_profil#index', 'profil');
        $router->map('GET|POST', '/test', 'c_testController#index', 'test');
        $router->map('GET', '/test/[i:id]', 'c_testController#index', 'test2');

        // Retourne l'instance du routeur avec les routes définies
        return $router;
    }
}
