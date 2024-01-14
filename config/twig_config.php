<?php

class TwigConfig
{
    private $twig;
    private $router;

    public function __construct($router)
    {
        $loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../templates');
        $this->twig = new \Twig\Environment($loader, [
            'cache' => false,
        ]);

        $this->router = $router;

        // Ajoutez la fonction personnalisée pour générer les URL via AltoRouter
        $this->twig->addFunction(new \Twig\TwigFunction('path', [$this, 'generateUrl']));

        // Ajoutez la fonction personnalisée pour simuler asset
        $this->twig->addFunction(new \Twig\TwigFunction('asset', [$this, 'generateAssetPath']));

        // Ajoutez ici d'autres configurations Twig si nécessaire
        // Par exemple :
        // $this->twig->addExtension(new \Twig\Extension\DebugExtension());
    }

    public function getTwig(): \Twig\Environment
    {
        return $this->twig;
    }

    // Fonction personnalisée pour générer les URL via AltoRouter
    public function generateUrl($routeName, $params = [])
    {
        return $this->router->generate($routeName, $params);
    }

    // Fonction pour obtenir le chemin de base pour les assets
    public function getBaseUrl()
    {
        // Remplacez "/public" par le chemin de votre répertoire d'assets s'il est différent
        return '/assets';
    }

    // Fonction personnalisée pour simuler asset
    public function generateAssetPath($assetPath)
    {
        // Retourne le chemin complet vers l'asset en utilisant le chemin de base
        return $this->getBaseUrl() . '/' . ltrim($assetPath, '/');
    }
}
