<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#070b15">
    <title>{{ $title ?? 'Business Access' }} | DPM AI</title>
    <link rel="icon" href="{{ asset('dpm-icon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #070b15;
            --bg-soft: #0d1324;
            --card: #121a2f;
            --line: rgba(135, 163, 255, 0.25);
            --line-strong: rgba(126, 249, 183, 0.45);
            --text: #edf2ff;
            --muted: #b0bddf;
            --primary: #7ef9b7;
            --secondary: #ffe169;
            --danger: #ff7a9d;
            --success: #94f7c7;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            min-height: 100%;
            font-family: "Outfit", sans-serif;
            color: var(--text);
            background:
                radial-gradient(circle at 16% 12%, rgba(124, 184, 255, 0.18), transparent 32%),
                radial-gradient(circle at 88% 22%, rgba(126, 249, 183, 0.12), transparent 30%),
                linear-gradient(180deg, #050911 0%, #070b15 52%, #0b1120 100%);
        }

        .grid-overlay {
            position: fixed;
            inset: 0;
            pointer-events: none;
            opacity: 0.2;
            background-image:
                linear-gradient(rgba(136, 168, 255, 0.10) 1px, transparent 1px),
                linear-gradient(90deg, rgba(136, 168, 255, 0.08) 1px, transparent 1px);
            background-size: 40px 40px;
            mask-image: radial-gradient(circle at center, black 45%, transparent 100%);
            z-index: 0;
        }

        .page {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 1rem;
            position: relative;
            z-index: 1;
        }

        .shell {
            width: min(1120px, 100%);
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            gap: 1rem;
        }

        .brand img {
            height: 34px;
            object-fit: contain;
            filter: drop-shadow(0 0 12px rgba(126, 249, 183, 0.35));
        }

        .topbar-links {
            display: flex;
            gap: 0.65rem;
        }

        .link-btn {
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 0.56rem 0.95rem;
            color: var(--text);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
            background: rgba(12, 18, 35, 0.72);
            transition: 0.2s ease;
        }

        .link-btn:hover {
            border-color: rgba(124, 184, 255, 0.7);
            box-shadow: 0 0 20px rgba(124, 184, 255, 0.22);
        }

        .layout {
            border: 1px solid var(--line);
            border-radius: 24px;
            overflow: hidden;
            background: linear-gradient(145deg, rgba(15, 22, 40, 0.9), rgba(9, 14, 27, 0.88));
            display: grid;
            grid-template-columns: 1.04fr 0.96fr;
            box-shadow:
                0 24px 50px rgba(0, 0, 0, 0.45),
                inset 0 0 30px rgba(124, 184, 255, 0.06);
        }

        .left {
            padding: 2rem 1.8rem 1.8rem;
        }

        .pill {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            margin-bottom: 0.9rem;
            padding: 0.38rem 0.72rem;
            border: 1px solid var(--line);
            border-radius: 999px;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--muted);
            background: rgba(8, 12, 23, 0.75);
        }

        .title {
            margin: 0;
            font-family: "Space Grotesk", sans-serif;
            font-size: clamp(1.7rem, 4.5vw, 2.4rem);
            line-height: 1.15;
            letter-spacing: -0.02em;
        }

        .subtitle {
            margin: 0.7rem 0 1.2rem;
            color: var(--muted);
            line-height: 1.7;
            font-size: 0.97rem;
        }

        .flash {
            border-radius: 12px;
            border: 1px solid var(--line);
            padding: 0.75rem 0.85rem;
            margin-bottom: 0.95rem;
            background: rgba(10, 15, 28, 0.82);
            line-height: 1.55;
            font-size: 0.9rem;
        }

        .flash.error {
            border-color: rgba(255, 122, 157, 0.45);
            color: #ffd5e1;
            background: rgba(102, 30, 54, 0.26);
        }

        .flash.success {
            border-color: rgba(148, 247, 199, 0.45);
            color: #ddfff0;
            background: rgba(25, 82, 58, 0.22);
        }

        .form {
            display: grid;
            gap: 0.85rem;
        }

        .field {
            display: grid;
            gap: 0.4rem;
        }

        label {
            color: #cfdcff;
            font-size: 0.83rem;
            font-weight: 600;
            letter-spacing: 0.02em;
        }

        .input,
        .select {
            width: 100%;
            min-height: 46px;
            border-radius: 12px;
            border: 1px solid var(--line);
            padding: 0 0.8rem;
            background: rgba(8, 12, 23, 0.82);
            color: var(--text);
            font: 500 0.94rem "Outfit", sans-serif;
            transition: 0.2s ease;
        }

        .input::placeholder {
            color: #7d8baa;
        }

        .input:focus,
        .select:focus {
            outline: none;
            border-color: var(--line-strong);
            box-shadow:
                0 0 0 3px rgba(126, 249, 183, 0.12),
                0 0 18px rgba(126, 249, 183, 0.2);
        }

        .field-row {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.75rem;
        }

        .password-wrap {
            position: relative;
        }

        .password-wrap .input {
            padding-right: 4.5rem;
        }

        .toggle-pass {
            position: absolute;
            top: 50%;
            right: 0.45rem;
            transform: translateY(-50%);
            border: 1px solid var(--line);
            background: rgba(17, 25, 45, 0.95);
            color: #dce8ff;
            border-radius: 9px;
            min-height: 34px;
            padding: 0 0.62rem;
            font: 700 0.74rem "Outfit", sans-serif;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            cursor: pointer;
        }

        .submit {
            border: 0;
            border-radius: 13px;
            min-height: 49px;
            margin-top: 0.15rem;
            font: 700 0.86rem "Space Grotesk", sans-serif;
            letter-spacing: 0.09em;
            text-transform: uppercase;
            color: #0a0f1b;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            box-shadow:
                0 12px 28px rgba(126, 249, 183, 0.28),
                0 4px 14px rgba(255, 225, 105, 0.25);
            cursor: pointer;
            transition: 0.2s ease;
        }

        .submit:hover {
            transform: translateY(-1px);
            filter: brightness(1.02);
        }

        .helper {
            margin-top: 0.7rem;
            color: var(--muted);
            font-size: 0.88rem;
            line-height: 1.6;
        }

        .helper a {
            color: #d8f7ea;
            text-underline-offset: 3px;
        }

        .right {
            padding: 1.3rem;
            border-left: 1px solid var(--line);
            background: linear-gradient(160deg, rgba(15, 23, 41, 0.84), rgba(10, 15, 28, 0.9));
            display: flex;
            align-items: stretch;
        }

        .showcase {
            border-radius: 18px;
            border: 1px solid var(--line);
            width: 100%;
            overflow: hidden;
            position: relative;
            box-shadow:
                0 18px 36px rgba(0, 0, 0, 0.42),
                inset 0 0 20px rgba(124, 184, 255, 0.08);
        }

        .showcase img {
            width: 100%;
            height: 100%;
            min-height: 420px;
            object-fit: cover;
            filter: saturate(1.05) contrast(1.05) brightness(0.68);
        }

        .showcase-overlay {
            position: absolute;
            inset: 0;
            padding: 1.2rem;
            background:
                linear-gradient(0deg, rgba(6, 10, 19, 0.94) 12%, rgba(6, 10, 19, 0.12) 62%),
                radial-gradient(circle at 22% 22%, rgba(126, 249, 183, 0.22), transparent 42%);
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
        }

        .showcase-title {
            margin: 0;
            font-family: "Space Grotesk", sans-serif;
            font-size: clamp(1.3rem, 3.5vw, 1.8rem);
            line-height: 1.3;
        }

        .showcase-text {
            margin: 0.4rem 0 0;
            color: #ccdbff;
            line-height: 1.65;
            font-size: 0.92rem;
            max-width: 28ch;
        }

        .muted-btn {
            border: 1px solid var(--line);
            border-radius: 12px;
            min-height: 43px;
            padding: 0 0.95rem;
            color: var(--text);
            background: rgba(11, 16, 30, 0.76);
            font: 600 0.84rem "Outfit", sans-serif;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .action-row {
            display: flex;
            gap: 0.6rem;
            flex-wrap: wrap;
        }

        @media (max-width: 960px) {
            .layout {
                grid-template-columns: 1fr;
            }

            .right {
                display: none;
            }

            .left {
                padding: 1.5rem 1rem 1.2rem;
            }
        }

        @media (max-width: 620px) {
            .field-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
<div class="grid-overlay"></div>
<div class="page">
    <div class="shell">
        <div class="topbar">
            <a class="brand" href="{{ route('home') }}">
                <img src="{{ asset('dpm-logo-white.png') }}" alt="DPM AI">
            </a>
            <div class="topbar-links">
                <a href="{{ route('home') }}" class="link-btn">Home</a>
                @if (Route::has('login'))
                    <a href="{{ route('login') }}" class="link-btn">Login</a>
                @endif
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="link-btn">Register</a>
                @endif
            </div>
        </div>

        <main class="layout">
            <section class="left">
                <span class="pill">{{ $pill ?? 'Business Access' }}</span>
                <h1 class="title">{{ $heading ?? 'Welcome' }}</h1>
                @if (!empty($subtitle))
                    <p class="subtitle">{{ $subtitle }}</p>
                @endif

                @if (session('status') && !in_array(session('status'), ['verification-link-sent', 'email-verified'], true))
                    <div class="flash success">{{ session('status') }}</div>
                @endif

                @if ($errors->any())
                    <div class="flash error">{{ $errors->first() }}</div>
                @endif

                @yield('content')
            </section>

            <aside class="right">
                <div class="showcase">
                    <img src="{{ asset('login.jpg') }}" alt="AI showcase">
                    <div class="showcase-overlay">
                        <h2 class="showcase-title">{{ $sideTitle ?? 'Scale Support With AI' }}</h2>
                        <p class="showcase-text">{{ $sideText ?? 'Automate replies, capture leads, and keep your WhatsApp conversations active 24/7.' }}</p>
                    </div>
                </div>
            </aside>
        </main>
    </div>
</div>
<script>
    document.querySelectorAll('[data-password-toggle]').forEach(function (button) {
        button.addEventListener('click', function () {
            var input = document.getElementById(button.getAttribute('data-password-toggle'));
            if (!input) {
                return;
            }

            var show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            button.textContent = show ? 'Hide' : 'Show';
        });
    });
</script>
</body>
</html>
