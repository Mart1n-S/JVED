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
                SELECT t.id, t.nom, u.pseudo as auteur, t.createdAt, t.updatedAt, t.deletedAt
                FROM topic t
                JOIN user u ON t.auteur = u.id;
            ";
    
            $stmt = $this->db->connect()->query($query);
            $topics = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
            return $topics;
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }

    public function editTopic( array $data): bool
    {
        try {
            $query = "
                UPDATE topic
                SET nom = :nom, auteur = :auteur, updatedAt = :updatedAt
                WHERE id = :id;
            ";
            $stmt = $this->db->connect()->prepare($query);
            $stmt->bindParam(':id',$data['id'], PDO::PARAM_INT);
            $stmt->bindParam(':nom', $data['nom'], PDO::PARAM_STR);
            $stmt->bindParam(':auteur', $data['auteur'], PDO::PARAM_STR);
            $stmt->bindValue(':updatedAt', date('Y-m-d H:i:s'), PDO::PARAM_STR);
            $stmt->execute();
    
            return true;
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }

    public function deleteTopic(int $id): bool
    {
        try {
            $query = "UPDATE topic SET deletedAt = CURRENT_TIMESTAMP WHERE id = :id";
            $stmt = $this->db->connect()->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
    
            return true;
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }
    
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
    public function editCategories( array $data): bool
    {
        try {
            $query = "
                UPDATE categorie
                SET nom = :nom, updatedAt = :updatedAt
                WHERE id = :id;
            ";
            $stmt = $this->db->connect()->prepare($query);
            $stmt->bindParam(':id',$data['id'], PDO::PARAM_INT);
            $stmt->bindValue(':nom', $data['nom'], PDO::PARAM_STR);
            $stmt->bindValue(':updatedAt', date('Y-m-d H:i:s'), PDO::PARAM_STR);
            $stmt->execute();
    
            return true;
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }

    public function deleteCategories(int $id): bool
    {
        try {
            $query = "UPDATE categorie SET deletedAt = CURRENT_TIMESTAMP WHERE id = :id";
            $stmt = $this->db->connect()->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
    
            return true;
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }
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

    public function editUsers( array $data): bool
    {
        try {
            $query = "
                UPDATE user
                SET pseudo = :pseudo, email = :email, password = :password, idRole = :idRole, bloque = :bloque, updatedAt = :updatedAt
                WHERE id = :id;
            ";
            $stmt = $this->db->connect()->prepare($query);
            $stmt->bindParam(':pseudo', $data['pseudo'], PDO::PARAM_STR);
            $stmt->bindParam(':email', $data['email'], PDO::PARAM_STR);
            $stmt->bindParam(':password', $data['password'], PDO::PARAM_STR);
            $stmt->bindParam(':bloque', $data['bloque'], PDO::PARAM_STR);
            $stmt->bindParam(':idRole', $data['idRole'], PDO::PARAM_STR);
            $stmt->bindValue(':createdAt', date('Y-m-d H:i:s'), PDO::PARAM_STR);
            $stmt->bindValue(':updatedAt', date('Y-m-d H:i:s'), PDO::PARAM_STR);
            $stmt->execute();
    
            return true;
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }

    public function deleteUsers(int $id): bool
    {
        try {
            $query = "UPDATE user SET deletedAt = CURRENT_TIMESTAMP WHERE id = :id";
            $stmt = $this->db->connect()->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
    
            return true;
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }

    public function restoreUsers(int $id): bool
    {
        try {
            $query = "UPDATE user SET deletedAt = NULL WHERE id = :id";
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