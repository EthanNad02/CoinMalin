<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CoinMalin - <?php echo $title ?? 'Accueil'; ?></title>
    <!-- Chemin absolu depuis la racine -->
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="container">
            <a href="liste_annonces.php" class="logo">CoinMalin </a>
            <nav>
                <ul>
                    <li><a href="liste_annonces.php">Accueil</a></li>
                    <?php if (est_connecte()): ?>
                        <?php
                        $conn = connect_db();
                        $user_id = $_SESSION['user_id'];

                        // Compter les messages non lus
                        $sql_messages = "SELECT COUNT(*) as count FROM messages WHERE id_destinataire = $user_id AND est_lu = FALSE";
                        $result_messages = mysqli_query($conn, $sql_messages);
                        $messages_count = mysqli_fetch_assoc($result_messages)['count'];

                        // Compter les favoris
                        $sql_favoris = "SELECT COUNT(*) as count FROM favoris WHERE id_utilisateur = $user_id";
                        $result_favoris = mysqli_query($conn, $sql_favoris);
                        $favoris_count = mysqli_fetch_assoc($result_favoris)['count'];

                        mysqli_close($conn);
                        ?>
                        <li><a href="profil.php">Mon profil</a></li>
                        <li>
                            <a href="mes_favoris.php">Mes favoris<?php echo $favoris_count > 0 ? '<span class="notification-badge">' . $favoris_count . '</span>' : ''; ?></a>
                        </li>
                        <li>
                            <a href="messages.php">Messages<?php echo $messages_count > 0 ? '<span class="notification-badge">' . $messages_count . '</span>' : ''; ?></a>
                        </li>
                        <li><a href="deconnexion.php">Déconnexion</a></li>
                    <?php else: ?>
                        <li><a href="connexion.php">Connexion</a></li>
                        <li><a href="inscription.php">Inscription</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>
    <main class="container">
