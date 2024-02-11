<?php

class c_profil
{
    private $twig;
    private $connexionDB;
    private $userSession;

    /**
     * Constructeur prenant une instance de TwigConfig en paramètre
     *
     * @param $twig Instance de TwigConfig
     * @param $connexionDB Instance de PDO pour la connexion à la base de données
     */
    public function __construct($twig, $connexionDB, $userSession)
    {
        $this->twig = $twig;
        $this->connexionDB = $connexionDB;
        $this->userSession = $userSession;
    }

    /**
     * Méthode d'affichage de la page profil
     *
     * @return void 
     */
    public function index(): void
    {
        if (!$this->userSession) {
            header('Location: /');
            exit;
        }
        // Chargement du template spécifique à la page profil
        $template = $this->twig->getTwig()->load('profil/profil.html.twig');

        // Affichage du template avec les données nécessaires
        $template->display([
            'user' =>  $this->userSession
        ]);
    }

    /**
     * Méthode pour changer le thème
     *
     * @return void 
     */
    public function theme(): void
    {

        $errorMessages = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            if (isset($_POST['theme'])) {
                $value = trim($_POST['theme']);

                // Vérifie si la valeur est dans le tableau des valeurs valides
                if ($this->isValidValue($value)) {
                    $affichage = new Affichage($this->connexionDB);
                    $resultat = $affichage->updateTheme($value, $this->userSession['id']);

                    if ($resultat) {
                        $_SESSION['user']['template'] = $value;
                        $this->userSession['template'] = $_SESSION['user']['template'];
                        $template = $this->twig->getTwig()->load('profil/profil.html.twig');

                        // Affichage du template avec les données nécessaires
                        $template->display([
                            'user' =>  $this->userSession
                        ]);
                    }else{
                        $errorMessages[] = "Une erreur s'est produite";
                    }
                } else {
                    $errorMessages[] = "La valeur du thème n'est pas valide.";
                }
            } else {
                $errorMessages[] = "Le champ thème est manquant.";
            }

            // Vérifier que toutes les valeurs sont non vides
            if (!empty($errorMessages)) {
                $template = $this->twig->getTwig()->load('profil/profil.html.twig');

                // Affichage du template avec les données nécessaires
                $template->display([
                    'error' => $errorMessages,
                    'user' =>  $this->userSession
                ]);
            }
        }
    }

    /**
     * Vérifie si une valeur est dans un tableau de valeurs valides.
     *
     * @param mixed $value La valeur à vérifier.
     * @return bool Retourne true si la valeur est valide, sinon false.
     */
    private function isValidValue(string $value): bool
    {
        $validValues = ['', 'template1', 'template2', 'template3'];

        return in_array($value, $validValues, true);
    }
}
