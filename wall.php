<?php
include("config.php");

// Check if the user is logged in
if (!isset($_SESSION['connected_id'])) {
    header("Location: login.php");
    exit();
}

// Get the user ID of the logged in user
$loggedInUserId = intval($_SESSION['connected_id']);

// Get the user ID from the URL or default to the logged in user
$wallUserId = isset($_GET['user_id']) ? intval($_GET['user_id']) : $loggedInUserId;

// Determine if the user is visiting their own wall
$isOwnWall = ($loggedInUserId == $wallUserId);

// Check if the logged-in user is already following the wall owner
$subscriptionQuery = "SELECT * FROM followers WHERE following_user_id='$loggedInUserId' AND followed_user_id='$wallUserId'";
$subscriptionResult = $mysqli->query($subscriptionQuery);
$isSubscribed = $subscriptionResult->num_rows > 0;

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
        // Fetch user information
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
            // Check if a form has been submitted
            if (isset($_POST['message'])) {
                $postContent = $_POST['message'];
                // Check if post content is not empty
                if (!empty($postContent)) {
                    // Avoid SQL injection
                    $authorIdEscaped = intval($mysqli->real_escape_string($loggedInUserId)); // This should be the logged-in user's ID
                    $wallIdEscaped = intval($mysqli->real_escape_string($wallUserId)); // This should be the wall owner's ID
                    $postContentEscaped = $mysqli->real_escape_string($postContent);
                    // Construct the query
                    $lInstructionSql = "INSERT INTO posts (id, user_id, wall_id, content, created, parent_id) VALUES (NULL, '$authorIdEscaped', '$wallIdEscaped', '$postContentEscaped', NOW(), NULL)";
                    // Execute the query
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
            <!-- Display the subscribe/unsubscribe button -->
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
// Fetch posts of the user and their followers on the user's wall
$laQuestionEnSql = "
    SELECT posts.content, posts.created, users.alias as author_name, users.id as author_id,
    COUNT(likes.id) as like_number, GROUP_CONCAT(DISTINCT tags.label) AS taglist
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
<?php
}
?>
    </main>
</div>
</body>
</html>
