<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ __('site.maintenance.title') }} — {{ config('app.name') }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 2.5rem;
            font-family: 'Montserrat', system-ui, -apple-system, 'Segoe UI', Arial, sans-serif;
            background: radial-gradient(60rem 30rem at 50% -10%, #3C206B 0%, transparent 60%),
                        linear-gradient(160deg, #1A0929 0%, #0A0413 100%);
            color: #F9F9F9;
            text-align: center;
            padding: 2rem 1.25rem;
        }
        .brand { display: flex; align-items: center; gap: .75rem; }
        .brand img { width: 44px; height: 44px; }
        .brand span {
            font-size: 1.5rem;
            font-weight: 800;
            letter-spacing: .02em;
            background: linear-gradient(90deg, #7D30FA 0%, #FC912C 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        main { max-width: 40rem; }
        .gear {
            width: 88px;
            height: 88px;
            margin: 0 auto 1.75rem;
            animation: spin 9s linear infinite;
            filter: drop-shadow(0 0 18px rgba(125, 48, 250, .55));
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        @media (prefers-reduced-motion: reduce) { .gear { animation: none; } }
        h1 {
            margin: 0 0 .75rem;
            font-size: clamp(2rem, 6vw, 3rem);
            line-height: 1.15;
            background: linear-gradient(90deg, #7D30FA 0%, #FC912C 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .badge {
            display: inline-block;
            margin-bottom: 1.25rem;
            padding: .35rem 1rem;
            border: 1px solid #7D30FA;
            border-radius: 9999px;
            font-size: .8rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: #C9A8FF;
        }
        p.lead { color: #B9B9C6; line-height: 1.7; font-size: 1.05rem; margin: 0 0 2rem; }
        .cta {
            display: inline-block;
            padding: .9rem 2rem;
            border-radius: 9999px;
            font-weight: 700;
            font-size: 1.05rem;
            text-decoration: none;
            color: #fff;
            background: linear-gradient(90deg, #7D30FA 0%, #FC912C 100%);
            box-shadow: 0 8px 24px rgba(125, 48, 250, .35);
        }
        .cta:hover { filter: brightness(1.12); }
        .contact { margin-top: 2.25rem; display: flex; flex-wrap: wrap; justify-content: center; gap: .75rem 2rem; color: #9C9CAB; font-size: .95rem; }
        .contact a { color: #C9C8D4; text-decoration: none; }
        .contact a:hover { color: #FC912C; }
        .socials { display: flex; justify-content: center; gap: 1.25rem; margin-top: 2.5rem; }
        .socials a { color: #B5B4C2; transition: color .2s; }
        .socials a:hover { color: #FC912C; }
        .socials svg { width: 22px; height: 22px; fill: currentColor; }
        footer { color: #8A8A99; font-size: .8rem; }
        footer a { color: #B5B4C2; text-decoration: none; }
        footer a:hover { color: #FC912C; }
    </style>
</head>
<body>
    <div class="brand">
        <img src="{{ asset('images/logo.svg') }}" alt="{{ __('site.brand') }}" width="44" height="44">
        <span>{{ config('app.name') }}</span>
    </div>

    <main>
        <svg class="gear" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <path d="M12 15.5A3.5 3.5 0 1 0 12 8.5a3.5 3.5 0 0 0 0 7Z" stroke="#7D30FA" stroke-width="1.6"/>
            <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09a1.65 1.65 0 0 0-1-1.51 1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 1 1 0-4h.09a1.65 1.65 0 0 0 1.51-1 1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33h.01a1.65 1.65 0 0 0 1-1.51V3a2 2 0 1 1 4 0v.09a1.65 1.65 0 0 0 1 1.51h.01a1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82v.01a1.65 1.65 0 0 0 1.51 1H21a2 2 0 1 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1Z" stroke="#FC912C" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>

        <span class="badge">{{ __('site.maintenance.badge') }}</span>
        <h1>{{ __('site.maintenance.title') }}</h1>
        <p class="lead">{{ __('site.maintenance.text') }}</p>

        <a class="cta" href="{{ __('site.whatsapp_base') }}?text={{ rawurlencode(__('site.maintenance.whatsapp_message')) }}" rel="noopener noreferrer">{{ __('site.maintenance.contact') }}</a>

        <div class="contact">
            <span>{{ __('site.footer.city') }}</span>
            <a href="tel:{{ __('site.phone_raw') }}">{{ __('site.phone') }}</a>
            <a href="mailto:{{ __('site.email') }}">{{ __('site.email') }}</a>
        </div>

        <div class="socials">
            <a href="https://facebook.com/Tech.Honowa" aria-label="{{ __('site.footer.social_facebook') }}" rel="noopener noreferrer" target="_blank">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M22 12a10 10 0 1 0-11.6 9.9v-7H7.9V12h2.5V9.8c0-2.5 1.5-3.9 3.8-3.9 1.1 0 2.2.2 2.2.2v2.5h-1.3c-1.2 0-1.6.8-1.6 1.6V12h2.8l-.4 2.9h-2.4v7A10 10 0 0 0 22 12Z"/></svg>
            </a>
            <a href="https://twitter.com/honowa6" aria-label="{{ __('site.footer.social_twitter') }}" rel="noopener noreferrer" target="_blank">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18.9 2H22l-6.8 7.8L23.2 22h-6.3l-4.9-6.4L6.4 22H3.3l7.3-8.3L1.2 2h6.4l4.4 5.9L18.9 2Zm-1.1 18.1h1.7L6.7 3.8H4.9l12.9 16.3Z"/></svg>
            </a>
            <a href="https://cm.linkedin.com/company/honowa-technologies" aria-label="{{ __('site.footer.social_linkedin') }}" rel="noopener noreferrer" target="_blank">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4.98 3.5A2.49 2.49 0 1 1 2.5 6a2.49 2.49 0 0 1 2.48-2.5ZM2.9 8.75h4.16V21H2.9V8.75Zm6.66 0h3.98v1.67h.06a4.37 4.37 0 0 1 3.93-2.16c4.2 0 4.98 2.77 4.98 6.37V21h-4.16v-5.6c0-1.34-.02-3.06-1.86-3.06-1.87 0-2.15 1.46-2.15 2.96V21H9.56V8.75Z"/></svg>
            </a>
        </div>
    </main>

    <footer>
        <p>© {{ date('Y') }} Focus Rent. {{ __('site.footer.rights') }} <a href="https://honowa.com/home" rel="noopener noreferrer">Honowa Technologies</a></p>
    </footer>
</body>
</html>
