<?php

class Database
{
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $connexion;

    /**
     * Constructeur de la classe Database.
     *
     * @param string $host     L'hôte de la base de données
     * @param string $db_name  Le nom de la base de données
     * @param string $username Le nom d'utilisateur de la base de données
     * @param string $password Le mot de passe de la base de données
     */
    public function __construct($host, $db_name, $username, $password)
    {
        $this->host = $host;
        $this->db_name = $db_name;
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * Méthode pour établir la connexion à la base de données.
     *
     * @return PDO|null Retourne l'instance PDO ou null en cas d'erreur de connexion
     */
    public function connect(): PDO|null
    {
        $this->connexion = null;

        try {
            $this->connexion = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->connexion->query("SET CHARACTER SET utf8");
            $this->connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo "Connection Error: " . $e->getMessage();
        }

        return $this->connexion;
    }


    /**
     * Récupère toutes les catégories non supprimées de la base de données.
     *
     * @return array|null Tableau associatif contenant les catégories récupérées ou null en cas d'erreur.
     */
    public function getCategories(): ?array
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

            $stmt = $this->connect()->prepare($query);
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
     * Récupère tous les sujets associés à une catégorie spécifique à partir de la base de données.
     *
     * @param int $id L'identifiant de la catégorie.
     * @return array|null Tableau associatif contenant les sujets récupérés ou null en cas d'erreur.
     */
    public function getTopicsFromCategorie(int $id): ?array
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

            $stmt = $this->connect()->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return null;
        }
    }

    /**
     * Récupère tous les commentaires associés à un sujet spécifique à partir de la base de données.
     *
     * @param int $idTopic L'identifiant du sujet.
     * @return array|false Tableau associatif contenant les commentaires récupérés ou false en cas d'erreur.
     */
    public function getCommentairesFromTopic(int $idTopic): array|bool
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

            $stmt = $this->connect()->prepare($query);
            $stmt->bindParam(':idTopic', $idTopic, PDO::PARAM_INT);
            $stmt->execute();

            // Récupération des résultats
            $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $comments;
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Récupère le nombre d'utilisateurs avec l'email spécifié.
     *
     * @param string $email L'email de l'utilisateur à rechercher.
     * @return int|false Le nombre d'utilisateurs avec l'email spécifié ou false si une erreur se produit.
     */
    public function getUserMail(string $email): int|false
    {
        try {
            $query = "
                SELECT COUNT(*)
                FROM user 
                WHERE email = :email;
            ";

            $stmt = $this->connect()->prepare($query);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();

            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return null;
        }
    }

    /**
     * Récupère tous les rôles disponibles.
     *
     * @return array|null Un tableau associatif contenant les rôles ou null en cas d'erreur.
     */
    public function getRoles(): array|null
    {
        try {
            $query = "
                SELECT *
                FROM role;
            ";

            $stmt = $this->connect()->prepare($query);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return null;
        }
    }

    /**
     * Crée un nouvel utilisateur dans la base de données.
     *
     * @param string $pseudo Le pseudo de l'utilisateur.
     * @param string $email L'email de l'utilisateur.
     * @param string $password Le mot de passe de l'utilisateur.
     * @param string $userRoleId L'ID du rôle de l'utilisateur.
     * @param string $token Le token générer pour check le mail.
     * @param string $dateToken Date de validité (24h).
     * @return bool|null True si l'utilisateur a été créé avec succès, sinon null en cas d'erreur.
     */
    public function createUser(string $pseudo, string $email, string $password, string $userRoleId, string $token, string $dateToken): bool
    {
        try {
            $query = "INSERT INTO user (pseudo, email, password, idRole, token, dateToken) VALUES (:pseudo, :email, :pass, :userRoleId, :token, :dateToken)";

            $stmt = $this->connect()->prepare($query);
            $stmt->bindParam(':pseudo', $pseudo, PDO::PARAM_STR);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->bindParam(':pass', $password, PDO::PARAM_STR);
            $stmt->bindParam(':userRoleId', $userRoleId, PDO::PARAM_STR);
            $stmt->bindParam(':token', $token, PDO::PARAM_STR);
            $stmt->bindParam(':dateToken', $dateToken, PDO::PARAM_STR);

            return $stmt->execute();
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return null;
        }
    }

    /**
     * Récupère les informations de l'utilisateur à partir de son email.
     *
     * @param string $email L'email de l'utilisateur à rechercher.
     * @return array|bool Retourne un tableau contenant les informations de l'utilisateur s'il est trouvé, sinon false en cas d'échec ou d'erreur.
     */
    public function getUser(string $email): array|bool
    {
        try {
            $query = "
            SELECT user.*, roleName as role 
            FROM user 
            INNER JOIN role ON user.idRole = role.id 
            WHERE (bloque IS NULL OR bloque <> 1) 
            AND deletedAt IS NULL
            AND emailCheck != 0 
            AND email = :email;
            ";

            $stmt = $this->connect()->prepare($query);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return null;
        }
    }

    /**
     * Crée un nouvel utilisateur dans la base de données.
     *
     * @param string $email L'email de l'utilisateur.
     * @param string $token Date de validité (24h).
     * @param string $date Date du jour pour check si token invalide
     * @return bool True si le mail est check, false si l'email existe pas ou si token expiré
     */
    public function verificationEmailUser(string $email, string $token, string $date): bool
    {
        try {
            $query = "
            UPDATE user SET emailCheck = 1 WHERE email = :email AND token = :token AND dateToken > :date AND emailCheck = 0
            ";

            $stmt = $this->connect()->prepare($query);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->bindParam(':token', $token, PDO::PARAM_STR);
            $stmt->bindParam(':date', $date, PDO::PARAM_STR);
            $stmt->execute();

            // Vérifie si la mise à jour a affecté une ligne dans la base de données
            if ($stmt->rowCount() > 0) {
                // Mise à jour pour supprimer le token et la dateToken
                $deleteQuery = "
            UPDATE user SET token = NULL, dateToken = NULL WHERE email = :email
            ";

                $deleteStmt = $this->connect()->prepare($deleteQuery);
                $deleteStmt->bindParam(':email', $email, PDO::PARAM_STR);
                $deleteStmt->execute();

                return true;
            } else {
                return false;
            }
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return null;
        }
    }

    /**
     * Met à jour le token et la dateToken en base de données pour l'email correspondant si emailCheck = 0.
     *
     * @param string $email L'email de l'utilisateur.
     * @param string $token Le nouveau token à mettre à jour.
     * @param string $dateToken La nouvelle date de validité du token.
     * @return string|bool Le pseudo si la mise à jour a réussi, false sinon.
     */
    public function reVerificationEmailUser(string $email, string $token, string $dateToken): string|bool
    {
        try {
            $query = "
        SELECT pseudo, emailCheck 
        FROM user 
        WHERE email = :email 
        AND emailCheck = 0
        ";

            $stmt = $this->connect()->prepare($query);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result && $result['emailCheck'] == 0) {
                // Appel à la méthode pour mettre à jour le token et la dateToken
                if ($this->updateTokenAndDateToken($email, $token, $dateToken)) {
                    return $result['pseudo'];
                } else {
                    return false;
                }
            } else {
                // L'email est déjà vérifié ou n'existe pas
                return false;
            }
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Met à jour le token et la dateToken en base de données pour l'email correspondant.
     *
     * @param string $email L'email de l'utilisateur.
     * @param string $token Le nouveau token à mettre à jour.
     * @param string $dateToken La nouvelle date de validité du token.
     * @return bool True si la mise à jour a réussi, false sinon.
     */
    public function updateTokenAndDateToken(string $email, string $token, string $dateToken): bool
    {
        try {
            $updateQuery = "
            UPDATE user 
            SET token = :token, dateToken = :dateToken 
            WHERE email = :email
        ";

            $updateStmt = $this->connect()->prepare($updateQuery);
            $updateStmt->bindParam(':email', $email, PDO::PARAM_STR);
            $updateStmt->bindParam(':token', $token, PDO::PARAM_STR);
            $updateStmt->bindParam(':dateToken', $dateToken, PDO::PARAM_STR);
            $updateStmt->execute();

            // Vérifier si au moins une ligne a été affectée par la mise à jour
            if ($updateStmt->rowCount() > 0) {
                return true;
            } else {
                return false;
            }
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Met à jour le mot de passe en base de données pour l'email correspondant si la date est inférieure à la dateToken.
     * Si au moins une ligne a été affectée, met à jour le token et la dateToken à null pour l'email correspondant et retourne true.
     * Sinon, retourne false.
     *
     * @param string $email L'email de l'utilisateur.
     * @param string $password Le nouveau mot de passe à mettre à jour.
     * @param string $token Le token à vérifier.
     * @param string $date La date du jour.
     * @return bool True si la mise à jour a réussi et au moins une ligne a été affectée, false sinon.
     */
    public function updatePassword(string $email, string $password, string $token, string $date): bool
    {
        try {
            $query = "
            UPDATE user 
            SET password = :password 
            WHERE email = :email 
            AND token = :token 
            AND dateToken > :dateToken
        ";

            $stmt = $this->connect()->prepare($query);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->bindParam(':password', $password, PDO::PARAM_STR);
            $stmt->bindParam(':token', $token, PDO::PARAM_STR);
            $stmt->bindParam(':dateToken', $date, PDO::PARAM_STR);
            $stmt->execute();

            // Vérifier si au moins une ligne a été affectée
            if ($stmt->rowCount() > 0) {
                // Mettre à jour le token et la dateToken à null
                $nullUpdateQuery = "
                UPDATE user 
                SET token = NULL, dateToken = NULL 
                WHERE email = :email
            ";

                $nullUpdateStmt = $this->connect()->prepare($nullUpdateQuery);
                $nullUpdateStmt->bindParam(':email', $email, PDO::PARAM_STR);
                $nullUpdateStmt->execute();

                return true;
            } else {
                return false;
            }
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Met à jour le thème de l'utilisateur dans la base de données.
     *
     * @param string $valueTheme Le thème à mettre à jour.
     * @param int $idUser L'ID de l'utilisateur.
     * @return bool Retourne true si la mise à jour est réussie, sinon false en cas d'échec.
     */
    public function updateThemeUser(string $valueTheme, int $idUser): bool
    {
        try {
            $query = "
        UPDATE user
        SET template = :valueTheme
        WHERE id = :idUser;
        ";

            $stmt =  $this->connect()->prepare($query);
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
     * Méthode pour obtenir les topics de l'utilisateur
     *
     * @param int $idUser L'ID de l'utilisateur dont on veut récupérer les topics
     * @return array|null Retourne un tableau des topics de l'utilisateur ou null en cas d'erreur
     */
    public function getTopicsUser(int $idUser): array|null
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

            $stmt = $this->connect()->prepare($query);
            $stmt->bindParam(':id', $idUser, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return null;
        }
    }

    /**
     * Méthode pour supprimer un topic de manière logique.
     *
     * @param int $idTopic L'ID du topic à supprimer.
     * @param int $idUser L'ID de l'utilisateur qui supprime le topic.
     * @return bool Retourne true si la suppression est réussie, sinon false.
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

            $stmt = $this->connect()->prepare($query);
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
}
