<?php
session_start();
if(!isset($_SESSION['id_user'])){ header('Location: /phpprojet/login/index.php'); exit; }
$page_title = 'Proposer mon aide';
$page_css = 'marketplace.css';

require_once __DIR__ . '/../../dbopen/db.php';
$id_user = $_SESSION['id_user'];

$id_demande = (int)($_GET['id_demande'] ?? 0);
$error = '';

if ($id_demande <= 0) {
    header("Location: marketplace.php");
    exit;
}

$stmt = $db->prepare("SELECT id_user, titre, description, status FROM demandes_aide WHERE id_demande = ?");
$stmt->execute([$id_demande]);
$demande = $stmt->fetch();

if (!$demande) {
    header("Location: marketplace.php");
    exit;
}

if ($demande['id_user'] == $id_user) {
    header("Location: marketplace.php");
    exit;
}

if ($demande['status'] !== 'ouverte') {
    header("Location: marketplace.php");
    exit;
}

$stmt = $db->prepare("SELECT id FROM propositions_aide WHERE id_demande = ? AND id_mentor = ?");
$stmt->execute([$id_demande, $id_user]);
if ($stmt->fetch()) {
    header("Location: marketplace.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['proposer'])) {
    extract($_POST);
    $message = trim(isset($message) ? $message : '');

    if (empty($message)) {
        $error = 'Veuillez écrire une explication pour votre proposition.';
    } else {
        $stmt = $db->prepare("INSERT INTO propositions_aide (id_demande, id_mentor, message, status) VALUES (?, ?, ?, 'en_attente')");
        $stmt->execute([$id_demande, $id_user, $message]);
        header("Location: marketplace.php");
        exit;
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>
<div class="topbar-actions">
    <div class="topbar-left">
        <h1 class="section-title">Proposer mon aide</h1>
    </div>
</div>

<?php if ($error): ?>
<div style="background:#fef2f2;color:#991b1b;padding:14px 20px;border-radius:12px;margin-bottom:18px;font-weight:600;">
    <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>

<div class="market-card" style="margin-bottom:20px;">
    <div class="card-head">
        <div>
            <p class="status-pill open">Demande</p>
            <h2><?= htmlspecialchars($demande['titre']) ?></h2>
        </div>
    </div>
    <p class="card-copy"><?= htmlspecialchars($demande['description']) ?></p>
</div>

<div class="market-card form-card">
    <form method="POST">
        <div class="form-group">
            <label>Votre explication <span style="color:#ef4444;">*</span></label>
            <textarea name="message" rows="6" placeholder="Expliquez comment vous comptez aider le stagiaire, les notions que vous allez aborder, etc." required><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
        </div>
        <div class="form-actions">
            <button type="submit" name="proposer" class="btn primary full">
                <i class="fas fa-paper-plane"></i> Envoyer ma proposition
            </button>
        </div>
    </form>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
