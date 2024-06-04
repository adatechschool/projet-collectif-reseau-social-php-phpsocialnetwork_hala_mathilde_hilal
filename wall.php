<?php
include("config.php");
// Check if the user is logged in
if (!isset($_SESSION['connected_id'])) {
    header("Location: login.php");
    exit();
}

// Get the user ID from the session
$userId = intval($_SESSION['connected_id']);
?>

<!doctype html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <title>ReSoC - Mur</title> 
        <meta name="author" content="Julien Falconnet">
        <link rel="stylesheet" href="style.css"/>
    </head>
    <body>
        <header>
        <a href='admin.php'><img src="resoc.jpg" alt="Logo de notre réseau social"/></a>
            <nav id="menu">
                <a href="news.php">Actualités</a>
                <a href="wall.php?user_id=<?php echo $userId; ?>">Mur</a>
                <a href="feed.php?user_id=<?php echo $userId; ?>">Flux</a>
                <a href="tags.php?tag_id=1">Mots-clés</a>
            </nav>
            <nav id="user">
                <a href="#">Profil</a>
                <ul>
                    <li><a href="settings.php?user_id=<?php echo $userId; ?>">Paramètres</a></li>
                    <li><a href="followers.php?user_id=<?php echo $userId; ?>">Mes suiveurs</a></li>
                    <li><a href="subscriptions.php?user_id=<?php echo $userId; ?>">Mes abonnements</a></li>
                </ul>
            </nav>
        </header>
        <div id="wrapper">
            <aside>
                <?php
                // Fetch user information
                $laQuestionEnSql = "SELECT * FROM users WHERE id= '$userId'";
                $lesInformations = $mysqli->query($laQuestionEnSql);
                $user = $lesInformations->fetch_assoc();
                ?>
                <img src="user.jpg" alt="Portrait de l'utilisateur"/>
                <section>
                    <h3>Présentation</h3>
                    <p>Sur cette page vous trouverez tous les messages de l'utilisateur : <?php echo htmlspecialchars($user["alias"]); ?> (n° <?php echo $userId; ?>)</p>
                </section>
            </aside>
            <main>
                <?php
                // Fetch posts of the user
                $laQuestionEnSql = "
                    SELECT posts.content, posts.created, users.alias as author_name, users.id as author_id, 
                    COUNT(likes.id) as like_number, GROUP_CONCAT(DISTINCT tags.label) AS taglist 
                    FROM posts
                    JOIN users ON users.id=posts.user_id
                    LEFT JOIN posts_tags ON posts.id = posts_tags.post_id  
                    LEFT JOIN tags ON posts_tags.tag_id = tags.id 
                    LEFT JOIN likes ON likes.post_id = posts.id 
                    WHERE posts.user_id='$userId' 
                    GROUP BY posts.id
                    ORDER BY posts.created DESC
                ";
                $lesInformations = $mysqli->query($laQuestionEnSql);
                if (!$lesInformations) {
                    echo("Échec de la requête : " . $mysqli->error);
                }

                // Display posts
                while ($post = $lesInformations->fetch_assoc()) {
                    ?>
                    <article>
                        <h3>
                            <time><?php echo $post['created']; ?></time>
                        </h3>
                        <address><a href="wall.php?user_id=<?php echo $post['author_id']; ?>"><?php echo htmlspecialchars($post['author_name']); ?></a></address>
                        <div>
                            <?php
                            $cleaned_content = str_replace('#', '', $post['content']);
                            echo "<p>" . htmlspecialchars($cleaned_content) . "</p>";
                            ?>
                        </div>
                        <footer>
                            <small>♥ <?php echo $post['like_number']; ?></small>
                            <a href=""><?php echo "#" . htmlspecialchars($post['taglist']); ?></a>
                        </footer>
                    </article>        
                <?php } ?>
            </main>
        </div>
    </body>
</html>
