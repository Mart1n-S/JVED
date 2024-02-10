<?php

class User
{
    private $db;

    /**
     * Constructeur de la classe User.
     *
     * @param Database $db L'instance de la classe Database utilisée pour l'accès à la base de données.
     */
    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Définit un nouvel utilisateur dans la base de données.
     *
     * @param string $pseudo Le pseudo de l'utilisateur.
     * @param string $email L'email de l'utilisateur.
     * @param string $password Le mot de passe de l'utilisateur.
     * @param string $userRoleId L'ID du rôle de l'utilisateur.
     * @param string $token Le token générer pour check le mail.
     * @param string $dateToken Date de validité (24h).
     * @return bool True si l'utilisateur a été créé avec succès, sinon false.
     */
    public function setUser(string $pseudo, string $email, string $password, string $userRoleId, string $token, string $dateToken): bool
    {
        return $this->db->createUser($pseudo, $email, $password, $userRoleId, $token, $dateToken);
    }


    /**
     * Récupère le nombre d'utilisateurs avec l'email spécifié.
     *
     * @param string $email L'email de l'utilisateur à rechercher.
     * @return int Le nombre d'utilisateurs avec l'email spécifié.
     */
    public function getUserMail(string $email): int
    {
        return $this->db->getUserMail($email);
    }

    /**
     * Récupère tous les rôles disponibles.
     *
     * @return array Un tableau associatif contenant les rôles.
     */
    public function getRoles(): array
    {
        return $this->db->getRoles();
    }

    /**
     * Récupère le nombre d'utilisateurs avec l'email spécifié.
     *
     * @param string $email L'email de l'utilisateur à rechercher.
     * @return array|bool Retourne un tableau contenant les informations de l'utilisateur s'il est trouvé, sinon false en cas d'échec ou d'erreur.
     */
    public function getUser(string $email): array|bool
    {
        return $this->db->getUser($email);
    }

    /**
     * Permet de valider l'email de l'utilisateur après son inscription
     *
     * @param string $email L'email de l'utilisateur à valider.
     * @param string $token Le token reçu par mail.
     * @param string $date La date du jour pour vérifier la validité du token.
     * @return bool True si le mail est check, false si l'email existe pas ou si token expiré.
     */
    public function verificationEmailUser(string $email, string $token, string $date): bool
    {
        return $this->db->verificationEmailUser($email, $token, $date);
    }

    /**
     * Permet de re-valider l'email de l'utilisateur après son inscription
     *
     * @param string $email L'email de l'utilisateur à valider.
     * @param string $token Le token généré.
     * @param string $dateToken Date de validité (24h).
     * @return string|bool Le pseudo si la nouvelle verification à marché, sinon false.
     */
    public function reVerificationEmailUser(string $email, string $token, string $dateToken): string|bool
    {
        return $this->db->reVerificationEmailUser($email, $token, $dateToken);
    }

    /**
     * Permet de créer un nouveau token pour réinitialiser son mot de passe
     *
     * @param string $email L'email de l'utilisateur.
     * @param string $token Le token généré.
     * @param string $dateToken Date de validité (24h).
     * @return bool True si la mise à jour du token à marché, sinon false.
     */
    public function demandeResetPassword(string $email, string $token, string $dateToken): bool
    {
        return $this->db->updateTokenAndDateToken($email, $token, $dateToken);
    }

    /**
     * Met à jour le mot de passe 
     *
     * @param string $email L'email de l'utilisateur.
     * @param string $password Le nouveau mot de passe à mettre à jour.
     * @param string $token Le token à vérifier.
     * @param string $dateToken La date du jour.
     * @return bool True si la mise à jour a réussi et au moins une ligne a été affectée, false sinon.
     */

    public function updatePassword(string $email, string $password, string $token, string $dateToken){
        return $this->db->updatePassword($email, $password, $token, $dateToken);
    }
}
