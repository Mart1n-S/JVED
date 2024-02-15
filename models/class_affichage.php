<?php

class Affichage
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

    /**
     * Récupère les sujets les plus commentés pour les afficher sur la page d'accueil.
     *
     * @return array Retourne un tableau associatif des sujets les plus commentés.
     */
    public function getTopTopicsAccueil(): array
    {
        return $this->db->getTopTopics();
    }

    /**
     * Met à jour le template de l'utilisateur.
     *
     * @return bool Retourne True si l'update a marché sinon false
     */
    public function updateTheme(string $valueTheme, int $idUser): bool
    {
        return $this->db->updateThemeUser($valueTheme, $idUser);
    }

    /**
     * Récupère toutes les catégories en utilisant la fonction getCategories de la base de données.
     *
     * @return array|null Tableau associatif contenant les catégories récupérées ou null en cas d'erreur.
     */
    public function getAllCategorie(): ?array
    {
        // Utilise la fonction getCategories de la base de données pour récupérer toutes les catégories.
        return $this->db->getCategories();
    }

    /**
     * Récupère tous les sujets associés à une catégorie spécifique en utilisant la fonction
     * getTopicsFromCategorie de la base de données.
     *
     * @param int $id L'identifiant de la catégorie.
     * @return array|null Tableau associatif contenant les sujets récupérés ou null en cas d'erreur.
     */
    public function getTopicsFromCategorie(int $id): array|null
    {
        return $this->db->getTopicsFromCategorie($id);
    }

    /**
     * Récupère tous les commentaires associés à un sujet spécifique en utilisant la fonction
     * getCommentairesFromTopic de la base de données.
     *
     * @param int $id L'identifiant du sujet.
     * @return array|false Tableau associatif contenant les commentaires récupérés ou false en cas d'erreur.
     */
    public function getCommentairesFromTopic(int $id): array|bool
    {
        return $this->db->getCommentairesFromTopic($id);
    }
}
