
<?php
class likeC {
    private $pdo;

    public function __construct() {
        $db = new PDO('mysql:host=localhost;dbname=blog 2', 'root', '');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo = $db;
    }

    // Like ou unlike un post
    public function toggleLike($user_id, $blog_id) {
        $sql = "SELECT id FROM likes WHERE user_id = ? AND blog_id = ? AND commentaire_id IS NULL";
        $req = $this->pdo->prepare($sql);
        $req->execute([$user_id, $blog_id]);

        if ($req->fetch()) {
            // déjà liké → on supprime
            $sql = "DELETE FROM likes WHERE user_id = ? AND blog_id = ? AND commentaire_id IS NULL";
            $this->pdo->prepare($sql)->execute([$user_id, $blog_id]);
            return 'unliked';
        } else {
            // pas encore liké → on ajoute
            $sql = "INSERT INTO likes (user_id, blog_id) VALUES (?, ?)";
            $this->pdo->prepare($sql)->execute([$user_id, $blog_id]);
            return 'liked';
        }
    }

    public function countLikes($blog_id) {
        $sql = "SELECT COUNT(*) FROM likes WHERE blog_id = ? AND commentaire_id IS NULL";
        $req = $this->pdo->prepare($sql);
        $req->execute([$blog_id]);
        return $req->fetchColumn();
    }

    public function isLiked($user_id, $blog_id) {
        $sql = "SELECT 1 FROM likes WHERE user_id = ? AND blog_id = ? AND commentaire_id IS NULL";
        $req = $this->pdo->prepare($sql);
        $req->execute([$user_id, $blog_id]);
        return $req->rowCount() > 0;
    }
}
?>
