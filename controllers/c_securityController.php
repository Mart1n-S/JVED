<?php

class c_securityController
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
     * Méthode d'affichage de la page de login
     *
     * @return void 
     */
    public function index(): void
    {

        $user = isset($_SESSION['user']) ? $_SESSION['user'] : null;

        if ($user) {
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
                    $this->validateEmail($values['mail'])
                );

                if (empty($errorMessages)) {
                    $user = new User($this->connexionDB);
                    $user = $user->getUser($values['mail']);

                    if ($user != null && password_verify($values['password'], $user['password'] ?? "")) {
                        $this->storeUserDataInSession($user);

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
        $user = isset($_SESSION['user']) ? $_SESSION['user'] : null;

        if ($user) {
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
                    $this->validatePseudo($values['pseudo']),
                    $this->validateEmail($values['mail']),
                    $this->validatePassword($values['password'], $values['confirmPassword']),
                    $this->checkDoublonEmail($values['mail'])
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
                    $token = $this->generateToken();
                    $currentTime = new DateTime();
                    $expirationDate = $currentTime->setTimezone(new DateTimeZone('Europe/Paris'));
                    $expirationDate->modify('+24 hours');
                    $formattedExpirationDate = $expirationDate->format('Y-m-d H:i:s');

                    $resultat = $user->setUser($values['pseudo'], $values['mail'], $hashedPassword, $userRoleId, $token, $formattedExpirationDate);

                    if ($resultat) {
                        $sendMail = $this->sendMail($values['pseudo'], $values['mail'], $token);
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
                    $this->validateEmail($values['mail'])
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
                        $errorMessages[] = "Une erreur est survenue : votre adresse e-mail est invalide ou votre token a expiré. </br>Veuillez refaire une demande de vérification.";
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
            'success' => $successMessage
        ]);
    }

    /**
     * Valide la longueur du pseudo.
     *
     * @param string $pseudo Le pseudo à valider.
     * @return array Tableau des erreurs. S'il n'y a pas d'erreur, le tableau est vide.
     */
    private function validatePseudo(string $pseudo): array
    {
        $errors = [];

        if (strlen($pseudo) < 3 || strlen($pseudo) > 15) {
            $errors[] = "La longueur du champ 'pseudo' doit être entre 3 et 15 caractères.";
        }

        return $errors;
    }

    /**
     * Valide la format de l'adresse e-mail.
     *
     * @param string $email L'adresse e-mail à valider.
     * @return array Tableau des erreurs. S'il n'y a pas d'erreur, le tableau est vide.
     */
    private function validateEmail(string $email): array
    {
        $errors = [];

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "L'adresse e-mail n'est pas valide.";
        }

        return $errors;
    }

    /**
     * Valide le mot de passe en termes de longueur, de caractères spéciaux et de correspondance avec le champ de confirmation.
     *
     * @param string $password Le mot de passe à valider.
     * @param string $confirmPassword Le champ de confirmation du mot de passe.
     * @return array Tableau des erreurs. S'il n'y a pas d'erreur, le tableau est vide.
     */
    private function validatePassword(string $password, string $confirmPassword): array
    {
        $errors = [];

        if (strlen($password) < 8 || strlen($password) > 24 || !preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,24}$/', $password)) {
            $errors[] = "Le mot de passe doit avoir entre 8 et 24 caractères et contenir au moins 1 minuscule, 1 majuscule, 1 chiffre, 1 caractère spécial.";
        }

        if ($password !== $confirmPassword) {
            $errors[] = "Les mots de passe ne correspondent pas.";
        }

        return $errors;
    }

    /**
     * Vérifie si le mail n'existe pas déjà
     *
     * @param string $email le mail à vérifier.
     * @return array Tableau des erreurs. S'il n'y a pas d'erreur, le tableau est vide.
     */
    private function checkDoublonEmail(string $email): array
    {
        $errors = [];
        $user = new User($this->connexionDB);
        $result = $user->getUserMail($email);

        // Si le résultat n'est pas vide, cela signifie qu'un utilisateur avec cet e-mail existe déjà
        if ($result > 0) {
            $errors[] = "L'adresse e-mail est déjà utilisée par un autre utilisateur.";
        }

        // Sinon, l'e-mail n'existe pas encore dans la base de données
        return $errors;
    }

    /**
     * Stocke les informations spécifiques de l'utilisateur en session
     *
     * @param array $userData Les données de l'utilisateur
     * @return void
     */
    public function storeUserDataInSession(array $userData): void
    {
        $_SESSION['user'] = [
            'id' => $userData['id'],
            'pseudo' => $userData['pseudo'],
            'email' => $userData['email'],
            'role' => $userData['role'],
            'template' => $userData['template']
        ];
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

    /**
     * Génère un token aléatoire.
     *
     * @return string Le token généré.
     */
    private function generateToken(): string
    {
        $token = bin2hex(random_bytes(32));
        return $token;
    }

    /**
     * Construit l'URL de vérification.
     *
     * @param string $token Le token généré.
     * @return string L'URL de vérification.
     */
    public function createVerificationUrl(string $token): string
    {

        $tokenUrl = "http://localhost:8080/verification?token=" . $token;

        return $tokenUrl;
    }

    /**
     * Envoie un e-mail de vérification à l'utilisateur.
     *
     * @param string $pseudo Le pseudo de l'utilisateur.
     * @param string $email L'adresse e-mail de l'utilisateur.
     * @param string $token Le token généré pour créer l'url de vérification.
     * @return bool True si l'e-mail est envoyé avec succès, sinon false.
     */
    public function sendMail(string $pseudo, string $emailUser, string $token): bool
    {
        // Générer l'URL de vérification
        $verificationUrl = $this->createVerificationUrl($token);

        // Construire le sujet et le message de l'e-mail
        $subject = 'Vérifiez votre adresse e-mail sur JVED';
        $message = "Bonjour $pseudo,\n\nVeuillez cliquer sur le lien suivant pour vérifier votre adresse e-mail sur JVED :\n$verificationUrl\n\nCordialement,\nL'équipe JVED";

        // Construire les en-têtes de l'e-mail
        $headers = 'From: jved@contact.fr' . "\r\n";
        $headers .= " Content-Type: text/html; charset=UTF-8\r\n";

        // Utiliser l'adresse IP du conteneur MailDev comme hôte SMTP (mettre à jour à chaque docker-compose up -d car l'ip du conteneur change)
        $smtpHost = MAILDEV_IP;
        $smtpPort = 1025;

        // Fonction mail() de PHP pour envoyer l'e-mail
        if (mail($emailUser, $subject, $message, $headers, "-f jved@contact.fr -S $smtpHost:$smtpPort")) {
            return true;
        } else {
            return false;
        }
    }
}
