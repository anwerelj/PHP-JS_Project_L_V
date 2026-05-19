<?php
session_start();

if (!isset($_SESSION['client_id'])) {
    header("Location: connexion.php");
    exit();
}

require_once('../config/config.php');

$message    = '';
$error      = '';
$voiture_id = isset($_GET['voiture_id']) ? (int)$_GET['voiture_id'] : 0;
$date_debut = isset($_GET['date_debut']) ? htmlentities(trim($_GET['date_debut'])) : '';
$date_fin   = isset($_GET['date_fin'])   ? htmlentities(trim($_GET['date_fin']))   : '';

    // Récupérer la voiture
    $voiture = null;
try {
    $stmt = $db->prepare("SELECT * FROM Voitures WHERE ID = :id");
    $stmt->execute([':id' => $voiture_id]);
    $voiture = $stmt->fetch(PDO::FETCH_OBJ);
} catch (PDOException $e) {
    $error = "Erreur : " . $e->getMessage();
}

if (!$voiture && !$error) {
    $error = "Cette voiture n'existe pas.";
}

if (isset($_POST['voiture_id']) && !$error) {
    $date_debut = isset($_POST['date_debut']) ? htmlentities(trim($_POST['date_debut'])) : '';
    $date_fin   = isset($_POST['date_fin'])   ? htmlentities(trim($_POST['date_fin'])) : '';
    $voiture_id = (int)$_POST['voiture_id'];
    $client_id  = (int)$_SESSION['client_id'];

    if (empty($date_debut) || empty($date_fin)) {
        $error = "Veuillez sélectionner les dates de location.";
    } elseif ($date_fin < $date_debut) {
        $error = "La date de fin doit être après la date de début.";
    } elseif ($date_debut < date('Y-m-d')) {
        $error = "La date de début ne peut pas être dans le passé.";
    } else {
        try {
            // Vérifier disponibilité
            $stmt = $db->prepare("
                SELECT COUNT(*) AS cnt FROM Reservations
                WHERE Voiture_ID = :vid
                  AND Statut IN ('confirmee','en_attente')
                  AND Date_debut <= :df AND Date_fin >= :dd
            ");
            $stmt->execute([':vid' => $voiture_id, ':df' => $date_fin, ':dd' => $date_debut]);
            $row = $stmt->fetch(PDO::FETCH_OBJ);

            if ($row->cnt > 0) {
                $error = "Cette voiture n'est pas disponible pour ces dates.";
            } else {
                // Insérer la réservation
                $stmt = $db->prepare("
                    INSERT INTO Reservations (Date_debut, Date_fin, Voiture_ID, Client_ID, Statut)
                    VALUES (:dd, :df, :vid, :cid, 'en_attente')
                ");
                $stmt->execute([
                    ':dd'  => $date_debut,
                    ':df'  => $date_fin,
                    ':vid' => $voiture_id,
                    ':cid' => $client_id,
                ]);
                $reservation_id = $db->lastInsertId();
                $message = "Demande envoyée ! Réservation #" . $reservation_id . " — en attente de validation par l'administrateur.";
            }
        } catch (PDOException $e) {
            $error = "Erreur : " . $e->getMessage();
        }
    }
}

// Calcul prix total
$nb_jours   = 0;
$prix_total = 0;
if ($date_debut && $date_fin && $date_fin >= $date_debut && $voiture) {
    $d1       = new DateTime($date_debut);
    $d2       = new DateTime($date_fin);
    $nb_jours = max(1, $d2->diff($d1)->days);
    $prix_total = $nb_jours * $voiture->Prix_par_jour;
}

define('DEFAULT_CAR_IMG', 'https://via.placeholder.com/500x260?text=Pas+de+photo');

if ($voiture && !empty($voiture->Photo)) {
    $car_img = '../uploads/voitures/' . htmlentities($voiture->Photo);
} else {
    $car_img = DEFAULT_CAR_IMG;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réserver une voiture</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <h1 class="logo"><a href="../index.php" style="text-decoration:none;color:inherit;">AutoLoc Tunisie</a></h1>
            <ul class="nav-links">
                <li><a href="dashboard.php">Tableau de bord</a></li>
                <li><a href="recherche.php">Rechercher</a></li>
                <li><a href="contact.php">Contact</a></li>
                <li><a href="deconnexion.php">Déconnexion</a></li>
            </ul>
        </div>
    </nav>

    <div class="reserver-wrapper">
        <h2 class="reserver-title">Réserver une voiture</h2>

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
            <div style="text-align:center;margin-top:1.5rem;display:flex;gap:1rem;justify-content:center;">
                <a href="dashboard.php" class="btn btn-primary">Voir mes réservations</a>
                <a href="recherche.php" class="btn btn-secondary-dark">Nouvelle recherche</a>
            </div>

        <?php elseif ($error && !$voiture): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
            <div style="text-align:center;margin-top:1rem;">
                <a href="recherche.php" class="btn btn-primary">Retour à la recherche</a>
            </div>

        <?php else: ?>
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="reserver-layout">

                <!-- Carte voiture -->
                <div class="reserver-car-card">
                    <img src="<?php echo $car_img; ?>"
                         alt="<?php echo htmlentities($voiture->Marque); ?>"
                         class="reserver-car-img">
                    <div class="reserver-car-info">
                        <h3><?php echo htmlentities($voiture->Marque . ' ' . $voiture->Modele); ?></h3>
                        <div class="reserver-car-details">
                            <div class="detail-row"><span>Année</span><strong><?php echo $voiture->Annee; ?></strong></div>
                            <div class="detail-row"><span>Immatriculation</span><strong><?php echo htmlentities($voiture->Immatriculation); ?></strong></div>
                            <div class="detail-row"><span>Prix/jour</span><strong class="price-highlight"><?php echo number_format($voiture->Prix_par_jour, 2); ?> DT</strong></div>
                        </div>
                    </div>
                </div>

                <!-- Formulaire + résumé -->
                <div class="reserver-form-side">
                    <form method="POST" action="" id="reservForm">
                        <input type="hidden" name="voiture_id" value="<?php echo $voiture_id; ?>">

                        <div class="form-group">
                            <label for="date_debut">📅 Date de début *</label>
                            <input type="date" id="date_debut" name="date_debut" required
                                   min="<?php echo date('Y-m-d'); ?>"
                                   value="<?php echo $date_debut; ?>">
                        </div>
                        <div class="form-group">
                            <label for="date_fin">📅 Date de fin *</label>
                            <input type="date" id="date_fin" name="date_fin" required
                                   min="<?php echo date('Y-m-d'); ?>"
                                   value="<?php echo $date_fin; ?>">
                        </div>

                        <!-- Résumé prix total -->
                        <div class="prix-total-box" id="prixBox" <?php echo $nb_jours > 0 ? '' : 'style="display:none"'; ?>>
                            <div class="prix-row"><span>Durée</span><strong id="nbJours"><?php echo $nb_jours; ?> jour(s)</strong></div>
                            <div class="prix-row"><span>Prix/jour</span><strong><?php echo number_format($voiture->Prix_par_jour, 2); ?> DT</strong></div>
                            <div class="prix-row prix-total-final">
                                <span>💰 Prix Total</span>
                                <strong id="prixTotal"><?php echo number_format($prix_total, 2); ?> DT</strong>
                            </div>
                            <p class="prix-note">⏳ La réservation sera confirmée après validation par l'administrateur.</p>
                        </div>

                        <div style="display:flex;gap:1rem;margin-top:1.2rem;">
                            <button type="submit" class="btn btn-primary" style="flex:1;">Envoyer la demande</button>
                            <a href="recherche.php" class="btn btn-secondary-dark" style="flex:1;text-align:center;">Annuler</a>
                        </div>
                    </form>
                </div>

            </div>
        <?php endif; ?>
    </div>

    <footer class="site-footer">
        <div class="footer-inner">
            <div class="footer-social">
                <a href="https://facebook.com" target="_blank" title="Facebook"><svg viewBox="0 0 24 24" fill="currentColor" width="22" height="22"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg></a>
                <a href="https://instagram.com" target="_blank" title="Instagram"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="22" height="22"><rect x="2" y="2" width="20" height="20" rx="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg></a>
                <a href="https://twitter.com" target="_blank" title="Twitter"><svg viewBox="0 0 24 24" fill="currentColor" width="22" height="22"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.744l7.737-8.835L1.254 2.25H8.08l4.253 5.622 5.911-5.622zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg></a>
                <a href="https://wa.me/21650192517" target="_blank" title="WhatsApp"><svg viewBox="0 0 24 24" fill="currentColor" width="22" height="22"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/></svg></a>
            </div>
            <p class="footer-contact">📞 <a href="tel:+21650192517">+216 50192517</a> &nbsp;|&nbsp; 💬 <a href="https://wa.me/21650192517" target="_blank">WhatsApp</a> &nbsp;|&nbsp; ✉️ <a href="mailto:contact@autoloc.tn">contact@autoloc.tn</a></p>
            <p class="footer-copy">&copy; 2026 AutoLoc Tunisie</p>
        </div>
    </footer>

    <!-- prixJour utilisé par reservation.js -->
    <script>var prixJour = <?php echo $voiture ? (float)$voiture->Prix_par_jour : 0; ?>;</script>
    <script src="../js/main.js"></script>
    <script src="../js/reservation.js"></script>
</body>
</html>



