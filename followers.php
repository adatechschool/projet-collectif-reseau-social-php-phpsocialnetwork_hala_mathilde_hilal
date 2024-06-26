<?php include("config.php");

if (!isset($_SESSION['connected_id'])) {
    header("Location: login.php");
    exit();
}

// Get the user ID from the session
$userId = intval($_SESSION['connected_id']) ?>
<!doctype html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <title>ReSoC - Mes abonnés </title> 
        <meta name="author" content="Julien Falconnet">
        <link rel="stylesheet" href="style.css"/>
    </head>
    <body>
        <header>
        <a href='login.php'><img src="resoc.jpg" alt="Logo de notre réseau social"/></a> 
            <nav id="menu">
                <a href="news.php">Actualités</a>
                <a href="wall.php?user_id=<?php echo $userId; ?>">Mur</a>
                <a href="feed.php?user_id=<?php echo $userId; ?>">Flux</a>
                <a href="tags.php?tag_id=1">Mots-clés</a>
            </nav>
            <nav id="user">
                <a href="#">Profil</a>
                <ul>
                    <li><a href="login.php?user_id=<?php echo $userId; ?>">Login</a></li>
                    <li><a href="settings.php?user_id=5">Paramètres</a></li>
                    <li><a href="followers.php?user_id=5">Mes suiveurs</a></li>
                    <li><a href="subscriptions.php?user_id=5">Mes abonnements</a></li>
                    
                </ul>
            </nav>
        </header>
        <div id="wrapper">          
            <aside>
                <img src = "user.jpg" alt = "Portrait de l'utilisatrice"/>
                <section>
                    <h3>Présentation</h3>
                    <p>Sur cette page vous trouverez la liste des personnes qui
                        suivent les messages de l'utilisatrice
                        n° <?php echo intval($_GET['user_id']) ?></p>

                </section>
            </aside>
            <main class='contacts'>
                <?php
                // Etape 1: récupérer l'id de l'utilisateur
                $userId = intval($_GET['user_id']);
                // Etape 3: récupérer le nom de l'utilisateur
                $laQuestionEnSql = "
                    SELECT users.*
                    FROM followers
                    LEFT JOIN users ON users.id=followers.following_user_id
                    WHERE followers.followed_user_id='$userId'
                    GROUP BY users.id
                    ";
                // Etape 4: à vous de jouer
                //@todo: faire la boucle while de parcours des abonnés et mettre les bonnes valeurs ci dessous 
                $lesInformations = $mysqli->query($laQuestionEnSql);
                if ( ! $lesInformations)
                {
                    echo("Échec de la requete : " . $mysqli->error);
                }
                while ($follower = $lesInformations->fetch_assoc())
                {
                  // echo "<pre>" . print_r($follower, 5) . "</pre>";
                ?> 
                <article>
                    <img src="user.jpg" alt="blason"/>
                    <h3>
                        <?php echo $follower["alias"]?>
                    </h3>
                        <p><?php echo $follower["id"]?></p>
                </article>
                <?php } ?>
            </main>
        </div>
    </body>
</html>
