<?php
include("config.php");


// Vérifie si l'utilisateur est connecté
if (!isset($_SESSION['connected_id'])) {
    header("Location: login.php");
    exit();
}

// Get the user ID of the logged in user
// Récupère l'ID de l'utilisateur connecté
$loggedInUserId = intval($_SESSION['connected_id']);

// Get the user ID from the URL or default to the logged in user
// Récupère l'ID de l'utilisateur du mur ou utilise l'ID de l'utilisateur connecté par défaut
$wallUserId = isset($_GET['user_id']) ? intval($_GET['user_id']) : $loggedInUserId;

// Détermine si l'utilisateur visite son propre mur
$isOwnWall = ($loggedInUserId == $wallUserId);

// Vérifie si l'utilisateur connecté suit déjà le propriétaire du mur
$subscriptionQuery = "SELECT * FROM followers WHERE following_user_id='$loggedInUserId' AND followed_user_id='$wallUserId'";
$subscriptionResult = $mysqli->query($subscriptionQuery);
$isSubscribed = $subscriptionResult->num_rows > 0;

// Gérer les requêtes AJAX pour les likes
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['like'])) {
    $postId = intval($_POST['post_id']);
    
    // Vérifie si l'utilisateur a déjà liké le post
    $likeQuery = "SELECT * FROM likes WHERE user_id='$loggedInUserId' AND post_id='$postId'";
    $likeResult = $mysqli->query($likeQuery);

    if ($likeResult->num_rows > 0) {
        // Si déjà liké, retirer le like
        $deleteLikeQuery = "DELETE FROM likes WHERE user_id='$loggedInUserId' AND post_id='$postId'";
        $mysqli->query($deleteLikeQuery);
    } else {
        // Sinon, ajouter le like
        $insertLikeQuery = "INSERT INTO likes (user_id, post_id) VALUES ('$loggedInUserId', '$postId')";
        $mysqli->query($insertLikeQuery);
    }

    // Compte le nouveau nombre de likes
    $countLikesQuery = "SELECT COUNT(*) as like_count FROM likes WHERE post_id='$postId'";
    $countLikesResult = $mysqli->query($countLikesQuery);
    $countLikesRow = $countLikesResult->fetch_assoc();
    $likeCount = $countLikesRow['like_count'];

    echo json_encode([
        'success' => true,
        'like_count' => $likeCount,
        'is_liked' => ($likeResult->num_rows == 0)
    ]);
    exit();
}

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
    <a href='login.php'><img src="resoc.jpg" alt="Logo de notre réseau social"/></a>
    <nav id="menu">
        <a href="news.php">Actualités</a>
        <a href="wall.php?user_id=<?php echo $loggedInUserId; ?>">Mur</a>
        <a href="feed.php?user_id=<?php echo $loggedInUserId; ?>">Flux</a>
        <a href="tags.php?tag_id=1">Mots-clés</a>
    </nav>
    <nav id="user">
        <a href="#">Profil</a>
        <ul>
            <li><a href="login.php?user_id=<?php echo $loggedInUserId; ?>">Login</a></li>
            <li><a href="settings.php?user_id=<?php echo $loggedInUserId; ?>">Paramètres</a></li>
            <li><a href="followers.php?user_id=<?php echo $loggedInUserId; ?>">Mes suiveurs</a></li>
            <li><a href="subscriptions.php?user_id=<?php echo $loggedInUserId; ?>">Mes abonnements</a></li>
        </ul>
    </nav>
</header>
<div id="wrapper">
    <aside>
        <?php
        // Récupère les informations de l'utilisateur
        $laQuestionEnSql = "SELECT * FROM users WHERE id= '$wallUserId'";
        $lesInformations = $mysqli->query($laQuestionEnSql);
        $user = $lesInformations->fetch_assoc();
        ?>
        <img src="user.jpg" alt="Portrait de l'utilisateur"/>
        <section>
            <h3>Présentation</h3>
            <p>Sur cette page vous trouverez tous les messages de l'utilisateur : <?php echo htmlspecialchars($user["alias"]); ?> (n° <?php echo $wallUserId; ?>)</p>
        </section>
        <section>
            <h2>Poster un message</h2>
            <?php
            // Vérifie si un formulaire a été soumis
            if (isset($_POST['message'])) {
                $postContent = $_POST['message'];
                // Vérifie si le contenu du post n'est pas vide
                if (!empty($postContent)) {
                    // Éviter l'injection SQL
                    $authorIdEscaped = intval($mysqli->real_escape_string($loggedInUserId)); // ID de l'utilisateur connecté
                    $wallIdEscaped = intval($mysqli->real_escape_string($wallUserId)); // ID du propriétaire du mur
                    $postContentEscaped = $mysqli->real_escape_string($postContent);
                    // Construire la requête
                    $lInstructionSql = "INSERT INTO posts (id, user_id, wall_id, content, created, parent_id) VALUES (NULL, '$authorIdEscaped', '$wallIdEscaped', '$postContentEscaped', NOW(), NULL)";
                    // Exécuter la requête
                    $ok = $mysqli->query($lInstructionSql);
                    if (!$ok) {
                        echo "Impossible d'ajouter le message: " . $mysqli->error;
                    } else {
                        echo "Message posté sur le mur de l'utilisateur " . htmlspecialchars($user["alias"]);
                    }
                } else {
                    echo "Veuillez entrer un message valide.";
                }
            }
            ?>
            <form action="wall.php?user_id=<?php echo $wallUserId; ?>" method="post">
                <dl>
                    <dt><label for='message'>Message</label></dt>
                    <dd><textarea name='message' id='message'></textarea></dd>
                </dl>
                <input type='submit' value='Poster'>
            </form>
        </section>
        <?php if (!$isOwnWall): ?>
            <!-- Affiche le bouton s'abonner/se désabonner -->
            <?php if ($isSubscribed): ?>
                <form action="unsubscribe.php" method="post">
                    <input type="hidden" name="unsubscribe_to_user_id" value="<?php echo $wallUserId; ?>">
                    <button type="submit">Se désabonner</button>
                </form>
                <p>Vous êtes abonné à cet utilisateur.</p>
            <?php else: ?>
                <form action="subscribe.php" method="post">
                    <input type="hidden" name="subscribe_to_user_id" value="<?php echo $wallUserId; ?>">
                    <button type="submit">S'abonner</button>
                </form>
            <?php endif; ?>
        <?php endif; ?>
    </aside>
    <main>
        <?php
        // Récupère les posts de l'utilisateur et de ses abonnés sur son mur
        $laQuestionEnSql = "
            SELECT posts.id, posts.content, posts.created, users.alias as author_name, users.id as author_id,
            COUNT(likes.id) as like_number, GROUP_CONCAT(DISTINCT tags.label) AS taglist,
            (SELECT COUNT(*) FROM likes WHERE likes.post_id = posts.id AND likes.user_id = '$loggedInUserId') as is_liked
            FROM posts
            JOIN users ON users.id = posts.user_id
            LEFT JOIN posts_tags ON posts.id = posts_tags.post_id
            LEFT JOIN tags ON posts_tags.tag_id = tags.id
            LEFT JOIN likes ON likes.post_id = posts.id
            WHERE posts.wall_id = '$wallUserId' OR posts.user_id  = '$wallUserId'
            GROUP BY posts.id
            ORDER BY posts.created DESC
        ";
        $lesInformations = $mysqli->query($laQuestionEnSql);
        if (!$lesInformations) {
            echo("Échec de la requête : " . $mysqli->error);
        }

        // Affiche les posts
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
                    <small>
                        <span class="like-button" data-post-id="<?php echo $post['id']; ?>" style="cursor: pointer;">
                            ♥
                        </span>
                        <span id="like-count-<?php echo $post['id']; ?>"><?php echo $post['like_number']; ?></span>
                    </small>
                    <a href=""><?php echo "#" . htmlspecialchars($post['taglist']); ?></a>
                </footer>
            </article>
        <?php } ?>
    </main>
</div>
<script>
document.addEventListener('DOMContentLoaded', (event) => {
    document.querySelectorAll('.like-button').forEach(button => {
        button.addEventListener('click', () => {
            const postId = button.getAttribute('data-post-id');
            fetch('wall.php?user_id=<?php echo $wallUserId; ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `like=1&post_id=${postId}`,
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const likeCount = document.getElementById(`like-count-${postId}`);
                    likeCount.textContent = data.like_count;
                } else {
                    alert('Erreur: ' + data.error);
                }
            });
        });
    });
});
</script>
</body>
</html>
