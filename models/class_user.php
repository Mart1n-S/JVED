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
}
