<?php
require_once 'config.php';
require_once 'functions.php';
$title = "Envoyer un Message";
require_once 'header.php';

if (!est_connecte()) {
    rediriger('connexion.php');
}

if (!isset($_GET['annonce_id']) || !isset($_GET['destinataire_id'])) {
    rediriger('index.php');
}

$annonce_id = (int)$_GET['annonce_id'];
$destinataire_id = (int)$_GET['destinataire_id'];
$expediteur_id = $_SESSION['user_id'];

$conn = connect_db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contenu = mysqli_real_escape_string($conn, $_POST['contenu']);

    $sql = "INSERT INTO messages (id_expediteur, id_destinataire, id_annonce, contenu)
            VALUES ($expediteur_id, $destinataire_id, $annonce_id, '$contenu')";

    if (mysqli_query($conn, $sql)) {
        echo "<p class='success'>Message envoyé avec succès !</p>";
        rediriger('voir_annonce.php?id=' . $annonce_id);
    } else {
        echo "<p class='error'>Erreur : " . mysqli_error($conn) . "</p>";
    }
}

// Récupérer les informations de l'annonce pour le contexte
$sql_annonce = "SELECT titre FROM annonces WHERE id = $annonce_id";
$annonce = mysqli_fetch_assoc(mysqli_query($conn, $sql_annonce));
$sql_vendeur = "SELECT nom, prenom FROM utilisateurs WHERE id = $destinataire_id";
$vendeur = mysqli_fetch_assoc(mysqli_query($conn, $sql_vendeur));
?>

<h1>Envoyer un Message</h1>
<p>Vous envoyez un message à <strong><?php echo htmlspecialchars($vendeur['prenom'] . ' ' . $vendeur['nom']); ?></strong> concernant l'annonce : <strong><?php echo htmlspecialchars($annonce['titre']); ?></strong></p>

<form method="POST" class="form">
    <div class="form-group">
        <label for="contenu">Votre message :</label>
        <textarea id="contenu" name="contenu" rows="6" required></textarea>
    </div>
    <button type="submit" class="btn">Envoyer</button>
</form>

<?php mysqli_close($conn); ?>
<?php require_once 'footer.php'; ?>
