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

}
