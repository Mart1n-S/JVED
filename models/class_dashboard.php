<?php

class Dashboard
{
    private $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function getTopics(): array|false
    {
        try {
            $query = "
                SELECT t.id, t.nom, u.pseudo as auteur, t.createdAt, t.updatedAt, t.deletedAt, s.nom as nomSujet
                FROM topic t
                JOIN user u ON t.auteur = u.id
                JOIN sujet s ON t.idSujet = s.id;
            ";
    
            $stmt = $this->db->connect()->query($query);
            $topics = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
            return $topics;
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }

    // public function editTopic( array $data): bool
    // {
    //     try {
    //         $query = "
    //             UPDATE topic
    //             SET nom = :nom, auteur = :auteur, updatedAt = :updatedAt
    //             WHERE id = :id;
    //         ";
    //         $stmt = $this->db->connect()->prepare($query);
    //         $stmt->bindParam(':id',$data['id'], PDO::PARAM_INT);
    //         $stmt->bindParam(':nom', $data['nom'], PDO::PARAM_STR);
    //         $stmt->bindParam(':auteur', $data['auteur'], PDO::PARAM_STR);
    //         $stmt->bindValue(':updatedAt', date('Y-m-d H:i:s'), PDO::PARAM_STR);
    //         $stmt->execute();
    
    //         return true;
    //     } catch (PDOException $e) {
    //         echo "Error: " . $e->getMessage();
    //         return false;
    //     }
    // }

    // public function deleteTopic(int $id): bool
    // {
    //     try {
    //         $query = "UPDATE topic SET deletedAt = CURRENT_TIMESTAMP WHERE id = :id";
    //         $stmt = $this->db->connect()->prepare($query);
    //         $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    //         $stmt->execute();
    
    //         return true;
    //     } catch (PDOException $e) {
    //         echo "Error: " . $e->getMessage();
    //         return false;
    //     }
    // }
    
    public function getCategories(): array|false
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
    // public function editCategories( array $data): bool
    // {
    //     try {
    //         $query = "
    //             UPDATE categorie
    //             SET nom = :nom
    //             WHERE id = :id;
    //         ";
    //         $stmt = $this->db->connect()->prepare($query);
    //         $stmt->bindParam(':id',$data['id'], PDO::PARAM_INT);
    //         $stmt->bindValue(':nom', $data['nom'], PDO::PARAM_STR);
    //         $stmt->execute();
    
    //         return true;
    //     } catch (PDOException $e) {
    //         echo "Error: " . $e->getMessage();
    //         return false;
    //     }
    // }

    // public function deleteCategories(int $id): bool
    // {
    //     try {
    //         $query = "UPDATE categorie SET deletedAt = CURRENT_TIMESTAMP WHERE id = :id";
    //         $stmt = $this->db->connect()->prepare($query);
    //         $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    //         $stmt->execute();
    
    //         return true;
    //     } catch (PDOException $e) {
    //         echo "Error: " . $e->getMessage();
    //         return false;
    //     }
    // }
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

   

    public function editItem(array $data, string $var): bool
{
    try {
        // Début de la requête
        $query = "UPDATE $var SET ";
        
        // Initialisation d'un tableau pour stocker les paramètres à lier
        $params = array();

        // Vérifie si chaque champ est défini dans $data et ajoute à la requête
        if(isset($data['pseudo']) && !empty($data['pseudo'])) {
            $query .= "pseudo = :pseudo, ";
            $params[':pseudo'] = $data['pseudo'];
        }
        if(isset($data['email']) && !empty($data['email'])) {
            $query .= "email = :email, ";
            $params[':email'] = $data['email'];
        }
        if(isset($data['password']) && !empty($data['password'])) {
            $query .= "password = :password, ";
            $params[':password'] = $data['password'];
        }
        if(isset($data['idRole'])  && !empty($data['idRole'])) {
            $query .= "idRole = :idRole, ";
            $params[':idRole'] = $data['idRole'];
        }
        if(isset($data['nom'])  && !empty($data['nom'])) {
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
        foreach($params as $param => &$value) {
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

public function getContents(): array|false
{
    try {
        $query = "
            SELECT c.id, c.commentaire, u.pseudo as auteur, t.nom as topic, c.deletedAt
            FROM content c
            JOIN user u ON c.auteur = u.id
            JOIN topic t ON c.idTopic = t.id;
        ";

        $stmt = $this->db->connect()->query($query);
        $contents = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $contents;
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
        return false;
    }
}

    public function deleteContent(int $id): bool
    {
        try {
            $query = "UPDATE content SET deletedAt = CURRENT_TIMESTAMP WHERE id = :id";
            $stmt = $this->db->connect()->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
    
            return true;
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }

    public function getRoles(){
        return $this->db->getRoles();
    }
}