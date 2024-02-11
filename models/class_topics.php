<?php

class Topic
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
     * Récupère les topics d'un utilisateur à partir de son ID.
     *
     * @param int $id L'ID de l'utilisateur.
     * @return array|null Retourne un tableau contenant les topics de l'utilisateur s'il en a, sinon null.
     */
    public function getTopicsUser(int $id): array|null
    {
        return $this->db->getTopicsUser($id);
    }
}