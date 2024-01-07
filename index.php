<?php
require_once './vendor/autoload.php';
require_once './config/twig_config.php';
require_once './routes/routes.php';
require_once './models/class_database.php';
require_once './config/config.php';

// Connexion à la base de données
$db = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASSWORD);
$connexionDB = $db->connect();

// Récupérer l'instance du routeur en utilisant la méthode statique de la classe Routes
$router = Routes::getRouter();

// Dispatch la requête actuelle
$match = $router->match();

if (is_array($match)) {
    // Séparer le nom du contrôleur et la méthode à appeler
    list($controllerName, $methodName) = explode('#', $match['target']);

    // Inclure le fichier du contrôleur
    require_once 'controllers/' . $controllerName . '.php';

    // Création d'une instance de TwigConfig en lui passant le routeur
    $twigConfig = new TwigConfig($router);

    // Instancier le contrôleur en passant l'instance de TwigConfig et de PDO et appeler la méthode associée
    $controller = new $controllerName($twigConfig, $connexionDB);

    call_user_func_array([$controller, $methodName], [$match]);
} else {
    // Gérer les cas où aucune route ne correspond à la requête => page erreur à créer 
    header($_SERVER["SERVER_PROTOCOL"] . ' 404 Not Found');
    echo "404 Accueil Page Not Found";
}
