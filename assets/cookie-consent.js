(function () {
  var key = 'pl_cookie_consent_v1';
  if (localStorage.getItem(key) === 'accepted') {
    return;
  }

  var banner = document.createElement('div');
  banner.setAttribute('role', 'dialog');
  banner.setAttribute('aria-live', 'polite');
  banner.style.cssText = [
    'position:fixed','left:16px','right:16px','bottom:16px','z-index:9999',
    'background:#0f172a','color:#fff','padding:14px 16px','border-radius:10px',
    'box-shadow:0 10px 30px rgba(2,6,23,.35)','font-size:14px'
  ].join(';');

  banner.innerHTML = '<div style="display:flex;gap:12px;align-items:center;justify-content:space-between;flex-wrap:wrap">'
    + '<p style="margin:0;max-width:760px">We use cookies for basic analytics and ad delivery improvements. Read our <a href="/privacy-policy.php" style="color:#93c5fd">Privacy Policy</a>.</p>'
    + '<button id="pl-cookie-accept" style="background:#22c55e;color:#052e16;border:0;padding:8px 12px;border-radius:8px;font-weight:600;cursor:pointer">Accept</button>'
    + '</div>';

  document.body.appendChild(banner);
  var button = document.getElementById('pl-cookie-accept');
  if (button) {
    button.addEventListener('click', function () {
      localStorage.setItem(key, 'accepted');
      banner.remove();
    });
  }
})();
