<?php

class Security
{
    private $db;

    /**
     * Constructeur de la classe Affichage.
     *
     * @param Database $db L'instance de la classe Database utilisée pour l'accès à la base de données.
     */
    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function checkConnexion()
    {
        if (!$_SESSION['user']) {
            header('Location: /');
            exit;
        }
    }
    public function checkAutorisation()
    {
        if (!$_SESSION['user'] && ($_SESSION['user']['role'] != 'superAdmin' || $_SESSION['user']['role'] != 'moderateur')) {
            header('Location: /');
            exit;
        }
    }


    /**
     * Valide la longueur du pseudo.
     *
     * @param string $pseudo Le pseudo à valider.
     * @return array Tableau des erreurs. S'il n'y a pas d'erreur, le tableau est vide.
     */
    public function validatePseudo(string $pseudo): array
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
    public function validateEmail(string $email): array
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
    public function validatePassword(string $password, string $confirmPassword): array
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
    public function checkDoublonEmail(string $email): array
    {
        $errors = [];
        $user = new User($this->db);
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
     * Génère le token et la date d'expiration dans le fuseau horaire Europe/Paris.
     *
     * @return array Un tableau contenant le token et la date d'expiration.
     */
    public function generateToken(): array {
        $currentTime = new DateTime();
        $expirationDate = $currentTime->setTimezone(new DateTimeZone('Europe/Paris'));
        $expirationDate->modify('+24 hours');
        $formattedExpirationDate = $expirationDate->format('Y-m-d H:i:s');
        $token = bin2hex(random_bytes(32));
        return ['token' => $token, 'expiration_date' => $formattedExpirationDate];
    }

    /**
     * Construit l'URL de vérification.
     *
     * @param string $token Le token généré.
     * @param string $var la variable pour savoir si on envoie un mail pour réinitialiser le mdp ou pour valider l'email
     * @return string L'URL de vérification.
     */
    public function createVerificationUrl(string $token, string $var): string
    {
        if ($var !== 'resetMdp') {
            $tokenUrl = "http://localhost:8080/verification?token=" . $token;
        } else {
            $tokenUrl = "http://localhost:8080/reset-password?token=" . $token;
        }



        return $tokenUrl;
    }

    /**
     * Envoie un e-mail de vérification à l'utilisateur.
     *
     * @param string $pseudo Le pseudo de l'utilisateur.
     * @param string $email L'adresse e-mail de l'utilisateur.
     * @param string $token Le token généré pour créer l'url de vérification.
     * @param string $var la variable pour savoir si on envoie un mail pour réinitialiser le mdp ou pour valider l'email
     * @return bool True si l'e-mail est envoyé avec succès, sinon false.
     */
    public function sendMail(string $pseudo, string $emailUser, string $token, string $var = ''): bool
    {

        // Générer l'URL de vérification
        $verificationUrl = $this->createVerificationUrl($token, $var);

        if ($var !== 'resetMdp') {
            // Construire le sujet et le message de l'e-mail
            $subject = 'Vérifiez votre adresse e-mail sur JVED';
            $message = "Bonjour $pseudo,\n\nVeuillez cliquer sur le lien suivant pour vérifier votre adresse e-mail sur JVED :\n$verificationUrl\n\nCordialement,\nL'équipe JVED";
        } else {
            // Construire le sujet et le message de l'e-mail
            $subject = 'Réinitialiser votre mot de passe';
            $message = "Bonjour $pseudo,\n\nVeuillez cliquer sur le lien suivant pour réinitialiser votre adresse e-mail sur JVED :\n$verificationUrl\n\nCordialement,\nL'équipe JVED";
        }

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
