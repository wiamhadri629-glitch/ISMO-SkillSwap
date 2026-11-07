<?php
session_start();
if (!isset($_SESSION['id_user'])) {
    header('Location: /phpprojet/login/index.php');
    exit;
}
require_once __DIR__ . '/../../dbopen/db.php';

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

$page_title = 'Modifier Profil';
$page_css = 'profile.css';
require_once __DIR__ . '/../../includes/header.php';
?>
<section class="profile-edit-section">
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
</section>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
