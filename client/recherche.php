<?php
session_start();
require_once('../config/config.php');

$voitures_disponibles = [];
$date_debut = '';
$date_fin   = '';
$search_performed = false;
$error = '';

if (isset($_POST['search'])) {
    $date_debut       = isset($_POST['date_debut']) ? htmlentities(trim($_POST['date_debut'])) : '';
    $date_fin         = isset($_POST['date_fin'])   ? htmlentities(trim($_POST['date_fin'])) : '';
    $search_performed = true;

    if (empty($date_debut) || empty($date_fin)) {
        $error = "Veuillez sélectionner les deux dates.";
    } elseif ($date_fin < $date_debut) {
        $error = "La date de fin doit être après la date de début.";
    } elseif ($date_debut < date('Y-m-d')) {
        $error = "La date de début ne peut pas être dans le passé.";
    } else {
        try {
            $stmt = $db->prepare("
                SELECT v.* FROM Voitures v
                WHERE v.ID NOT IN (
                    SELECT r.Voiture_ID FROM Reservations r
                    WHERE r.Statut IN ('confirmee','en_attente')
                      AND r.Date_debut <= :df AND r.Date_fin >= :dd
                )
                ORDER BY v.Marque, v.Modele
            ");
            $stmt->execute([':dd' => $date_debut, ':df' => $date_fin]);
            $voitures_disponibles = $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $error = "Erreur : " . $e->getMessage();
        }
    }
}

define('DEFAULT_IMG', 'https://via.placeholder.com/400x220?text=Pas+de+photo');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rechercher une voiture</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <h1 class="logo"><a href="../index.php" style="text-decoration:none;color:inherit;">AutoLoc Tunisie</a></h1>
            <ul class="nav-links">
                <li><a href="../index.php">Accueil</a></li>
                <?php if (isset($_SESSION['client_id'])): ?>
                    <li><a href="dashboard.php">Tableau de bord</a></li>
                    <li><a href="contact.php">Contact</a></li>
                    <li><a href="deconnexion.php">Déconnexion</a></li>
                <?php else: ?>
                    <li><a href="connexion.php">Connexion</a></li>
                    <li><a href="inscription.php">Inscription</a></li>
                    <li><a href="contact.php">Contact</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <div style="max-width:1200px;margin:2rem auto;padding:0 2rem;">
        <h2 style="text-align:center;color:#0f172a;margin-bottom:.5rem;">Rechercher une voiture</h2>
        <p style="text-align:center;color:#64748b;margin-bottom:2rem;">Entrez vos dates pour voir les véhicules disponibles</p>

        <div class="form-container" style="max-width:700px;">
            <form method="POST" action="" id="rechercheForm">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                    <div class="form-group">
                        <label>📅 Date de début *</label>
                        <input type="date" name="date_debut" required
                               min="<?php echo date('Y-m-d'); ?>"
                               value="<?php echo $date_debut; ?>">
                    </div>
                    <div class="form-group">
                        <label>📅 Date de fin *</label>
                        <input type="date" name="date_fin" required
                               min="<?php echo date('Y-m-d'); ?>"
                               value="<?php echo $date_fin; ?>">
                    </div>
                </div>
                <button type="submit" name="search" class="btn btn-primary" style="width:100%;">🔍 Rechercher</button>
            </form>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error" style="max-width:700px;margin:1rem auto;"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if ($search_performed && !$error):
            $d1    = new DateTime($date_debut);
            $d2    = new DateTime($date_fin);
            $jours = max(1, $d2->diff($d1)->days);
        ?>
            <h3 style="margin:2rem 0 1rem;color:#0f172a;">
                Voitures disponibles du <strong><?php echo date('d/m/Y', strtotime($date_debut)); ?></strong>
                au <strong><?php echo date('d/m/Y', strtotime($date_fin)); ?></strong>
                — <span style="color:#14b8a6;"><?php echo $jours; ?> jour(s)</span>
            </h3>

            <input type="text" id="filtreVoiture" placeholder="🔍 Filtrer les résultats..."
                   style="width:100%;max-width:400px;padding:.6rem 1rem;border:1.5px solid #e2e8f0;border-radius:8px;margin-bottom:1rem;font-size:.9rem;">
            <span id="compteurVoitures" style="color:#64748b;font-size:.9rem;margin-left:.5rem;"></span>

            <?php if (count($voitures_disponibles) > 0): ?>
                <div class="car-grid">
                    <?php foreach ($voitures_disponibles as $v):
                        $img = !empty($v->Photo)
                            ? '../uploads/voitures/' . htmlentities($v->Photo)
                            : DEFAULT_IMG;
                        $prix_total = $jours * $v->Prix_par_jour;
                    ?>
                    <div class="car-card">
                        <div class="car-card-img">
                            <img src="<?php echo $img; ?>" alt="<?php echo htmlentities($v->Marque); ?>">
                            <div class="car-card-overlay">
                                <span class="badge badge-success">Disponible</span>
                            </div>
                        </div>
                        <div class="car-info">
                            <h3><?php echo htmlentities($v->Marque . ' ' . $v->Modele); ?></h3>
                            <p class="car-details">
                                📅 <strong>Année :</strong> <?php echo $v->Annee; ?><br>
                                🔑 <strong>Immat. :</strong> <?php echo htmlentities($v->Immatriculation); ?><br>
                                💵 <strong>Prix/jour :</strong> <?php echo number_format($v->Prix_par_jour, 2); ?> DT
                            </p>
                            <div class="car-prix-total">
                                Total <?php echo $jours; ?> jour(s) :
                                <strong><?php echo number_format($prix_total, 2); ?> DT</strong>
                            </div>
                            <?php if (isset($_SESSION['client_id'])): ?>
                                <a href="reserver.php?voiture_id=<?php echo $v->ID; ?>&date_debut=<?php echo $date_debut; ?>&date_fin=<?php echo $date_fin; ?>"
                                   class="btn btn-primary" style="width:100%;text-align:center;margin-top:.8rem;">
                                    Réserver cette voiture
                                </a>
                            <?php else: ?>
                                <p style="text-align:center;color:#64748b;margin-top:.8rem;">
                                    <a href="connexion.php" style="color:#14b8a6;font-weight:600;">Connectez-vous</a> pour réserver
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-error">Aucune voiture disponible pour ces dates. Essayez d'autres dates.</div>
            <?php endif; ?>
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
    <script src="../js/main.js"></script>
    <script src="../js/recherche.js"></script>
</body>
</html>



