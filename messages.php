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
    
    // Get user's conversations
    $stmt = $db->getConnection()->prepare("
        SELECT DISTINCT 
            u.user_id,
            u.full_name,
            u.profile_image,
            (SELECT message 
             FROM messages 
             WHERE (sender_id = ? AND receiver_id = u.user_id) 
                OR (sender_id = u.user_id AND receiver_id = ?)
             ORDER BY created_at DESC 
             LIMIT 1) as last_message,
            (SELECT created_at 
             FROM messages 
             WHERE (sender_id = ? AND receiver_id = u.user_id) 
                OR (sender_id = u.user_id AND receiver_id = ?)
             ORDER BY created_at DESC 
             LIMIT 1) as last_message_time
        FROM users u
        JOIN messages m ON (m.sender_id = u.user_id AND m.receiver_id = ?)
            OR (m.receiver_id = u.user_id AND m.sender_id = ?)
        WHERE u.user_id != ?
        ORDER BY last_message_time DESC
    ");
    $stmt->execute([
        $_SESSION['user_id'], $_SESSION['user_id'],
        $_SESSION['user_id'], $_SESSION['user_id'],
        $_SESSION['user_id'], $_SESSION['user_id'],
        $_SESSION['user_id']
    ]);
    $conversations = $stmt->fetchAll();

} catch(Exception $e) {
    error_log("Messages page error: " . $e->getMessage());
    $error = "System error. Please try again later.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - ResearchHub</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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

        .nav-content {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            color: var(--text-primary);
            font-size: 1.5rem;
            font-weight: 700;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .logo::before {
            content: '⚡';
            font-size: 1.8rem;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
            align-items: center;
        }

        .nav-links a {
            color: var(--text-secondary);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            padding: 0.5rem 1rem;
            border-radius: 8px;
        }

        .nav-links a:hover {
            color: var(--text-primary);
            background: rgba(255, 255, 255, 0.1);
        }

        .messages-container {
            max-width: 1400px;
            margin: 100px auto 0;
            padding: 2rem;
            display: grid;
            grid-template-columns: 350px 1fr;
            gap: 2rem;
            height: calc(100vh - 100px);
            position: relative;
        }

        .conversations-list {
            background: var(--card-color);
            border-radius: 24px;
            padding: 1.5rem;
            overflow-y: auto;
            height: 100%;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
        }

        .chat-area {
            background: var(--card-color);
            border-radius: 24px;
            display: flex;
            flex-direction: column;
            height: 100%;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
            position: relative;
            overflow: hidden;
        }

        .welcome-message {
            padding: 3rem;
            text-align: center;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            gap: 2rem;
            background: radial-gradient(circle at top right, rgba(79, 70, 229, 0.1) 0%, transparent 70%),
                        radial-gradient(circle at bottom left, rgba(45, 212, 191, 0.1) 0%, transparent 70%);
            position: relative;
        }

        .welcome-message::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg width="30" height="30" viewBox="0 0 30 30" xmlns="http://www.w3.org/2000/svg"><rect width="1" height="1" fill="rgba(255,255,255,0.05)"/></svg>');
            opacity: 0.5;
            z-index: 0;
        }

        .welcome-icon {
            font-size: 5rem;
            background: var(--gradient-1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: float 3s ease-in-out infinite;
            position: relative;
            z-index: 1;
        }

        .welcome-title {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 1rem;
            background: var(--gradient-2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            position: relative;
            z-index: 1;
        }

        .welcome-text {
            color: var(--text-secondary);
            line-height: 1.8;
            max-width: 600px;
            margin-bottom: 1rem;
            font-size: 1.1rem;
            position: relative;
            z-index: 1;
        }

        .conversation-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1.2rem;
            border-radius: 16px;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid transparent;
            margin-bottom: 0.8rem;
            position: relative;
            overflow: hidden;
        }

        .conversation-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: var(--gradient-1);
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: 0;
        }

        .conversation-item:hover {
            transform: translateX(5px) translateY(-2px);
            border-color: rgba(255, 255, 255, 0.1);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .conversation-item:hover::before {
            opacity: 0.1;
        }

        .conversation-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid transparent;
            background: var(--gradient-1);
            padding: 2px;
            z-index: 1;
        }

        .contact-button {
            background: var(--gradient-1);
            color: white;
            border: none;
            padding: 1.2rem 2.5rem;
            border-radius: 16px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            gap: 1rem;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }

        .contact-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: var(--gradient-2);
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: -1;
        }

        .contact-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px var(--shadow-color);
        }

        .contact-button:hover::before {
            opacity: 1;
        }

        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
            100% { transform: translateY(0px); }
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .welcome-message .particles {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            overflow: hidden;
            z-index: 0;
        }

        .particle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: var(--accent-color);
            border-radius: 50%;
            animation: moveParticle 15s infinite linear;
            opacity: 0.3;
        }

        @keyframes moveParticle {
            0% { transform: translate(0, 0); }
            100% { transform: translate(400px, -400px); }
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--gradient-1);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--gradient-2);
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

    <div class="messages-container">
        <!-- Conversations List -->
        <div class="conversations-list">
            <h2 style="margin-bottom: 1.5rem;">Messages</h2>
            <?php if (empty($conversations)): ?>
                <p style="color: var(--text-secondary);">No conversations yet</p>
            <?php else: ?>
                <?php foreach ($conversations as $conv): ?>
                    <div class="conversation-item" data-user-id="<?php echo $conv['user_id']; ?>">
                        <img src="<?php echo htmlspecialchars($conv['profile_image'] ?? 'assets/default-profile.png'); ?>" 
                             alt="Profile" class="conversation-avatar">
                        <div class="conversation-info">
                            <div class="conversation-name"><?php echo htmlspecialchars($conv['full_name']); ?></div>
                            <div class="conversation-last-message">
                                <?php echo htmlspecialchars(substr($conv['last_message'], 0, 50)); ?>...
                            </div>
                        </div>
                        <div class="conversation-time">
                            <?php echo date('H:i', strtotime($conv['last_message_time'])); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Chat Area / Welcome Message -->
        <div class="chat-area">
            <div class="welcome-message">
                <div class="particles">
                    <?php for($i = 0; $i < 20; $i++): ?>
                        <div class="particle" style="
                            left: <?php echo rand(0, 100); ?>%;
                            top: <?php echo rand(0, 100); ?>%;
                            animation-delay: <?php echo $i * 0.5; ?>s;
                        "></div>
                    <?php endfor; ?>
                </div>
                <i class="fas fa-paper-plane welcome-icon"></i>
                <h1 class="welcome-title">Welcome to Your Research Hub!</h1>
                <p class="welcome-text">
                    Thank you for being part of our growing research community! We're thrilled to have you here. 
                    Connect with brilliant minds, share groundbreaking ideas, and collaborate on innovative projects.
                </p>
                <p class="welcome-text">
                    Our platform is designed to foster meaningful connections and facilitate knowledge exchange. 
                    Start a conversation, explore new research possibilities, and be part of something extraordinary!
                </p>
                <button class="contact-button" onclick="location.href='contact_support.php'">
                    <i class="fas fa-headset"></i>
                    Get in Touch with Us
                </button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Conversation click handler
            document.querySelectorAll('.conversation-item').forEach(item => {
                item.addEventListener('click', function() {
                    const userId = this.dataset.userId;
                    // Navigate to individual chat page
                    window.location.href = `chat.php?user_id=${userId}`;
                });
            });
        });
    </script>
</body>
</html>
