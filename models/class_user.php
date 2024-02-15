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
        try {
            $query = "INSERT INTO user (pseudo, email, password, idRole, token, dateToken) VALUES (:pseudo, :email, :pass, :userRoleId, :token, :dateToken)";

            $stmt = $this->db->connect()->prepare($query);
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
     * Récupère le nombre d'utilisateurs avec l'email spécifié.
     *
     * @param string $email L'email de l'utilisateur à rechercher.
     * @return int Le nombre d'utilisateurs avec l'email spécifié.
     */
    public function getUserMail(string $email): int
    {
        try {
            $query = "
                SELECT COUNT(*)
                FROM user 
                WHERE email = :email;
            ";

            $stmt = $this->db->connect()->prepare($query);
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

            $stmt = $this->db->connect()->prepare($query);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return null;
        }
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
        try {
            $query = "
            UPDATE user SET emailCheck = 1 WHERE email = :email AND token = :token AND dateToken > :date AND emailCheck = 0
            ";

            $stmt = $this->db->connect()->prepare($query);
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

                $deleteStmt = $this->db->connect()->prepare($deleteQuery);
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
     * Permet de re-valider l'email de l'utilisateur après son inscription
     *
     * @param string $email L'email de l'utilisateur à valider.
     * @param string $token Le token généré.
     * @param string $dateToken Date de validité (24h).
     * @return string|bool Le pseudo si la nouvelle verification à marché, sinon false.
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

            $stmt = $this->db->connect()->prepare($query);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result && $result['emailCheck'] == 0) {
                // Appel à la méthode pour mettre à jour le token et la dateToken
                if ($this->demandeResetPassword($email, $token, $dateToken)) {
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
     * Permet de créer un nouveau token pour réinitialiser son mot de passe
     *
     * @param string $email L'email de l'utilisateur.
     * @param string $token Le token généré.
     * @param string $dateToken Date de validité (24h).
     * @return bool True si la mise à jour du token à marché, sinon false.
     */
    public function demandeResetPassword(string $email, string $token, string $dateToken): bool
    {
        try {
            $updateQuery = "
            UPDATE user 
            SET token = :token, dateToken = :dateToken 
            WHERE email = :email
        ";

            $updateStmt = $this->db->connect()->prepare($updateQuery);
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
     * Met à jour le mot de passe 
     *
     * @param string $email L'email de l'utilisateur.
     * @param string $password Le nouveau mot de passe à mettre à jour.
     * @param string $token Le token à vérifier.
     * @param string $dateToken La date du jour.
     * @return bool True si la mise à jour a réussi et au moins une ligne a été affectée, false sinon.
     */

    public function updatePassword(string $email, string $password, string $token, string $dateToken)
    {
        try {
            $query = "
            UPDATE user 
            SET password = :password 
            WHERE email = :email 
            AND token = :token 
            AND dateToken > :dateToken
        ";

            $stmt = $this->db->connect()->prepare($query);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->bindParam(':password', $password, PDO::PARAM_STR);
            $stmt->bindParam(':token', $token, PDO::PARAM_STR);
            $stmt->bindParam(':dateToken', $dateToken, PDO::PARAM_STR);
            $stmt->execute();

            // Vérifier si au moins une ligne a été affectée
            if ($stmt->rowCount() > 0) {
                // Mettre à jour le token et la dateToken à null
                $nullUpdateQuery = "
                UPDATE user 
                SET token = NULL, dateToken = NULL 
                WHERE email = :email
            ";

                $nullUpdateStmt = $this->db->connect()->prepare($nullUpdateQuery);
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
}
