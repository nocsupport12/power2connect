<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Power2Connect | Your Smart Energy & Internet Assistant</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background: linear-gradient(135deg, #0a0e1a 0%, #141a2e 100%);
            font-family: 'Inter', sans-serif;
            overflow: hidden;
            position: relative;
        }

        /* Animated background particles */
        .particles {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 0;
        }

        .particle {
            position: absolute;
            background: rgba(74, 144, 226, 0.15);
            border-radius: 50%;
            pointer-events: none;
            animation: float 20s infinite linear;
        }

        @keyframes float {
            0% {
                transform: translateY(100vh) rotate(0deg);
                opacity: 0;
            }
            10% {
                opacity: 0.5;
            }
            90% {
                opacity: 0.5;
            }
            100% {
                transform: translateY(-100vh) rotate(360deg);
                opacity: 0;
            }
        }

        /* Main container */
        .container {
            position: relative;
            z-index: 1;
            text-align: center;
            animation: fadeInScale 0.8s ease-out;
        }

        /* Logo / Icon */
        .logo-icon {
            width: 90px;
            height: 90px;
            margin: 0 auto 30px;
            background: linear-gradient(135deg, #4a90e2, #7b68ee);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: pulse 2s infinite;
            box-shadow: 0 10px 40px rgba(74,144,226,0.3);
        }

        .logo-icon i {
            font-size: 45px;
            color: white;
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
                box-shadow: 0 10px 40px rgba(74,144,226,0.3);
            }
            50% {
                transform: scale(1.05);
                box-shadow: 0 15px 50px rgba(74,144,226,0.5);
            }
        }

        /* Welcome text */
        .welcome-text {
            font-size: clamp(2rem, 6vw, 3.5rem);
            font-weight: 800;
            background: linear-gradient(135deg, #fff 0%, #a0c4ff 100%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            margin-bottom: 20px;
            letter-spacing: -0.02em;
            animation: slideUp 0.6s ease-out;
        }

        /* Tagline */
        .tagline {
            font-size: clamp(1rem, 3vw, 1.5rem);
            font-weight: 500;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 40px;
            animation: slideUp 0.6s ease-out 0.2s both;
        }

        /* Loading bar */
        .loading-container {
            width: 300px;
            max-width: 80vw;
            margin: 30px auto;
        }

        .loading-bar {
            width: 100%;
            height: 4px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            overflow: hidden;
            position: relative;
        }

        .loading-progress {
            position: absolute;
            top: 0;
            left: 0;
            height: 100%;
            width: 0%;
            background: linear-gradient(90deg, #4a90e2, #7b68ee);
            border-radius: 10px;
            animation: loading 4s ease-in-out forwards;
        }

        @keyframes loading {
            0% { width: 0%; }
            20% { width: 30%; }
            50% { width: 65%; }
            80% { width: 85%; }
            100% { width: 100%; }
        }

        .loading-text {
            margin-top: 15px;
            font-size: 0.875rem;
            color: rgba(255, 255, 255, 0.6);
            letter-spacing: 2px;
        }

        /* Loading dots animation */
        .dots {
            display: inline-block;
        }

        .dots span {
            animation: blink 1.4s infinite;
            animation-fill-mode: both;
        }

        .dots span:nth-child(2) {
            animation-delay: 0.2s;
        }

        .dots span:nth-child(3) {
            animation-delay: 0.4s;
        }

        @keyframes blink {
            0%, 80%, 100% { opacity: 0; }
            40% { opacity: 1; }
        }

        /* Slide up animation */
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Fade in scale */
        @keyframes fadeInScale {
            from {
                opacity: 0;
                transform: scale(0.95);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        /* Feature cards – updated for Power2Connect */
        .features {
            display: flex;
            gap: 20px;
            margin-top: 50px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .feature {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            padding: 12px 20px;
            border-radius: 50px;
            font-size: 0.75rem;
            color: rgba(255, 255, 255, 0.85);
            animation: slideUp 0.6s ease-out 0.4s both;
            border: 1px solid rgba(74,144,226,0.3);
        }

        /* Responsive */
        @media (max-width: 600px) {
            .features {
                gap: 10px;
                margin-top: 30px;
            }
            .feature {
                font-size: 0.7rem;
                padding: 8px 15px;
            }
        }
    </style>
</head>
<body>
    <!-- Animated particles background -->
    <div class="particles" id="particles"></div>

    <div class="container">
        <!-- Logo / Icon (lightning bolt for Power2Connect) -->
        <div class="logo-icon">
            <i class="fas fa-bolt"></i>
        </div>

        <!-- Welcome Text -->
        <div class="welcome-text">
            POWER2CONNECT
        </div>

        <!-- Tagline -->
        <div class="tagline">
            Your Smart Energy & Internet Assistant
        </div>

        <!-- Loading Bar -->
        <div class="loading-container">
            <div class="loading-bar">
                <div class="loading-progress"></div>
            </div>
            <div class="loading-text">
                Loading
                <span class="dots">
                    <span>.</span><span>.</span><span>.</span>
                </span>
            </div>
        </div>

        <!-- Feature Highlights (matching Power2Connect services) -->
        <div class="features">
            <span class="feature">☀️ Solar Energy Solutions</span>
            <span class="feature">📡 High‑Speed Internet</span>
            <span class="feature">💬 AI‑Powered Support</span>
        </div>
    </div>

    <script>
        // Generate floating particles
        function createParticles() {
            const particlesContainer = document.getElementById('particles');
            const particleCount = 50;
            
            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.classList.add('particle');
                
                // Random size between 2px and 8px
                const size = Math.random() * 6 + 2;
                particle.style.width = size + 'px';
                particle.style.height = size + 'px';
                
                // Random starting position
                particle.style.left = Math.random() * 100 + '%';
                
                // Random animation duration between 10s and 25s
                const duration = Math.random() * 15 + 10;
                particle.style.animationDuration = duration + 's';
                
                // Random delay
                particle.style.animationDelay = Math.random() * 20 + 's';
                
                particlesContainer.appendChild(particle);
            }
        }
        
        // Create particles on load
        createParticles();
        
        // Redirect after animation finishes
        setTimeout(() => {
            // Add fade out effect
            document.querySelector('.container').style.transition = 'opacity 0.5s ease';
            document.querySelector('.container').style.opacity = '0';
            
            setTimeout(() => {
                window.location.href = 'chatbot.php';
            }, 500);
        }, 5000); // 5 seconds - matches loading bar animation
    </script>
</body>
</html>