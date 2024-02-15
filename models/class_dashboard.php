<?php

class Dashboard
{
    private $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Récupère tous les sujets valides avec leurs informations associées.
     *
     * @return array|false Un tableau contenant les informations des sujets valides, ou false en cas d'erreur.
     */
    public function getTopics(): array|false
    {
        try {
            $query = "
                SELECT t.id, t.nom, u.pseudo as auteur, t.createdAt, t.updatedAt, t.deletedAt, s.nom as nomSujet, t.valide
                FROM topic t
                JOIN user u ON t.auteur = u.id
                JOIN sujet s ON t.idSujet = s.id
                WHERE t.valide = 1 ;
            ";

            $stmt = $this->db->connect()->query($query);
            $topics = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $topics;
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Récupère les articles en attente de validation.
     *
     * Cette méthode récupère les articles en attente de validation à partir de la base de données.
     * Elle sélectionne les informations pertinentes sur les articles non validés, y compris l'identifiant,
     * le nom, l'auteur, la date de création, la date de mise à jour, le nom du sujet, et l'état de validation.
     * Seuls les articles non validés qui ne sont pas supprimés sont récupérés.
     *
     * @return array|false Un tableau associatif contenant les articles en attente de validation,
     * ou false en cas d'erreur.
     */
    public function getTopicsWaitingValidation(): array|false
    {
        try {
            $query = "
                SELECT t.id, t.nom, u.pseudo as auteur, t.createdAt, t.updatedAt, t.deletedAt, s.nom as nomSujet, t.valide
                FROM topic t
                JOIN user u ON t.auteur = u.id
                JOIN sujet s ON t.idSujet = s.id
                WHERE t.valide = 0 
                AND t.deletedAt IS NULL;
            ";

            $stmt = $this->db->connect()->query($query);
            $topics = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $topics;
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Accepte un sujet en le validant.
     *
     * Cette méthode prend en paramètre l'identifiant du sujet à valider et met à jour son état de validation à 1.
     * 
     * @param int $id L'identifiant du sujet à valider.
     * @return bool True si le sujet a été validé avec succès, sinon False.
     */
    public function acceptItem(int $id): bool
    {
        try {
            $query = "
            UPDATE topic 
            SET valide = 1 
            WHERE id = :id;
        ";

            $stmt = $this->db->connect()->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_STR);
            $stmt->execute();

            return true;
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }


    /**
     * Récupère toutes les catégories depuis la base de données.
     *
     * @return array|bool Un tableau contenant toutes les catégories ou false en cas d'erreur.
     */
    public function getCategories(): array|bool
    {
        try {
            $query = "
                SELECT id, nom, createdAt, updatedAt, deletedAt
                FROM categorie;
            ";

            $stmt = $this->db->connect()->query($query);
            $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $categories;
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Ajoute une nouvelle catégorie à la base de données avec les données fournies.
     * 
     * @param array $data Les données de la nouvelle catégorie à ajouter.
     * @return bool True en cas de succès de l'ajout, sinon False.
     */
    public function addCategories(array $data): bool
    {
        try {
            $query = "
                INSERT INTO categorie (nom, createdAt, updatedAt)
                VALUES (:nom, :createdAt, :updatedAt);
            ";

            $stmt = $this->db->connect()->prepare($query);
            $stmt->bindParam(':nom', $data['nom'], PDO::PARAM_STR);
            $stmt->bindValue(':createdAt', date('Y-m-d H:i:s'), PDO::PARAM_STR);
            $stmt->bindValue(':updatedAt', date('Y-m-d H:i:s'), PDO::PARAM_STR);
            $stmt->execute();

            return true;
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }
    /**
     * Récupère la liste des utilisateurs depuis la base de données.
     * 
     * Cette méthode exécute une requête SQL pour récupérer les informations des utilisateurs.
     * Elle sélectionne l'identifiant, le pseudo, l'email, le rôle, l'état de blocage, les dates de création, de mise à jour et de suppression des utilisateurs.
     * Elle effectue une jointure gauche avec la table des rôles pour obtenir le nom du rôle de chaque utilisateur.
     * 
     * @return array|false Retourne un tableau contenant les informations des utilisateurs s'ils existent, sinon retourne false en cas d'erreur.
     */
    public function getUsers(): array|false
    {
        try {
            $query = "
                SELECT u.id, u.pseudo, u.email, r.roleName, u.bloque, u.createdAt, u.updatedAt, u.deletedAt
                FROM user u
                LEFT JOIN role r ON u.idRole = r.id;
            ";

            $stmt = $this->db->connect()->query($query);
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $users;
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }


    /**
     * Modifie un élément avec les données spécifiées dans $data pour une table SQL donnée par $var.
     *
     * @param array $data Les données à modifier.
     * @param string $var La table SQL à modifier.
     * @return bool True si la modification réussit, sinon false.
     */
    public function editItem(array $data, string $var): bool
    {
        try {
            // Début de la requête
            $query = "UPDATE $var SET ";

            // Initialisation d'un tableau pour stocker les paramètres à lier
            $params = array();

            // Vérifie si chaque champ est défini dans $data et ajoute à la requête
            if (isset($data['pseudo']) && !empty($data['pseudo'])) {
                $query .= "pseudo = :pseudo, ";
                $params[':pseudo'] = $data['pseudo'];
            }
            if (isset($data['email']) && !empty($data['email'])) {
                $query .= "email = :email, ";
                $params[':email'] = $data['email'];
            }
            if (isset($data['password']) && !empty($data['password'])) {
                $query .= "password = :password, ";
                $params[':password'] = $data['password'];
            }
            if (isset($data['idRole'])  && !empty($data['idRole'])) {
                $query .= "idRole = :idRole, ";
                $params[':idRole'] = $data['idRole'];
            }
            if (isset($data['nom'])  && !empty($data['nom'])) {
                $query .= "nom = :nom, ";
                $params[':nom'] = $data['nom'];
            }

            // Suppression de la virgule en trop à la fin de la requête
            $query = rtrim($query, ', ');

            // Ajout de la condition WHERE
            $query .= " WHERE id = :id;";

            // Préparation de la requête
            $stmt = $this->db->connect()->prepare($query);

            // Lier les paramètres à la requête
            foreach ($params as $param => &$value) {
                $stmt->bindParam($param, $value, PDO::PARAM_STR);
            }

            // Lier l'ID (toujours lié)
            $stmt->bindParam(':id', $data['id'], PDO::PARAM_STR);

            // Exécution de la requête
            $stmt->execute();

            return true;
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Supprime un élément de la base de données en le marquant comme supprimé.
     *
     * Cette méthode prend en paramètre l'identifiant de l'élément à supprimer et le nom de la table dans laquelle l'élément est stocké.
     * L'élément est marqué comme supprimé en définissant la date de suppression (deletedAt) sur l'heure actuelle.
     * 
     * @param int $id L'identifiant de l'élément à supprimer.
     * @param string $var Le nom de la table dans laquelle l'élément est stocké.
     * @return bool True si l'élément a été supprimé avec succès, sinon False.
     */
    public function deleteItem(int $id, string $var): bool
    {
        try {
            $query = "UPDATE $var SET deletedAt = CURRENT_TIMESTAMP WHERE id = :id";
            $stmt = $this->db->connect()->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            return true;
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }
    /**
     * Restaure un élément supprimé en réinitialisant la date de suppression.
     *
     * Cette méthode prend en paramètre l'identifiant de l'élément à restaurer et le nom de la table dans laquelle l'élément est stocké.
     * L'élément est restauré en réinitialisant la date de suppression (deletedAt) à NULL.
     * 
     * @param int $id L'identifiant de l'élément à restaurer.
     * @param string $var Le nom de la table dans laquelle l'élément est stocké.
     * @return bool True si l'élément a été restauré avec succès, sinon False.
     */
    public function restoreItem(int $id, string $var): bool
    {
        try {
            $query = "UPDATE $var SET deletedAt = NULL WHERE id = :id";
            $stmt = $this->db->connect()->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            return true;
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Bloque un élément en définissant son état "bloqué".
     *
     * Cette méthode prend en paramètre l'identifiant de l'élément à bloquer et le nom de la table dans laquelle l'élément est stocké.
     * L'élément est bloqué en définissant la valeur de l'attribut "bloque" sur 1.
     * 
     * @param int $id L'identifiant de l'élément à bloquer.
     * @param string $var Le nom de la table dans laquelle l'élément est stocké.
     * @return bool True si l'élément a été bloqué avec succès, sinon False.
     */
    public function blockItem(int $id, string $var): bool
    {
        try {
            $query = "UPDATE $var SET bloque = 1 WHERE id = :id";
            $stmt = $this->db->connect()->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            return true;
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Débloque un élément en supprimant son état "bloqué".
     *
     * Cette méthode prend en paramètre l'identifiant de l'élément à débloquer et le nom de la table dans laquelle l'élément est stocké.
     * L'élément est débloqué en définissant la valeur de l'attribut "bloque" sur NULL.
     * 
     * @param int $id L'identifiant de l'élément à débloquer.
     * @param string $var Le nom de la table dans laquelle l'élément est stocké.
     * @return bool True si l'élément a été débloqué avec succès, sinon False.
     */
    public function unblockItem(int $id, string $var): bool
    {
        try {
            $query = "UPDATE $var SET bloque = NULL WHERE id = :id";
            $stmt = $this->db->connect()->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            return true;
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Ajoute un nouvel utilisateur à la base de données.
     *
     * Cette méthode prend en paramètre un tableau contenant les données de l'utilisateur à ajouter.
     * Les données comprennent le pseudo, l'email, l'identifiant du rôle, le mot de passe et l'état de vérification de l'email.
     * 
     * @param array $data Les données de l'utilisateur à ajouter.
     * @return bool True si l'utilisateur a été ajouté avec succès, sinon False.
     */
    public function addUsers(array $data): bool
    {
        try {
            $query = "
            INSERT INTO user (pseudo, email, idRole, password, emailCheck)
            VALUES (:pseudo, :email, :idRole, :password, 1);
        ";

            $stmt = $this->db->connect()->prepare($query);
            $stmt->bindParam(':pseudo', $data['pseudo'], PDO::PARAM_STR);
            $stmt->bindParam(':email', $data['email'], PDO::PARAM_STR);
            $stmt->bindParam(':password', $data['password'], PDO::PARAM_STR);
            $stmt->bindParam(':idRole', $data['idRole'], PDO::PARAM_STR);
            $stmt->execute();

            return true;
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Récupère le contenu associé à un sujet donné.
     *
     * Cette méthode prend en paramètre l'identifiant du sujet et retourne un tableau contenant les informations sur le contenu associé.
     * Les informations comprennent l'identifiant du contenu, le commentaire, le pseudo de l'auteur, le nom du sujet, la date de suppression et l'état de validation du sujet.
     * 
     * @param int $idTopics L'identifiant du sujet pour lequel récupérer le contenu.
     * @return array|bool Un tableau contenant les informations sur le contenu associé, ou False en cas d'erreur.
     */
    public function getContents(int $idTopics): array|bool
    {
        try {
            $query = "
                SELECT c.id, c.commentaire, u.pseudo as auteur, t.nom as topic, c.deletedAt, t.valide
                FROM content c
                JOIN user u ON c.auteur = u.id
                JOIN topic t ON c.idTopic = t.id
                WHERE idTopic = :idTopic; 
            ";

            $stmt = $this->db->connect()->prepare($query);
            $stmt->bindParam(':idTopic', $idTopics, PDO::PARAM_INT);
            $stmt->execute();
            $contents = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $contents;
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }


    /**
     * Récupère les rôles depuis la base de données.
     * 
     * Cette méthode fait appel à la méthode correspondante de l'instance de base de données pour obtenir les rôles enregistrés dans la base de données.
     * 
     * @return array|null Un tableau contenant les données des rôles ou null en cas d'erreur.
     */
    public function getRoles()
    {
        return $this->db->getRoles();
    }
}
