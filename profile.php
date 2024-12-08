<?php
require_once 'includes/config.php';
require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

try {
    $db = Database::getInstance();
    $user = $db->getUserById($_SESSION['user_id']);
    $interests = $db->getUserInterests($_SESSION['user_id']);
    
    // Get user's publications/articles
    $stmt = $db->getConnection()->prepare("
        SELECT * FROM articles 
        WHERE user_id = ? 
        ORDER BY created_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $publications = $stmt->fetchAll();

    // Get user's stats
    $stmt = $db->getConnection()->prepare("
        SELECT 
            (SELECT COUNT(*) FROM articles WHERE user_id = ?) as publication_count,
            (SELECT COUNT(*) FROM connections WHERE (user_id1 = ? OR user_id2 = ?) AND status = 'accepted') as connection_count,
            (SELECT COUNT(*) FROM user_interests WHERE user_id = ?) as interest_count
    ");
    $stmt->execute([$_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id']]);
    $stats = $stmt->fetch();

} catch(Exception $e) {
    error_log("Profile error: " . $e->getMessage());
    $error = "System error. Please try again later.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - ResearchHub</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4f46e5;
            --secondary-color: #818cf8;
            --accent-color: #6366f1;
            --background-color: #0f172a;
            --card-color: #1e293b;
            --text-primary: #f8fafc;
            --text-secondary: #94a3b8;
            --success-color: #22c55e;
            --gradient-1: linear-gradient(135deg, #4f46e5 0%, #818cf8 100%);
            --gradient-2: linear-gradient(45deg, #3b82f6 0%, #2dd4bf 100%);
            --shadow-color: rgba(79, 70, 229, 0.2);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            background: var(--background-color);
            color: var(--text-primary);
            min-height: 100vh;
        }

        .navbar {
            background: rgba(30, 41, 59, 0.8);
            backdrop-filter: blur(10px);
            padding: 1rem 2rem;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .profile-container {
            max-width: 1200px;
            margin: 100px auto 0;
            padding: 2rem;
            display: grid;
            grid-template-columns: 400px 1fr;
            gap: 2rem;
        }

        .profile-sidebar {
            position: sticky;
            top: 120px;
            height: fit-content;
        }

        .profile-card {
            background: var(--card-color);
            border-radius: 24px;
            padding: 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .profile-cover {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 150px;
            background: var(--gradient-1);
            opacity: 0.8;
        }

        .profile-picture-container {
            position: relative;
            margin-top: 40px;
            margin-bottom: 1.5rem;
            width: 250px;
            height: 250px;
            margin-left: auto;
            margin-right: auto;
        }

        .profile-picture {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            border: 4px solid var(--card-color);
            object-fit: cover;
            position: relative;
            z-index: 1;
            background: var(--gradient-1);
            padding: 4px;
            transition: transform 0.3s ease;
        }

        .profile-picture:hover {
            transform: scale(1.05);
        }

        .edit-picture {
            position: absolute;
            bottom: 20px;
            right: 20px;
            background: var(--gradient-2);
            border: none;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            z-index: 2;
            font-size: 1.2rem;
        }

        .edit-picture:hover {
            transform: scale(1.1);
        }

        .profile-name {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            background: var(--gradient-2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .profile-title {
            color: var(--text-secondary);
            margin-bottom: 1.5rem;
            font-size: 1.1rem;
        }

        .profile-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin: 2rem 0;
            padding: 1rem 0;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .stat-item {
            text-align: center;
        }

        .stat-value {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--accent-color);
        }

        .stat-label {
            font-size: 1rem;
            color: var(--text-secondary);
            margin-top: 0.3rem;
        }

        .profile-content {
            display: grid;
            gap: 2rem;
        }

        .content-section {
            background: var(--card-color);
            border-radius: 24px;
            padding: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .section-title {
            font-size: 1.4rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
        }

        .section-title i {
            color: var(--accent-color);
        }

        .edit-bio-btn {
            background: var(--gradient-2);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 12px;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .edit-bio-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px var(--shadow-color);
        }

        .bio-content {
            color: var(--text-secondary);
            line-height: 1.8;
            font-size: 1.1rem;
            white-space: pre-wrap;
        }

        .bio-editor {
            display: none;
            width: 100%;
        }

        .bio-textarea {
            width: 100%;
            min-height: 150px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 1rem;
            color: var(--text-primary);
            font-size: 1.1rem;
            line-height: 1.8;
            resize: vertical;
            margin-bottom: 1rem;
        }

        .bio-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
        }

        .bio-save-btn, .bio-cancel-btn {
            padding: 0.5rem 1.5rem;
            border-radius: 8px;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .bio-save-btn {
            background: var(--gradient-1);
            color: white;
            border: none;
        }

        .bio-cancel-btn {
            background: transparent;
            color: var(--text-secondary);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .bio-save-btn:hover, .bio-cancel-btn:hover {
            transform: translateY(-2px);
        }

        .interests-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 0.8rem;
        }

        .interest-tag {
            background: rgba(99, 102, 241, 0.1);
            color: var(--accent-color);
            padding: 0.8rem 1.5rem;
            border-radius: 12px;
            font-size: 1rem;
            border: 1px solid rgba(99, 102, 241, 0.2);
            transition: all 0.3s ease;
        }

        .interest-tag:hover {
            background: rgba(99, 102, 241, 0.2);
            transform: translateY(-2px);
        }

        .publication-item {
            padding: 1.5rem;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 16px;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }

        .publication-item:hover {
            transform: translateX(5px);
            background: rgba(255, 255, 255, 0.08);
        }

        .publication-title {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--text-primary);
            font-size: 1.2rem;
        }

        .publication-meta {
            font-size: 1rem;
            color: var(--text-secondary);
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .edit-profile-btn {
            background: var(--gradient-1);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 12px;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .edit-profile-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px var(--shadow-color);
        }

        @media (max-width: 1000px) {
            .profile-container {
                grid-template-columns: 1fr;
            }

            .profile-sidebar {
                position: static;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-content">
            <a href="dashboard.php" class="logo">ResearchHub</a>
            <div class="nav-links">
                <a href="notifications.php">
                    <i class="fas fa-bell"></i>
                    Notifications
                </a>
                <a href="messages.php">
                    <i class="fas fa-envelope"></i>
                    Messages
                </a>
                <a href="profile.php">
                    <i class="fas fa-user"></i>
                    Profile
                </a>
                <a href="logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="profile-container">
        <!-- Profile Sidebar -->
        <div class="profile-sidebar">
            <div class="profile-card">
                <div class="profile-cover"></div>
                <div class="profile-picture-container">
                    <img src="<?php echo htmlspecialchars($user['profile_image'] ?? 'assets/default-profile.png'); ?>" 
                         alt="Profile Picture" class="profile-picture">
                    <button class="edit-picture" title="Change Profile Picture">
                        <i class="fas fa-camera" style="color: white;"></i>
                    </button>
                </div>
                <h1 class="profile-name"><?php echo htmlspecialchars($user['full_name']); ?></h1>
                <p class="profile-title"><?php echo htmlspecialchars($user['title'] ?? 'Researcher'); ?></p>
                
                <div class="profile-stats">
                    <div class="stat-item">
                        <div class="stat-value"><?php echo $stats['publication_count']; ?></div>
                        <div class="stat-label">Publications</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?php echo $stats['connection_count']; ?></div>
                        <div class="stat-label">Connections</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?php echo $stats['interest_count']; ?></div>
                        <div class="stat-label">Interests</div>
                    </div>
                </div>

                <button class="edit-profile-btn">
                    <i class="fas fa-edit"></i> Edit Profile
                </button>
            </div>
        </div>

        <!-- Profile Content -->
        <div class="profile-content">
            <!-- About Section -->
            <div class="content-section">
                <h2 class="section-title">
                    <span><i class="fas fa-user"></i> About</span>
                    <button class="edit-bio-btn">
                        <i class="fas fa-edit"></i> Edit Bio
                    </button>
                </h2>
                <div class="bio-content">
                    <?php echo nl2br(htmlspecialchars($user['bio'] ?? 'No bio available. Click "Edit Bio" to add your story!')); ?>
                </div>
                <div class="bio-editor">
                    <textarea class="bio-textarea"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                    <div class="bio-actions">
                        <button class="bio-cancel-btn">Cancel</button>
                        <button class="bio-save-btn">Save Changes</button>
                    </div>
                </div>
            </div>

            <!-- Research Interests -->
            <div class="content-section">
                <h2 class="section-title">
                    <i class="fas fa-lightbulb"></i> Research Interests
                </h2>
                <div class="interests-grid">
                    <?php foreach ($interests as $interest): ?>
                        <span class="interest-tag">
                            <?php echo htmlspecialchars($interest['field_name']); ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Publications -->
            <div class="content-section">
                <h2 class="section-title">
                    <i class="fas fa-book"></i> Publications
                </h2>
                <?php if (empty($publications)): ?>
                    <p style="color: var(--text-secondary);">No publications yet.</p>
                <?php else: ?>
                    <?php foreach ($publications as $pub): ?>
                        <div class="publication-item">
                            <h3 class="publication-title"><?php echo htmlspecialchars($pub['title']); ?></h3>
                            <div class="publication-meta">
                                <span><i class="far fa-calendar"></i> <?php echo date('M d, Y', strtotime($pub['created_at'])); ?></span>
                                <span><i class="far fa-file-alt"></i> Research Article</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Profile picture upload functionality
        document.querySelector('.edit-picture').addEventListener('click', function() {
            const input = document.createElement('input');
            input.type = 'file';
            input.accept = 'image/*';
            input.onchange = async function(e) {
                const file = e.target.files[0];
                if (file) {
                    const formData = new FormData();
                    formData.append('profile_image', file);

                    try {
                        const response = await fetch('api/update_profile_image.php', {
                            method: 'POST',
                            body: formData
                        });

                        if (response.ok) {
                            window.location.reload();
                        } else {
                            alert('Failed to update profile picture');
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('Failed to update profile picture');
                    }
                }
            };
            input.click();
        });

        // Bio editing functionality
        const bioContent = document.querySelector('.bio-content');
        const bioEditor = document.querySelector('.bio-editor');
        const bioTextarea = document.querySelector('.bio-textarea');
        const editBioBtn = document.querySelector('.edit-bio-btn');
        const saveBioBtn = document.querySelector('.bio-save-btn');
        const cancelBioBtn = document.querySelector('.bio-cancel-btn');

        editBioBtn.addEventListener('click', function() {
            bioContent.style.display = 'none';
            bioEditor.style.display = 'block';
            bioTextarea.focus();
        });

        cancelBioBtn.addEventListener('click', function() {
            bioContent.style.display = 'block';
            bioEditor.style.display = 'none';
        });

        saveBioBtn.addEventListener('click', async function() {
            const newBio = bioTextarea.value.trim();
            
            try {
                const response = await fetch('api/update_bio.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ bio: newBio })
                });

                if (response.ok) {
                    window.location.reload();
                } else {
                    alert('Failed to update bio');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Failed to update bio');
            }
        });

        // Edit profile button functionality
        document.querySelector('.edit-profile-btn').addEventListener('click', function() {
            window.location.href = 'edit_profile.php';
        });
    </script>
</body>
</html>
