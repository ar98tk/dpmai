<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>DPM AI</title>
    <link rel="icon" href="{{ asset('dpm-icon.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;700;800&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #070b15;
            --bg-soft: #0d1324;
            --card: #121a2f;
            --line: rgba(135, 163, 255, 0.25);
            --text: #edf2ff;
            --muted: #b0bddf;
            --primary: #7ef9b7;
            --secondary: #ffe169;
            --neon: #7cb8ff;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            font-family: "Outfit", sans-serif;
            color: var(--text);
            background:
                radial-gradient(circle at 15% 10%, rgba(124, 184, 255, 0.16), transparent 30%),
                radial-gradient(circle at 88% 24%, rgba(126, 249, 183, 0.10), transparent 28%),
                linear-gradient(180deg, #060a13 0%, #070b15 55%, #0b1120 100%);
            min-height: 100%;
        }

        html {
            scroll-behavior: smooth;
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
            mask-image: radial-gradient(circle at center, black 40%, transparent 100%);
            z-index: 0;
        }

        .page-container {
            width: min(1180px, calc(100% - 2rem));
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }

        .nav {
            padding: 1.25rem 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-family: "Space Grotesk", sans-serif;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            text-shadow: 0 0 12px rgba(124, 184, 255, 0.6);
        }

        .brand img {
            /*width: 34px;*/
            height: 34px;
            object-fit: contain;
            filter: drop-shadow(0 0 10px rgba(126, 249, 183, 0.45));
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }

        .btn {
            border: 1px solid transparent;
            border-radius: 12px;
            padding: 0.68rem 1.2rem;
            color: var(--text);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.95rem;
            transition: 0.2s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 24px rgba(0, 0, 0, 0.32);
        }

        .btn-outline {
            background: rgba(18, 26, 47, 0.62);
            border-color: var(--line);
        }

        .btn-outline:hover {
            border-color: rgba(124, 184, 255, 0.7);
            box-shadow: 0 0 20px rgba(124, 184, 255, 0.32);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: #0a0f1b;
            border-color: rgba(255, 255, 255, 0.4);
            box-shadow:
                0 12px 35px rgba(126, 249, 183, 0.28),
                0 4px 14px rgba(255, 225, 105, 0.35);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
        }

        .hero {
            padding: 3rem 0 4rem;
            display: grid;
            gap: 1.8rem;
            grid-template-columns: 1.2fr 1fr;
            align-items: center;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.45rem 0.7rem;
            background: rgba(12, 19, 38, 0.85);
            border: 1px solid var(--line);
            border-radius: 999px;
            color: var(--muted);
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.35);
        }

        .hero-title {
            margin: 1rem 0;
            font-family: "Space Grotesk", sans-serif;
            font-size: clamp(2.2rem, 5.2vw, 4.2rem);
            line-height: 1.05;
            letter-spacing: -0.02em;
            text-shadow:
                0 0 18px rgba(124, 184, 255, 0.42),
                0 0 34px rgba(126, 249, 183, 0.22);
        }

        .hero-title span {
            background: linear-gradient(90deg, #91a6ff, #7ef9b7, #ffe169);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .hero-desc {
            margin: 0;
            max-width: 62ch;
            color: var(--muted);
            font-size: 1.04rem;
            line-height: 1.8;
            text-shadow: 0 0 12px rgba(124, 184, 255, 0.2);
        }

        .hero-actions {
            margin-top: 1.8rem;
            display: flex;
            flex-wrap: wrap;
            gap: 0.8rem;
        }

        .orb-panel {
            background: linear-gradient(160deg, rgba(18, 26, 47, 0.88), rgba(11, 17, 33, 0.88));
            border: 1px solid var(--line);
            border-radius: 24px;
            padding: 1.4rem;
            box-shadow:
                0 20px 45px rgba(0, 0, 0, 0.4),
                inset 0 0 40px rgba(124, 184, 255, 0.07);
            position: relative;
            overflow: hidden;
        }

        .orb-panel::before,
        .orb-panel::after {
            content: "";
            position: absolute;
            border-radius: 50%;
            filter: blur(35px);
        }

        .orb-panel::before {
            width: 180px;
            height: 180px;
            right: -40px;
            top: -40px;
            background: rgba(124, 184, 255, 0.35);
        }

        .orb-panel::after {
            width: 170px;
            height: 170px;
            left: -35px;
            bottom: -35px;
            background: rgba(126, 249, 183, 0.25);
        }

        .orb-core {
            width: 230px;
            aspect-ratio: 1;
            margin: 0 auto;
            border-radius: 50%;
            border: 1px solid rgba(255, 255, 255, 0.3);
            display: grid;
            place-items: center;
            position: relative;
            background:
                radial-gradient(circle at 40% 35%, rgba(255, 255, 255, 0.32), transparent 35%),
                radial-gradient(circle at center, rgba(124, 184, 255, 0.28), rgba(7, 11, 21, 0.8) 70%);
            box-shadow:
                0 0 40px rgba(124, 184, 255, 0.35),
                0 0 80px rgba(126, 249, 183, 0.25);
        }

        .orb-core::before {
            content: "";
            width: 72%;
            aspect-ratio: 1;
            border-radius: 50%;
            border: 1px dashed rgba(255, 255, 255, 0.4);
            animation: spin 15s linear infinite;
        }

        .orb-core::after {
            content: "AI CORE";
            position: absolute;
            font-weight: 700;
            letter-spacing: 0.22em;
            font-size: 0.8rem;
            color: #d9e6ff;
            text-shadow: 0 0 8px rgba(124, 184, 255, 0.85);
        }

        .orb-stats {
            margin-top: 1.5rem;
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0.6rem;
        }

        .orb-stat {
            border: 1px solid var(--line);
            border-radius: 14px;
            background: rgba(8, 12, 23, 0.78);
            padding: 0.8rem 0.65rem;
            text-align: center;
            box-shadow: inset 0 0 16px rgba(124, 184, 255, 0.1);
        }

        .orb-stat strong {
            display: block;
            font-family: "Space Grotesk", sans-serif;
            font-size: 1rem;
            color: #e4edff;
            text-shadow: 0 0 10px rgba(126, 249, 183, 0.45);
        }

        .orb-stat span {
            font-size: 0.77rem;
            color: var(--muted);
        }

        .section {
            padding: 3.2rem 0;
        }

        .section-title {
            margin: 0 0 0.7rem;
            font-family: "Space Grotesk", sans-serif;
            font-size: clamp(1.7rem, 3.8vw, 2.7rem);
            letter-spacing: -0.02em;
            text-shadow: 0 0 14px rgba(124, 184, 255, 0.3);
        }

        .section-subtitle {
            margin: 0;
            color: var(--muted);
            font-size: 1rem;
            line-height: 1.7;
        }

        .feature-grid {
            margin-top: 1.4rem;
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 1rem;
        }

        .feature-card {
            background: linear-gradient(145deg, rgba(18, 26, 47, 0.86), rgba(9, 14, 27, 0.86));
            border: 1px solid var(--line);
            border-radius: 18px;
            padding: 1.2rem;
            box-shadow:
                0 18px 30px rgba(0, 0, 0, 0.35),
                inset 0 0 20px rgba(124, 184, 255, 0.08);
        }

        .feature-card h3 {
            margin: 0 0 0.55rem;
            font-size: 1.05rem;
            text-shadow: 0 0 12px rgba(124, 184, 255, 0.3);
        }

        .feature-card p {
            margin: 0;
            color: var(--muted);
            line-height: 1.65;
            font-size: 0.96rem;
        }

        .hero-visual {
            display: grid;
            gap: 0.9rem;
        }

        .hero-robot-image {
            width: 100%;
            height: 260px;
            object-fit: cover;
            border-radius: 16px;
            border: 1px solid var(--line);
            box-shadow: 0 16px 34px rgba(0, 0, 0, 0.45);
        }

        .chat-sample {
            border: 1px solid var(--line);
            border-radius: 16px;
            padding: 0.9rem;
            background: rgba(9, 14, 27, 0.74);
            box-shadow: inset 0 0 22px rgba(124, 184, 255, 0.08);
        }

        .chat-line {
            margin: 0;
            padding: 0.65rem 0.7rem;
            border-radius: 12px;
            font-size: 0.94rem;
            line-height: 1.55;
        }

        .chat-line + .chat-line {
            margin-top: 0.5rem;
        }

        .chat-line.user {
            background: rgba(124, 184, 255, 0.15);
            border: 1px solid rgba(124, 184, 255, 0.33);
        }

        .chat-line.ai {
            background: rgba(126, 249, 183, 0.12);
            border: 1px solid rgba(126, 249, 183, 0.30);
        }

        .problem-grid {
            margin-top: 1.4rem;
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 1rem;
        }

        .problem-card {
            background: linear-gradient(145deg, rgba(18, 26, 47, 0.86), rgba(9, 14, 27, 0.86));
            border: 1px solid rgba(255, 102, 135, 0.35);
            border-radius: 18px;
            padding: 1rem;
            box-shadow: 0 16px 28px rgba(0, 0, 0, 0.35);
        }

            .problem-card-header {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            margin-bottom: 0.4rem;
        }

        .problem-icon {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            display: grid;
            place-items: center;
            font-size: 1rem;
            background: linear-gradient(135deg, rgba(255, 124, 161, 0.28), rgba(255, 102, 135, 0.18));
            border: 1px solid rgba(255, 124, 161, 0.45);
            box-shadow: 0 10px 20px rgba(255, 102, 135, 0.2);
        }

        .problem-card h3 {
            margin: 0;
            color: #ffd9e2;
            text-shadow: 0 0 14px rgba(255, 115, 145, 0.25);
        }

        .solution-banner {
            margin-top: 1rem;
            display: grid;
            grid-template-columns: auto 1fr;
            align-items: center;
            gap: 0.8rem;
            background: linear-gradient(135deg, rgba(126, 249, 183, 0.18), rgba(124, 184, 255, 0.2));
            border: 1px solid rgba(126, 249, 183, 0.45);
            border-radius: 18px;
            padding: 1rem 1.1rem;
            color: #d9e6ff;
            line-height: 1.75;
            box-shadow:
                0 16px 30px rgba(0, 0, 0, 0.38),
                inset 0 0 22px rgba(126, 249, 183, 0.1);
        }

        .solution-icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            display: grid;
            place-items: center;
            font-size: 1.2rem;
            border: 1px solid rgba(126, 249, 183, 0.55);
            background: linear-gradient(135deg, rgba(126, 249, 183, 0.22), rgba(255, 225, 105, 0.15));
            box-shadow: 0 10px 20px rgba(126, 249, 183, 0.2);
        }

        .solution-banner p {
            margin: 0;
        }

        .solution-banner strong {
            color: #f4fbff;
            text-shadow: 0 0 12px rgba(124, 184, 255, 0.38);
        }

        .feature-grid-compact {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .feature-control-card {
            display: grid;
            gap: 0.95rem;
        }

        .service-stack {
            margin-top: 1.3rem;
            display: grid;
            gap: 1rem;
        }

        .service-hero-card {
            background: linear-gradient(145deg, rgba(18, 26, 47, 0.9), rgba(9, 14, 27, 0.9));
            border: 1px solid rgba(124, 184, 255, 0.35);
            border-radius: 20px;
            padding: 1.2rem 1.25rem;
            box-shadow:
                0 18px 34px rgba(0, 0, 0, 0.35),
                inset 0 0 18px rgba(124, 184, 255, 0.08);
        }

        .service-hero-card h3 {
            margin: 0 0 0.45rem;
            font-size: 1.25rem;
            text-shadow: 0 0 12px rgba(124, 184, 255, 0.28);
        }

        .service-hero-card p {
            margin: 0;
            color: var(--muted);
            line-height: 1.75;
        }

        .service-badges {
            margin-top: 0.9rem;
            display: flex;
            flex-wrap: wrap;
            gap: 0.55rem;
        }

        .service-badge {
            border: 1px solid rgba(126, 249, 183, 0.38);
            border-radius: 999px;
            padding: 0.35rem 0.7rem;
            font-size: 0.83rem;
            color: #e8fff3;
            background: rgba(126, 249, 183, 0.11);
            box-shadow: inset 0 0 12px rgba(126, 249, 183, 0.12);
        }

        .service-columns {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
        }

        .service-column {
            background: linear-gradient(145deg, rgba(18, 26, 47, 0.86), rgba(9, 14, 27, 0.86));
            border: 1px solid var(--line);
            border-radius: 18px;
            padding: 1.05rem;
            box-shadow: 0 16px 30px rgba(0, 0, 0, 0.35);
        }

        .service-column h4 {
            margin: 0 0 0.7rem;
            font-size: 1.05rem;
            text-shadow: 0 0 10px rgba(124, 184, 255, 0.24);
        }

        .service-bullets {
            margin: 0;
            padding-left: 1.05rem;
            color: var(--muted);
            line-height: 1.8;
        }

        .service-bullets li + li {
            margin-top: 0.45rem;
        }

        .service-bullets strong {
            color: #f1f6ff;
            font-weight: 600;
        }

        .service-examples-title {
            margin: 0.8rem 0 0.45rem;
            font-size: 0.95rem;
            color: #e4eeff;
            text-shadow: 0 0 8px rgba(124, 184, 255, 0.24);
        }

        .steps-grid {
            margin-top: 1.3rem;
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 1rem;
        }

        .step-card {
            background: linear-gradient(145deg, rgba(18, 26, 47, 0.86), rgba(9, 14, 27, 0.86));
            border: 1px solid var(--line);
            border-radius: 18px;
            padding: 1rem;
            box-shadow: 0 16px 28px rgba(0, 0, 0, 0.35);
        }

        .step-index {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: #0a0f1b;
            font-family: "Space Grotesk", sans-serif;
            font-weight: 700;
            display: grid;
            place-items: center;
            box-shadow: 0 10px 22px rgba(126, 249, 183, 0.32);
            margin-bottom: 0.65rem;
        }

        .control-grid {
            margin-top: 1.3rem;
            display: grid;
            grid-template-columns: 1.2fr 1fr;
            gap: 1rem;
            align-items: stretch;
        }

        .control-card {
            background: linear-gradient(145deg, rgba(18, 26, 47, 0.86), rgba(9, 14, 27, 0.86));
            border: 1px solid var(--line);
            border-radius: 18px;
            padding: 1.1rem;
            box-shadow: 0 16px 30px rgba(0, 0, 0, 0.35);
        }

        .point-list,
        .why-list {
            margin: 0.8rem 0 0;
            padding-left: 1.05rem;
            color: var(--muted);
            line-height: 1.85;
        }

        .example-list {
            margin-top: 0.8rem;
            display: grid;
            gap: 0.55rem;
        }

        .example-item {
            border: 1px solid rgba(124, 184, 255, 0.34);
            background: rgba(8, 12, 23, 0.7);
            color: #d9e6ff;
            border-radius: 12px;
            padding: 0.7rem;
            font-size: 0.92rem;
            box-shadow: inset 0 0 14px rgba(124, 184, 255, 0.08);
        }

        .robot-image {
            width: 100%;
            height: 100%;
            min-height: 260px;
            object-fit: cover;
            border-radius: 14px;
            border: 1px solid var(--line);
            box-shadow: 0 18px 36px rgba(0, 0, 0, 0.45);
        }

        .why-section {
            margin-top: 0.3rem;
        }

        .why-section .section-subtitle {
            max-width: 760px;
        }

        .plans-grid {
            margin-top: 1.5rem;
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 1rem;
        }

        #plans {
            scroll-margin-top: 18px;
        }

        #plans.plans-spotlight {
            animation: plansSpotlight 1.25s ease;
        }

        .plan-card {
            background: linear-gradient(150deg, rgba(16, 23, 42, 0.9), rgba(11, 17, 31, 0.95));
            border: 1px solid rgba(124, 184, 255, 0.35);
            border-radius: 20px;
            padding: 1.2rem;
            box-shadow:
                0 20px 38px rgba(0, 0, 0, 0.35),
                inset 0 0 28px rgba(124, 184, 255, 0.1);
            display: flex;
            flex-direction: column;
            min-height: 100%;
            position: relative;
        }

        .plan-card.highlight {
            border-color: rgba(126, 249, 183, 0.75);
            box-shadow:
                0 22px 45px rgba(0, 0, 0, 0.45),
                0 0 30px rgba(126, 249, 183, 0.24),
                inset 0 0 35px rgba(126, 249, 183, 0.08);
        }

        .plan-badge {
            position: absolute;
            top: -11px;
            right: 14px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: #101722;
            font-size: 0.73rem;
            font-weight: 700;
            padding: 0.2rem 0.55rem;
            border-radius: 999px;
            box-shadow: 0 10px 20px rgba(126, 249, 183, 0.32);
        }

        .plan-name {
            margin: 0;
            font-size: 1.1rem;
            font-family: "Space Grotesk", sans-serif;
            text-shadow: 0 0 12px rgba(124, 184, 255, 0.25);
        }

        .plan-price {
            margin: 0.55rem 0 0.2rem;
            font-size: 2.1rem;
            font-family: "Space Grotesk", sans-serif;
            line-height: 1.1;
            text-shadow: 0 0 16px rgba(126, 249, 183, 0.25);
        }

        .plan-price small {
            font-size: 0.9rem;
            color: var(--muted);
            font-weight: 500;
        }

        .plan-meta {
            margin: 0.2rem 0 0.9rem;
            color: var(--muted);
            font-size: 0.88rem;
            display: grid;
            gap: 0.35rem;
        }

        .plan-features {
            margin: 0;
            padding: 0;
            list-style: none;
            display: grid;
            gap: 0.45rem;
            color: #d7e3ff;
            font-size: 0.92rem;
        }

        .plan-features li {
            display: flex;
            align-items: flex-start;
            gap: 0.45rem;
            line-height: 1.55;
        }

        .plan-features li::before {
            content: "";
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            margin-top: 0.4rem;
            box-shadow: 0 0 10px rgba(126, 249, 183, 0.6);
            flex-shrink: 0;
        }

        .plan-card .btn {
            margin-top: auto;
        }

        .empty-plans {
            margin-top: 1.3rem;
            border: 1px dashed var(--line);
            border-radius: 16px;
            padding: 1rem;
            color: var(--muted);
            background: rgba(12, 18, 35, 0.6);
        }

        .footer {
            margin-top: 3.5rem;
            padding: 1.4rem 0 2.4rem;
            border-top: 1px solid rgba(135, 163, 255, 0.2);
            color: var(--muted);
            text-align: center;
            font-size: 0.9rem;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        @keyframes plansSpotlight {
            0% {
                transform: translateY(22px);
                opacity: 0.55;
            }
            100% {
                transform: translateY(0);
                opacity: 1;
            }
        }

        @media (max-width: 1024px) {
            .hero {
                grid-template-columns: 1fr;
            }

            .feature-grid,
            .problem-grid,
            .steps-grid,
            .plans-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .control-grid {
                grid-template-columns: 1fr;
            }

            .service-columns {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 720px) {
            .nav {
                flex-wrap: wrap;
                gap: 0.7rem;
            }

            .feature-grid,
            .problem-grid,
            .steps-grid,
            .plans-grid {
                grid-template-columns: 1fr;
            }

            .solution-banner {
                grid-template-columns: 1fr;
            }

            .orb-stats {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
<div class="grid-overlay"></div>

@php
    $plans = isset($plans) ? $plans : collect();
@endphp

<div class="page-container">
    <nav class="nav">
        <div class="brand">
            <img src="{{ asset('dpm-logo-white.png') }}" alt="DPM Logo">
        </div>
        <div class="nav-actions">
            <a href="{{ route('login') }}" class="btn btn-outline">Login</a>
            <a href="{{ route('register') }}" class="btn btn-primary">Start Free Trial</a>
        </div>
    </nav>

    <section class="hero">
        <div>
            <div class="hero-badge">
                <span>Realtime AI Assistant</span>
            </div>
            <h1 class="hero-title">
                Automate Your <span>WhatsApp Business</span> in 60 Seconds
            </h1>
            <p class="hero-desc">
                Reply instantly, capture leads, and grow your business with AI — without any technical setup.
            </p>
            <div class="hero-actions">
                <a href="{{ route('register') }}" class="btn btn-primary">Start Free Trial</a>
                <a href="#plans" class="btn btn-outline plan-scroll">See Plans</a>
            </div>
        </div>

        <div class="orb-panel hero-visual">

            <div class="chat-sample">
                <p class="chat-line user"><strong>Customer:</strong> Hi, I want to know your prices</p>
                <p class="chat-line ai"><strong>AI:</strong> Sure! I'd be happy to help. Which service are you interested in?</p>
            </div>
            <div class="orb-stats">
                <div class="orb-stat">
                    <strong>24/7</strong>
                    <span>AI Replies</span>
                </div>
                <div class="orb-stat">
                    <strong>Fast</strong>
                    <span>Setup</span>
                </div>
                <div class="orb-stat">
                    <strong>Smart</strong>
                    <span>Leads</span>
                </div>
            </div>
        </div>
    </section>

    <section class="section">
        <h2 class="section-title">Problem → Solution</h2>
        <p class="section-subtitle">
            Your customers expect instant replies.
            Our AI handles every conversation for you - automatically and in real time.
        </p>
        <div class="problem-grid">
            <article class="problem-card">
                <div class="problem-card-header">
                    <span class="problem-icon">&#128229;</span>
                    <h3>Missing messages</h3>
                </div>
                <p>Important chats get lost and customers move to competitors.</p>
            </article>
            <article class="problem-card">
                <div class="problem-card-header">
                    <span class="problem-icon">&#9201;</span>
                    <h3>Slow replies</h3>
                </div>
                <p>Response delays reduce trust and kill conversion opportunities.</p>
            </article>
            <article class="problem-card">
                <div class="problem-card-header">
                    <span class="problem-icon">&#128201;</span>
                    <h3>Lost customers</h3>
                </div>
                <p>Leads drop when no one answers quickly with clear information.</p>
            </article>
        </div>
        <div class="solution-banner">
            <span class="solution-icon">&#9889;</span>
            <p><strong>Keep your team focused</strong> while AI responds, qualifies prospects, and keeps every conversation alive.</p>
        </div>
    </section>

    <section class="py-24">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="bg-slate-900/55 border border-slate-700/60 rounded-3xl p-8 lg:p-14 shadow-[0_25px_80px_rgba(2,6,23,0.55)]">
                <div class="text-center max-w-4xl mx-auto">
                    <p class="inline-flex items-center rounded-full border border-cyan-400/40 bg-cyan-400/10 px-4 py-1 text-xs font-semibold tracking-[0.22em] text-cyan-200 uppercase">
                        Powerful Features
                    </p>
                    <h2 class="mt-5 text-4xl lg:text-5xl font-bold tracking-tight text-white">
                        What You Get With Our AI Service
                    </h2>
                    <p class="mt-5 text-base lg:text-lg leading-8 text-slate-300">
                        This is not just autoresponse. It is a complete WhatsApp service engine that replies fast, captures leads, keeps context, and gives you full control over how the AI behaves.
                    </p>
                </div>

                <div class="mt-14 grid grid-cols-1 lg:grid-cols-12 gap-8">
                    <div class="lg:col-span-7">
                        <h3 class="text-xl font-semibold text-white mb-5">Core Service Features</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <article class="rounded-xl border border-slate-700/70 bg-slate-800/60 p-5 transition-all duration-300 hover:scale-105 hover:shadow-lg hover:shadow-cyan-500/20">
                                <h4 class="text-white font-semibold">WhatsApp Integration</h4>
                                <p class="mt-2 text-sm leading-6 text-slate-300">Connect your WhatsApp number in seconds with no technical setup.</p>
                            </article>
                            <article class="rounded-xl border border-slate-700/70 bg-slate-800/60 p-5 transition-all duration-300 hover:scale-105 hover:shadow-lg hover:shadow-cyan-500/20">
                                <h4 class="text-white font-semibold">AI Auto Replies</h4>
                                <p class="mt-2 text-sm leading-6 text-slate-300">Respond instantly to every message, 24/7, without missing customers.</p>
                            </article>
                            <article class="rounded-xl border border-slate-700/70 bg-slate-800/60 p-5 transition-all duration-300 hover:scale-105 hover:shadow-lg hover:shadow-cyan-500/20">
                                <h4 class="text-white font-semibold">Lead Collection</h4>
                                <p class="mt-2 text-sm leading-6 text-slate-300">Turn conversations into qualified leads automatically.</p>
                            </article>
                            <article class="rounded-xl border border-slate-700/70 bg-slate-800/60 p-5 transition-all duration-300 hover:scale-105 hover:shadow-lg hover:shadow-cyan-500/20">
                                <h4 class="text-white font-semibold">Chat History & Insights</h4>
                                <p class="mt-2 text-sm leading-6 text-slate-300">Keep conversations organized and understand customer behavior.</p>
                            </article>
                            <article class="rounded-xl border border-slate-700/70 bg-slate-800/60 p-5 transition-all duration-300 hover:scale-105 hover:shadow-lg hover:shadow-cyan-500/20 md:col-span-2">
                                <h4 class="text-white font-semibold">Fast Setup</h4>
                                <p class="mt-2 text-sm leading-6 text-slate-300">Go live in less than 60 seconds.</p>
                            </article>
                        </div>
                    </div>

                    <div class="lg:col-span-5">
                        <div class="rounded-2xl bg-gradient-to-br from-cyan-400/30 via-emerald-300/25 to-yellow-200/25 p-[1px] shadow-[0_0_35px_rgba(34,211,238,0.25)]">
                            <div class="rounded-2xl bg-slate-900/90 border border-slate-700/60 p-6 lg:p-7 h-full">
                                <p class="text-xs font-semibold tracking-[0.2em] text-cyan-200 uppercase">Highlighted Control</p>
                                <h3 class="mt-3 text-2xl font-bold text-white">AI Behavior Control</h3>
                                <p class="mt-3 text-sm leading-7 text-slate-300">Define exactly how your AI responds for each number</p>

                                <ul class="mt-6 space-y-3 text-sm text-slate-200">
                                    <li class="flex items-start gap-3">
                                        <span class="mt-1 h-2 w-2 rounded-full bg-cyan-300 shadow-[0_0_12px_rgba(103,232,249,0.9)]"></span>
                                        <span>Set a custom system prompt for each number</span>
                                    </li>
                                    <li class="flex items-start gap-3">
                                        <span class="mt-1 h-2 w-2 rounded-full bg-cyan-300 shadow-[0_0_12px_rgba(103,232,249,0.9)]"></span>
                                        <span>Add rules and restrictions for responses</span>
                                    </li>
                                    <li class="flex items-start gap-3">
                                        <span class="mt-1 h-2 w-2 rounded-full bg-cyan-300 shadow-[0_0_12px_rgba(103,232,249,0.9)]"></span>
                                        <span>Define customer intents and behavior</span>
                                    </li>
                                </ul>

                                <div class="mt-6 rounded-xl border border-cyan-400/25 bg-slate-950/80 p-4 shadow-[inset_0_0_20px_rgba(34,211,238,0.08)]">
                                    <p class="text-xs uppercase tracking-widest text-cyan-200/90 font-semibold mb-3">Examples</p>
                                    <div class="space-y-2 font-mono text-xs lg:text-sm text-slate-200">
                                        <p class="rounded-md bg-slate-900/90 border border-slate-700/70 px-3 py-2">"Only answer questions about our services"</p>
                                        <p class="rounded-md bg-slate-900/90 border border-slate-700/70 px-3 py-2">"Ask for name and phone number before giving prices"</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="section">
        <h2 class="section-title">How It Works</h2>
        <div class="steps-grid">
            <article class="step-card">
                <div class="step-index">1</div>
                <h3>Connect your WhatsApp number</h3>
                <p class="section-subtitle">Link your number quickly and prepare it for live AI handling.</p>
            </article>
            <article class="step-card">
                <div class="step-index">2</div>
                <h3>Customize your AI behavior</h3>
                <p class="section-subtitle">Set tone, rules, restrictions, and business-specific instructions.</p>
            </article>
            <article class="step-card">
                <div class="step-index">3</div>
                <h3>Start receiving and converting messages automatically</h3>
                <p class="section-subtitle">Let AI answer, qualify, and move each conversation forward.</p>
            </article>
        </div>
    </section>

    <section class="section" id="plans">
        <h2 class="section-title">Our Plans</h2>

        @if ($plans->isEmpty())
            <div class="empty-plans">
                No plans available yet. Create plans from the admin dashboard and they will appear here automatically.
            </div>
        @else
            <div class="plans-grid">
                @foreach ($plans as $plan)
                    @php
                        $planFeatures = [];
                        if (is_array($plan->features)) {
                            foreach ($plan->features as $feature) {
                                if (is_string($feature) && trim($feature) !== '') {
                                    $planFeatures[] = $feature;
                                }
                            }
                        }

                        $isHighlighted = $loop->iteration === 2;
                        $dailyLimit = (int) $plan->daily_token_limit;
                        $monthlyLimit = (int) $plan->monthly_token_limit;
                    @endphp

                    <article class="plan-card {{ $isHighlighted ? 'highlight' : '' }}">
                        @if ($isHighlighted)
                            <div class="plan-badge">Popular</div>
                        @endif

                        <h3 class="plan-name">{{ $plan->name }}</h3>
                        <p class="plan-price">
                            ${{ number_format((float) $plan->price, 2) }}
                            <small>/ month</small>
                        </p>

                        <div class="plan-meta">
                            <div>Max Instances: {{ number_format((int) $plan->max_instances) }}</div>
                            <div>Daily Tokens: {{ $dailyLimit > 0 ? number_format($dailyLimit) : 'Unlimited' }}</div>
                            <div>Monthly Tokens: {{ $monthlyLimit > 0 ? number_format($monthlyLimit) : 'Unlimited' }}</div>
                        </div>

                        <ul class="plan-features">
                            @if (!empty($planFeatures))
                                @foreach (array_slice($planFeatures, 0, 4) as $feature)
                                    <li>{{ $feature }}</li>
                                @endforeach
                            @else
                                <li>AI-powered WhatsApp automation</li>
                                <li>Business-level usage tracking</li>
                                <li>Dashboard analytics and lead capture</li>
                            @endif
                        </ul>
                            <br>
                        <a href="{{ route('register', ['plan' => $plan->id]) }}" class="btn {{ $isHighlighted ? 'btn-primary' : 'btn-outline' }}">
                            Select {{ $plan->name }}
                        </a>
                    </article>
                @endforeach
            </div>
        @endif
    </section>

<footer class="footer">
        {{ date('Y') }} DPM AI. Built for scalable automation.
    </footer>
</div>
<script>
    (function () {
        var triggers = document.querySelectorAll('.plan-scroll');
        var plansSection = document.getElementById('plans');

        if (!triggers.length || !plansSection) {
            return;
        }

        triggers.forEach(function (trigger) {
            trigger.addEventListener('click', function (event) {
                event.preventDefault();
                plansSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                plansSection.classList.remove('plans-spotlight');
                window.setTimeout(function () {
                    plansSection.classList.add('plans-spotlight');
                }, 250);
            });
        });
    })();
</script>
</body>
</html>
