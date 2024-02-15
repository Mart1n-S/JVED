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
        $router->map('GET|POST', '/dashboard', 'c_dashboardController#index', 'dashboard');
        $router->map('GET|POST', '/posts', 'c_dashboardController#posts', 'posts');
        $router->map('GET|POST', '/posts_edit[.:format]?', 'c_dashboardController#posts_edit', 'posts_edit');
        $router->map('GET|POST', '/categories', 'c_dashboardController#categories', 'categories');
        $router->map('GET|POST', '/categories_edit[.:format]?', 'c_dashboardController#categories_edit', 'categories_edit');
        $router->map('GET|POST', '/categories_add', 'c_dashboardController#categories_add', 'categories_add');
        $router->map('GET|POST', '/users', 'c_dashboardController#users', 'users');
        $router->map('GET|POST', '/users_edit[.:format]?', 'c_dashboardController#users_edit', 'users_edit');
        $router->map('GET|POST', '/users_add', 'c_dashboardController#users_add', 'users_add');
        $router->map('GET|POST', '/contents/[i:id]', 'c_dashboardController#contents', 'contents');
        $router->map('GET|POST', '/contents_edit[.:format]?', 'c_dashboardController#contents_edit', 'contents_edit');
        $router->map('GET|POST', '/validation-posts', 'c_dashboardController#validationPosts', 'waitingPosts');
        $router->map('POST', '/edit-validation-posts', 'c_dashboardController#editValidationPosts', 'editValidationPosts');
        $router->map('GET', '/categorie', 'c_post#categorie', 'categorie');
        $router->map('GET|POST', '/new-topic', 'c_post#new', 'newTopic');
        $router->map('GET', '/sujet/[*:nom]?/[i:id]', 'c_post#sujet', 'sujet');
        $router->map('GET', '/topics/[*:categorie]?/[i:idCategorie]/[*:nom]?/[i:id]', 'c_post#topics', 'topics');
        $router->map('POST', '/topics/[*:categorie]?/[i:idCategorie]/[*:nom]?/[i:id]', 'c_post#comment', 'comment');
        // Retourne l'instance du routeur avec les routes définies
        return $router;
    }
}
