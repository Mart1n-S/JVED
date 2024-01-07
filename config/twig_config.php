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
}
