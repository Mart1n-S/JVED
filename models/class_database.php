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
     * Méthode pour obtenir les cinq sujets les plus commentés.
     *
     * @return array|null Retourne un tableau des sujets les plus commentés ou null en cas d'erreur
     */
    public function getTopTopics(): array|null
    {
        try {
            $query = "
                SELECT nom, topic.auteur, topic.updatedAt AS derniere_activite, COUNT(content.id) AS nombre_commentaires
                FROM topic 
                LEFT JOIN content ON topic.id = idTopic
                GROUP BY topic.id
                ORDER BY COUNT(content.id) DESC, topic.updatedAt DESC
                LIMIT 5;
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
}
