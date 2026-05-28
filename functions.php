<?php
require_once 'config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function connect_db() {
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if (!$conn) {
        die("Erreur de connexion à MySQL : " . mysqli_connect_error());
    }
    mysqli_set_charset($conn, 'utf8mb4');
    return $conn;
}

function est_connecte() {
    return isset($_SESSION['user_id']);
}

function rediriger($path) {
    header("Location: " . $path);
    exit();
}

function get_utilisateur($id) {
    $conn = connect_db();
    $id = (int)$id;
    $sql = "SELECT * FROM utilisateurs WHERE id = $id";
    $result = mysqli_query($conn, $sql);
    return mysqli_fetch_assoc($result);
}

// ============================================================
// FONCTIONS GESTION MOT DE PASSE MENSUEL
// ============================================================

/**
 * Vérifie si le mot de passe de l'utilisateur a expiré (> 30 jours)
 * @param array $user  Tableau utilisateur issu de get_utilisateur()
 * @return bool
 */
function mdp_expire($user) {
    if (empty($user['last_password_change'])) {
        return false; // ✅ Nouvel utilisateur = pas expiré
    }
    $last = new DateTime($user['last_password_change']);
    $now  = new DateTime();
    $diff = $now->diff($last);
    return $diff->days >= 30;
}

/**
 * Retourne le nombre de jours/heures/minutes restants avant expiration
 * @param array $user
 * @return array ['jours' => int, 'heures' => int, 'minutes' => int, 'total_secondes' => int]
 */
function temps_restant_mdp($user) {
    if (empty($user['last_password_change'])) {
        return ['jours' => 30, 'heures' => 0, 'minutes' => 0, 'total_secondes' => 30 * 86400];
    }
    $last    = new DateTime($user['last_password_change']);
    $expiry  = clone $last;
    $expiry->modify('+30 days');
    $now     = new DateTime();

    if ($now >= $expiry) {
        return ['jours' => 0, 'heures' => 0, 'minutes' => 0, 'total_secondes' => 0];
    }

    $diff           = $now->diff($expiry);
    $total_secondes = $expiry->getTimestamp() - $now->getTimestamp();

    return [
        'jours'          => $diff->days,
        'heures'         => $diff->h,
        'minutes'        => $diff->i,
        'total_secondes' => $total_secondes,
        'expiry_iso'     => $expiry->format('c'),
    ];
}

/**
 * Vérifie si le nouveau hash correspond à un des 3 derniers mots de passe
 * @param string $new_password  Mot de passe en clair
 * @param string|null $history_json  JSON stocké en BDD
 * @return bool  true = mot de passe déjà utilisé récemment
 */
function mdp_deja_utilise($new_password, $history_json) {
    if (empty($history_json)) return false;
    $history = json_decode($history_json, true);
    if (!is_array($history)) return false;
    foreach ($history as $old_hash) {
        if (password_verify($new_password, $old_hash)) {
            return true;
        }
    }
    return false;
}

/**
 * Met à jour le mot de passe + historique + date de changement
 * @param int    $user_id
 * @param string $new_password  Mot de passe en clair
 * @param string $current_hash  Hash actuel (pour l'historique)
 * @param string|null $history_json  Historique actuel
 * @return bool
 */
function mettre_a_jour_mdp($user_id, $new_password, $current_hash, $history_json) {
    $conn    = connect_db();
    $user_id = (int)$user_id;

    $history = [];
    if (!empty($history_json)) {
        $history = json_decode($history_json, true) ?? [];
    }
    array_unshift($history, $current_hash);
    $history = array_slice($history, 0, 3);

    $new_hash    = password_hash($new_password, PASSWORD_BCRYPT);
    $new_history = mysqli_real_escape_string($conn, json_encode($history));
    $now         = date('Y-m-d H:i:s');

    $sql = "UPDATE utilisateurs SET
                mot_de_passe         = '$new_hash',
                last_password_change = '$now',
                password_history     = '$new_history'
            WHERE id = $user_id";

    return mysqli_query($conn, $sql);
}

// ============================================================
// FONCTIONS EXISTANTES (inchangées)
// ============================================================

function est_favoris($id_utilisateur, $id_annonce) {
    $conn = connect_db();
    $sql = "SELECT COUNT(*) FROM favoris WHERE id_utilisateur = $id_utilisateur AND id_annonce = $id_annonce";
    $result = mysqli_query($conn, $sql);
    $count = mysqli_fetch_array($result)[0];
    return $count > 0;
}

function ajouter_favoris($id_utilisateur, $id_annonce) {
    $conn = connect_db();
    $sql = "INSERT INTO favoris (id_utilisateur, id_annonce) VALUES ($id_utilisateur, $id_annonce)";
    return mysqli_query($conn, $sql);
}

function retirer_favoris($id_utilisateur, $id_annonce) {
    $conn = connect_db();
    $sql = "DELETE FROM favoris WHERE id_utilisateur = $id_utilisateur AND id_annonce = $id_annonce";
    return mysqli_query($conn, $sql);
}

function get_favoris($id_utilisateur) {
    $conn = connect_db();
    $sql = "SELECT a.* FROM annonces a
            JOIN favoris f ON a.id = f.id_annonce
            WHERE f.id_utilisateur = $id_utilisateur
            ORDER BY f.date_ajout DESC";
    $result  = mysqli_query($conn, $sql);
    $favoris = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $favoris[] = $row;
    }
    return $favoris;
}

function get_messages($id_utilisateur) {
    $conn = connect_db();
    $sql_recus = "SELECT m.*, u.nom, u.prenom, a.titre FROM messages m
                  JOIN utilisateurs u ON m.id_expediteur = u.id
                  LEFT JOIN annonces a ON m.id_annonce = a.id
                  WHERE m.id_destinataire = $id_utilisateur
                  ORDER BY m.date_envoi DESC";
    $result_recus   = mysqli_query($conn, $sql_recus);
    $messages_recus = [];
    while ($row = mysqli_fetch_assoc($result_recus)) {
        $messages_recus[] = $row;
    }

    $sql_envoyes = "SELECT m.*, u.nom, u.prenom, a.titre FROM messages m
                    JOIN utilisateurs u ON m.id_destinataire = u.id
                    LEFT JOIN annonces a ON m.id_annonce = a.id
                    WHERE m.id_expediteur = $id_utilisateur
                    ORDER BY m.date_envoi DESC";
    $result_envoyes   = mysqli_query($conn, $sql_envoyes);
    $messages_envoyes = [];
    while ($row = mysqli_fetch_assoc($result_envoyes)) {
        $messages_envoyes[] = $row;
    }

    return [
        'recus'   => $messages_recus,
        'envoyes' => $messages_envoyes
    ];
}