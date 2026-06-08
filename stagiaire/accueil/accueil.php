<!doctype html>
<html lang="fr">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>ISMO-SkillSwap - Accueil Admin</title>
  <link rel="stylesheet" href="../../css/styles.css">
  <link rel="stylesheet" href="accueil.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

</head>

<body>
  <div class="container">
    <!-- Sidebar -->
                <aside class="sidebar sidebar-stagiaire">
      <div class="sidebar-header">
        <div class="sidebar-logo">
          <i class="fas fa-graduation-cap"></i>
        </div>
        <div>
          <h1>ISMO-SkillSwap</h1>
          <p>ISMO Tétouan</p>
        </div>
      </div>
      <nav class="sidebar-nav">
        <a href="../accueil/accueil.php" class="nav-item active">
          <i class="fas fa-home"></i>
          <span>Accueil</span>
        </a>
        <a href="../marketplace/marketplace.php" class="nav-item">
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
      <div class="sidebar-footer">
        <div class="user-info">
          <div class="avatar">JS</div>
          <div>
            <p class="user-name">John Student</p>
            <p class="user-role">Stagiaire</p>
          </div>
        </div>
      </div>
    </aside>
        <header class="top-header">
      <div class="search-bar">
        <i class="fas fa-search"></i>
        <input type="text" placeholder="Rechercher...">
      </div>
      <div class="header-actions">
        <button class="notification-btn">
          <i class="fas fa-bell"></i>
          <span class="notification-badge">3</span>
        </button>
        <div class="user-menu" id="profileToggle">
          <div class="avatar-large">JS</div>
          <div>
            <p class="user-name">John Student</p>
            <p class="user-role">Stagiaire</p>
          </div>
          <i class="fas fa-chevron-down" id="chevronIcon"></i>
        </div>
      </div>

      <!-- Profile Dropdown -->
      <div class="profile-dropdown" id="profileMenu">
        <div class="dropdown-header">
          <div class="dropdown-avatar">JS</div>
          <div class="dropdown-user-info">
            <h4>John Student</h4>
            <p>Platform Stagiaire</p>
            <span class="role-badge role-badge-stagiaire">Stagiaire</span>
          </div>
        </div>
        <div class="dropdown-meta">
          <div class="meta-item"><i class="fas fa-envelope"></i> student@ismo.com</div>
          <div class="meta-item"><i class="fas fa-calendar"></i> Joined 9/2/2023</div>
          <div class="meta-item"><i class="fas fa-chart-line"></i> 1,245 Reputation Points</div>
          <div class="meta-item"><i class="fas fa-medal"></i> 12 Skills Validated</div>
        </div>
        <div class="dropdown-actions">
          <a href="../passport/passport.php" class="dropdown-action">
            <i class="fas fa-download"></i> Download Skills Passport (PDF)
          </a>
          <a href="../profile/profile.php" class="dropdown-action">
            <i class="fas fa-user"></i> View Profile
          </a>
        </div>
        <div class="dropdown-signout">
          <a href="../../login/index.php" class="dropdown-action signout">
            <i class="fas fa-right-from-bracket"></i> Sign Out
          </a>
        </div>
      </div>
    </header>
<main class="main-content">
      <h1 class="section-title">Tableau de Bord</h1>
      <p class="sub">Voici un aperçu de ton activité et de tes compétences validées</p>
      <div class="cards-grid">
        <article class="stat-card">
          <div class="stat-head">
            <span>Aides réalisées</span>
            <span>👥</span>
          </div>
          <h2>32</h2>
          <p>+5 cette semaine</p>
        </article>

        <article class="stat-card">
          <div class="stat-head">
            <span>Note moyenne</span>
            <span>⭐</span>
          </div>
          <h2>4.8</h2>
          <p>sur 5</p>
        </article>

        <article class="stat-card">
          <div class="stat-head">
            <span>Demandes actives</span>
            <span>💬</span>
          </div>
          <h2>8</h2>
          <p>3 en attente</p>
        </article>

        <article class="stat-card">
          <div class="stat-head">
            <span>Badges obtenus</span>
            <span>🏅</span>
          </div>
          <h2>8</h2>
          <p>7 restants</p>
        </article>
      </div>

      <section class="skills-section">
        <div class="section-header">
          <h3>Compétences les plus demandées</h3>
        </div>
        <div class="skills-bar-list">
          <div class="skill-bar-item">
            <div class="skill-bar-label"><span>React</span><span>42 demandes</span></div>
            <div class="skill-bar-track"><div class="skill-bar-fill" style="width:100%"></div></div>
          </div>
          <div class="skill-bar-item">
            <div class="skill-bar-label"><span>SQL</span><span>28 demandes</span></div>
            <div class="skill-bar-track"><div class="skill-bar-fill" style="width:67%"></div></div>
          </div>
          <div class="skill-bar-item">
            <div class="skill-bar-label"><span>CSS</span><span>22 demandes</span></div>
            <div class="skill-bar-track"><div class="skill-bar-fill" style="width:52%"></div></div>
          </div>
          <div class="skill-bar-item">
            <div class="skill-bar-label"><span>Git</span><span>18 demandes</span></div>
            <div class="skill-bar-track"><div class="skill-bar-fill" style="width:43%"></div></div>
          </div>
          <div class="skill-bar-item">
            <div class="skill-bar-label"><span>TypeScript</span><span>12 demandes</span></div>
            <div class="skill-bar-track"><div class="skill-bar-fill" style="width:29%"></div></div>
          </div>
        </div>
      </section>
    </main>
  </div>
  
  <script>
    const profileToggle = document.getElementById('profileToggle');
    const profileMenu   = document.getElementById('profileMenu');
    const chevronIcon   = document.getElementById('chevronIcon');
    profileToggle.addEventListener('click', (e) => {
      e.stopPropagation();
      profileMenu.classList.toggle('open');
      chevronIcon.classList.toggle('rotated');
    });
    document.addEventListener('click', (e) => {
      if (!profileToggle.contains(e.target) && !profileMenu.contains(e.target)) {
        profileMenu.classList.remove('open');
        chevronIcon.classList.remove('rotated');
      }
    });
  </script>
</body>

</html>
