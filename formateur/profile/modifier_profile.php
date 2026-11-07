<?php
session_start();
if (!isset($_SESSION['id_user'])) {
    header('Location: /phpprojet/login/index.php');
    exit;
}
require_once __DIR__ . '/../../dbopen/db.php';
extract($_SESSION);
$init = strtoupper(substr($prenom, 0, 1) . substr($nom, 0, 1));
$current_url = $_SERVER["PHP_SELF"];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modifier_profile'])) {
    extract($_POST);
    $nom = trim(isset($nom) ? $nom : ''); $prenom = trim(isset($prenom) ? $prenom : ''); $email = trim(isset($email) ? $email : '');
    $filiere = trim(isset($filiere) ? $filiere : ''); $motdepasse = isset($motdepasse) ? $motdepasse : '';
    $id_user = $_SESSION['id_user'];

    if ($email !== $_SESSION['email']) {
        $stmt = $db->prepare("SELECT id_user FROM utilisateurs WHERE email = ? AND id_user != ?");
        $stmt->execute([$email, $id_user]);
        if (count($stmt->fetchAll()) > 0) {
            $error = 'Cet email est déjà utilisé.';
        }
    }

    if (!isset($error)) {
        $sql = "UPDATE utilisateurs SET nom = ?, prenom = ?, email = ?, filiere = ?";
        $params = [$nom, $prenom, $email, $filiere ?: null];

        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = __DIR__ . '/../../uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $filename = uniqid('photo_') . '.' . $ext;
                move_uploaded_file($_FILES['photo']['tmp_name'], $upload_dir . $filename);
                $sql .= ", photo = ?";
                $params[] = $filename;
            }
        }

        if (!empty($motdepasse)) {
            $sql .= ", motdepasse = ?";
            $params[] = password_hash($motdepasse, PASSWORD_DEFAULT);
        }

        $sql .= " WHERE id_user = ?";
        $params[] = $id_user;

        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        $_SESSION['nom'] = $nom;
        $_SESSION['prenom'] = $prenom;
        $_SESSION['email'] = $email;
        $_SESSION['filiere'] = $filiere ?: null;
        if (isset($filename)) {
            $_SESSION['photo'] = $filename;
        }

        header('Location: profile.php?updated=1');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ISMO-SkillSwap - Modifier Profil</title>
    <link rel="stylesheet" href="/phpprojet/css/styles.css">
    <link rel="stylesheet" href="profile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="container">
    <aside class="sidebar sidebar-formateur">
        <div class="sidebar-header">
            <div class="sidebar-logo"><i class="fas fa-graduation-cap"></i></div>
            <div>
                <h1>ISMO-SkillSwap</h1>
                <p>ISMO Tétouan</p>
            </div>
        </div>
        <nav class="sidebar-nav">
            <a href="/phpprojet/formateur/accueil/accueil.php" class="nav-item<?= strpos($current_url,'accueil')!==false?' active':'' ?>"><i class="fas fa-home"></i><span>Accueil</span></a>
            <a href="/phpprojet/formateur/marketplace/marketplace.php" class="nav-item<?= strpos($current_url,'marketplace')!==false?' active':'' ?>"><i class="fas fa-store"></i><span>Marketplace</span></a>
            <a href="/phpprojet/formateur/profile/profile.php" class="nav-item<?= strpos($current_url,'profile')!==false?' active':'' ?>"><i class="fas fa-user"></i><span>Profil</span></a>
            <a href="/phpprojet/formateur/passport/passport.php" class="nav-item<?= strpos($current_url,'passport')!==false?' active':'' ?>"><i class="fas fa-passport"></i><span>Passport</span></a>
            <a href="/phpprojet/formateur/validation/validations.php" class="nav-item<?= strpos($current_url,'validation')!==false?' active':'' ?>"><i class="fas fa-check-circle"></i><span>Validations</span></a>
            <a href="/phpprojet/formateur/badge/badges.php" class="nav-item<?= strpos($current_url,'badge')!==false?' active':'' ?>"><i class="fas fa-medal"></i><span>Badges</span></a>
            <a href="/phpprojet/formateur/classement/classement.php" class="nav-item<?= strpos($current_url,'classement')!==false?' active':'' ?>"><i class="fas fa-ranking-star"></i><span>Classement</span></a>
        </nav>
        <div class="sidebar-footer">
            <div class="user-info">
                <div class="avatar"><?= $init ?></div>
                <div>
                    <p class="user-name"><?= htmlspecialchars("$prenom $nom") ?></p>
                    <p class="user-role"><?= ucfirst($role) ?></p>
                </div>
            </div>
        </div>
    </aside>
    <header class="top-header">
        <div class="search-bar">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Rechercher..." id="globalSearch">
        </div>
        <div class="header-actions">
            <button class="notification-btn"><i class="fas fa-bell"></i><span class="notification-badge">0</span></button>
            <div class="user-menu" id="profileToggle">
                <div class="avatar-large"><?= $init ?></div>
                <div>
                    <p class="user-name"><?= htmlspecialchars("$prenom $nom") ?></p>
                    <p class="user-role"><?= ucfirst($role) ?></p>
                </div>
                <i class="fas fa-chevron-down" id="chevronIcon"></i>
            </div>
        </div>
        <div class="profile-dropdown" id="profileMenu">
            <div class="dropdown-header">
                <div class="dropdown-avatar"><?= $init ?></div>
                <div class="dropdown-user-info">
                    <h4><?= htmlspecialchars("$prenom $nom") ?></h4>
                    <p><?= $filiere ? "Filière $filiere" : "Platform ".ucfirst($role) ?></p>
                    <span class="role-badge role-badge-formateur"><?= ucfirst($role) ?></span>
                </div>
            </div>
            <div class="dropdown-meta">
                <div class="meta-item"><i class="fas fa-envelope"></i> <?= htmlspecialchars($email) ?></div>
                <div class="meta-item"><i class="fas fa-calendar"></i> Membre depuis <?= date("d/m/Y",strtotime($date_inscription)) ?></div>
            </div>
            <div class="dropdown-actions">
                <a href="/phpprojet/formateur/passport/passport.php" class="dropdown-action"><i class="fas fa-download"></i> Télécharger le Passeport (PDF)</a>
                <a href="/phpprojet/formateur/profile/profile.php" class="dropdown-action"><i class="fas fa-user"></i> Voir le profil</a>
            </div>
            <div class="dropdown-signout">
                <a href="/phpprojet/login/deconnexion.php" class="dropdown-action signout"><i class="fas fa-right-from-bracket"></i> Déconnexion</a>
            </div>
        </div>
    </header>
    <main class="main-content">
        <h1 class="section-title">Modifier mon profil</h1>

        <?php if (isset($error)): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="settings-form" style="max-width:600px">
            <div class="form-group">
                <label>Photo de profil</label>
                <?php if (!empty($photo)): ?>
                <div style="margin-bottom:10px">
                    <img src="/phpprojet/uploads/<?= htmlspecialchars($photo) ?>" alt="Photo" style="width:100px;height:100px;object-fit:cover;border-radius:50%;">
                </div>
                <?php endif; ?>
                <input type="file" name="photo" accept="image/*" class="form-input">
            </div>
            <div class="form-group">
                <label for="nom">Nom</label>
                <input type="text" name="nom" id="nom" value="<?= htmlspecialchars($nom) ?>" required>
            </div>
            <div class="form-group">
                <label for="prenom">Prénom</label>
                <input type="text" name="prenom" id="prenom" value="<?= htmlspecialchars($prenom) ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" value="<?= htmlspecialchars($email) ?>" required>
            </div>
            <div class="form-group">
                <label for="filiere">Filière</label>
                <input type="text" name="filiere" id="filiere" value="<?= htmlspecialchars(isset($filiere) ? $filiere : '') ?>">
            </div>
            <div class="form-group">
                <label for="motdepasse">Nouveau mot de passe</label>
                <input type="password" name="motdepasse" id="motdepasse" placeholder="Laisser vide pour conserver l'actuel">
            </div>
            <div class="form-actions" style="display:flex;gap:12px;margin-top:24px">
                <button type="submit" name="modifier_profile" class="btn primary">Enregistrer les modifications</button>
                <a href="profile.php" class="btn secondary">Annuler</a>
            </div>
        </form>
    </main>
</div>
<script src="/phpprojet/js/profile-menu.js"></script>
<script src="/phpprojet/js/search.js"></script>
</body>
</html>
