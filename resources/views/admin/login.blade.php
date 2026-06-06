<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Host Login — The Underground Mic</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;1,400&family=Special+Elite&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    :root { --red: #ff2d55; --amber: #ffb830; --teal: #00e5cc; --cream: #f5ead7; --dark: #0d0a07; --dark-card: #1a1208; --mid: #2a1f0f; }
    body { background: var(--dark); color: var(--cream); font-family: 'DM Sans', sans-serif; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
    .login-card { background: var(--dark-card); border: 1px solid rgba(255,184,48,0.2); border-radius: 6px; padding: 40px 36px; width: 100%; max-width: 380px; position: relative; overflow: hidden; }
    .login-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 2px; background: linear-gradient(90deg, transparent, rgba(255,184,48,0.53), transparent); }
    h2 { font-family: 'Playfair Display', serif; font-style: italic; color: var(--amber); font-size: 26px; margin-bottom: 6px; }
    p { font-size: 13px; color: rgba(245,234,215,0.47); margin-bottom: 28px; }
    label { display: block; font-family: 'Special Elite', cursive; font-size: 10px; letter-spacing: 2px; color: rgba(255,184,48,0.6); text-transform: uppercase; margin-bottom: 8px; }
    input[type="password"] { width: 100%; background: var(--mid); border: 1px solid rgba(255,184,48,0.2); border-radius: 3px; padding: 12px 14px; color: var(--cream); font-family: 'DM Sans', sans-serif; font-size: 14px; outline: none; transition: border-color 0.2s; margin-bottom: 16px; }
    input:focus { border-color: rgba(0,229,204,0.47); box-shadow: 0 0 0 3px rgba(0,229,204,0.09); }
    button { width: 100%; padding: 13px; background: linear-gradient(135deg, var(--red), rgba(255,45,85,0.8)); color: white; font-family: 'Special Elite', cursive; font-size: 12px; letter-spacing: 3px; text-transform: uppercase; border: none; border-radius: 3px; cursor: pointer; }
    .error { background: rgba(255,45,85,0.1); border: 1px solid rgba(255,45,85,0.3); border-radius: 3px; padding: 10px 14px; font-size: 13px; color: var(--red); margin-bottom: 16px; }
    .back { display: block; text-align: center; margin-top: 16px; color: rgba(245,234,215,0.27); font-size: 12px; text-decoration: none; font-family: 'Special Elite', cursive; letter-spacing: 2px; }
    .back:hover { color: var(--amber); }
  </style>
</head>
<body>
  <div class="login-card">
    <h2>Host Login</h2>
    <p>Members only. You know the drill.</p>

    @if ($errors->any())
      <div class="error">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('admin.login') }}">
      @csrf
      <label for="password">Password</label>
      <input type="password" id="password" name="password" autofocus>
      <button type="submit">Enter</button>
    </form>
    <a href="/" class="back">← Back</a>
  </div>
</body>
</html>
