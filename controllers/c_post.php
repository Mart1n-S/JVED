<?php

class c_post
{
    private $twig;
    private $connexionDB;
    private $security;
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
        $this->security = new Security($this->connexionDB);
        $this->userSession = $userSession;
    }

    /**
     * Méthode d'affichage de la page des categories
     *
     * @return void 
     */
    public function categorie(): void
    {
        $affichage = new Affichage($this->connexionDB);
        // Chargement du template spécifique à la page d'accueil
        $template = $this->twig->getTwig()->load('post/categorie.html.twig');

        // Affichage du template avec les données nécessaires
        $template->display([
            'categories' => $affichage->getAllCategorie(),
            'user' =>  $this->userSession
        ]);
    }

    /**
     * Méthode d'affichage de la page des sujets lié à la catégorie
     *
     * @return void 
     */
    public function sujet($match): void
    {
        $affichage = new Affichage($this->connexionDB);
        // Chargement du template spécifique à la page d'accueil
        $template = $this->twig->getTwig()->load('post/sujet.html.twig');

        // Affichage du template avec les données nécessaires
        $template->display([
            'nomCategorie' => rawurldecode($match['params']['nom']),
            'idCategorie' => rawurldecode($match['params']['id']),
            'topics' =>  $affichage->getTopicsFromCategorie($match['params']['id']),
            'user' =>  $this->userSession
        ]);
    }

     /**
     * Méthode d'affichage de la page du détails du topics
     *
     * @return void 
     */
    public function topics($match): void
    {
        $errorMessages=[];
        if(isset($_GET['errors'])) {
            // Décoder la chaîne JSON
            $errorMessagesJson = urldecode($_GET['errors']);
            $errorMessages = json_decode($errorMessagesJson, true);
        
        }
        $affichage = new Affichage($this->connexionDB);
        // Chargement du template spécifique à la page d'accueil
        $template = $this->twig->getTwig()->load('post/topics.html.twig');
        // Affichage du template avec les données nécessaires
        $template->display([
            'nomCategorie' => rawurldecode($match['params']['categorie']),
            'idCategorie' => rawurldecode($match['params']['idCategorie']),
            'nomTopic' => rawurldecode($match['params']['nom']),
            'commentaires' =>  $affichage->getCommentairesFromTopic($match['params']['id']),
            'idTopic' => $match['params']['id'],
            'user' =>  $this->userSession,
            'error' =>   $errorMessages
        ]);
    }

    public function comment(): void
    {
        $errorMessages = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $requiredFields = ['idTopic', 'commentaire'];
                $values = [];

                foreach ($requiredFields as $field) {
                    if (isset($_POST[$field])) {
                        $values[$field] = trim($_POST[$field]);
                    } else {
                        $errorMessages[] = "Le champ $field est manquant.";
                    }
                }

                // Vérifier que toutes les valeurs sont non vides
            if (empty($errorMessages)) {
                $errorMessages = array_merge(
                    $errorMessages,
                    $this->security->validateCommentaire($values['commentaire']),
                    $this->security->validateTopic($values['idTopic'])
                );

                if (empty($errorMessages)) {
                    $topic = new Topic($this->connexionDB);
                    $resultat = $topic->addCommentaire($values['idTopic'], $values['commentaire'], $this->userSession['id']);

                    if ( $resultat) {

                        header('Location: /topics/'.$_POST['nomCategorie'].'/'.$_POST['idCategorie'].'/'.$_POST['nomTopic'].'/'.$_POST['idTopic']);
                        exit;
                    } else {
                        $errorMessages[] = "Une erreur s'est produite.";
                    }
                }
            } else {
                $errorMessages[] = "Merci de remplir tous les champs.";
            }
        }
        $errorMessagesJson = json_encode($errorMessages);

        $errorMessagesUrlEncoded = urlencode($errorMessagesJson);

        // Redirection avec les messages d'erreur dans l'URL
        header('Location: /topics/'.$_POST['nomCategorie'].'/'.$_POST['idCategorie'].'/'.$_POST['nomTopic'].'/'.$_POST['idTopic'].'?errors='.$errorMessagesUrlEncoded);
        exit;

    }

    public function new(): void
    {
        $this->security->checkConnexion();
        $errorMessages= [];
        $success=null;
        $dashboard = new Dashboard($this->connexionDB);
        $categories = $dashboard->getCategories();
        if(isset($_GET['errors'])) {
            // Décoder la chaîne JSON
            $errorMessagesJson = urldecode($_GET['errors']);
            $errorMessages = json_decode($errorMessagesJson, true);
        
        }elseif(isset($_GET['success'])){
            $successMessagesJson = urldecode($_GET['success']);
            $success = json_decode($successMessagesJson, true);
        }
       

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $requiredFields = ['categorie', 'sujet', 'nomTopic', 'commentaire'];
            $values = [];
            

            foreach ($requiredFields as $field) {
                if (isset($_POST[$field])) {
                    $values[$field] = trim($_POST[$field]);
                } else {
                    $errorMessages[] = "Le champ $field est manquant.";
                }
            }

            // Vérifier que toutes les valeurs sont non vides
            if (empty($errorMessages)) {
                $errorMessages = array_merge(
                    $errorMessages,
                    $this->security->validateCommentaire($values['commentaire']),
                    $this->security->validateCategorie($values['categorie']),
                    $this->security->validateSujet($values['sujet']),
                    $this->security->validateNomTopic($values['nomTopic']),
                );

                if (empty($errorMessages)) {
                    $topic = new Topic($this->connexionDB);
                    $resultat = $topic->createTopic($values['categorie'], $values['sujet'], $values['nomTopic'], $values['commentaire'], $this->userSession['id']);

                    if ($resultat) {
                        $successMessagesJson = json_encode('Votre topic à bien été créer, il est en cours de validation !');

                        $successMessagesUrlEncoded = urlencode($successMessagesJson);
                        header('Location: /new-topic?success='.$successMessagesUrlEncoded);
                        exit;
                    } else {
                        $errorMessages[] = "Une erreur s'est produite";
                    }
                }
            } else {
                $errorMessages[] = "Merci de remplir tous les champs.";
            }

            $errorMessagesJson = json_encode($errorMessages);

            $errorMessagesUrlEncoded = urlencode($errorMessagesJson);

            // Redirection avec les messages d'erreur dans l'URL
            header('Location: /new-topic' . '?errors=' . $errorMessagesUrlEncoded);
            exit;
        }

        // Chargement du template spécifique à la page d'accueil
        $template = $this->twig->getTwig()->load('post/new.html.twig');
        // Affichage du template avec les données nécessaires
        $template->display([
            'categories' => $categories,
            'user' =>  $this->userSession,
            'error' => $errorMessages,
            'success'=> $success
        ]);
    }
}
