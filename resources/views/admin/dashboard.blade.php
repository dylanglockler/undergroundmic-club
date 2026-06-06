<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin — The Underground Mic</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;1,400&family=Special+Elite&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    :root { --red: #ff2d55; --amber: #ffb830; --teal: #00e5cc; --cream: #f5ead7; --dark: #0d0a07; --dark-card: #1a1208; --mid: #2a1f0f; }
    body { background: var(--dark); color: var(--cream); font-family: 'DM Sans', sans-serif; min-height: 100vh; }
    .panel { max-width: 720px; margin: 0 auto; padding: 40px 20px; }
    h2 { font-family: 'Playfair Display', serif; font-style: italic; color: var(--amber); font-size: 32px; margin-bottom: 6px; }
    .subtitle { font-size: 13px; color: rgba(245,234,215,0.47); margin-bottom: 40px; }
    .section { background: var(--dark-card); border: 1px solid rgba(255,184,48,0.13); border-radius: 6px; padding: 24px; margin-bottom: 24px; position: relative; overflow: hidden; }
    .section::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 2px; background: linear-gradient(90deg, transparent, rgba(255,184,48,0.4), transparent); }
    .section h3 { font-family: 'Special Elite', cursive; font-size: 13px; letter-spacing: 3px; text-transform: uppercase; color: var(--amber); margin-bottom: 16px; }
    .guest-list { list-style: none; max-height: 280px; overflow-y: auto; }
    .guest-row { display: flex; align-items: center; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid rgba(255,184,48,0.07); }
    .guest-row:last-child { border-bottom: none; }
    .guest-name { font-weight: 500; }
    .guest-contact { color: rgba(245,234,215,0.6); font-size: 12px; }
    .guest-method { font-family: 'Special Elite', cursive; font-size: 10px; letter-spacing: 2px; text-transform: uppercase; padding: 2px 8px; border-radius: 2px; background: rgba(0,229,204,0.1); color: var(--teal); border: 1px solid rgba(0,229,204,0.2); }
    .guest-delete { background: none; border: none; color: rgba(245,234,215,0.27); cursor: pointer; font-size: 16px; padding: 0 4px; transition: color 0.2s; }
    .guest-delete:hover { color: var(--red); }
    .empty { color: rgba(245,234,215,0.27); font-size: 14px; text-align: center; padding: 24px 0; }
    .btn-row { display: flex; gap: 12px; margin-top: 16px; flex-wrap: wrap; }
    .btn { padding: 10px 18px; font-family: 'Special Elite', cursive; font-size: 11px; letter-spacing: 2px; text-transform: uppercase; border: 1px solid rgba(0,229,204,0.33); background: transparent; color: var(--cream); border-radius: 3px; cursor: pointer; transition: all 0.2s; }
    .btn:hover { border-color: var(--teal); box-shadow: 0 0 12px rgba(0,229,204,0.2); }
    .btn.primary { background: linear-gradient(135deg, var(--red), rgba(255,45,85,0.8)); border-color: transparent; }
    .btn.primary:hover { box-shadow: 0 0 20px rgba(255,45,85,0.53); }
    .btn:disabled { opacity: 0.5; cursor: not-allowed; }
    textarea { width: 100%; background: var(--mid); border: 1px solid rgba(255,184,48,0.2); border-radius: 3px; padding: 12px 14px; color: var(--cream); font-family: 'DM Sans', sans-serif; font-size: 13px; outline: none; resize: vertical; transition: border-color 0.2s; margin-top: 8px; }
    textarea:focus { border-color: rgba(0,229,204,0.47); }
    input[type="text"] { width: 100%; background: var(--mid); border: 1px solid rgba(255,184,48,0.2); border-radius: 3px; padding: 10px 14px; color: var(--cream); font-family: 'DM Sans', sans-serif; font-size: 13px; outline: none; margin-bottom: 8px; }
    input:focus { border-color: rgba(0,229,204,0.47); }
    .status { margin-top: 12px; font-size: 13px; padding: 10px 14px; border-radius: 3px; display: none; }
    .status.success { background: rgba(0,229,204,0.1); border: 1px solid rgba(0,229,204,0.3); color: var(--teal); }
    .status.error   { background: rgba(255,45,85,0.1); border: 1px solid rgba(255,45,85,0.3); color: var(--red); }
    .logout-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px; }
    .logout-btn { background: none; border: 1px solid rgba(245,234,215,0.13); color: rgba(245,234,215,0.47); font-family: 'Special Elite', cursive; font-size: 10px; letter-spacing: 2px; text-transform: uppercase; padding: 6px 14px; border-radius: 2px; cursor: pointer; }
    .logout-btn:hover { color: var(--red); border-color: rgba(255,45,85,0.3); }
    label.field-label { display: block; font-family: 'Special Elite', cursive; font-size: 10px; letter-spacing: 2px; color: rgba(255,184,48,0.6); text-transform: uppercase; margin-bottom: 6px; margin-top: 12px; }
  </style>
</head>
<body>
<div class="panel">

  <div class="logout-row">
    <div>
      <h2>The Underground Mic</h2>
      <p class="subtitle">Host Dashboard · {{ $guests->count() }} guest(s) on the list</p>
    </div>
    <form method="POST" action="{{ route('admin.logout') }}">
      @csrf
      <button class="logout-btn" type="submit">Log Out</button>
    </form>
  </div>

  <!-- Guest List -->
  <div class="section">
    <h3>Guest List</h3>
    @if ($guests->isEmpty())
      <p class="empty">No guests yet.</p>
    @else
      <ul class="guest-list" id="guest-list">
        @foreach ($guests as $guest)
          <li class="guest-row" id="guest-{{ $guest->id }}">
            <div>
              <div class="guest-name">{{ $guest->name }}{{ $guest->stage_name ? ' ("' . $guest->stage_name . '")' : '' }}</div>
              <div class="guest-contact">{{ $guest->contact }}{{ $guest->phone ? ' · ' . $guest->phone : '' }}</div>
            </div>
            <div style="display:flex;align-items:center;gap:10px;">
              <span class="guest-method">{{ $guest->method }}</span>
              <button class="guest-delete" onclick="deleteGuest({{ $guest->id }})" title="Remove">✕</button>
            </div>
          </li>
        @endforeach
      </ul>
    @endif

    <div class="btn-row">
      <button class="btn" onclick="copyPhones()">📱 Copy Phone Numbers</button>
      <button class="btn" onclick="copyEmails()">📋 Copy Email List</button>
      <button class="btn" onclick="openMessages()">💬 Open Messages</button>
    </div>
    <div id="copy-status" class="status"></div>
  </div>

  <!-- Email Blast -->
  <div class="section">
    <h3>Email Blast</h3>
    <label class="field-label">Subject</label>
    <input type="text" id="email-subject" placeholder="The Underground Mic — Party Reminder">
    <label class="field-label">Message Body</label>
    <textarea id="email-body" rows="6" placeholder="Your message here..."></textarea>
    <div class="btn-row">
      <button class="btn" onclick="draftEmail()">✨ Draft with AI</button>
      <button class="btn primary" id="send-btn" onclick="sendEmails()">Send Emails ({{ $guests->whereIn('method', ['email', 'calendar'])->count() }})</button>
    </div>
    <div id="email-status" class="status"></div>
  </div>

  <!-- Text Blast -->
  <div class="section">
    <h3>Text Blast</h3>
    <textarea id="text-body" rows="3" placeholder="Your SMS message..."></textarea>
    <div class="btn-row">
      <button class="btn" onclick="draftText()">✨ Draft with AI</button>
      <button class="btn" onclick="openMessages()">📱 Open Messages App ({{ $guests->filter(fn($g) => $g->phone)->count() }} numbers)</button>
    </div>
    <div id="text-status" class="status"></div>
  </div>

</div>

<script>
const csrf = document.querySelector('meta[name="csrf-token"]').content;

// Store phone/email lists from server-rendered data
const phoneGuests = @json($guests->filter(fn($g) => $g->phone)->values());
const emailGuests = @json($guests->whereIn('method', ['email', 'calendar'])->values());

function showStatus(id, type, msg) {
  const el = document.getElementById(id);
  el.className = 'status ' + type;
  el.textContent = msg;
  el.style.display = 'block';
  if (type === 'success') setTimeout(() => { el.style.display = 'none'; }, 4000);
}

async function deleteGuest(id) {
  if (!confirm('Remove this guest?')) return;
  const res = await fetch('/admin/guests/' + id, {
    method: 'DELETE',
    headers: { 'X-CSRF-TOKEN': csrf },
  });
  if (res.ok) {
    document.getElementById('guest-' + id)?.remove();
  }
}

async function copyPhones() {
  const numbers = phoneGuests.map(g => g.phone).join(', ');
  if (!numbers) { showStatus('copy-status', 'error', 'No phone numbers yet.'); return; }
  try {
    await navigator.clipboard.writeText(numbers);
    showStatus('copy-status', 'success', `Copied ${phoneGuests.length} phone number(s).`);
  } catch {
    showStatus('copy-status', 'error', 'Could not copy. Try again.');
  }
}

async function copyEmails() {
  const emails = emailGuests.map(g => g.contact).join(', ');
  if (!emails) { showStatus('copy-status', 'error', 'No email addresses yet.'); return; }
  try {
    await navigator.clipboard.writeText(emails);
    showStatus('copy-status', 'success', `Copied ${emailGuests.length} email(s).`);
  } catch {
    showStatus('copy-status', 'error', 'Could not copy. Try again.');
  }
}

function openMessages() {
  const numbers = phoneGuests.map(g => g.phone).join(',');
  const body    = encodeURIComponent(document.getElementById('text-body').value);
  window.location.href = `sms:${numbers}${body ? '?&body=' + body : ''}`;
}

async function draftEmail() {
  const btn = event.target;
  btn.disabled = true;
  btn.textContent = 'Drafting...';
  try {
    const res  = await fetch('/admin/draft', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf }, body: JSON.stringify({ type: 'email' }) });
    const data = await res.json();
    if (data.error) { showStatus('email-status', 'error', data.error); return; }
    document.getElementById('email-subject').value = data.subject || '';
    document.getElementById('email-body').value    = data.body || '';
  } catch { showStatus('email-status', 'error', 'Draft failed. Try again.'); }
  finally { btn.disabled = false; btn.textContent = '✨ Draft with AI'; }
}

async function draftText() {
  const btn = event.target;
  btn.disabled = true;
  btn.textContent = 'Drafting...';
  try {
    const res  = await fetch('/admin/draft', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf }, body: JSON.stringify({ type: 'text' }) });
    const data = await res.json();
    if (data.error) { showStatus('text-status', 'error', data.error); return; }
    document.getElementById('text-body').value = data.body || '';
  } catch { showStatus('text-status', 'error', 'Draft failed. Try again.'); }
  finally { btn.disabled = false; btn.textContent = '✨ Draft with AI'; }
}

async function sendEmails() {
  const subject = document.getElementById('email-subject').value;
  const body    = document.getElementById('email-body').value;
  if (!subject || !body) { showStatus('email-status', 'error', 'Subject and body are required.'); return; }
  if (!confirm(`Send to ${emailGuests.length} email guest(s)?`)) return;
  const btn = document.getElementById('send-btn');
  btn.disabled = true;
  btn.textContent = 'Sending...';
  try {
    const res  = await fetch('/admin/send-emails', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf }, body: JSON.stringify({ subject, body }) });
    const data = await res.json();
    if (data.error) { showStatus('email-status', 'error', data.error); return; }
    showStatus('email-status', 'success', `Sent ${data.sent} email(s)${data.failed ? ', ' + data.failed + ' failed.' : '.'}`);
  } catch { showStatus('email-status', 'error', 'Send failed. Try again.'); }
  finally { btn.disabled = false; btn.textContent = `Send Emails (${emailGuests.length})`; }
}
</script>
</body>
</html>
