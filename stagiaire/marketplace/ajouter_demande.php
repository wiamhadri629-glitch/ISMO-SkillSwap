<?php
if($_SERVER['REQUEST_METHOD']=="POST"){
    if(isset($_POST['publier'])){
    extract($_POST);
    $err=[];
    if(!isset($titre) || empty($titre)) $err[]="Le titre est obligatoire";
    if(!isset($description) || empty($description)) $err[]="La description est obligatoire";
    if(empty($err)){
        $titre = htmlspecialchars(trim($titre));
        $description = htmlspecialchars(trim($description));
        include ("../../dbopen/db.php");
        try {
            $pub = $db->prepare("INSERT INTO demandes_aide (titre, description) VALUES (?, ?)");
            $p=$pub->execute([$titre, $description]);
            if($p==False) echo "<div style='color:red'>Echec de publication</div>";
            else {
                header("Location: marketplace.php?msg=Demande publiée avec succès");
            }            
        } catch(PDOException $e) {
            die("Erreur de connexion : " . $e->getMessage());
    }
    }
}
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Publier une demande</title>
    <link rel="stylesheet" href="../../css/styles.css">
    <link rel="stylesheet" href="marketplace.css">
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="container">
    <aside class="sidebar sidebar-stagiaire">
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <div>
                <h1>ISMO-SkillSwap</h1>
                <p>ISMO Tétouan</p>
            </div>
        </div
        <nav class="sidebar-nav">
            <a href="../accueil/accueil.php" class="nav-item">
                <i class="fas fa-home"></i>
                <span>Accueil</span>
            </a>
            <a href="marketplace.php" class="nav-item active">
                <i class="fas fa-store"></i>
                <span>Marketplace</span>
            </a>
            <a href="../profile/profile.php" class="nav-item">
                <i class="fas fa-user"></i>
                <span>Profil</span>
            </a>
            <a href="../passport/passport.php" class="nav-item">
                <i class="fas fa-passport"></i>
                <span>Passport</span>
            </a>
            <a href="../classement/classement.php" class="nav-item">
                <i class="fas fa-ranking-star"></i>
                <span>Classement</span>
            </a>
        </nav>
    </aside>
    <div style="flex:1;">
        <header class="top-header">
            <div class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Rechercher...">
            </div>
            <div class="header-actions">
                <button class="notification-btn">
                    <i class="fas fa-bell"></i>
                </button>
                <div class="user-menu">
                    <div class="avatar-large">JS</div>
                    <div>
                        <p class="user-name"> Ahmadi Ahmed </p>
                        <p class="user-role">Stagiaire</p>
                    </div>
                </div>
            </div>
        </header>
        <main class="main-content">
            <div class="market-card">
                <p class="eyebrow">
                    Marketplace
                </p>
                <h1 style="margin-top:0;">
                    Publier une demande d'aide
                </h1>
                <p class="card-copy">
                    Décrivez votre problème afin que les autres stagiaires
                    ou formateurs puissent vous aider.
                </p>
            </div>
            <div class="market-card">
                <form id="demandeForm" method="POST">
                    <div class="form-group">
                        <label>
                            Titre du problème
                        </label>
                        <input
                            type="text"
                            id="titre"
                            name="titre"
                            placeholder="Ex : Problème avec les jointures SQL"
                            >
                    <?php if (isset($err["description"])) echo "<div style='color:red'>" . $err["description"] . "</div>"; ?>
                    </div>
                    <br><br>
                    <div class="form-group">
                        <label>
                            Description détaillée
                        </label>
                        <textarea
                            id="description"
                            name="description"
                            rows="8"
                            placeholder="Expliquez votre problème..."
                            ></textarea>
                    <?php if (isset($err["description"])) echo "<div style='color:red'>" . $err["description"] . "</div>"; ?>
                    </div>
                    <br><br>    
                    <div class="form-actions">
                        <button
                            type="submit"
                            name="publier"
                            class="btn primary">
                            <i class="fas fa-paper-plane"></i>
                            Publier la demande
                        </button>
                    </div>
                </form>
            </div>
            <div id="resultat"></div>
        </main>
    </div>
</div>
<script>
const form = document.getElementById("demandeForm");
const resultat = document.getElementById("resultat");
form.addEventListener("submit", function(e){
    e.preventDefault();
    const titre =
        document.getElementById("titre").value;
    const description =
        document.getElementById("description").value;
    resultat.innerHTML = `
        <div class="market-card">
            <div class="card-head">
                <div>
                    <span class="status-pill open">
                        Nouvelle demande
                    </span>
                    <h2 style="margin-top:15px;">
                        ${titre}
                    </h2>
                </div>
            </div>
            <p class="card-copy">
                ${description}
            </p>
            <div class="card-footer">
                <div class="author">
                    <div class="avatar-sm">
                        JS
                    </div>
                    <div>
                        <strong>
                            John Student
                        </strong>
                        <small>
                            Publié maintenant
                        </small>
                    </div>
                </div>
            </div>
        </div>
    `;
    form.reset();
});

</script>

</body>
</html>