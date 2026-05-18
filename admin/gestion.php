<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: connexion.php");
    exit();
}

require_once('../config/config.php');

$message      = '';
$error        = '';
$mode         = 'liste';
$voiture_edit = null;

if (isset($_GET['action']) && $_GET['action'] === 'valider_resa' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    try {
        $stmt = $db->prepare("SELECT Voiture_ID FROM Reservations WHERE ID = :id");
        $stmt->execute([':id' => $id]);
        $resa = $stmt->fetch(PDO::FETCH_OBJ);
        if ($resa) {
            $stmt2 = $db->prepare("UPDATE Voitures SET Disponibilite = 0 WHERE ID = :vid");
            $stmt2->execute([':vid' => $resa->Voiture_ID]);
        }
        $stmt3 = $db->prepare("UPDATE Reservations SET Statut = 'confirmee' WHERE ID = :id");
        $stmt3->execute([':id' => $id]);
        $message = "Réservation #" . $id . " confirmée. La voiture est marquée indisponible.";
    } catch (PDOException $e) {
        $error = "Erreur : " . $e->getMessage();
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'terminer_resa' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    try {
        $stmt = $db->prepare("SELECT Voiture_ID FROM Reservations WHERE ID = :id");
        $stmt->execute([':id' => $id]);
        $resa = $stmt->fetch(PDO::FETCH_OBJ);
        if ($resa) {
            $stmt2 = $db->prepare("UPDATE Voitures SET Disponibilite = 1 WHERE ID = :vid");
            $stmt2->execute([':vid' => $resa->Voiture_ID]);
        }
        $stmt3 = $db->prepare("UPDATE Reservations SET Statut = 'terminee' WHERE ID = :id");
        $stmt3->execute([':id' => $id]);
        $message = "Réservation #" . $id . " terminée. La voiture est de nouveau disponible.";
    } catch (PDOException $e) {
        $error = "Erreur : " . $e->getMessage();
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'refuser_resa' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    try {
        $stmt = $db->prepare("SELECT Voiture_ID FROM Reservations WHERE ID = :id");
        $stmt->execute([':id' => $id]);
        $resa = $stmt->fetch(PDO::FETCH_OBJ);
        if ($resa) {
            $stmt2 = $db->prepare("UPDATE Voitures SET Disponibilite = 1 WHERE ID = :vid");
            $stmt2->execute([':vid' => $resa->Voiture_ID]);
        }
        $stmt3 = $db->prepare("UPDATE Reservations SET Statut = 'annulee' WHERE ID = :id");
        $stmt3->execute([':id' => $id]);
        $message = "Réservation #" . $id . " refusée. La voiture est de nouveau disponible.";
    } catch (PDOException $e) {
        $error = "Erreur : " . $e->getMessage();
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'supprimer' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    try {
        $stmt = $db->prepare("DELETE FROM Voitures WHERE ID = :id");
        $stmt->execute([':id' => $id]);
        $message = "Voiture supprimée avec succès.";
    } catch (PDOException $e) {
        $error = "Erreur lors de la suppression : " . $e->getMessage();
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'modifier' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $db->prepare("SELECT * FROM Voitures WHERE ID = :id");
    $stmt->execute([':id' => $id]);
    $voiture_edit = $stmt->fetch(PDO::FETCH_OBJ);
    if ($voiture_edit) {
        $mode = 'modifier';
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'ajouter') {
    $mode = 'ajouter';
}

if (isset($_POST['ajouter'])) {
    $marque          = htmlentities(trim($_POST['marque']));
    $modele          = htmlentities(trim($_POST['modele']));
    $annee           = (int)$_POST['annee'];
    $immatriculation = htmlentities(trim($_POST['immatriculation']));
    $prix            = (float)$_POST['prix'];
    $disponibilite   = isset($_POST['disponibilite']) ? 1 : 0;
    $photo           = null;

    if (!empty($_FILES['photo']['name'])) {
        $ext     = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array($ext, $allowed)) {
            $filename = 'voiture_' . time() . '.' . $ext;
            move_uploaded_file($_FILES['photo']['tmp_name'], '../uploads/voitures/' . $filename);
            $photo = $filename;
        }
    }

    if (empty($marque) || empty($modele) || empty($annee) || empty($immatriculation)) {
        $error = "Tous les champs sont obligatoires.";
        $mode  = 'ajouter';
    } else {
        try {
            $stmt = $db->prepare("INSERT INTO Voitures (Marque, Modele, Annee, Immatriculation, Disponibilite, Prix_par_jour, Photo) VALUES (:marque, :modele, :annee, :immat, :dispo, :prix, :photo)");
            $stmt->execute([
                ':marque' => $marque,
                ':modele' => $modele,
                ':annee'  => $annee,
                ':immat'  => $immatriculation,
                ':dispo'  => $disponibilite,
                ':prix'   => $prix,
                ':photo'  => $photo,
            ]);
            $message = "Voiture ajoutée avec succès !";
        } catch (PDOException $e) {
            $error = "Erreur : " . $e->getMessage();
            $mode  = 'ajouter';
        }
    }
}

if (isset($_POST['modifier'])) {
    $id              = (int)$_POST['id'];
    $marque          = htmlentities(trim($_POST['marque']));
    $modele          = htmlentities(trim($_POST['modele']));
    $annee           = (int)$_POST['annee'];
    $immatriculation = htmlentities(trim($_POST['immatriculation']));
    $prix            = (float)$_POST['prix'];
    $disponibilite   = isset($_POST['disponibilite']) ? 1 : 0;
    $photo           = isset($_POST['photo_actuelle']) ? $_POST['photo_actuelle'] : null;

    if (!empty($_FILES['photo']['name'])) {
        $ext     = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array($ext, $allowed)) {
            $filename = 'voiture_' . time() . '.' . $ext;
            move_uploaded_file($_FILES['photo']['tmp_name'], '../uploads/voitures/' . $filename);
            $photo = $filename;
        }
    }

    if (empty($marque) || empty($modele) || empty($annee) || empty($immatriculation)) {
        $error = "Tous les champs sont obligatoires.";
        $stmt  = $db->prepare("SELECT * FROM Voitures WHERE ID = :id");
        $stmt->execute([':id' => $id]);
        $voiture_edit = $stmt->fetch(PDO::FETCH_OBJ);
        $mode = 'modifier';
    } else {
        try {
            $stmt = $db->prepare("UPDATE Voitures SET Marque=:marque, Modele=:modele, Annee=:annee, Immatriculation=:immat, Disponibilite=:dispo, Prix_par_jour=:prix, Photo=:photo WHERE ID=:id");
            $stmt->execute([
                ':marque' => $marque,
                ':modele' => $modele,
                ':annee'  => $annee,
                ':immat'  => $immatriculation,
                ':dispo'  => $disponibilite,
                ':prix'   => $prix,
                ':photo'  => $photo ?: null,
                ':id'     => $id,
            ]);
            $message = "Voiture modifiée avec succès !";
        } catch (PDOException $e) {
            $error = "Erreur : " . $e->getMessage();
            $stmt  = $db->prepare("SELECT * FROM Voitures WHERE ID = :id");
            $stmt->execute([':id' => $id]);
            $voiture_edit = $stmt->fetch(PDO::FETCH_OBJ);
            $mode = 'modifier';
        }
    }
}

$stmt_voitures = $db->prepare("SELECT * FROM Voitures ORDER BY created_at DESC");
$stmt_voitures->execute();
$voitures = $stmt_voitures->fetchAll(PDO::FETCH_OBJ);

// Réservations en attente
$stmt_resa = $db->prepare("
    SELECT r.*, c.Nom AS Client_Nom, c.Email AS Client_Email,
           v.Marque, v.Modele, v.Annee, v.Immatriculation, v.Prix_par_jour
    FROM Reservations r
    JOIN Clients c ON r.Client_ID = c.ID
    JOIN Voitures v ON r.Voiture_ID = v.ID
    WHERE r.Statut = 'en_attente'
    ORDER BY r.created_at ASC
");
$stmt_resa->execute();
$reservations_attente = $stmt_resa->fetchAll(PDO::FETCH_OBJ);

// Réservations confirmées
$stmt_conf = $db->prepare("
    SELECT r.*, c.Nom AS Client_Nom,
           v.Marque, v.Modele, v.Immatriculation, v.Prix_par_jour
    FROM Reservations r
    JOIN Clients c ON r.Client_ID = c.ID
    JOIN Voitures v ON r.Voiture_ID = v.ID
    WHERE r.Statut = 'confirmee'
    ORDER BY r.Date_debut ASC
");
$stmt_conf->execute();
$reservations_confirmees = $stmt_conf->fetchAll(PDO::FETCH_OBJ);

// Statistiques
$stmt_stats = $db->prepare("SELECT COUNT(*) FROM Voitures");
$stmt_stats->execute();
$total_voitures = $stmt_stats->fetchColumn();

$stmt_stats = $db->prepare("SELECT COUNT(*) FROM Voitures WHERE Disponibilite = 1");
$stmt_stats->execute();
$voitures_dispo = $stmt_stats->fetchColumn();

$stmt_stats = $db->prepare("SELECT COUNT(*) FROM Clients");
$stmt_stats->execute();
$total_clients = $stmt_stats->fetchColumn();

$stmt_stats = $db->prepare("SELECT COUNT(*) FROM Reservations");
$stmt_stats->execute();
$total_reservations = $stmt_stats->fetchColumn();

$resa_en_attente    = count($reservations_attente);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .form-inline { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem; }
        .action-buttons { display: flex; gap: 1rem; margin-top: 1.5rem; }
        .section-block {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.07);
            padding: 2rem;
            margin-bottom: 2.5rem;
        }
        .section-block h2 {
            color: #0f172a;
            margin-bottom: 1.5rem;
            padding-bottom: .75rem;
            border-bottom: 2px solid #f1f5f9;
            display: flex;
            align-items: center;
            gap: .5rem;
        }
        .badge-count {
            background: #ef4444;
            color: white;
            border-radius: 20px;
            padding: .15rem .6rem;
            font-size: .8rem;
            font-weight: 700;
        }
        .btn-confirm { background: #10b981; color: white; font-weight: 600; }
        .btn-confirm:hover { background: #059669; }
        .btn-refuse  { background: #ef4444; color: white; font-weight: 600; }
        .btn-refuse:hover  { background: #dc2626; }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <h1 class="logo"><a href="../index.php" style="text-decoration:none;color:inherit;">Admin — AutoLoc</a></h1>
            <ul class="nav-links">
                <li><a href="gestion.php">Dashboard</a></li>
                <li><a href="deconnexion.php">Déconnexion</a></li>
            </ul>
        </div>
    </nav>

    <div class="dashboard">

        
        <?php if ($mode === 'liste'): ?>
        <div class="dashboard-header">
            <h2>Tableau de bord &nbsp;<span id="horloge" style="font-size:1rem;color:#14b8a6;font-weight:600;"></span></h2>
            <a href="?action=ajouter" class="btn btn-primary">+ Ajouter une voiture</a>
        </div>
        <div class="stats-grid">
            <div class="stat-card"><h3><?php echo $total_voitures; ?></h3><p>Total Voitures</p></div>
            <div class="stat-card"><h3><?php echo $voitures_dispo; ?></h3><p>Disponibles</p></div>
            <div class="stat-card"><h3><?php echo $total_clients; ?></h3><p>Clients</p></div>
            <div class="stat-card"><h3><?php echo $total_reservations; ?></h3><p>Réservations</p></div>
            <div class="stat-card" style="border-left-color:#ef4444;">
                <h3 style="color:#ef4444;"><?php echo $resa_en_attente; ?></h3>
                <p>En attente</p>
            </div>
        </div>
        <?php endif; ?>

        
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        
        <?php if ($mode === 'liste'): ?>
        <div class="section-block">
            <h2>
                ⏳ Réservations en attente
                <?php if ($resa_en_attente > 0): ?>
                    <span class="badge-count"><?php echo $resa_en_attente; ?></span>
                <?php endif; ?>
            </h2>

            <?php if (count($reservations_attente) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Client</th>
                        <th>Voiture</th>
                        <th>Date début</th>
                        <th>Date fin</th>
                        <th>Durée</th>
                        <th>Prix Total</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while ($r = array_shift($reservations_attente)):
                        $d1    = new DateTime($r->Date_debut);
                        $d2    = new DateTime($r->Date_fin);
                        $jours = max(1, $d2->diff($d1)->days);
                        $total = $jours * $r->Prix_par_jour;
                    ?>
                    <tr>
                        <td><strong>#<?php echo $r->ID; ?></strong></td>
                        <td>
                            <?php echo htmlentities($r->Client_Nom); ?><br>
                            <small style="color:#64748b;"><?php echo htmlentities($r->Client_Email); ?></small>
                        </td>
                        <td>
                            <?php echo htmlentities($r->Marque . ' ' . $r->Modele); ?><br>
                            <small style="color:#64748b;"><?php echo htmlentities($r->Immatriculation); ?></small>
                        </td>
                        <td><?php echo date('d/m/Y', strtotime($r->Date_debut)); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($r->Date_fin)); ?></td>
                        <td><?php echo $jours; ?> jour(s)</td>
                        <td><strong style="color:#14b8a6;"><?php echo number_format($total, 2); ?> DT</strong></td>
                        <td>
                            <div class="actions">
                                <a href="?action=valider_resa&id=<?php echo $r->ID; ?>"
                                   onclick="return confirm('Confirmer la réservation #<?php echo $r->ID; ?> ?')"
                                   class="btn btn-sm btn-confirm">✅ Valider</a>
                                <a href="?action=refuser_resa&id=<?php echo $r->ID; ?>"
                                   onclick="return confirm('Refuser la réservation #<?php echo $r->ID; ?> ?')"
                                   class="btn btn-sm btn-refuse">❌ Refuser</a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
                <p style="color:#64748b;text-align:center;padding:1.5rem 0;">
                    ✅ Aucune réservation en attente.
                </p>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        
        <?php if ($mode === 'liste'): ?>
        <div class="section-block">
            <h2>✅ Réservations confirmées (voitures en location)</h2>
            <?php if (count($reservations_confirmees) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>#</th><th>Client</th><th>Voiture</th>
                        <th>Date début</th><th>Date fin</th><th>Prix Total</th><th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while ($r = array_shift($reservations_confirmees)):
                        $d1    = new DateTime($r->Date_debut);
                        $d2    = new DateTime($r->Date_fin);
                        $jours = max(1, $d2->diff($d1)->days);
                        $total = $jours * $r->Prix_par_jour;
                    ?>
                    <tr>
                        <td><strong>#<?php echo $r->ID; ?></strong></td>
                        <td><?php echo htmlentities($r->Client_Nom); ?></td>
                        <td>
                            <?php echo htmlentities($r->Marque . ' ' . $r->Modele); ?><br>
                            <small style="color:#64748b;"><?php echo htmlentities($r->Immatriculation); ?></small>
                        </td>
                        <td><?php echo date('d/m/Y', strtotime($r->Date_debut)); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($r->Date_fin)); ?></td>
                        <td><strong style="color:#14b8a6;"><?php echo number_format($total, 2); ?> DT</strong></td>
                        <td>
                            <a href="?action=terminer_resa&id=<?php echo $r->ID; ?>"
                               onclick="return confirm('Marquer comme terminée ? La voiture redeviendra disponible.')"
                               class="btn btn-sm btn-confirm">🏁 Voiture retournée</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
                <p style="color:#64748b;text-align:center;padding:1.5rem 0;">Aucune voiture actuellement en location.</p>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        
        <?php if ($mode === 'ajouter'): ?>
        <div class="section-block">
            <h2>➕ Ajouter une nouvelle voiture</h2>
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="form-inline">
                    <div class="form-group">
                        <label>Marque *</label>
                        <input type="text" name="marque" required
                               value="<?php echo isset($_POST['marque']) ? htmlentities($_POST['marque']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label>Modèle *</label>
                        <input type="text" name="modele" required
                               value="<?php echo isset($_POST['modele']) ? htmlentities($_POST['modele']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label>Année *</label>
                        <input type="number" name="annee" min="1990" max="<?php echo date('Y') + 1; ?>" required
                               value="<?php echo isset($_POST['annee']) ? (int)$_POST['annee'] : date('Y'); ?>">
                    </div>
                    <div class="form-group">
                        <label>Immatriculation *</label>
                        <input type="text" name="immatriculation" required
                               value="<?php echo isset($_POST['immatriculation']) ? htmlentities($_POST['immatriculation']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label>Prix par jour (DT) *</label>
                        <input type="number" name="prix" min="0" step="0.01" required
                               value="<?php echo isset($_POST['prix']) ? (float)$_POST['prix'] : ''; ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label>Photo de la voiture</label>
                    <input type="file" name="photo" accept=".jpg,.jpeg,.png,.webp">
                </div>
                <div class="form-group">
                    <label><input type="checkbox" name="disponibilite" checked> Disponible à la location</label>
                </div>
                <div class="action-buttons">
                    <button type="submit" name="ajouter" class="btn btn-primary" style="flex:1;">Ajouter la voiture</button>
                    <a href="gestion.php" class="btn btn-secondary-dark" style="flex:1;text-align:center;">Annuler</a>
                </div>
            </form>
        </div>
        <?php endif; ?>

        
        <?php if ($mode === 'modifier' && $voiture_edit): ?>
        <div class="section-block">
            <h2>✏️ Modifier la voiture #<?php echo $voiture_edit->ID; ?></h2>
            <form method="POST" action="" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?php echo $voiture_edit->ID; ?>">
                <input type="hidden" name="photo_actuelle" value="<?php echo htmlentities($voiture_edit->Photo ?? ''); ?>">
                <div class="form-inline">
                    <div class="form-group">
                        <label>Marque *</label>
                        <input type="text" name="marque" required
                               value="<?php echo htmlentities($voiture_edit->Marque); ?>">
                    </div>
                    <div class="form-group">
                        <label>Modèle *</label>
                        <input type="text" name="modele" required
                               value="<?php echo htmlentities($voiture_edit->Modele); ?>">
                    </div>
                    <div class="form-group">
                        <label>Année *</label>
                        <input type="number" name="annee" min="1990" max="<?php echo date('Y') + 1; ?>" required
                               value="<?php echo $voiture_edit->Annee; ?>">
                    </div>
                    <div class="form-group">
                        <label>Immatriculation *</label>
                        <input type="text" name="immatriculation" required
                               value="<?php echo htmlentities($voiture_edit->Immatriculation); ?>">
                    </div>
                    <div class="form-group">
                        <label>Prix par jour (DT) *</label>
                        <input type="number" name="prix" min="0" step="0.01" required
                               value="<?php echo $voiture_edit->Prix_par_jour; ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label>Photo de la voiture</label>
                    <?php if (!empty($voiture_edit->Photo)): ?>
                        <img src="../uploads/voitures/<?php echo htmlentities($voiture_edit->Photo); ?>"
                             style="height:70px;border-radius:6px;object-fit:cover;display:block;margin-bottom:.5rem;" alt="photo">
                    <?php endif; ?>
                    <input type="file" name="photo" accept=".jpg,.jpeg,.png,.webp">
                    <small style="color:#64748b;">Laisser vide pour garder la photo actuelle</small>
                </div>
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="disponibilite"
                               <?php echo $voiture_edit->Disponibilite ? 'checked' : ''; ?>>
                        Disponible à la location
                    </label>
                </div>
                <div class="action-buttons">
                    <button type="submit" name="modifier" class="btn btn-primary" style="flex:1;">Enregistrer</button>
                    <a href="gestion.php" class="btn btn-secondary-dark" style="flex:1;text-align:center;">Annuler</a>
                </div>
            </form>
        </div>
        <?php endif; ?>

        
        <div class="section-block">
            <h2>Liste des Voitures (<?php echo count($voitures); ?>)</h2>
            <input type="text" id="searchVoiture" placeholder="🔍 Rechercher une voiture..."
                   style="width:100%;padding:.7rem 1rem;border:1.5px solid #e2e8f0;border-radius:8px;font-size:.95rem;margin-bottom:1rem;">
            <?php if (count($voitures) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th><th>Photo</th><th>Marque</th><th>Modèle</th><th>Année</th>
                        <th>Immatriculation</th><th>Prix/jour</th><th>Disponibilité</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody id="voituresTableBody">
                    <?php foreach ($voitures as $v): ?>
                    <tr>
                        <td><strong><?php echo $v->ID; ?></strong></td>
                        <td>
                            <?php if (!empty($v->Photo)): ?>
                                <img src="../uploads/voitures/<?php echo htmlentities($v->Photo); ?>"
                                     style="width:70px;height:45px;object-fit:cover;border-radius:6px;" alt="photo">
                            <?php else: ?>
                                <span style="color:#94a3b8;font-size:.8rem;">Aucune</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlentities($v->Marque); ?></td>
                        <td><?php echo htmlentities($v->Modele); ?></td>
                        <td><?php echo $v->Annee; ?></td>
                        <td><code><?php echo htmlentities($v->Immatriculation); ?></code></td>
                        <td><?php echo number_format($v->Prix_par_jour, 2); ?> DT</td>
                        <td>
                            <?php if ($v->Disponibilite): ?>
                                <span class="badge badge-success">Disponible</span>
                            <?php else: ?>
                                <span class="badge badge-danger">Non disponible</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="actions">
                                <a href="?action=modifier&id=<?php echo $v->ID; ?>" class="btn btn-sm btn-edit">Modifier</a>
                                <a href="?action=supprimer&id=<?php echo $v->ID; ?>"
                                   data-confirm="Supprimer cette voiture ?"
                                   class="btn btn-sm btn-delete">Supprimer</a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
                <div class="alert alert-error">Aucune voiture. <a href="?action=ajouter">Ajouter la première</a></div>
            <?php endif; ?>
        </div>

    </div>

    <footer>
        <p>&copy; 2026 AutoLoc Tunisie</p>
    </footer>
    <script src="../js/main.js"></script>
    <script src="../js/admin.js"></script>
</body>
</html>




