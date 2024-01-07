<?php

class c_testController
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
     * Méthode d'affichage de la page de test
     *
     * @param $match Tableau contenant les paramètres de la requête
     * @return void
     */
    public function index($match)
    {
        $id = null;

        // Vérifier s'il y a un paramètre dans l'URL
        if (isset($match['params']['id'])) {
            // Récupérer la valeur du paramètre 'id'
            $id = $match['params']['id'];

            // Vérifier si la valeur du paramètre est égale à 2
            if ($id == 2) {
                // Afficher un template spécifique si le paramètre 'id' est égal à 2
                $template = 'testRoutes/testRouteParams.html.twig';
            } else {
                // Si le paramètre 'id' n'existe pas, afficher une page d'erreur personnalisée
                $template = 'errors/erreur.html.twig';
                $this->renderErrorPage($template, '404');
                exit;
            }
        } else {
            $template = 'testRoutes/testRouteNoParams.html.twig';
        }

        // Charger le template avec Twig et passer des données au rendu
        $template = $this->twig->getTwig()->load($template);
        $template->display([
            'param_id' => $id
        ]);
    }

    /**
     * Méthode pour afficher une page d'erreur personnalisée
     *
     * @param string $template Le chemin du template Twig pour la page d'erreur
     * @param string $errorCode Le code d'erreur à afficher
     * @return void
     */
    private function renderErrorPage($template, $errorCode): void
    {
        $template = $this->twig->getTwig()->load($template);
        $template->display([
            'typeErreur' => $errorCode,
        ]);
    }
}
