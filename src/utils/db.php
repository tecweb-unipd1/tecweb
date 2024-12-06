<?php

class DB
{
    private PDO $conn;

    public function __construct($host, $dbname, $user, $pass)
    {
        try {
            $this->conn = new PDO("mysql:host={$host};dbname={$dbname}", $user, $pass, [PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_BOTH]);
        } catch (PDOException $e) {
            throw new Exception("Errore di connessione al database: {$e}");
        }
    }

    public static function from_env(): Self
    {
        $host = getenv("DB_HOST");
        $user = getenv("DB_USER");
        $pass = getenv("DB_PASS");
        $dbname = getenv("DB_NAME");

        return new Self($host, $dbname, $user, $pass);
    }

    public function get_user_with_password(string $username, string $password): mixed
    {
        // TODO: use hashing
        $stmt = $this->conn->prepare("SELECT * FROM user WHERE username = :username");

        $res = $stmt->execute(["username" => $username]);
        if ($res) {
            $row = $stmt->fetch();
            if ($row && $row["password"] == $password) {
                return $row;
            }
            return null;
        }

        return null;
    }

    public function get_user(string $username): mixed
    {
        $stmt = $this->conn->prepare("SELECT * FROM user WHERE username = :username");

        $res = $stmt->execute(["username" => $username]);
        if ($res) {
            $row = $stmt->fetch();
            return $row;
        }

        return null;
    }

    public function create_user(string $username, string $password, string $name, string $last_name): bool
    {
        $stmt = $this->conn->prepare("INSERT INTO user (username, password, name, last_name, is_admin) VALUES (:username, :password, :name, :last_name, 0)");

        $res = $stmt->execute([
            "username" => $username,
            "password" => $password,
            "name" => $name,
            "last_name" => $last_name,
        ]);

        return $res && $stmt->rowCount() > 0;
    }

    public function get_users(): ?array
    {
        $stmt = $this->conn->prepare("SELECT * FROM user");

        $res = $stmt->execute([]);
        if ($res) {
            $row = $stmt->fetchAll();
            return $row;
        }

        return null;
    }

    public function get_movies(): ?array
    {
        $stmt = $this->conn->prepare("SELECT * FROM movie");

        $res = $stmt->execute([]);
        if ($res) {
            $row = $stmt->fetchAll();
            return $row;
        }

        return null;
    }

    public function get_movies_by_category(string $category): ?array
    {
        $stmt = $this->conn->prepare("SELECT * FROM movie
                          JOIN movie_category ON movie.id = movie_category.movie_id
                          WHERE movie_category.category_name = :category");

        $res = $stmt->execute(["category" => $category]);
        if ($res) {
            $row = $stmt->fetchAll();
            return $row;
        }

        return null;
    }

    public function get_category(string $name): ?array
    {
        $stmt = $this->conn->prepare("SELECT * FROM category WHERE name = :name");

        $res = $stmt->execute(["name" => $name]);
        if ($res) {
            $row = $stmt->fetchAll();
            return $row;
        }

        return null;
    }

    public function get_movie(int $id): ?array
    {
        $stmt = $this->conn->prepare("SELECT * FROM movie WHERE id = :id");

        $res = $stmt->execute(["id" => $id]);
        if ($res) {
            $row = $stmt->fetch();
            if (!$row) return null;
            return $row;
        }

        return null;
    }

    public function get_categories(): ?array
    {
        $stmt = $this->conn->prepare("SELECT DISTINCT * FROM category");

        $res = $stmt->execute();
        if ($res) {
            $row = $stmt->fetchAll();
            return $row;
        }

        return null;
    }

    public function get_reviews(int $film_id): ?array
    {
        $stmt = $this->conn->prepare("SELECT * FROM review WHERE movie_id = :film_id");

        $res = $stmt->execute(["film_id" => $film_id]);
        if ($res) {
            $row = $stmt->fetchAll();
            return $row;
        }

        return null;
    }

    public function create_review(int $film_id, string $username, string $title, string $content, int $rating): bool
    {
        $stmt = $this->conn->prepare("INSERT INTO review (
            title,
            content,
            rating,
            username,
            movie_id,
            data
        ) VALUES (
            :title,
            :content,
            :rating,
            :username,
            :film_id,
            NOW()
        )");

        $res = $stmt->execute([
            "film_id" => $film_id,
            "username" => $username,
            "title" => $title,
            "content" => $content,
            "rating" => $rating,
        ]);
        return $res && $stmt->rowCount() > 0;
    }
}
