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
     * @return array|null Retourne un tableau des sujets les plus commentés ou null en cas d'erreur
     */
    public function getTopTopicsAccueil(): array|null
    {
        try {
            $query = "
                SELECT 
                topic.id,
                topic.nom, 
                user.pseudo AS auteur, 
                topic.updatedAt AS derniere_activite, 
                COUNT(content.id) AS nb_messages,
                cat.nom as categorieNom,
                cat.id as categorieId
            FROM 
                topic 
            LEFT JOIN 
                content ON topic.id = content.idTopic
            JOIN 
                user ON topic.auteur = user.id
            JOIN 
                sujet ON topic.idSujet = sujet.id
            JOIN 
                categorie cat ON sujet.idCategorie = cat.id
            WHERE 
                topic.deletedAt IS NULL
            AND 
                topic.valide = 1
            GROUP BY 
                topic.id
            ORDER BY 
                nb_messages DESC, 
                derniere_activite DESC
            LIMIT 5;
            ";

            $stmt = $this->db->connect()->prepare($query);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return null;
        }
    }

   /**
     * Met à jour le thème de l'utilisateur dans la base de données.
     *
     * @param string $valueTheme Le thème à mettre à jour.
     * @param int $idUser L'ID de l'utilisateur.
     * @return bool Retourne true si la mise à jour est réussie, sinon false en cas d'échec.
     */
    public function updateTheme(string $valueTheme, int $idUser): bool
    {
        try {
            $query = "
        UPDATE user
        SET template = :valueTheme
        WHERE id = :idUser;
        ";

            $stmt =  $this->db->connect()->prepare($query);
            $stmt->bindParam(':valueTheme', $valueTheme, PDO::PARAM_STR);
            $stmt->bindParam(':idUser', $idUser, PDO::PARAM_INT);
            $stmt->execute();


            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Récupère toutes les catégories en utilisant la fonction getCategories de la base de données.
     *
     * @return array|null Tableau associatif contenant les catégories récupérées ou null en cas d'erreur.
     */
    public function getAllCategorie(): ?array
    {
        try {
            $query = "
            SELECT categorie.*, COUNT(topic.id) AS nb_topic
            FROM categorie
            INNER JOIN sujet ON categorie.id = sujet.idCategorie
            INNER JOIN topic ON sujet.id = topic.idSujet
            WHERE categorie.deletedAt IS NULL
            GROUP BY categorie.id;
        ";

            $stmt = $this->db->connect()->prepare($query);
            $stmt->execute();

            // Renvoie un tableau associatif des catégories récupérées depuis la base de données.
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // En cas d'erreur, affiche un message d'erreur et renvoie null.
            echo "Error: " . $e->getMessage();
            return null;
        }
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
        try {
            $query = "
            SELECT 
            topic.id,
            topic.nom, 
            user.pseudo AS auteur, 
            topic.updatedAt AS derniere_activite, 
            COUNT(content.id) AS nb_messages
        FROM 
            topic 
        LEFT JOIN 
            content ON topic.id = content.idTopic
        JOIN 
            user ON topic.auteur = user.id
        JOIN 
            sujet ON topic.idSujet = sujet.id
        JOIN 
            categorie ON sujet.idCategorie = categorie.id
        WHERE 
            topic.deletedAt IS NULL
        AND
            topic.valide = 1
        AND
            categorie.id = :id
        GROUP BY 
            topic.id
        ORDER BY 
            nb_messages DESC, 
            derniere_activite DESC;
                ";

            $stmt = $this->db->connect()->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return null;
        }
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
        try {
            $query = "
        SELECT c.id, c.commentaire, u.pseudo as auteur, c.createdAt, cat.nom as nomCategorie, cat.id as idCategorie
        FROM content c
        JOIN user u ON c.auteur = u.id
        JOIN topic t ON t.id = c.idTopic
        JOIN sujet s ON s.id = t.idSujet
        JOIN categorie cat ON s.idCategorie= cat.id
        WHERE idTopic = :idTopic
        AND c.deletedAt IS NULL
        ORDER BY c.createdAt ASC;
        ";

            $stmt = $this->db->connect()->prepare($query);
            $stmt->bindParam(':idTopic', $id, PDO::PARAM_INT);
            $stmt->execute();

            // Récupération des résultats
            $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $comments;
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }
}
