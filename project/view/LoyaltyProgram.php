<?php
// view/LoyaltyProgram.php
session_start();
if (!isset($_SESSION['status']) || $_SESSION['status'] !== true) {
    header('Location: login.php'); exit;
}
$userId   = (int)($_SESSION['user_id'] ?? 0);
$username = (string)($_SESSION['username'] ?? '');

require_once __DIR__ . '/../model/LoyaltyModel.php';

$state = getLoyaltyPoints($userId); // ['points'=>int,'updated_at'=>..., 'tier'=>computed]
$prog  = getLoyaltyProgress((int)$state['points'], $state['tier'], 5000); // ['nextTierPoints'=>int|string, 'progress'=>0-100]
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Loyalty Program</title>
  <style>
    body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;margin:24px;color:#111}
    .card{max-width:720px;border:1px solid #eee;border-radius:14px;padding:20px;box-shadow:0 2px 10px rgba(0,0,0,.04)}
    .row{display:flex;gap:20px;flex-wrap:wrap}
    .stat{flex:1;min-width:180px;background:#fafafa;border:1px solid #eee;border-radius:12px;padding:16px}
    .bar{height:10px;background:#e8e8e8;border-radius:999px;overflow:hidden}
    .bar>span{display:block;height:100%;width:<?= (int)$prog['progress'] ?>%;background:#4CAF50}
    .muted{color:#666}
    button{padding:10px 14px;border-radius:10px;border:1px solid #ddd;background:#111;color:#fff;cursor:pointer}
    button:disabled{opacity:.5;cursor:not-allowed}
    input[type="number"]{padding:10px;border:1px solid #ddd;border-radius:10px;width:140px}
  </style>
</head>
<body>
  <h1>Loyalty Program</h1>
  <div class="card">
    <p class="muted">Signed in as <strong><?= htmlspecialchars($username) ?></strong></p>

    <div class="row" style="margin-top:10px">
      <div class="stat">
        <div class="muted">Points</div>
        <div style="font-size:28px;font-weight:700"><?= (int)$state['points'] ?></div>
      </div>
      <div class="stat">
        <div class="muted">Tier</div>
        <div style="font-size:28px;font-weight:700"><?= htmlspecialchars($state['tier']) ?></div>
      </div>
      <div class="stat">
        <div class="muted">Last Updated</div>
        <div style="font-size:18px"><?= htmlspecialchars($state['updated_at'] ?? '—') ?></div>
      </div>
    </div>

    <div style="margin:20px 0 6px" class="muted">Progress to next tier</div>
    <div class="bar"><span></span></div>
    <div style="margin-top:8px" class="muted">
      <?php if (is_numeric($prog['nextTierPoints'])): ?>
        <?= (int)$prog['nextTierPoints'] ?> points to next tier
      <?php else: ?>
        <?= htmlspecialchars($prog['nextTierPoints']) ?>
      <?php endif; ?>
    </div>

    <hr style="margin:22px 0;border:0;border-top:1px solid #eee">

    <form id="redeemForm" onsubmit="return redeemPoints(event)">
      <label class="muted" for="redeem">Redeem Points</label><br>
      <input type="number" id="redeem" name="redeemPoints" min="1" step="1" placeholder="e.g. 100">
      <button type="submit">Redeem</button>
      <span id="msg" class="muted" style="margin-left:10px"></span>
    </form>
  </div>

<script>
async function redeemPoints(e){
  e.preventDefault();
  const btn = e.target.querySelector('button');
  const msg = document.getElementById('msg');
  const amt = parseInt(document.getElementById('redeem').value || '0', 10);
  if(!amt || amt<1){ msg.textContent='Enter a positive number'; return false; }
  btn.disabled = true; msg.textContent='Processing…';
  try{
    const res = await fetch('../controller/Loyalty_handler.php', {
      method: 'POST',
      headers: {'Accept':'application/json'},
      body: new URLSearchParams({redeemPoints: String(amt)})
    });
    const data = await res.json();
    if(data.success){
      msg.textContent = 'Redeemed!';
      // Update stats inline
      document.querySelectorAll('.stat')[0].querySelector('div:nth-child(2)').textContent = data.points;
      document.querySelectorAll('.stat')[1].querySelector('div:nth-child(2)').textContent = data.tier;
      const pct = Math.min(100, Math.max(0, (data.progress?.progress ?? 0)));
      document.querySelector('.bar>span').style.width = pct + '%';
    } else {
      msg.textContent = data.message || 'Failed';
    }
  } catch(err){
    msg.textContent = 'Error';
  } finally {
    btn.disabled = false;
  }
  return false;
}
</script>
</body>
</html>
