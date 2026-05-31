<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --bg: #080b14;
            --panel: #0d1120;
            --border: rgba(255,255,255,0.07);
            --accent: #4f8eff;
            --accent2: #a259ff;
            --text: #e8eaf0;
            --muted: #5a6080;
            --input-bg: rgba(255,255,255,0.04);
            --error: #ff5a7d;
            --success: #36e8a0;
            --glow: rgba(79, 142, 255, 0.18);
        }

        html, body {
            height: 100%;
            font-family: 'DM Sans', sans-serif;
            background: var(--bg);
            color: var(--text);
            overflow: hidden;
        }

        /* === CANVAS FONDO === */
        #bg-canvas {
            position: fixed;
            inset: 0;
            z-index: 0;
            pointer-events: none;
        }

        /* === LAYOUT === */
        .page {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 24px;
        }

        /* === CARD === */
        .card {
            width: 100%;
            max-width: 440px;
            background: var(--panel);
            border: 1px solid var(--border);
            border-radius: 24px;
            padding: 48px 44px;
            position: relative;
            backdrop-filter: blur(24px);
            box-shadow:
                0 0 0 1px rgba(79,142,255,0.08),
                0 40px 80px rgba(0,0,0,0.6),
                inset 0 1px 0 rgba(255,255,255,0.06);
            animation: cardIn 0.7s cubic-bezier(0.22, 1, 0.36, 1) both;
        }

        @keyframes cardIn {
            from { opacity: 0; transform: translateY(32px) scale(0.97); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }

        /* Línea de acento superior */
        .card::before {
            content: '';
            position: absolute;
            top: 0; left: 50%;
            transform: translateX(-50%);
            width: 60%;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--accent), var(--accent2), transparent);
            border-radius: 0 0 4px 4px;
        }

        /* === HEADER === */
        .logo-wrap {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 32px;
            animation: fadeUp 0.6s 0.1s cubic-bezier(0.22,1,0.36,1) both;
        }

        .logo-icon {
            width: 40px; height: 40px;
            background: linear-gradient(135deg, var(--accent), var(--accent2));
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px;
            box-shadow: 0 0 20px var(--glow);
            flex-shrink: 0;
        }

        .logo-name {
            font-family: 'Syne', sans-serif;
            font-weight: 800;
            font-size: 20px;
            letter-spacing: -0.5px;
            background: linear-gradient(135deg, #fff 30%, var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .heading {
            font-family: 'Syne', sans-serif;
            font-size: 28px;
            font-weight: 700;
            letter-spacing: -0.8px;
            line-height: 1.15;
            margin-bottom: 6px;
            animation: fadeUp 0.6s 0.15s cubic-bezier(0.22,1,0.36,1) both;
        }

        .subheading {
            font-size: 14px;
            color: var(--muted);
            margin-bottom: 36px;
            animation: fadeUp 0.6s 0.2s cubic-bezier(0.22,1,0.36,1) both;
        }

        /* === FORM === */
        .field {
            margin-bottom: 18px;
            animation: fadeUp 0.6s cubic-bezier(0.22,1,0.36,1) both;
        }
        .field:nth-child(1) { animation-delay: 0.25s; }
        .field:nth-child(2) { animation-delay: 0.30s; }

        label {
            display: block;
            font-size: 12px;
            font-weight: 500;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: var(--muted);
            margin-bottom: 8px;
            transition: color 0.2s;
        }

        .input-wrap {
            position: relative;
        }

        .input-wrap svg {
            position: absolute;
            left: 16px; top: 50%;
            transform: translateY(-50%);
            width: 16px; height: 16px;
            color: var(--muted);
            transition: color 0.2s;
            pointer-events: none;
        }

        input[type="email"],
        input[type="password"],
        input[type="text"] {
            width: 100%;
            padding: 14px 16px 14px 44px;
            background: var(--input-bg);
            border: 1px solid var(--border);
            border-radius: 12px;
            color: var(--text);
            font-family: 'DM Sans', sans-serif;
            font-size: 15px;
            outline: none;
            transition: border-color 0.25s, box-shadow 0.25s, background 0.25s;
        }

        input::placeholder { color: var(--muted); }

        input:focus {
            border-color: var(--accent);
            background: rgba(79,142,255,0.06);
            box-shadow: 0 0 0 4px rgba(79,142,255,0.12);
        }

        .field:focus-within label { color: var(--accent); }
        .field:focus-within svg  { color: var(--accent); }

        /* Toggle password */
        .toggle-pw {
            position: absolute;
            right: 14px; top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: var(--muted);
            display: flex;
            align-items: center;
            padding: 4px;
            transition: color 0.2s;
        }
        .toggle-pw:hover { color: var(--text); }

        /* === OPCIONES === */
        .row-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 28px;
            animation: fadeUp 0.6s 0.35s cubic-bezier(0.22,1,0.36,1) both;
        }

        .checkbox-wrap {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            user-select: none;
        }

        .checkbox-wrap input[type="checkbox"] { display: none; }

        .custom-check {
            width: 18px; height: 18px;
            border: 1.5px solid var(--border);
            border-radius: 5px;
            background: var(--input-bg);
            display: flex; align-items: center; justify-content: center;
            transition: all 0.2s;
            flex-shrink: 0;
        }

        .checkbox-wrap input:checked + .custom-check {
            background: var(--accent);
            border-color: var(--accent);
        }

        .custom-check svg {
            width: 10px; height: 10px;
            color: white;
            opacity: 0;
            transform: scale(0.5);
            transition: all 0.2s cubic-bezier(0.34,1.56,0.64,1);
        }

        .checkbox-wrap input:checked + .custom-check svg {
            opacity: 1;
            transform: scale(1);
        }

        .checkbox-label {
            font-size: 13px;
            color: var(--muted);
        }

        .forgot {
            font-size: 13px;
            color: var(--accent);
            text-decoration: none;
            position: relative;
        }
        .forgot::after {
            content: '';
            position: absolute;
            bottom: -1px; left: 0; right: 0;
            height: 1px;
            background: var(--accent);
            transform: scaleX(0);
            transition: transform 0.2s;
        }
        .forgot:hover::after { transform: scaleX(1); }

        /* === BOTÓN === */
        .btn-login {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, var(--accent), var(--accent2));
            border: none;
            border-radius: 12px;
            color: white;
            font-family: 'Syne', sans-serif;
            font-size: 15px;
            font-weight: 600;
            letter-spacing: 0.02em;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            transition: transform 0.15s, box-shadow 0.15s;
            animation: fadeUp 0.6s 0.4s cubic-bezier(0.22,1,0.36,1) both;
            box-shadow: 0 4px 24px rgba(79,142,255,0.3);
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 32px rgba(79,142,255,0.45);
        }

        .btn-login:active { transform: translateY(0); }

        /* Ripple */
        .btn-login .ripple {
            position: absolute;
            border-radius: 50%;
            background: rgba(255,255,255,0.3);
            transform: scale(0);
            animation: ripple 0.6s linear;
            pointer-events: none;
        }

        @keyframes ripple {
            to { transform: scale(4); opacity: 0; }
        }

        /* Loading state */
        .btn-login .btn-text { transition: opacity 0.2s; }
        .btn-login .btn-spinner {
            position: absolute;
            inset: 0;
            display: flex; align-items: center; justify-content: center;
            opacity: 0;
            transition: opacity 0.2s;
        }
        .btn-login.loading .btn-text { opacity: 0; }
        .btn-login.loading .btn-spinner { opacity: 1; }

        .spinner-ring {
            width: 20px; height: 20px;
            border: 2px solid rgba(255,255,255,0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.7s linear infinite;
        }

        @keyframes spin { to { transform: rotate(360deg); } }

        /* === MENSAJE ERROR === */
        .msg {
            border-radius: 10px;
            padding: 12px 14px;
            font-size: 13px;
            display: none;
            align-items: center;
            gap: 10px;
            margin-bottom: 18px;
            animation: shake 0.4s cubic-bezier(0.36,0.07,0.19,0.97);
        }
        .msg.error   { background: rgba(255,90,125,0.1); border: 1px solid rgba(255,90,125,0.25); color: var(--error); display: flex; }
        .msg.success { background: rgba(54,232,160,0.1); border: 1px solid rgba(54,232,160,0.25); color: var(--success); display: flex; }

        @keyframes shake {
            10%, 90% { transform: translateX(-2px); }
            20%, 80% { transform: translateX(4px); }
            30%, 50%, 70% { transform: translateX(-4px); }
            40%, 60% { transform: translateX(4px); }
        }

        /* === DIVIDER === */
        .divider {
            display: flex; align-items: center; gap: 12px;
            margin: 28px 0;
            animation: fadeUp 0.6s 0.45s cubic-bezier(0.22,1,0.36,1) both;
        }
        .divider span {
            flex: 1;
            height: 1px;
            background: var(--border);
        }
        .divider p {
            font-size: 12px;
            color: var(--muted);
            white-space: nowrap;
        }

        /* === SOCIAL BTNS === */
        .socials {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            animation: fadeUp 0.6s 0.5s cubic-bezier(0.22,1,0.36,1) both;
        }

        .btn-social {
            display: flex; align-items: center; justify-content: center;
            gap: 8px;
            padding: 12px;
            background: var(--input-bg);
            border: 1px solid var(--border);
            border-radius: 10px;
            color: var(--text);
            font-family: 'DM Sans', sans-serif;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.2s, border-color 0.2s, transform 0.15s;
        }

        .btn-social:hover {
            background: rgba(255,255,255,0.06);
            border-color: rgba(255,255,255,0.12);
            transform: translateY(-1px);
        }

        .btn-social svg { width: 16px; height: 16px; }

        /* === FOOTER === */
        .card-footer {
            text-align: center;
            margin-top: 28px;
            font-size: 13px;
            color: var(--muted);
            animation: fadeUp 0.6s 0.55s cubic-bezier(0.22,1,0.36,1) both;
        }

        .card-footer a {
            color: var(--accent);
            text-decoration: none;
            font-weight: 500;
        }

        .card-footer a:hover { text-decoration: underline; }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* === INPUT ERROR STATE === */
        input.input-error {
            border-color: var(--error) !important;
            box-shadow: 0 0 0 4px rgba(255,90,125,0.1) !important;
        }
    </style>










</head>
<body>

<canvas id="bg-canvas"></canvas>

<div class="page">
    <div class="card">

        <!-- Logo -->
        <div class="logo-wrap">
            <div class="logo-icon">⚡</div>
            <span class="logo-name">Syncrostyle</span>
        </div>

        <!-- Títulos -->
        <h1 class="heading">Bienvenido de vuelta</h1>
        <p class="subheading">Ingresa tus credenciales para continuar</p>

        <!-- Mensaje de error/éxito -->
        <div class="msg" id="msg">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
            </svg>
            <span id="msg-text"></span>
        </div>

        <!-- Formulario -->
        <form id="loginForm" novalidate>
            @csrf {{-- Token CSRF de Laravel --}}

            <!-- Email -->
            <div class="field">
                <label for="email">Correo electrónico</label>
                <div class="input-wrap">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    <input type="email" id="email" name="email" placeholder="tu@correo.com" autocomplete="email"/>
                </div>
            </div>

            <!-- Contraseña -->
            <div class="field">
                <label for="password">Contraseña</label>
                <div class="input-wrap">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    <input type="password" id="password" name="password" placeholder="••••••••" autocomplete="current-password"/>
                    <button type="button" class="toggle-pw" id="togglePw" aria-label="Ver contraseña">
                        <svg id="eyeIcon" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="18" height="18">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Recordar / Olvidé -->
            <div class="row-options">
                <label class="checkbox-wrap">
                    <input type="checkbox" name="remember" id="remember">
                    <div class="custom-check">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <span class="checkbox-label">Recordarme</span>
                </label>
                <a href="#" class="forgot">¿Olvidaste tu contraseña?</a>
            </div>

            <!-- Botón principal -->
            <button type="submit" class="btn-login" id="btnLogin">
                <span class="btn-text">Iniciar sesión</span>
                <span class="btn-spinner"><div class="spinner-ring"></div></span>
            </button>
        </form>

        <!-- Divider -->
        <div class="divider">
            <span></span><p>o continúa con</p><span></span>
        </div>

        <!-- Social -->
        <div class="socials">
            <button class="btn-social" type="button">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                    <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                </svg>
                Google
            </button>
            <button class="btn-social" type="button">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2C6.477 2 2 6.477 2 12c0 4.418 2.865 8.166 6.839 9.489.5.092.682-.217.682-.482 0-.237-.009-.866-.013-1.7-2.782.604-3.369-1.34-3.369-1.34-.454-1.156-1.11-1.462-1.11-1.462-.908-.62.069-.608.069-.608 1.003.07 1.531 1.03 1.531 1.03.892 1.529 2.341 1.087 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.11-4.555-4.943 0-1.091.39-1.984 1.029-2.683-.103-.253-.446-1.27.098-2.647 0 0 .84-.269 2.75 1.025A9.578 9.578 0 0112 6.836c.85.004 1.705.114 2.504.336 1.909-1.294 2.747-1.025 2.747-1.025.546 1.377.203 2.394.1 2.647.64.699 1.028 1.592 1.028 2.683 0 3.842-2.339 4.687-4.566 4.935.359.309.678.919.678 1.852 0 1.336-.012 2.415-.012 2.743 0 .267.18.578.688.48C19.138 20.161 22 16.416 22 12c0-5.523-4.477-10-10-10z"/>
                </svg>
                GitHub
            </button>
        </div>

        <!-- Footer -->
        <p class="card-footer">
            ¿No tienes cuenta? <a href="registro">Regístrate gratis</a>
        </p>

    </div>
</div>










<script>
    /* =============================================
       CANVAS — PARTÍCULAS ANIMADAS DE FONDO
    ============================================= */
    (function() {
        const canvas = document.getElementById('bg-canvas');
        const ctx    = canvas.getContext('2d');
        let W, H, particles = [], mouse = { x: -999, y: -999 };

        const COLORS = ['79,142,255', '162,89,255', '54,232,160'];

        function resize() {
            W = canvas.width  = window.innerWidth;
            H = canvas.height = window.innerHeight;
        }

        function Particle() {
            this.reset();
        }

        Particle.prototype.reset = function() {
            this.x  = Math.random() * W;
            this.y  = Math.random() * H;
            this.r  = Math.random() * 1.5 + 0.3;
            this.vx = (Math.random() - 0.5) * 0.3;
            this.vy = (Math.random() - 0.5) * 0.3;
            this.alpha = Math.random() * 0.5 + 0.1;
            this.color = COLORS[Math.floor(Math.random() * COLORS.length)];
            this.life  = 0;
            this.maxLife = 200 + Math.random() * 300;
        };

        function init() {
            particles = Array.from({ length: 90 }, () => new Particle());
        }

        function draw() {
            ctx.clearRect(0, 0, W, H);

            // Fondo con gradiente radial sutil
            const grad = ctx.createRadialGradient(W/2, H/2, 0, W/2, H/2, Math.max(W, H) * 0.7);
            grad.addColorStop(0, 'rgba(13,17,32,0)');
            grad.addColorStop(1, 'rgba(8,11,20,0.6)');
            ctx.fillStyle = grad;
            ctx.fillRect(0, 0, W, H);

            particles.forEach(p => {
                p.life++;
                if (p.life > p.maxLife) p.reset();

                // Atracción suave hacia el mouse
                const dx = mouse.x - p.x;
                const dy = mouse.y - p.y;
                const dist = Math.sqrt(dx*dx + dy*dy);
                if (dist < 200) {
                    p.vx += dx * 0.00004;
                    p.vy += dy * 0.00004;
                }

                p.x += p.vx;
                p.y += p.vy;

                // Bounce
                if (p.x < 0 || p.x > W) p.vx *= -1;
                if (p.y < 0 || p.y > H) p.vy *= -1;

                // Fade in/out
                const progress = p.life / p.maxLife;
                const fade = progress < 0.1 ? progress / 0.1
                           : progress > 0.9 ? (1 - progress) / 0.1 : 1;

                ctx.beginPath();
                ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
                ctx.fillStyle = `rgba(${p.color},${p.alpha * fade})`;
                ctx.fill();
            });

            // Líneas entre partículas cercanas
            for (let i = 0; i < particles.length; i++) {
                for (let j = i + 1; j < particles.length; j++) {
                    const a = particles[i], b = particles[j];
                    const dx = a.x - b.x, dy = a.y - b.y;
                    const d  = Math.sqrt(dx*dx + dy*dy);
                    if (d < 100) {
                        ctx.beginPath();
                        ctx.moveTo(a.x, a.y);
                        ctx.lineTo(b.x, b.y);
                        ctx.strokeStyle = `rgba(79,142,255,${0.06 * (1 - d/100)})`;
                        ctx.lineWidth = 0.5;
                        ctx.stroke();
                    }
                }
            }

            requestAnimationFrame(draw);
        }

        window.addEventListener('mousemove', e => { mouse.x = e.clientX; mouse.y = e.clientY; });
        window.addEventListener('resize', () => { resize(); init(); });

        resize();
        init();
        draw();
    })();

    /* =============================================
       TOGGLE PASSWORD
    ============================================= */
    const togglePw  = document.getElementById('togglePw');
    const pwInput   = document.getElementById('password');
    const eyeIcon   = document.getElementById('eyeIcon');

    const eyeOpen  = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>`;
    const eyeClose = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>`;

    togglePw.addEventListener('click', () => {
        const isPass = pwInput.type === 'password';
        pwInput.type = isPass ? 'text' : 'password';
        eyeIcon.innerHTML = isPass ? eyeClose : eyeOpen;
    });

    /* =============================================
       RIPPLE EFFECT EN BOTÓN
    ============================================= */
    document.getElementById('btnLogin').addEventListener('click', function(e) {
        const btn = this;
        const rect = btn.getBoundingClientRect();
        const ripple = document.createElement('span');
        ripple.classList.add('ripple');
        const size = Math.max(rect.width, rect.height);
        ripple.style.cssText = `width:${size}px;height:${size}px;left:${e.clientX-rect.left-size/2}px;top:${e.clientY-rect.top-size/2}px`;
        btn.appendChild(ripple);
        setTimeout(() => ripple.remove(), 700);
    });

    /* =============================================
       VALIDACIÓN Y SUBMIT DEL FORMULARIO
    ============================================= */
    const form    = document.getElementById('loginForm');
    const btn     = document.getElementById('btnLogin');
    const msg     = document.getElementById('msg');
    const msgText = document.getElementById('msg-text');

    function showMsg(text, type = 'error') {
        msg.className = 'msg ' + type;
        msgText.textContent = text;
    }

    function hideMsg() {
        msg.className = 'msg';
    }

    function setError(input, show) {
        input.classList.toggle('input-error', show);
    }

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        hideMsg();

        const email    = document.getElementById('email');
        const password = document.getElementById('password');
        let valid = true;

        // Validar email
        const emailOk = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim());
        setError(email, !emailOk);
        if (!emailOk) valid = false;

        // Validar password
        const pwOk = password.value.length >= 6;
        setError(password, !pwOk);
        if (!pwOk) valid = false;

        if (!valid) {
            showMsg(!emailOk ? 'Ingresa un correo válido.' : 'La contraseña debe tener al menos 6 caracteres.');
            return;
        }

        // Animación de carga
        btn.classList.add('loading');
        btn.disabled = true;

        try {
            // ====================================================
            // AQUÍ VA TU LÓGICA DE LOGIN CON LARAVEL
            // Por ejemplo, hacer fetch al endpoint de auth:
            //
            // const res  = await fetch('/login', {
            //     method: 'POST',
            //     headers: {
            //         'Content-Type': 'application/json',
            //         'X-CSRF-TOKEN': document.querySelector('[name=_token]').value
            //     },
            //     body: JSON.stringify({
            //         email: email.value.trim(),
            //         password: password.value
            //     })
            // });
            //
            // if (res.ok) {
            //     showMsg('¡Sesión iniciada!', 'success');
            //     setTimeout(() => window.location.href = '/dashboard', 1000);
            // } else {
            //     showMsg('Credenciales incorrectas. Intenta de nuevo.');
            // }
            // ====================================================

            // SIMULACIÓN (borra esto cuando conectes el backend):
            await new Promise(r => setTimeout(r, 1800));
            showMsg('¡Sesión iniciada correctamente!', 'success');
            setTimeout(() => alert('Aquí redirigiría al dashboard con: window.location.href = "/dashboard"'), 1000);

        } catch (err) {
            showMsg('Error de conexión. Intenta más tarde.');
        } finally {
            btn.classList.remove('loading');
            btn.disabled = false;
        }
    });

    // Limpiar error al tipear
    ['email', 'password'].forEach(id => {
        document.getElementById(id).addEventListener('input', function() {
            this.classList.remove('input-error');
            hideMsg();
        });
    });
</script>

</body>
</html>