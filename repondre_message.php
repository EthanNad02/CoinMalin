<?php
require_once 'config.php';
require_once 'functions.php';
$title = "Répondre à un Message";
require_once 'header.php';

if (!est_connecte()) {
    rediriger('connexion.php');
}

if (!isset($_GET['message_id'])) {
    rediriger('messages.php');
}

$message_id = (int)$_GET['message_id'];
$user_id = $_SESSION['user_id'];
$conn = connect_db();

// Récupérer le message original
$sql = "SELECT m.*, u.nom, u.prenom, a.titre FROM messages m
        JOIN utilisateurs u ON m.id_expediteur = u.id
        LEFT JOIN annonces a ON m.id_annonce = a.id
        WHERE m.id = $message_id AND m.id_destinataire = $user_id";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) == 0) {
    echo "<p class='error'>Message non trouvé.</p>";
    require_once 'footer.php';
    exit();
}

$message = mysqli_fetch_assoc($result);

// Traitement de la réponse
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contenu = mysqli_real_escape_string($conn, $_POST['contenu']);

    $sql = "INSERT INTO messages (id_expediteur, id_destinataire, id_annonce, contenu)
            VALUES ($user_id, " . $message['id_expediteur'] . ", " . ($message['id_annonce'] ? $message['id_annonce'] : 'NULL') . ", '$contenu')";

    if (mysqli_query($conn, $sql)) {
        echo "<p class='success'>Réponse envoyée avec succès !</p>";
        rediriger('messages.php');
    } else {
        echo "<p class='error'>Erreur : " . mysqli_error($conn) . "</p>";
    }
}
?>

<h1>Répondre à un Message</h1>

<div class="message-original">
    <h2>Message original</h2>
    <div class="message-card">
        <div class="message-header">
            <span class="message-sender">De : <?php echo htmlspecialchars($message['prenom'] . ' ' . $message['nom']); ?></span>
            <span class="message-date"><?php echo date('d/m/Y H:i', strtotime($message['date_envoi'])); ?></span>
        </div>
        <?php if ($message['id_annonce']): ?>
            <div class="message-annonce">Concernant : <?php echo htmlspecialchars($message['titre']); ?></div>
        <?php endif; ?>
        <div class="message-content">
            <?php echo nl2br(htmlspecialchars($message['contenu'])); ?>
        </div>
    </div>
</div>

<form method="POST" class="form">
    <div class="form-group">
        <label for="contenu">Votre réponse :</label>
        <textarea id="contenu" name="contenu" rows="6" required></textarea>
    </div>
    <button type="submit" class="btn">Envoyer la réponse</button>
</form>

<?php mysqli_close($conn); ?>
<?php require_once 'footer.php'; ?>
