<?php

class c_accueilController
{
    private $twig;
    private $connexionDB;

    /**
     * Constructeur prenant une instance de TwigConfig en paramètre
     *
     * @param $twig Instance de TwigConfig
     * @param $connexionDB Instance de PDO pour la connexion à la base de données
     */
    public function __construct($twig, $connexionDB)
    {
        $this->twig = $twig;
        $this->connexionDB = $connexionDB;
    }

    /**
     * Méthode d'affichage de la page d'accueil
     *
     * @return void 
     */
    public function index(): void
    {
        $affichage = new Affichage($this->connexionDB);
        // Chargement du template spécifique à la page d'accueil
        $template = $this->twig->getTwig()->load('accueil/index.html.twig');

        // Affichage du template avec les données nécessaires
        $template->display([
            'topTopics' => $affichage->getTopTopicsAccueil()
        ]);
    }
}
