<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#050507">
    <title>Smart Access | Login</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Sora:wght@600;700;800&display=swap');

        :root {
            --bg-0: #050507;
            --bg-1: #0b0c11;
            --bg-2: #101219;
            --card: rgba(18, 20, 29, 0.78);
            --border: rgba(255, 255, 255, 0.16);
            --text: #eef2ff;
            --muted: #a3abc2;
            --neon-a: #73f16d;
            --neon-b: #ffd84f;
            --danger: #ff6b72;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            min-height: 100%;
        }

        body {
            font-family: "Manrope", "Segoe UI", sans-serif;
            color: var(--text);
            background:
                radial-gradient(circle at 8% 20%, rgba(115, 241, 109, 0.12), transparent 34%),
                radial-gradient(circle at 92% 90%, rgba(255, 216, 79, 0.1), transparent 30%),
                linear-gradient(135deg, var(--bg-0), var(--bg-1) 42%, var(--bg-2));
        }

        .screen {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 28px;
        }

        .layout {
            width: min(1200px, 100%);
            display: grid;
            grid-template-columns: 1fr 1fr;
            border: 1px solid var(--border);
            border-radius: 28px;
            overflow: hidden;
            background: rgba(9, 10, 14, 0.58);
            backdrop-filter: blur(12px);
            box-shadow:
                0 40px 100px rgba(0, 0, 0, 0.6),
                0 0 0 1px rgba(255, 255, 255, 0.03);
        }

        .left {
            padding: 54px 50px;
            background: linear-gradient(160deg, rgba(16, 18, 24, 0.9), rgba(8, 9, 13, 0.82));
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 34px;
        }

        .brand img {
            /*width: 38px;*/
            height: 38px;
            object-fit: contain;
            filter: drop-shadow(0 0 14px rgba(115, 241, 109, 0.25));
        }

        .brand-name {
            font: 700 15px/1 "Sora", sans-serif;
            letter-spacing: 0.08em;
            color: #f6f9ff;
            text-transform: uppercase;
        }

        .title {
            margin: 0;
            font-family: "Sora", sans-serif;
            font-size: clamp(28px, 3vw, 36px);
            line-height: 1.15;
            letter-spacing: -0.02em;
        }

        .subtitle {
            margin: 12px 0 0;
            max-width: 440px;
            color: var(--muted);
            font-size: 15px;
            line-height: 1.65;
        }

        .error-box {
            margin-top: 22px;
            border-radius: 12px;
            border: 1px solid rgba(255, 107, 114, 0.45);
            background: rgba(120, 32, 39, 0.24);
            color: #ffbdc2;
            font-size: 14px;
            padding: 11px 12px;
        }

        .form {
            margin-top: 28px;
        }

        .field {
            margin-bottom: 18px;
        }

        .field label {
            display: block;
            margin-bottom: 7px;
            color: #cad2ea;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 0.02em;
        }

        .input-wrap {
            position: relative;
        }

        .input {
            width: 100%;
            height: 52px;
            border-radius: 13px;
            border: 1px solid var(--border);
            background: rgba(11, 13, 18, 0.95);
            color: #f8fbff;
            padding: 0 14px;
            font: 500 15px "Manrope", sans-serif;
            transition: border-color 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
        }

        .input::placeholder {
            color: #77809e;
        }

        .input:focus {
            outline: none;
            border-color: rgba(198, 235, 96, 0.95);
            background: rgba(13, 16, 22, 1);
            box-shadow:
                0 0 0 3px rgba(165, 229, 94, 0.14),
                0 0 22px rgba(165, 229, 94, 0.22);
        }

        .password-input {
            padding-right: 86px;
        }

        .toggle-pass {
            position: absolute;
            top: 50%;
            right: 8px;
            transform: translateY(-50%);
            border: 1px solid rgba(255, 255, 255, 0.16);
            background: rgba(255, 255, 255, 0.03);
            color: #dce4fb;
            border-radius: 9px;
            height: 36px;
            min-width: 66px;
            padding: 0 10px;
            font: 700 12px "Manrope", sans-serif;
            letter-spacing: 0.04em;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .toggle-pass:hover {
            border-color: rgba(255, 255, 255, 0.36);
            background: rgba(255, 255, 255, 0.08);
        }

        .submit {
            width: 100%;
            height: 54px;
            border: 0;
            border-radius: 14px;
            margin-top: 8px;
            cursor: pointer;
            color: #11130f;
            font: 800 14px "Sora", sans-serif;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            background: linear-gradient(90deg, var(--neon-a), var(--neon-b));
            box-shadow:
                0 10px 25px rgba(140, 227, 84, 0.22),
                0 0 30px rgba(255, 216, 79, 0.2);
            transition: transform 0.2s ease, box-shadow 0.2s ease, filter 0.2s ease;
        }

        .submit:hover {
            transform: translateY(-1px);
            filter: brightness(1.03);
            box-shadow:
                0 14px 28px rgba(133, 221, 90, 0.3),
                0 0 38px rgba(255, 216, 79, 0.26);
        }

        .submit:active {
            transform: translateY(0);
        }

        .right {
            position: relative;
            padding: 34px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: radial-gradient(circle at 80% 20%, rgba(115, 241, 109, 0.08), transparent 35%);
        }

        .visual-card {
            position: relative;
            width: 100%;
            height: 100%;
            min-height: 540px;
            border-radius: 24px;
            overflow: hidden;
            border: 1px solid rgba(179, 255, 115, 0.26);
            box-shadow:
                inset 0 0 0 1px rgba(255, 255, 255, 0.06),
                0 0 34px rgba(168, 237, 98, 0.18);
        }

        .visual-card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            filter: saturate(1.15) contrast(1.05) brightness(0.62);
            transform: scale(1.03);
        }

        .visual-overlay {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding: 30px;
            background:
                linear-gradient(0deg, rgba(5, 8, 11, 0.9) 5%, rgba(5, 8, 11, 0.1) 62%),
                radial-gradient(circle at 22% 28%, rgba(115, 241, 109, 0.22), transparent 44%);
        }

        .visual-title {
            margin: 0;
            font-family: "Sora", sans-serif;
            font-size: clamp(24px, 2vw, 32px);
            line-height: 1.2;
            letter-spacing: -0.01em;
        }

        .visual-subtitle {
            margin: 10px 0 0;
            color: #d3ddf3;
            font-size: 15px;
            line-height: 1.6;
            max-width: 370px;
        }

        @media (max-width: 980px) {
            .screen {
                padding: 14px;
            }

            .layout {
                grid-template-columns: 1fr;
            }

            .right {
                display: none;
            }

            .left {
                padding: 34px 24px 30px;
            }

            .title {
                font-size: 30px;
            }
        }
    </style>
</head>
<body>
<div class="screen">
    <main class="layout">
        <section class="left">
            <div class="brand">
                <img src="{{ asset('dpm-logo-white.png') }}" alt="Logo">
            </div>

            <h1 class="title">Welcome Back</h1>
            <p class="subtitle">Enter your email and password to access your account</p>

            @if ($errors->any())
                <div class="error-box">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('admin.login.submit') }}" class="form">
                @csrf

                <div class="field">
                    <label for="email">Email</label>
                    <div class="input-wrap">
                        <input
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old('email') }}"
                            required
                            autocomplete="email"
                            class="input"
                            placeholder="you@example.com"
                        >
                    </div>
                </div>

                <div class="field">
                    <label for="password">Password</label>
                    <div class="input-wrap">
                        <input
                            id="password"
                            name="password"
                            type="password"
                            required
                            autocomplete="current-password"
                            class="input password-input"
                            placeholder="••••••••"
                        >
                        <button type="button" class="toggle-pass" id="togglePassword" aria-label="Toggle password visibility">Show</button>
                    </div>
                </div>

                <button type="submit" class="submit">Sign In</button>
            </form>
        </section>

        <section class="right">
            <div class="visual-card">
                <img src="{{ asset('login.jpg') }}" alt="Futuristic AI">
                <div class="visual-overlay">
                    <h2 class="visual-title">Welcome to DPM Smart AI Innovation</h2>
                    <p class="visual-subtitle">Sign in to continue your personalized experience</p>
                </div>
            </div>
        </section>
    </main>
</div>

<script>
    (function () {
        var passwordInput = document.getElementById('password');
        var toggleButton = document.getElementById('togglePassword');

        if (!passwordInput || !toggleButton) {
            return;
        }

        toggleButton.addEventListener('click', function () {
            var isHidden = passwordInput.type === 'password';
            passwordInput.type = isHidden ? 'text' : 'password';
            toggleButton.textContent = isHidden ? 'Hide' : 'Show';
        });
    })();
</script>
</body>
</html>
