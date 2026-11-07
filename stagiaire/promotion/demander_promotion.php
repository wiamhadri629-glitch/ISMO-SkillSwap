<?php
session_start();
if (!isset($_SESSION['id_user'])) {
    header('Location: /phpprojet/login/index.php');
    exit;
}
$page_title = 'Demander une promotion';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['demander'])) {
    require_once __DIR__ . '/../../dbopen/db.php';
    $id_user = $_SESSION['id_user'];
    $role = $_SESSION['role'];
    if ($role !== 'stagiaire') { header('Location: /phpprojet/login/index.php'); exit; }

    $stmt = $db->prepare("SELECT id FROM aide_effectuee WHERE id_mentor = ?");
    $stmt->execute([$id_user]);
    $nb_aides = count($stmt->fetchAll());

    $stmt = $db->prepare("SELECT note_mentor FROM aide_effectuee WHERE id_mentor = ? AND note_mentor IS NOT NULL");
    $stmt->execute([$id_user]);
    $notes = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $note_moyenne = count($notes) > 0 ? round(array_sum($notes) / count($notes), 2) : 0;

    $stmt = $db->prepare("SELECT * FROM demandes_promotion WHERE id_stagiaire = ? AND statut = 'en_attente'");
    $stmt->execute([$id_user]);
    $demande_existante = $stmt->fetch();

    $eligible = $nb_aides >= 4 && $note_moyenne >= 3 && !$demande_existante;

    $err = [];
    if (!$eligible) {
        $err[] = "Vous ne remplissez pas les conditions pour demander une promotion.";
    } else {
        $stmt = $db->prepare("INSERT INTO demandes_promotion (id_stagiaire, nb_aides, note_moyenne) VALUES (?, ?, ?)");
        $stmt->execute([$id_user, $nb_aides, $note_moyenne]);
        header("Location: demander_promotion.php");
        exit;
    }
}

require_once __DIR__ . '/../../includes/header.php';

$id_user = $_SESSION['id_user'];

$msg = $_GET['msg'] ?? '';
$err = isset($err) ? $err : [];

$stmt = $db->prepare("SELECT id FROM aide_effectuee WHERE id_mentor = ?");
$stmt->execute([$id_user]);
$nb_aides = count($stmt->fetchAll());

$stmt = $db->prepare("SELECT note_mentor FROM aide_effectuee WHERE id_mentor = ? AND note_mentor IS NOT NULL");
$stmt->execute([$id_user]);
$notes = $stmt->fetchAll(PDO::FETCH_COLUMN);
$note_moyenne = count($notes) > 0 ? round(array_sum($notes) / count($notes), 2) : 0;

$stmt = $db->prepare("SELECT * FROM demandes_promotion WHERE id_stagiaire = ? AND statut = 'en_attente'");
$stmt->execute([$id_user]);
$demande_existante = $stmt->fetch();

$stmt = $db->prepare("SELECT * FROM demandes_promotion WHERE id_stagiaire = ? ORDER BY date_demande DESC LIMIT 1");
$stmt->execute([$id_user]);
$derniere_demande = $stmt->fetch();

$eligible = $nb_aides >= 4 && $note_moyenne >= 3 && !$demande_existante;
?>
<div class="market-card header-card">
    <p class="eyebrow">Promotion</p>
    <h1>Demander une promotion en Mentor</h1>
</div>

<?php if ($msg): ?>
<div style="background:#dcfce7;color:#166534;padding:14px 20px;border-radius:12px;margin-bottom:18px;font-weight:600;">
    <i class="fas fa-check-circle"></i> <?= htmlspecialchars($msg) ?>
</div>
<?php endif; ?>

<?php if (!empty($err)): ?>
    <?php foreach ($err as $e): ?>
    <div style="background:#fef2f2;color:#991b1b;padding:14px 20px;border-radius:12px;margin-bottom:18px;font-weight:600;">
        <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($e) ?>
    </div>
    <?php endforeach; ?>
<?php endif; ?>

<div class="market-card" style="margin-bottom:20px;">
    <div class="card-head">
        <div>
            <h2>Statistiques</h2>
        </div>
    </div>
    <div style="display:flex;gap:20px;flex-wrap:wrap;margin-top:16px;">
        <div style="flex:1;min-width:150px;background:#f8fafc;padding:16px;border-radius:10px;text-align:center;">
            <p style="color:var(--muted);margin:0 0 4px 0;">Aides effectuées</p>
            <strong style="font-size:28px;"><?= $nb_aides ?></strong>
            <p style="color:var(--muted);font-size:12px;margin:4px 0 0 0;">Requis: 4</p>
        </div>
        <div style="flex:1;min-width:150px;background:#f8fafc;padding:16px;border-radius:10px;text-align:center;">
            <p style="color:var(--muted);margin:0 0 4px 0;">Note moyenne</p>
            <strong style="font-size:28px;"><?= number_format($note_moyenne, 2) ?></strong>
            <p style="color:var(--muted);font-size:12px;margin:4px 0 0 0;">Requis: 3.00</p>
        </div>
    </div>
</div>

<?php if ($demande_existante): ?>
<div class="market-card" style="margin-bottom:20px;border-left:4px solid #f59e0b;">
    <div class="card-head">
        <div>
            <p class="status-pill proposee" style="background:#f59e0b;">En attente</p>
            <h2>Demande déjà envoyée</h2>
        </div>
    </div>
    <p class="card-copy">Vous avez déjà une demande de promotion en attente. Veuillez patienter qu'un formateur la traite.</p>
</div>
<?php elseif ($derniere_demande && $derniere_demande['statut'] === 'acceptee'): ?>
<div class="market-card" style="margin-bottom:20px;border-left:4px solid #16a34a;">
    <div class="card-head">
        <div>
            <p class="status-pill validee" style="background:#16a34a;">Acceptée</p>
            <h2>Déjà Mentor</h2>
        </div>
    </div>
    <p class="card-copy">Votre demande de promotion a déjà été acceptée. Vous êtes désormais mentor !</p>
</div>
<?php elseif ($eligible): ?>
<div class="market-card" style="margin-bottom:20px;border-left:4px solid #2563eb;">
    <div class="card-head">
        <div>
            <h2>Vous êtes éligible !</h2>
        </div>
    </div>
    <p class="card-copy">Félicitations ! Vous remplissez toutes les conditions pour devenir mentor. Confirmez votre demande ci-dessous.</p>
    <form method="POST" style="margin-top:16px;">
        <button type="submit" name="demander" class="btn primary" style="padding:12px 24px;font-size:15px;">
            <i class="fas fa-arrow-up"></i> Demander la promotion
        </button>
    </form>
</div>
<?php else: ?>
<div class="market-card" style="margin-bottom:20px;border-left:4px solid #dc2626;">
    <div class="card-head">
        <div>
            <h2>Conditions non remplies</h2>
        </div>
    </div>
    <p class="card-copy">Vous ne remplissez pas encore toutes les conditions pour devenir mentor. Continuez à aider vos camarades et à obtenir de bonnes notes !</p>
    <ul style="margin-top:12px;padding-left:20px;">
        <li style="margin-bottom:8px;color:<?= $nb_aides >= 4 ? '#16a34a' : '#dc2626' ?>;">
            <i class="fas <?= $nb_aides >= 4 ? 'fa-check-circle' : 'fa-times-circle' ?>"></i>
            Aides effectuées: <?= $nb_aides ?>/4
        </li>
        <li style="color:<?= $note_moyenne >= 3 ? '#16a34a' : '#dc2626' ?>;">
            <i class="fas <?= $note_moyenne >= 3 ? 'fa-check-circle' : 'fa-times-circle' ?>"></i>
            Note moyenne: <?= number_format($note_moyenne, 2) ?>/3.00
        </li>
    </ul>
</div>
<?php endif; ?>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
