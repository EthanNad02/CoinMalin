<?php
require_once 'config.php';
require_once 'functions.php';
$title = "Mes Messages";
require_once 'header.php';

if (!est_connecte()) {
    rediriger('connexion.php');
}

$user_id = $_SESSION['user_id'];
$conn = connect_db();

// Marquer tous les messages reçus comme lus
$sql = "UPDATE messages SET est_lu = TRUE WHERE id_destinataire = $user_id AND est_lu = FALSE";
mysqli_query($conn, $sql);

// Récupérer toutes les conversations (contacts uniques)
$sql_conversations = "
    SELECT 
        u.id,
        u.nom,
        u.prenom,
        MAX(m.date_envoi) AS derniere_activite,
        SUM(CASE WHEN m.id_destinataire = $user_id AND m.est_lu = 0 THEN 1 ELSE 0 END) AS non_lus,
        (SELECT contenu FROM messages 
         WHERE (id_expediteur = u.id AND id_destinataire = $user_id)
            OR (id_expediteur = $user_id AND id_destinataire = u.id)
         ORDER BY date_envoi DESC LIMIT 1) AS dernier_message
    FROM messages m
    JOIN utilisateurs u ON (
        CASE WHEN m.id_expediteur = $user_id THEN m.id_destinataire = u.id
             ELSE m.id_expediteur = u.id END
    )
    WHERE m.id_expediteur = $user_id OR m.id_destinataire = $user_id
    GROUP BY u.id, u.nom, u.prenom
    ORDER BY derniere_activite DESC
";
$result_conv = mysqli_query($conn, $sql_conversations);
$conversations = [];
while ($row = mysqli_fetch_assoc($result_conv)) {
    $conversations[] = $row;
}

// Contact sélectionné
$contact_id = isset($_GET['contact_id']) ? (int)$_GET['contact_id'] : ($conversations[0]['id'] ?? null);

// Récupérer les messages de la conversation sélectionnée
$messages_conv = [];
if ($contact_id) {
    $sql_messages = "
        SELECT m.*, a.titre AS titre_annonce
        FROM messages m
        LEFT JOIN annonces a ON m.id_annonce = a.id
        WHERE (m.id_expediteur = $user_id AND m.id_destinataire = $contact_id)
           OR (m.id_expediteur = $contact_id AND m.id_destinataire = $user_id)
        ORDER BY m.date_envoi ASC
    ";
    $result_msg = mysqli_query($conn, $sql_messages);
    while ($row = mysqli_fetch_assoc($result_msg)) {
        $messages_conv[] = $row;
    }

    // Récupérer les infos du contact
    $sql_contact = "SELECT nom, prenom FROM utilisateurs WHERE id = $contact_id";
    $contact = mysqli_fetch_assoc(mysqli_query($conn, $sql_contact));

    // Récupérer l'annonce liée si elle existe
    $sql_annonce = "SELECT id, titre FROM annonces WHERE id_utilisateur = $contact_id OR id_utilisateur = $user_id LIMIT 1";
    $annonce_liee = mysqli_fetch_assoc(mysqli_query($conn, $sql_annonce));
}

// Envoi d'un message depuis la conversation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contenu']) && $contact_id) {
    $contenu = mysqli_real_escape_string($conn, $_POST['contenu']);
    $id_annonce = isset($_POST['id_annonce']) ? (int)$_POST['id_annonce'] : 'NULL';
    $sql_insert = "INSERT INTO messages (id_expediteur, id_destinataire, id_annonce, contenu)
                   VALUES ($user_id, $contact_id, $id_annonce, '$contenu')";
    mysqli_query($conn, $sql_insert);
    rediriger('messages.php?contact_id=' . $contact_id);
}
?>

<div class="whatsapp-container">

    <!-- COLONNE GAUCHE : liste des conversations -->
    <div class="wa-sidebar">
        <div class="wa-sidebar-header">
            <h2>Messages</h2>
        </div>
        <div class="wa-contacts">
            <?php if (empty($conversations)): ?>
                <p class="wa-empty">Aucune conversation.</p>
            <?php else: ?>
                <?php foreach ($conversations as $conv): ?>
                    <a href="messages.php?contact_id=<?php echo $conv['id']; ?>" 
                       class="wa-contact <?php echo ($contact_id == $conv['id']) ? 'active' : ''; ?>">
                        <div class="wa-avatar">
                            <?php echo strtoupper(substr($conv['prenom'], 0, 1) . substr($conv['nom'], 0, 1)); ?>
                        </div>
                        <div class="wa-contact-info">
                            <div class="wa-contact-top">
                                <span class="wa-contact-name"><?php echo htmlspecialchars($conv['prenom'] . ' ' . $conv['nom']); ?></span>
                                <span class="wa-contact-time"><?php echo date('H:i', strtotime($conv['derniere_activite'])); ?></span>
                            </div>
                            <div class="wa-contact-bottom">
                                <span class="wa-contact-preview"><?php echo htmlspecialchars(substr($conv['dernier_message'], 0, 35)) . '...'; ?></span>
                                <?php if ($conv['non_lus'] > 0): ?>
                                    <span class="wa-badge"><?php echo $conv['non_lus']; ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- COLONNE DROITE : conversation -->
    <div class="wa-chat">
        <?php if ($contact_id && !empty($contact)): ?>

            <!-- Header de la conversation -->
            <div class="wa-chat-header">
                <div class="wa-avatar">
                    <?php echo strtoupper(substr($contact['prenom'], 0, 1) . substr($contact['nom'], 0, 1)); ?>
                </div>
                <div class="wa-chat-header-info">
                    <span class="wa-chat-name"><?php echo htmlspecialchars($contact['prenom'] . ' ' . $contact['nom']); ?></span>
                </div>
            </div>

            <!-- Bulles de messages -->
            <div class="wa-messages" id="wa-messages">
                <?php if (empty($messages_conv)): ?>
                    <p class="wa-empty-chat">Commencez la conversation !</p>
                <?php else: ?>
                    <?php 
                    $last_date = null;
                    foreach ($messages_conv as $msg): 
                        $date_msg = date('d/m/Y', strtotime($msg['date_envoi']));
                        if ($date_msg !== $last_date):
                            $last_date = $date_msg;
                    ?>
                        <div class="wa-date-separator">
                            <span><?php echo $date_msg; ?></span>
                        </div>
                    <?php endif; ?>

                    <div class="wa-bubble <?php echo ($msg['id_expediteur'] == $user_id) ? 'sent' : 'received'; ?>">
                        <?php if (!empty($msg['titre_annonce'])): ?>
                            <div class="wa-bubble-annonce">📦 <?php echo htmlspecialchars($msg['titre_annonce']); ?></div>
                        <?php endif; ?>
                        <div class="wa-bubble-text"><?php echo nl2br(htmlspecialchars($msg['contenu'])); ?></div>
                        <div class="wa-bubble-time"><?php echo date('H:i', strtotime($msg['date_envoi'])); ?></div>
                    </div>

                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Zone de saisie -->
            <div class="wa-input-area">
                <form method="POST" class="wa-form">
                    <?php if (!empty($annonce_liee)): ?>
                        <input type="hidden" name="id_annonce" value="<?php echo $annonce_liee['id']; ?>">
                    <?php else: ?>
                        <input type="hidden" name="id_annonce" value="NULL">
                    <?php endif; ?>
                    <textarea name="contenu" class="wa-input" placeholder="Écrire un message..." rows="1" required
                        onkeydown="if(event.key==='Enter' && !event.shiftKey){ event.preventDefault(); this.form.submit(); }"></textarea>
                    <button type="submit" class="wa-send-btn">
                        <svg viewBox="0 0 24 24" fill="currentColor" width="22" height="22">
                            <path d="M2 21l21-9L2 3v7l15 2-15 2v7z"/>
                        </svg>
                    </button>
                </form>
            </div>

        <?php else: ?>
            <div class="wa-no-chat">
                <p>Sélectionnez une conversation pour commencer.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    // Scroll automatique vers le bas
    const waMessages = document.getElementById('wa-messages');
    if (waMessages) waMessages.scrollTop = waMessages.scrollHeight;

    // Auto-resize du textarea
    const textarea = document.querySelector('.wa-input');
    if (textarea) {
        textarea.addEventListener('input', function () {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 120) + 'px';
        });
    }
</script>

<?php mysqli_close($conn); ?>
<?php require_once 'footer.php'; ?>