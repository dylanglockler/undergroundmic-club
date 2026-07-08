<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>The Underground Mic — Speakeasy Karaoke</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Allura&family=Kaushan+Script&family=Pacifico&family=Playfair+Display:ital,wght@0,700;1,400&family=Special+Elite&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --red: #ff2d55;
      --amber: #ffb830;
      --teal: #00e5cc;
      --cream: #f5ead7;
      --dark: #0d0a07;
      --dark-card: #1a1208;
      --mid: #2a1f0f;
    }

    body {
      background: var(--dark);
      color: var(--cream);
      font-family: 'DM Sans', sans-serif;
      min-height: 100vh;
    }

    .app {
      min-height: 100vh;
      background:
        radial-gradient(ellipse at 20% 10%, rgba(255,45,85,0.12) 0%, transparent 50%),
        radial-gradient(ellipse at 80% 80%, rgba(0,229,204,0.08) 0%, transparent 50%),
        radial-gradient(ellipse at 50% 50%, rgba(255,184,48,0.04) 0%, transparent 70%),
        var(--dark);
      padding-bottom: 60px;
    }

    .app::before {
      content: '';
      position: fixed;
      inset: 0;
      background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)' opacity='0.04'/%3E%3C/svg%3E");
      pointer-events: none;
      z-index: 0;
      opacity: 0.6;
    }

    .content { position: relative; z-index: 1; }

    /* Hero */
    .hero { text-align: center; padding: 60px 20px 40px; }

    .mic-icon {
      font-size: 64px;
      display: block;
      margin: 0 auto 20px;
      filter: drop-shadow(0 0 20px rgba(255,45,85,0.5));
      animation: float 3s ease-in-out infinite;
      font-family: "Apple Color Emoji", "Segoe UI Emoji", "Noto Color Emoji", sans-serif;
    }

    @keyframes float {
      0%, 100% { transform: translateY(0px) rotate(-3deg); }
      50%       { transform: translateY(-10px) rotate(3deg); }
    }

    .hero-title {
      font-family: 'Playfair Display', serif;
      font-size: clamp(28px, 8vw, 88px);
      font-weight: 700;
      line-height: 1;
      color: var(--amber);
      text-shadow: 0 0 40px rgba(255,184,48,0.38), 0 0 80px rgba(255,184,48,0.19);
      margin-bottom: 12px;
      display: flex;
      align-items: baseline;
      justify-content: center;
      gap: 0.25em;
      flex-wrap: wrap;
    }

    .hero-title span {
      font-family: 'Kaushan Script', cursive;
      color: #fff0f0;
      font-style: normal;
      font-size: 1.15em;
      text-shadow:
        0 0 2px #fff,
        0 0 6px var(--red),
        0 0 14px var(--red),
        0 0 30px rgba(255,45,85,0.67),
        0 0 55px rgba(255,45,85,0.33),
        0 0 90px rgba(255,45,85,0.2);
      animation: flicker 5s infinite;
    }

    @keyframes flicker {
      0%, 93%, 97%, 100% { opacity: 1; }
      94%, 96% { opacity: 0.85; }
      95%       { opacity: 0.6; }
    }

    .hero-sub {
      font-family: 'Special Elite', cursive;
      font-size: clamp(13px, 4vw, 18px);
      font-weight: 700;
      letter-spacing: clamp(1.5px, 0.6vw, 3px);
      white-space: nowrap;
      color: var(--teal);
      text-transform: uppercase;
      margin-bottom: 40px;
      text-shadow: 0 0 20px rgba(0,229,204,0.5);
    }

    /* Divider */
    .divider { display: flex; align-items: center; gap: 16px; max-width: 500px; margin: 0 auto 40px; padding: 0 20px; }
    .divider-line { flex: 1; height: 1px; background: linear-gradient(90deg, transparent, rgba(255,184,48,0.33), transparent); }
    .divider-dot { width: 6px; height: 6px; border-radius: 50%; background: var(--amber); box-shadow: 0 0 8px var(--amber); }

    /* Info grid */
    .info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; max-width: 800px; margin: 0 auto 50px; padding: 0 20px; }
    .info-card { background: var(--dark-card); border: 1px solid rgba(255,184,48,0.13); border-radius: 4px; padding: 24px 20px; text-align: center; position: relative; overflow: hidden; }
    .info-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 2px; background: linear-gradient(90deg, transparent, rgba(255,184,48,0.53), transparent); }
    .info-card-icon { font-size: 28px; margin-bottom: 10px; display: block; }
    .info-card-label { font-family: 'Special Elite', cursive; font-size: 10px; letter-spacing: 3px; color: rgba(255,184,48,0.6); text-transform: uppercase; margin-bottom: 6px; }
    .info-card-value { font-family: 'DM Sans', sans-serif; font-size: 18px; font-weight: 500; color: var(--cream); line-height: 1.4; }

    /* Rules */
    .rules-section { max-width: 600px; margin: 0 auto 50px; padding: 0 20px; }
    .section-title { font-family: 'Playfair Display', serif; font-size: 28px; font-style: italic; color: var(--amber); text-align: center; margin-bottom: 24px; text-shadow: 0 0 20px rgba(255,184,48,0.38); }
    .rules-list { list-style: none; display: flex; flex-direction: column; gap: 12px; }
    .rule-item { display: flex; align-items: flex-start; gap: 14px; background: var(--dark-card); border: 1px solid var(--mid); border-radius: 4px; padding: 14px 18px; }
    .rule-num { font-family: 'Playfair Display', serif; font-size: 22px; font-weight: 700; color: rgba(255,45,85,0.53); line-height: 1; flex-shrink: 0; }
    .rule-text { font-size: 16px; color: rgba(245,234,215,0.8); line-height: 1.5; padding-top: 2px; }

    /* Neon bar */
    .neon-bar { height: 1px; background: linear-gradient(90deg, transparent, var(--red), var(--amber), var(--teal), transparent); max-width: 400px; margin: 0 auto; opacity: 0.6; }

    /* Signup card */
    .reminder-section { max-width: 560px; margin: 0 auto; padding: 0 20px; }
    .reminder-card { background: var(--dark-card); border: 1px solid rgba(0,229,204,0.2); border-radius: 6px; overflow: hidden; position: relative; }
    .reminder-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 2px; background: linear-gradient(90deg, transparent, rgba(0,229,204,0.53), transparent); }
    .reminder-header { padding: 28px 28px 0; text-align: center; }
    .reminder-header h3 { font-family: 'Playfair Display', serif; font-size: 28px; font-style: italic; color: var(--teal); margin-bottom: 6px; text-shadow: 0 0 20px rgba(0,229,204,0.38); }
    .reminder-header p { font-size: 14px; color: rgba(245,234,215,0.47); margin-bottom: 24px; }

    /* Tabs */
    .tabs { display: flex; border-bottom: 1px solid var(--mid); margin: 0 28px; }
    .tab { flex: 1; padding: 10px 8px; font-family: 'Special Elite', cursive; font-size: 14px; letter-spacing: 2px; text-transform: uppercase; color: rgba(245,234,215,0.33); background: none; border: none; cursor: pointer; transition: all 0.2s; border-bottom: 2px solid transparent; margin-bottom: -1px; }
    .tab.active { color: var(--teal); border-bottom-color: var(--teal); }
    .tab:hover:not(.active) { color: rgba(245,234,215,0.6); }

    /* Form */
    .form-body { padding: 24px 28px 28px; }
    .field { margin-bottom: 16px; }
    .field label { display: block; font-family: 'Special Elite', cursive; font-size: 13px; font-weight: 700; letter-spacing: 2px; color: rgba(255,184,48,0.6); text-transform: uppercase; margin-bottom: 8px; }
    .field input, .field select { width: 100%; background: var(--mid); border: 1px solid rgba(255,184,48,0.2); border-radius: 3px; padding: 12px 14px; color: var(--cream); font-family: 'DM Sans', sans-serif; font-size: 14px; outline: none; transition: border-color 0.2s; }
    .field input::placeholder { color: rgba(245,234,215,0.27); }
    .field input:focus, .field select:focus { border-color: rgba(0,229,204,0.47); box-shadow: 0 0 0 3px rgba(0,229,204,0.09); }
    .field select option { background: var(--mid); }

    .submit-btn { width: 100%; padding: 14px; background: linear-gradient(135deg, var(--red), rgba(255,45,85,0.8)); color: white; font-family: 'Special Elite', cursive; font-size: 13px; letter-spacing: 3px; text-transform: uppercase; border: none; border-radius: 3px; cursor: pointer; transition: all 0.2s; margin-top: 8px; }
    .submit-btn:hover:not(:disabled) { transform: translateY(-1px); box-shadow: 0 4px 20px rgba(255,45,85,0.4); }
    .submit-btn:disabled { opacity: 0.6; cursor: not-allowed; }

    /* Success / Error */
    .success-msg { text-align: center; padding: 20px 0; }
    .success-msg .checkmark { font-size: 40px; display: block; margin-bottom: 12px; }
    .success-msg h4 { font-family: 'Playfair Display', serif; font-size: 22px; color: var(--teal); margin-bottom: 8px; }
    .success-msg p { font-size: 14px; color: rgba(245,234,215,0.8); line-height: 1.6; }
    .error-msg { background: rgba(255,45,85,0.1); border: 1px solid rgba(255,45,85,0.3); border-radius: 3px; padding: 10px 14px; font-size: 13px; color: var(--red); margin-bottom: 16px; }

    /* Footer */
    .footer { text-align: center; margin-top: 60px; padding: 0 20px; }
    .footer p { font-size: 14px; color: rgba(245,234,215,0.27); letter-spacing: 1px; margin-bottom: 8px; }
    .admin-link { background: none; border: none; color: rgba(245,234,215,0.2); font-size: 11px; cursor: pointer; letter-spacing: 2px; font-family: 'Special Elite', cursive; text-decoration: none; transition: color 0.2s; }
    .admin-link:hover { color: var(--amber); }

    .carousel { position: relative; width: 100%; max-width: 700px; height: 420px; margin: 32px auto 0; border-radius: 6px; overflow: hidden; border: 1px solid rgba(255,184,48,0.2); box-shadow: 0 0 40px rgba(0,0,0,0.6); }
    .carousel-track { display: flex; height: 100%; transition: transform 0.4s ease; will-change: transform; }
    .carousel-slide { flex: 0 0 100%; height: 100%; position: relative; overflow: hidden; }
    .carousel-slide img, .carousel-slide video { width: 100%; height: 100%; object-fit: cover; object-position: 75% 55%; display: block; }
    .carousel-slide img { filter: brightness(1.15) contrast(1.05); }
    .carousel-fade { position: absolute; bottom: 0; left: 0; right: 0; height: 140px; background: linear-gradient(to bottom, transparent, var(--dark)); pointer-events: none; }
    .carousel-caption { position: absolute; bottom: 28px; left: 24px; right: 24px; font-family: 'Special Elite', cursive; font-size: clamp(18px, 4.5vw, 30px); font-style: normal; letter-spacing: 1px; line-height: 1.15; text-align: left; color: rgba(245,234,215,0.72); text-shadow: 0 2px 4px rgba(0,0,0,0.9), 0 4px 20px rgba(0,0,0,0.7); z-index: 2; }
    .carousel-caption-typewriter {
      display: inline-block; white-space: nowrap; overflow: hidden; width: 0;
      animation-name: captionType;
      animation-duration: 2.6s;
      animation-timing-function: steps(17, end);
      animation-delay: 0.6s;
      animation-fill-mode: forwards;
    }
    .carousel-caption-typewriter::after {
      content: ''; position: absolute; right: 0; bottom: 0.02em;
      width: 0.55em; height: 1.05em;
      background: rgba(245,234,215,0.85);
      animation-name: captionCaret;
      animation-duration: 0.8s;
      animation-timing-function: step-end;
      animation-delay: 3.2s;
      animation-iteration-count: 5;
      animation-fill-mode: forwards;
    }
    @keyframes captionType { to { width: 17ch; } }
    @keyframes captionCaret { 0% { opacity: 1; } 50% { opacity: 0; } 100% { opacity: 0; } }
    .carousel-btn { position: absolute; top: 50%; transform: translateY(-50%); background: rgba(0,0,0,0.45); border: 1px solid rgba(255,184,48,0.3); color: var(--amber); font-size: 28px; width: 42px; height: 42px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; z-index: 10; transition: all 0.2s; line-height: 1; }
    .carousel-btn:hover { background: rgba(255,184,48,0.2); }
    .carousel-prev { left: 12px; }
    .carousel-next { right: 12px; }
    .carousel-dots { position: absolute; bottom: 14px; right: 16px; display: flex; gap: 7px; z-index: 10; }
    .carousel-dot { width: 8px; height: 8px; border-radius: 50%; background: rgba(255,255,255,0.35); cursor: pointer; transition: all 0.2s; border: none; padding: 0; }
    .carousel-dot.active { background: var(--amber); box-shadow: 0 0 6px var(--amber); }
    @media (max-width: 600px) { .slide-4202 img { object-position: 20% 55%; } .slide-8728 img { object-position: 20% 55%; } .slide-video video { object-position: 39% 20%; } .slide-6782 img { object-position: 20% 55%; } }

    .cal-btn { display: inline-flex; align-items: center; justify-content: center; gap: 10px; font-family: 'Special Elite', cursive; font-size: 11px; letter-spacing: 3px; text-transform: uppercase; white-space: nowrap; color: var(--teal); border: 1px solid rgba(0,229,204,0.33); padding: 13px 28px; border-radius: 2px; background: rgba(0,229,204,0.06); text-decoration: none; transition: all 0.2s; width: 320px; }
    .cal-btn:hover { background: rgba(0,229,204,0.13); box-shadow: 0 0 20px rgba(0,229,204,0.18); }
    .cal-btn svg { flex-shrink: 0; }
  </style>
</head>
<body>
<div class="app">
  <div class="content">

    <!-- Hero -->
    <div class="hero">
      <h1 class="hero-title">The Underground <span>Mic</span></h1>
      <p class="hero-sub">Your Speakeasy Karaoke Club</p>
      <span class="mic-icon">🎤</span>

      <div class="carousel" id="carousel">
        <div class="carousel-track" id="carousel-track">

          <div class="carousel-slide slide-video">
            <video autoplay muted loop playsinline>
              <source src="/images/IMG_9554.mp4" type="video/mp4">
            </video>
            <div class="carousel-fade"></div>
            <div class="carousel-caption carousel-caption-typewriter">You're invited...</div>
          </div>

          <div class="carousel-slide">
            <img src="/images/IMG_1020.JPG" alt="The Underground Mic">
            <div class="carousel-fade"></div>
            <div class="carousel-caption">Sing with friends</div>
          </div>

          <div class="carousel-slide slide-8728">
            <img src="/images/IMG_8728.jpeg" alt="The Underground Mic">
            <div class="carousel-fade"></div>
            <div class="carousel-caption">All ages welcome</div>
          </div>

          <div class="carousel-slide">
            <img src="/images/IMG_1585.jpeg" alt="The Underground Mic" style="object-position: 75% 15%;">
            <div class="carousel-fade"></div>
            <div class="carousel-caption">35,000+ songs</div>
          </div>

          <div class="carousel-slide">
            <img src="/images/IMG_3881.jpeg" alt="The Underground Mic">
            <div class="carousel-fade"></div>
            <div class="carousel-caption">Air guitars available</div>
          </div>

          <div class="carousel-slide">
            <img src="/images/IMG_7609.jpeg" alt="The Underground Mic" style="object-position: 75% 20%;">
            <div class="carousel-fade"></div>
            <div class="carousel-caption">Duets (and trios) encouraged</div>
          </div>

          <div class="carousel-slide">
            <img src="/images/IMG_0505.jpeg" alt="The Underground Mic" style="object-position: center center; transform: scale(1.25); transform-origin: center center;">
            <div class="carousel-fade"></div>
            <div class="carousel-caption">Inner divas released</div>
          </div>

          <div class="carousel-slide">
            <img src="/images/IMG_1939.jpeg" alt="The Underground Mic">
            <div class="carousel-fade"></div>
            <div class="carousel-caption">Get your glow on</div>
          </div>

          <div class="carousel-slide">
            <img src="/images/IMG_0992.jpeg" alt="The Underground Mic" style="object-position: 75% 15%;">
            <div class="carousel-fade"></div>
            <div class="carousel-caption">Or just laugh your ass off</div>
          </div>

          <div class="carousel-slide slide-4202">
            <img src="/images/IMG_4202.JPG" alt="The Underground Mic">
            <div class="carousel-fade"></div>
            <div class="carousel-caption">The bar is open...</div>
          </div>


          <div class="carousel-slide slide-6782">
            <img src="/images/IMG_6782.jpeg" alt="The Underground Mic">
            <div class="carousel-fade"></div>
            <div class="carousel-caption">Let's rock!</div>
          </div>

        </div>
        <button class="carousel-btn carousel-prev" onclick="carouselPrev()">&#8249;</button>
        <button class="carousel-btn carousel-next" onclick="carouselNext()">&#8250;</button>
        <div class="carousel-dots" id="carousel-dots">
          <button class="carousel-dot active" onclick="carouselGoTo(0)"></button>
          <button class="carousel-dot" onclick="carouselGoTo(1)"></button>
          <button class="carousel-dot" onclick="carouselGoTo(2)"></button>
          <button class="carousel-dot" onclick="carouselGoTo(3)"></button>
          <button class="carousel-dot" onclick="carouselGoTo(4)"></button>
          <button class="carousel-dot" onclick="carouselGoTo(5)"></button>
          <button class="carousel-dot" onclick="carouselGoTo(6)"></button>
          <button class="carousel-dot" onclick="carouselGoTo(7)"></button>
          <button class="carousel-dot" onclick="carouselGoTo(8)"></button>
          <button class="carousel-dot" onclick="carouselGoTo(9)"></button>
          <button class="carousel-dot" onclick="carouselGoTo(10)"></button>
        </div>
      </div>
    </div>

    <div class="divider">
      <div class="divider-line"></div>
      <div class="divider-dot"></div>
      <div class="divider-line"></div>
    </div>

    <!-- Info cards -->
    <div class="info-grid">
      <div class="info-card">
        <span class="info-card-icon">📅</span>
        <div class="info-card-label">When</div>
        <div class="info-card-value">Last Saturday of Every Month</div>
      </div>
      <div class="info-card">
        <span class="info-card-icon">🕗</span>
        <div class="info-card-label">Time</div>
        <div class="info-card-value">7PM – Midnight</div>
      </div>
      <div class="info-card">
        <span class="info-card-icon">🎉</span>
        <div class="info-card-label">Next Party</div>
        <div class="info-card-value">{{ $nextParty }}</div>
      </div>
    </div>

    <!-- House Rules -->
    <div class="rules-section">
      <h2 class="section-title">House Rules</h2>
      <ul class="rules-list">
        <li class="rule-item"><span class="rule-num">01</span><span class="rule-text">No judgment. We're all here to have a good time, not to audition for The Voice.</span></li>
        <li class="rule-item"><span class="rule-num">02</span><span class="rule-text">Singers, fans, teens, kids, well-trained pups... come as you are. There's room at the inn.</span></li>
        <li class="rule-item"><span class="rule-num">03</span><span class="rule-text">Invite only — don't post the address publicly. Tell your friends personally. Sharing is the Speakeasy way.</span></li>
        <li class="rule-item"><span class="rule-num">04</span><span class="rule-text">We got the booze, beer, and wine. Bring something if you want, or just roll up.</span></li>
        <li class="rule-item"><span class="rule-num">05</span><span class="rule-text">Cheer loudly for every performer. The vibe you bring is the vibe we share.</span></li>
      </ul>
    </div>

    <div style="padding: 30px 0;">
      <div class="neon-bar"></div>
    </div>

    <!-- Signup form -->
    <div class="reminder-section">
      <div class="reminder-card">
        <div class="reminder-header">
          <h3>Join the Club</h3>
          <p>Never miss a mic drop moment. We'll ping you before the next party.</p>
        </div>

        <div id="signup-form">
          <div class="tabs">
            <button class="tab active" data-tab="email" onclick="switchTab('email', this)">Sign Up</button>
          </div>

          <div class="form-body">
            <div id="error-msg" class="error-msg" style="display:none"></div>
            <div id="success-msg" class="success-msg" style="display:none">
              <span class="checkmark">🎉</span>
              <h4>You're on the list!</h4>
              <p id="success-text"></p>
              <div id="success-cal" style="display:none; margin-top:20px; border-top:1px solid rgba(0,229,204,0.15); padding-top:20px;">
                <p style="font-family:'Special Elite',cursive; font-size:10px; letter-spacing:2px; color:rgba(0,229,204,0.6); text-transform:uppercase; margin-bottom:14px;">Save the Date</p>
                <div style="display:flex; gap:10px; justify-content:center; flex-wrap:wrap;">
                  <a id="success-gcal" href="#" target="_blank" rel="noopener" class="cal-btn" style="font-size:10px; padding:8px 16px;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><rect x="2" y="4" width="20" height="18" rx="2" fill="white"/><path d="M2 9h20V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v3z" fill="#4285F4"/><rect x="7" y="2" width="2" height="4" rx="1" fill="#4285F4"/><rect x="15" y="2" width="2" height="4" rx="1" fill="#4285F4"/><text x="12" y="18" text-anchor="middle" font-size="8" font-weight="700" fill="#4285F4" font-family="Arial,sans-serif">31</text></svg>
                    Google Calendar
                  </a>
                  <a href="/calendar.ics" class="cal-btn" style="font-size:10px; padding:8px 16px;" download>
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><rect x="2" y="4" width="20" height="18" rx="2" fill="white"/><path d="M2 9h20V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v3z" fill="#FF3B30"/><rect x="7" y="2" width="2" height="4" rx="1" fill="#FF3B30"/><rect x="15" y="2" width="2" height="4" rx="1" fill="#FF3B30"/><text x="12" y="18" text-anchor="middle" font-size="8" font-weight="700" fill="#1C1C1E" font-family="Arial,sans-serif">31</text></svg>
                    Apple Calendar
                  </a>
                </div>
              </div>
            </div>

            <div id="form-fields">
              <div class="field">
                <label>Full Name</label>
                <input type="text" id="name" placeholder="The name on your ID">
              </div>
              <div class="field">
                <label>Stage Name?</label>
                <input type="text" id="stage_name" placeholder="The name on the marquee">
              </div>
              <div class="field">
                <label>Phone Number</label>
                <input type="tel" id="phone" placeholder="(555) 123-4567" maxlength="14" oninput="formatPhone(this)">
              </div>
              <div class="field">
                <label id="contact-label">Your Email Address</label>
                <input type="email" id="contact" placeholder="yourname@email.com">
              </div>
              <div class="field">
                <label>Remind Me</label>
                <select id="reminder_time">
                  <option value="1week">1 week before</option>
                  <option value="1day" selected>1 day before</option>
                  <option value="dayof">Day of the party</option>
                </select>
              </div>
              <button class="submit-btn" id="submit-btn" onclick="handleSubmit()">Sign Me Up 🎤</button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Add to Calendar -->
    <div style="text-align:center; margin: 40px auto 0; padding: 0 20px;">
      <div style="display:flex; gap:12px; justify-content:center; flex-wrap:wrap;">
        <a href="{{ $gcalUrl }}" target="_blank" rel="noopener" class="cal-btn">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect x="2" y="4" width="20" height="18" rx="2" fill="white"/>
            <path d="M2 9h20V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v3z" fill="#4285F4"/>
            <rect x="7" y="2" width="2" height="4" rx="1" fill="#4285F4"/>
            <rect x="15" y="2" width="2" height="4" rx="1" fill="#4285F4"/>
            <text x="12" y="18" text-anchor="middle" font-size="8" font-weight="700" fill="#4285F4" font-family="Arial,sans-serif">31</text>
          </svg>
          Add to Google Calendar
        </a>
        <a href="/calendar.ics" class="cal-btn" download>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect x="2" y="4" width="20" height="18" rx="2" fill="white"/>
            <path d="M2 9h20V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v3z" fill="#FF3B30"/>
            <rect x="7" y="2" width="2" height="4" rx="1" fill="#FF3B30"/>
            <rect x="15" y="2" width="2" height="4" rx="1" fill="#FF3B30"/>
            <text x="12" y="18" text-anchor="middle" font-size="8" font-weight="700" fill="#1C1C1E" font-family="Arial,sans-serif">31</text>
          </svg>
          Add to Apple Calendar
        </a>
      </div>
      <p style="font-size:14px; color:rgba(245,234,215,0.3); margin-top:12px; letter-spacing:1px;">RSVP if you can. Or just show up.</p>
    </div>

    <div style="text-align:center; padding: 60px 20px 20px;">
      <p style="font-family:'Playfair Display',serif; font-size:28px; font-style:italic; font-weight:700; color:var(--amber); margin-bottom:12px; text-shadow: 0 0 20px rgba(255,184,48,0.38), 0 0 60px rgba(255,184,48,0.2);">See You At The Next One</p>
      <div id="countdown" style="font-family:'DM Sans',sans-serif; font-size:18px; font-weight:500; color:var(--cream); margin-bottom:8px;"></div>
    </div>

    <div class="footer">
      <p>The Underground Mic · No Judgment Ever.</p>
      <a href="/admin" class="admin-link">Host Login</a>
    </div>

  </div>
</div>

<script>
const gcalUrl = "{{ $gcalUrl }}";
document.getElementById('success-gcal').href = gcalUrl;

const partyTime = new Date({{ $partyTimestamp }} * 1000);

function updateCountdown() {
  const el = document.getElementById('countdown');
  if (!el) return;
  const diff = partyTime - new Date();
  if (diff <= 0) { el.textContent = "It's tonight!"; return; }
  const days    = Math.floor(diff / 86400000);
  const hours   = Math.floor((diff % 86400000) / 3600000);
  const minutes = Math.floor((diff % 3600000) / 60000);
  const seconds = Math.floor((diff % 60000) / 1000);
  el.textContent = `${days}d : ${String(hours).padStart(2,'0')}h : ${String(minutes).padStart(2,'0')}m : ${String(seconds).padStart(2,'0')}s`;
}
setInterval(updateCountdown, 1000);
updateCountdown();

let currentTab = 'email';

const tabConfig = {
  email:    { label: 'Your Email Address', placeholder: 'yourname@email.com', type: 'email' },
};

function switchTab(tab, el) {
  currentTab = tab;
  document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
  el.classList.add('active');
  const cfg = tabConfig[tab];
  document.getElementById('contact-label').textContent = cfg.label;
  const contactInput = document.getElementById('contact');
  contactInput.placeholder = cfg.placeholder;
  contactInput.type = cfg.type;
  contactInput.value = '';
  document.getElementById('error-msg').style.display = 'none';
}

function formatPhone(input) {
  let v = input.value.replace(/\D/g, '').slice(0, 10);
  if (v.length >= 7) v = '(' + v.slice(0,3) + ') ' + v.slice(3,6) + '-' + v.slice(6);
  else if (v.length >= 4) v = '(' + v.slice(0,3) + ') ' + v.slice(3);
  else if (v.length >= 1) v = '(' + v;
  input.value = v;
}

async function handleSubmit() {
  const name    = document.getElementById('name').value.trim();
  const contact = document.getElementById('contact').value.trim();
  const cfg     = tabConfig[currentTab];

  if (!name || !contact) {
    showError('Please fill in your name and ' + cfg.label.toLowerCase() + '.');
    return;
  }

  const btn = document.getElementById('submit-btn');
  btn.disabled = true;
  btn.textContent = 'Sending...';
  document.getElementById('error-msg').style.display = 'none';

  try {
    const res = await fetch('/guests', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
      },
      body: JSON.stringify({
        name,
        stage_name:    document.getElementById('stage_name').value.trim(),
        phone:         document.getElementById('phone').value.trim(),
        method:        currentTab,
        contact,
        reminder_time: document.getElementById('reminder_time').value,
      }),
    });

    const data = await res.json();

    if (!res.ok) {
      const errors = data.errors ? Object.values(data.errors).flat().join(' ') : (data.message || 'Something went wrong.');
      showError(errors);
      btn.disabled = false;
      btn.textContent = "Sign Me Up 🎤";
      return;
    }

    document.getElementById('form-fields').style.display = 'none';
    document.getElementById('success-text').textContent = data.message || "See you at The Underground Mic!";
    document.getElementById('success-msg').style.display = 'block';
    document.getElementById('success-cal').style.display = 'block';

  } catch (e) {
    showError('Network error. Please try again.');
    btn.disabled = false;
    btn.textContent = "Sign Me Up 🎤";
  }
}

function showError(msg) {
  const el = document.getElementById('error-msg');
  el.textContent = msg;
  el.style.display = 'block';
}

// Carousel
let carouselIndex = 0;
function carouselGoTo(index) {
  const slides = document.querySelectorAll('.carousel-slide');
  const dots   = document.querySelectorAll('.carousel-dot');
  carouselIndex = (index + slides.length) % slides.length;
  document.getElementById('carousel-track').style.transform = `translateX(-${carouselIndex * 100}%)`;
  dots.forEach((d, i) => d.classList.toggle('active', i === carouselIndex));
}
function carouselNext() { carouselGoTo(carouselIndex + 1); }
function carouselPrev() { carouselGoTo(carouselIndex - 1); }

// Swipe support
let touchStartX = 0;
const carouselEl = document.getElementById('carousel');
carouselEl.addEventListener('touchstart', e => { touchStartX = e.touches[0].clientX; }, { passive: true });
carouselEl.addEventListener('touchend', e => {
  const diff = touchStartX - e.changedTouches[0].clientX;
  if (Math.abs(diff) > 40) diff > 0 ? carouselNext() : carouselPrev();
});
</script>
</body>
</html>
