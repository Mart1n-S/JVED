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
        try {
            $query = "
            SELECT 
            topic.nom AS topicNom, 
            sujet.nom AS sujetNom, 
            topic.id AS topicId,
            categorie.nom AS categorieNom, 
            categorie.id AS categorieId
            FROM topic
            INNER JOIN 
                sujet ON topic.idSujet = sujet.id
            INNER JOIN categorie ON sujet.idCategorie = categorie.id
            WHERE topic.auteur = :id
            AND topic.valide = 1
            AND sujet.deletedAt IS NULL 
            AND topic.deletedAt IS NULL;
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
     * Permet de supprimer de manière logique un topic
     *
     * @param int $idTopic L'ID du topic.
     * @param int $idUser L'ID de l'utilisateur.
     * @return bool Retourne true si c'est bon, false sinon
     */
    public function deleteTopicsUser(int $idTopic, int $idUser): bool
    {
        try {
            // Requête SQL pour supprimer logiquement le topic
            $query = "
        UPDATE topic
        SET deletedAt = CONVERT_TZ(NOW(), 'UTC', 'Europe/Paris')
        WHERE id = :idTopic
        AND auteur = :idUser
        AND deletedAt IS NULL;
        ";

            $stmt = $this->db->connect()->prepare($query);
            $stmt->bindParam(':idTopic', $idTopic, PDO::PARAM_INT);
            $stmt->bindParam(':idUser', $idUser, PDO::PARAM_INT);
            $stmt->execute();

            // Vérification du nombre de lignes affectées
            if ($stmt->rowCount() > 0) {
                return true; // Suppression réussie
            } else {
                return false; // Aucun topic n'a été supprimé
            }
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }


    /**
     * Ajoute un commentaire à un sujet spécifique dans la base de données.
     *
     * @param int $idTopic L'identifiant du sujet auquel ajouter le commentaire.
     * @param string $commentaire Le contenu du commentaire à ajouter.
     * @param int $idUser L'identifiant de l'utilisateur qui ajoute le commentaire.
     * @return bool True si le commentaire a été ajouté avec succès, sinon False.
     */
    public function addCommentaire(int $idTopic, string $commentaire, int $idUser): bool
    {
        try {
            $query = "
            INSERT INTO content (idTopic, commentaire, auteur)
            VALUES (:idTopic, :commentaire, :idUser)
            ";

            $stmt = $this->db->connect()->prepare($query);
            $stmt->bindParam(':idTopic', $idTopic, PDO::PARAM_INT);
            $stmt->bindParam(':idUser', $idUser, PDO::PARAM_INT);
            $stmt->bindParam(':commentaire', $commentaire, PDO::PARAM_STR);
            $stmt->execute();



            return true;
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Crée un nouveau sujet avec un nouveau topic et un commentaire associé.
     *
     * @param int $idCategorie L'identifiant de la catégorie à laquelle le sujet appartient.
     * @param string $nomSujet Le nom du sujet.
     * @param string $nomTopic Le nom du topic.
     * @param string $commentaire Le commentaire associé au topic.
     * @param int $idUser L'identifiant de l'utilisateur qui crée le topic.
     * @return bool True si le sujet et le topic ont été créés avec succès, sinon False.
     */
    public function createTopic(int $idCategorie, string $nomSujet, string $nomTopic, string $commentaire, int $idUser): bool
    {
        $pdo = $this->db->connect();

        try {
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->beginTransaction();

            // Étape 1: Création du Sujet
            $querySujet = "INSERT INTO sujet (nom, auteur, idCategorie) VALUES (:nom, :auteur, :idCategorie)";
            $stmt = $pdo->prepare($querySujet);
            $stmt->bindParam(':nom', $nomSujet, PDO::PARAM_STR);
            $stmt->bindParam(':auteur', $idUser, PDO::PARAM_INT);
            $stmt->bindParam(':idCategorie', $idCategorie, PDO::PARAM_INT);
            $stmt->execute();
            $lastSubjectId = $pdo->lastInsertId();

            // Étape 2: Création du Topic
            $queryTopic = "INSERT INTO topic (nom, auteur, idSujet) VALUES (:nom, :auteur, :idSujet)";
            $stmt = $pdo->prepare($queryTopic);
            $stmt->bindParam(':nom', $nomTopic, PDO::PARAM_STR);
            $stmt->bindParam(':auteur', $idUser, PDO::PARAM_INT);
            $stmt->bindParam(':idSujet', $lastSubjectId, PDO::PARAM_INT);
            $stmt->execute();
            $lastTopicId = $pdo->lastInsertId();

            // Étape 3: Ajout du Commentaire
            $queryCommentaire = "INSERT INTO content (commentaire, auteur, idTopic) VALUES (:commentaire, :auteur, :idTopic)";
            $stmt = $pdo->prepare($queryCommentaire);
            $stmt->bindParam(':commentaire', $commentaire, PDO::PARAM_STR);
            $stmt->bindParam(':auteur', $idUser, PDO::PARAM_INT);
            $stmt->bindParam(':idTopic', $lastTopicId, PDO::PARAM_INT);
            $stmt->execute();

            $pdo->commit();
            return true;
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("Error: " . $e->getMessage());
            return false;
        }
    }


    /**
     * Récupère les sujets auxquels l'utilisateur a participé.
     *
     * @param int $idUser L'identifiant de l'utilisateur.
     * @return array|null Un tableau contenant les informations sur les sujets auxquels l'utilisateur a participé, ou null en cas d'erreur.
     */
    public function getParticipationFromTopic(int $idUser): ?array
    {
        try {
            $query = "
            SELECT DISTINCT
            topic.nom AS topicNom, 
            sujet.nom AS sujetNom, 
            topic.id AS topicId,
            categorie.nom AS categorieNom, 
            categorie.id AS categorieId
            FROM topic
            LEFT JOIN content ON content.idTopic = topic.id
            INNER JOIN 
                sujet ON topic.idSujet = sujet.id
            INNER JOIN categorie ON sujet.idCategorie = categorie.id
            WHERE content.auteur = :id
            AND topic.valide = 1
            AND sujet.deletedAt IS NULL 
            AND topic.deletedAt IS NULL;
            ";

            $stmt = $this->db->connect()->prepare($query);
            $stmt->bindParam(':id', $idUser, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return null;
        }
    }
}
