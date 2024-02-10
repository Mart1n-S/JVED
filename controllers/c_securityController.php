<?php

class c_securityController
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
     * Méthode d'affichage de la page de login
     *
     * @return void 
     */
    public function index(): void
    {

        

        if ( $this->userSession) {
            header('Location: /');
            exit;
        }
        $errorMessages = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $requiredFields = ['mail', 'password'];
            $values = [];

            // Vérifier la présence des champs requis et construire le tableau $values
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
                    $this->security->validateEmail($values['mail'])
                );

                if (empty($errorMessages)) {
                    $user = new User($this->connexionDB);
                    $user = $user->getUser($values['mail']);

                    if ($user != null && password_verify($values['password'], $user['password'] ?? "")) {
                        $this->security->storeUserDataInSession($user);

                        header('Location: /');
                        exit;
                    } else {
                        $errorMessages[] = "Email ou mot de passe invalide.";
                    }
                }
            } else {
                $errorMessages[] = "Merci de remplir tous les champs.";
            }
        }

        // Chargement du template spécifique au formulaire de connexion
        $template = $this->twig->getTwig()->load('security/login.html.twig');

        // Affichage du template avec les données nécessaires
        $template->display([
            'error' => $errorMessages
        ]);
    }

    /**
     * Méthode d'affichage de la page d'inscription
     *
     * @return void 
     */
    public function inscription(): void
    {
       

        if ( $this->userSession) {
            header('Location: /');
            exit;
        }
        $errorMessages = [];
        $successMessage = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $requiredFields = ['pseudo', 'mail', 'password', 'confirmPassword'];
            $values = [];

            // Vérifier la présence des champs requis et construire le tableau $values
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
                    $this->security->validatePseudo($values['pseudo']),
                    $this->security->validateEmail($values['mail']),
                    $this->security->validatePassword($values['password'], $values['confirmPassword']),
                    $this->security->checkDoublonEmail($values['mail'])
                );

                if (empty($errorMessages)) {
                    // Hasher le mot de passe avec Bcrypt
                    $hashedPassword = password_hash($values['password'], PASSWORD_BCRYPT);
                    $userRoleId = '';
                    $user = new User($this->connexionDB);
                    $roles = $user->getRoles();
                    foreach ($roles as $role) {
                        if ($role['roleName'] === 'user') {
                            $userRoleId = $role['id'];
                            break;
                        }
                    }
                    $tokenData = $this->security->generateToken();

                    $resultat = $user->setUser($values['pseudo'], $values['mail'], $hashedPassword, $userRoleId, $tokenData['token'], $tokenData['expiration_date']);

                    if ($resultat) {
                        $sendMail = $this->security->sendMail($values['pseudo'], $values['mail'], $tokenData['token']);
                        if ($sendMail) {
                            $successMessage = "Inscription terminée. Un email de confirmation vous a été envoyé pour confirmer votre adresse email.";
                        } else {
                            $errorMessages[] =  "Une erreur s'est produite lors de l'envoi de l'email de confirmation. Veuillez faire une demande de renvoi de confirmation.";
                        }
                    } else {
                        $errorMessages[] = "Une erreur s'est produite. Veuillez réessayer.";
                    }
                }
            } else {
                $errorMessages[] = "Merci de remplir tous les champs.";
            }
        }

        // Chargement du template spécifique au formulaire d'inscription
        $template = $this->twig->getTwig()->load('security/inscription.html.twig');

        // Affichage du template avec les données nécessaires
        $template->display([
            'error' => $errorMessages,
            'success' => $successMessage
        ]);
    }

    /**
     * Méthode d'affichage de la page de vérification
     *
     * @return void 
     */
    public function verification(): void
    {
        // Récupérer le token depuis l'URL
        $token = $_GET['token'] ?? null;
        $errorMessages = [];
        $successMessage = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $requiredFields = ['mail', 'token'];
            $values = [];

            // Vérifier la présence des champs requis et construire le tableau $values
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
                    $this->security->validateEmail($values['mail'])
                );

                if (empty($errorMessages)) {
                    $currentTime = new DateTime();
                    $expirationDate = $currentTime->setTimezone(new DateTimeZone('Europe/Paris'));
                    $formattedExpirationDate = $expirationDate->format('Y-m-d H:i:s');

                    $user = new User($this->connexionDB);
                    $resultat = $user->verificationEmailUser($values['mail'],$values['token'],$formattedExpirationDate);

                    if ($resultat) {
                        $successMessage = "Votre email est vérifié. Vous pouvez maintenant vous connecter.";

                    } else {
                        $errorMessages[] = "Une erreur est survenue : votre adresse e-mail est invalide ou votre token a expiré. Veuillez refaire une demande de vérification.";
                    }
                }
            } else {
                $errorMessages[] = "Merci de remplir tous les champs.";
            }
        }

       
        // Chargement du template spécifique au formulaire de verification de l'email
        $template = $this->twig->getTwig()->load('security/verification.html.twig');

        // Affichage du template avec les données nécessaires
        $template->display([
            'token' => $token,
            'error' => $errorMessages,
            'success' => $successMessage,
            'user' =>  $this->userSession
        ]);
    }

    
    /**
     * Méthode d'affichage de la page de revérification
     *
     * @return void 
     */
    public function reverification(): void
    {
        
        $errorMessages = [];
        $successMessage = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $requiredFields = ['mail'];
            $values = [];

            // Vérifier la présence des champs requis et construire le tableau $values
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
                    $this->security->validateEmail($values['mail'])
                );

                if (empty($errorMessages)) {
                    $tokenData = $this->security->generateToken();

                    $user = new User($this->connexionDB);
                    $resultat = $user->reVerificationEmailUser($values['mail'],$tokenData['token'],$tokenData['expiration_date']);

                    if ($resultat) {
                        $sendMail = $this->security->sendMail($resultat, $values['mail'], $tokenData['token']);
                        if ($sendMail) {
                            $successMessage = "Un email de confirmation vous a été envoyé pour confirmer votre adresse email.";
                        } else {
                            $errorMessages[] =  "Une erreur s'est produite lors de l'envoi de l'email de confirmation. Veuillez faire une demande de renvoi de confirmation.";
                        }
                    } else {
                        $errorMessages[] = "Votre email est déjà vérifié ou n'existe pas.";
                    }
                }
            } else {
                $errorMessages[] = "Merci de remplir tous les champs.";
            }
        }

       
        // Chargement du template spécifique au formulaire de reverification de l'email
        $template = $this->twig->getTwig()->load('security/reverification.html.twig');

        // Affichage du template avec les données nécessaires
        $template->display([
            'error' => $errorMessages,
            'success' => $successMessage,
            'user' =>  $this->userSession
        ]);
    }

     /**
     * Méthode d'affichage de la page de demande de réinitialisation du mot de passe 
     *
     * @return void 
     */
    public function demandeReset(): void
    {
        
        $errorMessages = [];
        $successMessage = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $requiredFields = ['mail'];
            $values = [];

            // Vérifier la présence des champs requis et construire le tableau $values
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
                   $this->security->validateEmail($values['mail'])
                );

                if (empty($errorMessages)) {
                    $tokenData =$this->security->generateToken();

                    $user = new User($this->connexionDB);
                    $resultat = $user->demandeResetPassword($values['mail'],$tokenData['token'],$tokenData['expiration_date']);

                    if ($resultat) {
                        $sendMail = $this->security->sendMail($resultat, $values['mail'], $tokenData['token'], 'resetMdp');
                        if ($sendMail) {
                            $successMessage = "Un email pour réinitialiser votre mot de passe vous a été envoyé.";
                        } else {
                            $errorMessages[] =  "Une erreur s'est produite lors de l'envoi de l'email de réinitialisation de votre mot de passe.";
                        }
                    } else {
                        $errorMessages[] = "Aucun compte avec cette email.";
                    }
                }
            } else {
                $errorMessages[] = "Merci de remplir tous les champs.";
            }
        }

       
        // Chargement du template spécifique au formulaire de reverification de l'email
        $template = $this->twig->getTwig()->load('security/demandeResetPassword.html.twig');

        // Affichage du template avec les données nécessaires
        $template->display([
            'error' => $errorMessages,
            'success' => $successMessage,
            'user' =>  $this->userSession
        ]);
    }

     /**
     * Méthode d'affichage de la page de resetPassword
     *
     * @return void 
     */
    public function resetPassword(): void
    {
        // Récupérer le token depuis l'URL
        $token = $_GET['token'] ?? null;
        $errorMessages = [];
        $successMessage = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $requiredFields = ['mail', 'token','password', 'confirmPassword'];
            $values = [];

            // Vérifier la présence des champs requis et construire le tableau $values
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
                   $this->security->validateEmail($values['mail']),
                   $this->security->validatePassword($values['password'], $values['confirmPassword'])
                );

                if (empty($errorMessages)) {
                    // Hasher le mot de passe avec Bcrypt
                    $hashedPassword = password_hash($values['password'], PASSWORD_BCRYPT);
                    $user = new User($this->connexionDB);

                    $currentTime = new DateTime();
                    $expirationDate = $currentTime->setTimezone(new DateTimeZone('Europe/Paris'));
                    $formattedExpirationDate = $expirationDate->format('Y-m-d H:i:s');

                    $resultat = $user->updatePassword($values['mail'],$hashedPassword, $values['token'], $formattedExpirationDate);

                    if ($resultat) {
                        $successMessage = "Votre mot de passe à été réinitialiser.";

                    } else {
                        $errorMessages[] = "Une erreur est survenue : votre adresse e-mail est invalide ou votre token a expiré. Veuillez refaire une demande de réinitialisation.";
                    }
                }
            } else {
                $errorMessages[] = "Merci de remplir tous les champs.";
            }
        }

       
        // Chargement du template spécifique au formulaire de resetPassword
        $template = $this->twig->getTwig()->load('security/resetPassword.html.twig');

        // Affichage du template avec les données nécessaires
        $template->display([
            'token' => $token,
            'error' => $errorMessages,
            'success' => $successMessage,
            'user' =>  $this->userSession
        ]);
    }

    /**
     * Détruit la session
     *
     * @return void
     */
    public function destroySession(): void
    {
        // Réinitialisez toutes les variables de session à un tableau vide
        $_SESSION = array();

        session_destroy();

        header('Location: /');
        exit;
    }

}
