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
}
