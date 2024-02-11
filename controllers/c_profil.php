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
        $resultatRequete = null;
        $resultatRequeteTheme = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['idTopicUser'])) {

                $resultatRequete = $this->deleteTopicUser($_POST['idTopicUser']);
            } elseif (isset($_POST['theme'])) {
                $resultatRequeteTheme = $this->theme($_POST['theme']);
            }
        }

        $topic = new Topic($this->connexionDB);
        $topicsUser = $topic->getTopicsUser($this->userSession['id']);


        // Chargement du template spécifique à la page profil
        $template = $this->twig->getTwig()->load('profil/profil.html.twig');

        // Affichage du template avec les données nécessaires
        $template->display([
            'user' =>  $this->userSession,
            'topics' => $topicsUser,
            'messagesTopics' =>  $resultatRequete,
            'error'=> $resultatRequeteTheme
        ]);
    }

    /**
     * Méthode pour changer le thème
     *
     */
    public function theme($requete)
    {

        $errorMessages = [];

            if (isset($requete)) {
                $value = trim($requete);

                // Vérifie si la valeur est dans le tableau des valeurs valides
                if ($this->isValidValue($value)) {
                    $affichage = new Affichage($this->connexionDB);
                    $resultat = $affichage->updateTheme($value, $this->userSession['id']);

                    if ($resultat) {
                        $_SESSION['user']['template'] = $value;
                        header('Location: /profil');
                        exit;
                    } else {
                        $errorMessages[] = "Une erreur s'est produite";
                    }
                } else {
                    $errorMessages[] = "La valeur du thème n'est pas valide.";
                }
            } else {
                $errorMessages[] = "Le champ thème est manquant.";
            }

        return $errorMessages;
    }

    /**
     * Méthode pour supprimer (de manière logique le topic de l'utilisateur)
     *
     * @return array|string
     */
    private function deleteTopicUser($requete): array|string
    {

        $errorMessagesDelete = [];
        $successMessage = '';


        if (isset($requete)) {
            $value = trim($requete);

            if (!empty($value)) {
                $topic = new Topic($this->connexionDB);
                $resultat = $topic->deleteTopicsUser($value, $this->userSession['id']);

                if ($resultat) {
                    return $successMessage = 'success';
                } else {
                    $errorMessagesDelete[] = "Le topic ne vous appartient pas, ou à déjà été supprimé !";
                }
            } else {
                $errorMessagesDelete[] = "La valeur n'est pas valide.";
            }
        } else {
            $errorMessagesDelete[] = "Le champ id du topic est manquant.";
        }
        return $errorMessagesDelete;
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
