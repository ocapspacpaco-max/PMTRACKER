<?php
session_start();

/* ---------- SMTP CONFIG (edit these once) ----------
   PMITS_SMTP_USER / PMITS_MAIL_FROM = the Gmail address that SENDS the codes
   PMITS_SMTP_PASS                   = a Gmail App Password (16 characters, NOT your normal password) */
define('PMITS_SMTP_USER', 'your.real.email@gmail.com');
define('PMITS_SMTP_PASS', 'your 16 char app password');
define('PMITS_SMTP_HOST', 'smtp.gmail.com');
define('PMITS_SMTP_PORT', 587);
define('PMITS_SMTP_SECURE', 'tls');                    // 'tls' (587) or 'ssl' (465)
define('PMITS_MAIL_FROM', 'your.real.email@gmail.com');
define('PMITS_SMTP_DEBUG', true);                      // true = show the real email error on screen; set false when finished testing
define('PMITS_SMTP_LOCAL_SSL_FIX', true);              // fixes "certificate verify failed" on XAMPP; set false in production

function cfg($k, $d = '') {
  $v = getenv($k);
  if ($v !== false && $v !== '') return $v;
  return defined($k) ? constant($k) : $d;
}

/* Load PHPMailer: Composer (vendor/) or manual copy (PHPMailer/src/) */
$autoload = __DIR__ . '/vendor/autoload.php';
if (is_file($autoload)) {
  require_once $autoload;
} elseif (is_file(__DIR__ . '/PHPMailer/src/PHPMailer.php')) {
  require_once __DIR__ . '/PHPMailer/src/Exception.php';
  require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
  require_once __DIR__ . '/PHPMailer/src/SMTP.php';
}

$pdo = new PDO('mysql:host=localhost;dbname=pmtracker;charset=utf8mb4', 'root', '', [
  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);

function q($sql, $a = []) { global $pdo; $s = $pdo->prepare($sql); $s->execute($a); return $s; }
function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES); }
function go($u) { header("Location: $u"); exit; }
function me() { return $_SESSION['u'] ?? null; }
function member($pid) { return q("SELECT role FROM project_members WHERE project_id=? AND user_id=?", [$pid, me()['id']])->fetchColumn(); }
function logact($pid, $msg) { q("INSERT INTO activity_log(project_id,user_id,action) VALUES(?,?,?)", [$pid, me()['id'], $msg]); }
function opts($arr, $sel = '') { $o = ''; foreach ($arr as $k => $v) $o .= '<option value="' . h($k) . '"' . ($k == $sel ? ' selected' : '') . '>' . h($v) . '</option>'; return $o; }
function pct($d, $t) { return $t ? (int)round($d / $t * 100) : 0; }
function bar($d, $t) { $p = pct($d, $t); return "<div class='bar'><i style='width:$p%'></i></div><small class='bt'>$d of $t done ($p%)</small>"; }
function tag($c, $t) { return "<span class='tag $c'>" . h($t) . "</span>"; }

function sendResetCode($to, $code) {
  if (!class_exists('\\PHPMailer\\PHPMailer\\PHPMailer')) {
    throw new RuntimeException('PHPMailer is missing. Put it in pmtracker/PHPMailer/src/ or run "composer require phpmailer/phpmailer".');
  }
  $smtpUser = trim((string)cfg('PMITS_SMTP_USER'));
  $smtpPass = (string)cfg('PMITS_SMTP_PASS');
  if ($smtpUser === '' || $smtpPass === '' || $smtpUser === 'your.real.email@gmail.com') {
    throw new RuntimeException('SMTP username/password are not configured.');
  }
  $host   = trim((string)cfg('PMITS_SMTP_HOST', 'smtp.gmail.com'));
  $secure = strtolower(trim((string)cfg('PMITS_SMTP_SECURE', 'tls')));
  $port   = (int)cfg('PMITS_SMTP_PORT', $secure === 'ssl' ? 465 : 587);
  $from   = str_replace(["\r", "\n"], '', trim((string)cfg('PMITS_MAIL_FROM', $smtpUser)));

  $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
  $mail->isSMTP();
  $mail->Host = $host;
  $mail->SMTPAuth = true;
  $mail->Username = $smtpUser;
  $mail->Password = str_replace(' ', '', $smtpPass); // App Passwords are shown with spaces
  $mail->Port = $port;
  $mail->Timeout = 20;
  $mail->CharSet = 'UTF-8';
  $mail->SMTPSecure = $secure === 'ssl'
    ? \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS
    : \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
  if (cfg('PMITS_SMTP_LOCAL_SSL_FIX', false)) {
    $mail->SMTPOptions = ['ssl' => ['verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true]];
  }
  $mail->setFrom($from, 'PMITS Password Reset');
  $mail->addAddress($to);
  $mail->Subject = 'PMITS password reset code';
  $mail->Body = "Your PMITS password reset code is: {$code}\n\nEnter this code on the password reset page. It expires in 10 minutes. If you did not request this, you can ignore this message.";
  $mail->isHTML(false);
  $mail->send();
}
function inproj($table, $id, $pid) { return ($id !== '' && $id !== null && q("SELECT 1 FROM $table WHERE id=? AND project_id=?", [$id, $pid])->fetchColumn()) ? (int)$id : null; }

/* ---------- icons ---------- */
function ic($n, $s = 18) {
  static $I = [
    'layers' => '<path d="m12.83 2.18a2 2 0 0 0-1.66 0L2.6 6.08a1 1 0 0 0 0 1.83l8.58 3.91a2 2 0 0 0 1.66 0l8.58-3.9a1 1 0 0 0 0-1.83Z"/><path d="m22 17.65-9.17 4.16a2 2 0 0 1-1.66 0L2 17.65"/><path d="m22 12.65-9.17 4.16a2 2 0 0 1-1.66 0L2 12.65"/>',
    'folder' => '<path d="M20 20a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.9a2 2 0 0 1-1.69-.9L9.6 3.9A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2Z"/>',
    'plus' => '<path d="M5 12h14"/><path d="M12 5v14"/>',
    'arrow-up-right' => '<path d="M7 7h10v10"/><path d="M7 17 17 7"/>',
    'arrow-right' => '<path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>',
    'chev' => '<path d="m9 18 6-6-6-6"/>',
    'user' => '<path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
    'users' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
    'layout-dashboard' => '<rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/>',
    'columns' => '<rect width="18" height="18" x="3" y="3" rx="2"/><path d="M9 3v18"/><path d="M15 3v18"/>',
    'list-checks' => '<path d="m3 17 2 2 4-4"/><path d="m3 7 2 2 4-4"/><path d="M13 6h8"/><path d="M13 12h8"/><path d="M13 18h8"/>',
    'timer' => '<line x1="10" x2="14" y1="2" y2="2"/><line x1="12" x2="15" y1="14" y2="11"/><circle cx="12" cy="14" r="8"/>',
    'rocket' => '<path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z"/><path d="m12 15-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z"/><path d="M9 12H4s.55-3.03 2-4c1.62-1.08 5 0 5 0"/><path d="M12 15v5s3.03-.55 4-2c1.08-1.62 0-5 0-5"/>',
    'activity' => '<path d="M22 12h-2.48a2 2 0 0 0-1.93 1.46l-2.35 8.36a.25.25 0 0 1-.48 0L9.24 2.18a.25.25 0 0 0-.48 0l-2.35 8.36A2 2 0 0 1 4.49 12H2"/>',
    'circle-dashed' => '<path d="M10.1 2.18a9.93 9.93 0 0 1 3.8 0"/><path d="M17.6 3.71a9.95 9.95 0 0 1 2.69 2.7"/><path d="M21.82 10.1a9.93 9.93 0 0 1 0 3.8"/><path d="M20.29 17.6a9.95 9.95 0 0 1-2.7 2.69"/><path d="M13.9 21.82a9.94 9.94 0 0 1-3.8 0"/><path d="M6.4 20.29a9.95 9.95 0 0 1-2.69-2.7"/><path d="M2.18 13.9a9.93 9.93 0 0 1 0-3.8"/><path d="M3.71 6.4a9.95 9.95 0 0 1 2.7-2.69"/>',
    'circle-dot' => '<circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="1"/>',
    'scan' => '<path d="M3 7V5a2 2 0 0 1 2-2h2"/><path d="M17 3h2a2 2 0 0 1 2 2v2"/><path d="M21 17v2a2 2 0 0 1-2 2h-2"/><path d="M7 21H5a2 2 0 0 1-2-2v-2"/><circle cx="12" cy="12" r="3"/>',
    'circle-check' => '<circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/>',
    'bug' => '<path d="m8 2 1.88 1.88"/><path d="M14.12 3.88 16 2"/><path d="M9 7.13v-1a3.003 3.003 0 1 1 6 0v1"/><path d="M12 20c-3.3 0-6-2.7-6-6v-3a4 4 0 0 1 4-4h4a4 4 0 0 1 4 4v3c0 3.3-2.7 6-6 6"/><path d="M12 20v-9"/><path d="M6.53 9C4.6 8.8 3 7.1 3 5"/><path d="M6 13H2"/><path d="M3 21c0-2.1 1.7-3.9 3.8-4"/><path d="M20.97 5c0 2.1-1.6 3.8-3.5 4"/><path d="M22 13h-4"/><path d="M17.2 17c2.1.1 3.8 1.9 3.8 4"/>',
    'file-text' => '<path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M10 9H8"/><path d="M16 13H8"/><path d="M16 17H8"/>',
    'book-open' => '<path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>',
    'square-check' => '<rect width="18" height="18" x="3" y="3" rx="2"/><path d="m9 12 2 2 4-4"/>',
    'arrow-down' => '<path d="M12 5v14"/><path d="m19 12-7 7-7-7"/>',
    'equal' => '<line x1="5" x2="19" y1="9" y2="9"/><line x1="5" x2="19" y1="15" y2="15"/>',
    'arrow-up' => '<path d="m5 12 7-7 7 7"/><path d="M12 19V5"/>',
    'chevrons-up' => '<path d="m17 11-5-5-5 5"/><path d="m17 18-5-5-5 5"/>',
    'info' => '<circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/>',
    'logout' => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/>',
  ];
  return "<svg class='ic' width='$s' height='$s' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='1.8' stroke-linecap='round' stroke-linejoin='round'>" . ($I[$n] ?? '') . "</svg>";
}

/* Put your PMITS logo next to index.php as logo.png and it is used automatically */
function logo() {
  if (file_exists(__DIR__ . '/logo.png')) return "<img src='logo.png' alt='PMITS' style='width:170px;max-width:100%;height:auto'>";
  return "<svg width='96' height='80' viewBox='0 0 96 80' fill='none'><defs><linearGradient id='lg' x1='0' y1='0' x2='1' y2='1'><stop offset='0' stop-color='#3b82f6'/><stop offset='1' stop-color='#a855f7'/></linearGradient></defs><rect x='8' y='12' width='62' height='52' rx='8' fill='#1e3a8a' stroke='url(#lg)' stroke-width='4'/><circle cx='18' cy='22' r='2' fill='#fff'/><circle cx='25' cy='22' r='2' fill='#fff'/><circle cx='32' cy='22' r='2' fill='#fff'/><path d='M18 34h36M18 44h36M18 54h24' stroke='#fff' stroke-width='4' stroke-linecap='round'/><circle cx='70' cy='54' r='14' fill='url(#lg)'/><path d='M64 54h12M70 48v12' stroke='#fff' stroke-width='3' stroke-linecap='round'/></svg><span class='wm'>PMITS</span>";
}

$ST  = ['todo' => 'To Do', 'in_progress' => 'In Progress', 'review' => 'In Review', 'done' => 'Done'];
$STI = ['todo' => 'circle-dashed', 'in_progress' => 'circle-dot', 'review' => 'scan', 'done' => 'circle-check'];
$TY  = ['requirement' => 'Requirement', 'story' => 'User Story', 'task' => 'Task', 'bug' => 'Bug'];
$TYP = ['requirement' => 'Requirements', 'story' => 'User Stories', 'task' => 'Tasks', 'bug' => 'Bugs'];
$TYI = ['requirement' => 'file-text', 'story' => 'book-open', 'task' => 'square-check', 'bug' => 'bug'];
$PR  = ['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'critical' => 'Critical'];
$PRI = ['low' => 'arrow-down', 'medium' => 'equal', 'high' => 'arrow-up', 'critical' => 'chevrons-up'];
$RL  = ['manager' => 'Manager', 'developer' => 'Developer', 'tester' => 'Tester'];
$TABS = ['overview' => ['Dashboard', 'layout-dashboard'], 'board' => ['Kanban Board', 'columns'], 'items' => ['Backlog & Issues', 'list-checks'],
         'sprints' => ['Sprints', 'timer'], 'releases' => ['Releases', 'rocket'], 'team' => ['Team', 'users'], 'activity' => ['Activity', 'activity']];
$CAP = ['overview' => ['Dashboard', 'Your project at a glance'], 'board' => ['Kanban Board', 'Move work from To Do to Done'],
        'items' => ['Backlog & Issues', 'Everything that needs to be done'], 'item' => ['Edit Item', 'Update the details'],
        'sprints' => ['Sprints', 'Short work periods with a goal'], 'releases' => ['Releases', 'Versions you deliver'],
        'team' => ['Team', 'Who works on this project'], 'activity' => ['Activity', 'Recent changes in this project']];

$err = $msg = '';
if (!empty($_SESSION['reset_notice'])) { $msg = $_SESSION['reset_notice']; unset($_SESSION['reset_notice']); }
if (!empty($_SESSION['reset_error'])) { $err = $_SESSION['reset_error']; unset($_SESSION['reset_error']); }
if (isset($_GET['registered'])) $msg = 'Account created! You can log in now.';
if (isset($_GET['reset'])) $msg = 'Password updated! You can log in now.';
if (isset($_GET['added'])) $msg = 'Member added to the team.';
if (($_GET['err'] ?? '') == 'nouser') $err = 'No registered user with that email. Ask them to register first.';

/* ---------- actions ---------- */
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $a = $_POST['a'] ?? '';
  if ($a == 'register') {
    try { q("INSERT INTO users(name,email,password) VALUES(?,?,?)", [trim($_POST['name']), trim($_POST['email']), password_hash($_POST['password'], PASSWORD_DEFAULT)]); go('index.php?registered=1'); }
    catch (Exception $e) { $err = 'That email is already registered.'; }
  } elseif ($a == 'login') {
    $u = q("SELECT * FROM users WHERE email=?", [trim($_POST['email'])])->fetch();
    if ($u && password_verify($_POST['password'], $u['password'])) { $_SESSION['u'] = ['id' => $u['id'], 'name' => $u['name']]; go('index.php'); }
    $err = 'Invalid email or password.';

  /* Step 1: user enters email -> a 6-digit code is emailed */
  } elseif ($a == 'forgot') {
    $email = strtolower(trim($_POST['email'] ?? ''));
    $u = q("SELECT id FROM users WHERE email=?", [$email])->fetch();
    $_SESSION['reset_email'] = $email;
    unset($_SESSION['password_reset']);
    $_SESSION['reset_attempts'] = 0;
    $failed = false;
    if ($u) {
      $code = (string)random_int(100000, 999999);
      q("DELETE FROM password_resets WHERE user_id=?", [$u['id']]);
      q("INSERT INTO password_resets(user_id,token_hash,expires_at) VALUES(?,?,DATE_ADD(NOW(), INTERVAL 10 MINUTE))", [$u['id'], hash('sha256', $code)]);
      try {
        sendResetCode($email, $code);
      } catch (Throwable $e) {
        $failed = true;
        error_log('PMITS reset email error: ' . $e->getMessage());
        q("DELETE FROM password_resets WHERE user_id=?", [$u['id']]);
        if (cfg('PMITS_SMTP_DEBUG', false)) $_SESSION['reset_error'] = 'Email failed: ' . $e->getMessage();
      }
    }
    if (!($failed && cfg('PMITS_SMTP_DEBUG', false))) {
      $_SESSION['reset_notice'] = 'If that email is registered, a 6-digit code has been sent. Check your inbox and spam folder.';
    }
    go('index.php?p=reset');

  /* Step 2: user pastes the code from the email */
  } elseif ($a == 'verify_reset_code') {
    $email = strtolower(trim($_POST['email'] ?? ''));
    $code = preg_replace('/\D/', '', (string)($_POST['code'] ?? ''));
    $attempts = (int)($_SESSION['reset_attempts'] ?? 0);
    $u = q("SELECT id FROM users WHERE email=?", [$email])->fetch();
    $row = $u ? q("SELECT token_hash FROM password_resets WHERE user_id=? AND expires_at>NOW()", [$u['id']])->fetch() : false;
    if ($attempts >= 5) {
      $err = 'Too many incorrect attempts. Request a new reset code.';
    } elseif ($u && $row && strlen($code) === 6 && hash_equals($row['token_hash'], hash('sha256', $code))) {
      $_SESSION['password_reset'] = ['user_id' => (int)$u['id'], 'expires' => time() + 600];
      unset($_SESSION['reset_attempts']);
      q("DELETE FROM password_resets WHERE user_id=?", [$u['id']]);
      go('index.php?p=reset&step=password');
    } else {
      $_SESSION['reset_attempts'] = $attempts + 1;
      $err = 'The reset code is invalid or expired. Check the code or request a new one.';
    }

  /* Step 3: code confirmed -> set the new password */
  } elseif ($a == 'reset_password') {
    $reset = $_SESSION['password_reset'] ?? null;
    if (!is_array($reset) || empty($reset['user_id']) || (int)($reset['expires'] ?? 0) < time()) {
      unset($_SESSION['password_reset']);
      $err = 'Your code confirmation has expired. Request a new reset code.';
    } elseif (strlen($_POST['password'] ?? '') < 6) $err = 'Password must be at least 6 characters.';
    elseif (($_POST['password'] ?? '') !== ($_POST['password2'] ?? '')) $err = 'Passwords do not match.';
    else {
      q("UPDATE users SET password=? WHERE id=?", [password_hash($_POST['password'], PASSWORD_DEFAULT), (int)$reset['user_id']]);
      q("DELETE FROM password_resets WHERE user_id=?", [(int)$reset['user_id']]);
      unset($_SESSION['password_reset'], $_SESSION['reset_email'], $_SESSION['reset_attempts']);
      go('index.php?reset=1');
    }
  } elseif (me()) {
    if ($a == 'new_project') {
      q("INSERT INTO projects(name,description,owner_id) VALUES(?,?,?)", [$_POST['name'], $_POST['description'], me()['id']]);
      $id = $pdo->lastInsertId();
      q("INSERT INTO project_members VALUES(?,?,'manager')", [$id, me()['id']]);
      logact($id, 'created the project');
      go("index.php?p=project&id=$id");
    }
    $pid = (int)($_POST['pid'] ?? 0);
    $role = member($pid);
    if (!$role) die('Forbidden');
    $mgr = $role == 'manager';
    $back = "index.php?p=project&id=$pid&t=";

    if ($a == 'save_item') {
      $iid = (int)($_POST['iid'] ?? 0);
      $title = trim($_POST['title'] ?? '');
      if ($title === '') go($back . 'items');
      $type = array_key_exists($_POST['type'] ?? '', $TY) ? $_POST['type'] : 'task';
      $pri = array_key_exists($_POST['priority'] ?? '', $PR) ? $_POST['priority'] : 'medium';
      $st = array_key_exists($_POST['status'] ?? '', $ST) ? $_POST['status'] : 'todo';
      $as = $_POST['assignee_id'] ?? '';
      $as = ($as !== '' && q("SELECT 1 FROM project_members WHERE project_id=? AND user_id=?", [$pid, $as])->fetchColumn()) ? (int)$as : null;
      $sp = inproj('sprints', $_POST['sprint_id'] ?? '', $pid);
      $rel = inproj('releases', $_POST['release_id'] ?? '', $pid);
      $f = [$type, $title, $_POST['description'] ?? '', $pri, $st, $as, $sp, $rel];
      if ($iid) {
        q("UPDATE items SET type=?,title=?,description=?,priority=?,status=?,assignee_id=?,sprint_id=?,release_id=? WHERE id=? AND project_id=?", array_merge($f, [$iid, $pid]));
        logact($pid, "updated {$TY[$type]} #$iid: $title");
      } else {
        q("INSERT INTO items(type,title,description,priority,status,assignee_id,sprint_id,release_id,points,project_id,reporter_id) VALUES(?,?,?,?,?,?,?,?,0,?,?)", array_merge($f, [$pid, me()['id']]));
        logact($pid, "created {$TY[$type]}: $title");
      }
      go($back . 'items');
    } elseif ($a == 'move') {
      $iid = (int)($_POST['iid'] ?? 0);
      if (array_key_exists($_POST['status'] ?? '', $ST)) {
        q("UPDATE items SET status=? WHERE id=? AND project_id=?", [$_POST['status'], $iid, $pid]);
        logact($pid, "moved item #$iid to " . $ST[$_POST['status']]);
      }
      $sf = $_POST['sprint'] ?? '';
      go($back . 'board' . ($sf !== '' ? '&sprint=' . urlencode($sf) : ''));
    } elseif ($a == 'assign') {
      $iid = (int)($_POST['iid'] ?? 0);
      $field = ($_POST['field'] ?? '') == 'release' ? 'release' : 'sprint';
      $val = inproj($field == 'release' ? 'releases' : 'sprints', $_POST['val'] ?? '', $pid);
      if ($iid) {
        q("UPDATE items SET {$field}_id=? WHERE id=? AND project_id=?", [$val, $iid, $pid]);
        logact($pid, $val ? "added item #$iid to $field #$val" : "removed item #$iid from its $field");
      }
      go($back . ($field == 'release' ? 'releases' : 'sprints'));
    } elseif ($a == 'delete_item' && $mgr) {
      q("DELETE FROM items WHERE id=? AND project_id=?", [(int)$_POST['iid'], $pid]);
      logact($pid, "deleted item #" . (int)$_POST['iid']);
      go($back . 'items');
    } elseif ($a == 'add_member' && $mgr) {
      $u = q("SELECT id FROM users WHERE email=?", [trim($_POST['email'])])->fetchColumn();
      if ($u) { q("INSERT IGNORE INTO project_members VALUES(?,?,?)", [$pid, $u, array_key_exists($_POST['role'], $RL) ? $_POST['role'] : 'developer']); logact($pid, 'added ' . trim($_POST['email']) . ' to the team'); }
      go($back . 'team' . ($u ? '&added=1' : '&err=nouser'));
    } elseif ($a == 'new_sprint' && $mgr) {
      if (trim($_POST['name']) !== '') {
        q("INSERT INTO sprints(project_id,name,goal,start_date,end_date) VALUES(?,?,?,?,?)", [$pid, trim($_POST['name']), $_POST['goal'], $_POST['start_date'] ?: null, $_POST['end_date'] ?: null]);
        logact($pid, 'created sprint ' . trim($_POST['name']));
      }
      go($back . 'sprints');
    } elseif ($a == 'sprint_state' && $mgr) {
      $s = in_array($_POST['status'], ['planned', 'active', 'completed']) ? $_POST['status'] : 'planned';
      q("UPDATE sprints SET status=? WHERE id=? AND project_id=?", [$s, (int)$_POST['sid'], $pid]);
      logact($pid, "set sprint #" . (int)$_POST['sid'] . " to $s");
      go($back . 'sprints');
    } elseif ($a == 'new_release' && $mgr) {
      if (trim($_POST['version']) !== '') {
        q("INSERT INTO releases(project_id,version,release_date,notes) VALUES(?,?,?,?)", [$pid, trim($_POST['version']), $_POST['release_date'] ?: null, $_POST['notes']]);
        logact($pid, 'created release ' . trim($_POST['version']));
      }
      go($back . 'releases');
    }
  }
}

/* ---------- page + project context ---------- */
$p = $_GET['p'] ?? 'home'; if (!is_string($p)) $p = 'home';
if ($p == 'logout') { session_destroy(); go('index.php'); }
if ($p == 'register' && me()) go('index.php');
if (in_array($p, ['forgot', 'reset']) && me()) go('index.php');

$t = $_GET['t'] ?? 'overview'; if (!is_string($t) || !isset($CAP[$t])) $t = 'overview';
$pid = 0; $role = false;
if (me() && $p == 'project') {
  $pid = (int)($_GET['id'] ?? 0);
  $role = $pid ? member($pid) : false;
  if ($role) {
    $mgr = $role == 'manager';
    $proj = q("SELECT * FROM projects WHERE id=?", [$pid])->fetch();
    $members = q("SELECT u.id,u.name,u.email,m.role FROM project_members m JOIN users u ON u.id=m.user_id WHERE m.project_id=?", [$pid])->fetchAll();
    $mopts = ['' => '— Nobody —']; foreach ($members as $m) $mopts[$m['id']] = $m['name'];
    $sprints = q("SELECT * FROM sprints WHERE project_id=? ORDER BY id DESC", [$pid])->fetchAll();
    $sopts = ['' => '— None (backlog) —']; foreach ($sprints as $s) $sopts[$s['id']] = $s['name'];
    $rels = q("SELECT * FROM releases WHERE project_id=? ORDER BY id DESC", [$pid])->fetchAll();
    $ropts = ['' => '— None —']; foreach ($rels as $r) $ropts[$r['id']] = $r['version'];
  }
}
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>PMITS – Project Management and Issue Tracker</title>
<style>
*{box-sizing:border-box}
html{min-height:100%}
body{margin:0;min-height:100vh;font-family:Inter,system-ui,-apple-system,'Segoe UI',Roboto,sans-serif;color:#eef2ff;
  background:radial-gradient(900px 600px at 100% 100%,#1a3dff 0%,transparent 60%),linear-gradient(135deg,#2a0e66 0%,#1a1fb0 32%,#1232ee 70%,#0f2bd8 100%);background-attachment:fixed}
a{color:inherit}
.ic{flex:none;vertical-align:middle}
h1,h2,h3,h4,p{margin:0}
small{font-size:12px;color:rgba(210,220,255,.7)}

/* ---------- form controls ---------- */
input,select,textarea,button{font:inherit;color:#fff}
input,select,textarea{width:100%;padding:10px 12px;border-radius:10px;border:1px solid rgba(255,255,255,.18);background:rgba(8,14,110,.45);outline:none}
input::placeholder,textarea::placeholder{color:rgba(220,228,255,.55)}
input:focus,select:focus,textarea:focus{border-color:rgba(160,185,255,.7);box-shadow:0 0 0 3px rgba(120,150,255,.2)}
select option{background:#16208f;color:#fff}
textarea{resize:vertical}
input[type=date]{color-scheme:dark}
button,.btn{cursor:pointer;display:inline-flex;align-items:center;gap:6px;padding:9px 16px;border-radius:10px;border:1px solid rgba(190,170,255,.5);background:rgba(140,110,255,.55);font-weight:600}
button:hover{background:rgba(150,125,255,.78)}
button.gray{background:rgba(255,255,255,.1);border-color:rgba(255,255,255,.2)}
button.red{background:rgba(220,38,38,.6);border-color:rgba(252,165,165,.5)}
button.sm{padding:4px 10px;font-size:13px;border-radius:8px}
form.inline{display:inline}
form.inline select{width:auto;max-width:260px;padding:6px 10px}
label{display:block;font-size:12px;color:rgba(210,220,255,.75);margin:0 0 6px}

/* ---------- glass ---------- */
.glass{background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.16);border-radius:18px;padding:18px;backdrop-filter:blur(8px);margin-bottom:14px}
.note{padding:10px 14px;border-radius:12px;margin-bottom:14px;font-size:14px;border:1px solid rgba(255,255,255,.2)}
.note.err{background:rgba(220,38,38,.25);border-color:rgba(252,165,165,.5)}
.note.ok{background:rgba(34,197,94,.22);border-color:rgba(134,239,172,.5)}

/* ---------- login / register ---------- */
.auth-frame{min-height:calc(100vh - 24px);margin:12px;border-radius:22px;border:1px solid rgba(255,255,255,.14);display:flex;flex-direction:column;align-items:center;justify-content:center;padding:30px 16px;gap:14px}
.logo{text-align:center}
.logo .wm{display:block;margin-top:-4px;font-weight:800;font-size:30px;letter-spacing:1px;background:linear-gradient(90deg,#3b82f6,#a855f7);-webkit-background-clip:text;background-clip:text;color:transparent}
.welcome{font-size:18px;margin-bottom:6px}
.acard{width:280px;padding:16px;border-radius:22px;border:1px solid rgba(255,255,255,.2);background:rgba(255,255,255,.07);backdrop-filter:blur(10px);display:flex;flex-direction:column;gap:12px}
.acard h3{text-align:center;font-weight:400;font-size:26px}
.acard button{align-self:center;padding:5px 20px}
.alink{font-size:14px}.alink a{text-decoration:underline}
.auth-frame .note{width:280px;text-align:center;margin:0}

/* ---------- app shell ---------- */
.shell{display:grid;grid-template-columns:232px minmax(0,1fr);gap:14px;padding:14px;min-height:100vh}
.side{position:sticky;top:14px;height:calc(100vh - 28px);display:flex;flex-direction:column;gap:3px;padding:16px 12px;border-radius:20px;border:1px solid rgba(255,255,255,.16);background:rgba(8,12,100,.35);backdrop-filter:blur(10px);overflow-y:auto}
.brand{display:flex;align-items:center;gap:10px;font-weight:600;font-size:15px;padding:2px 6px 14px}
.bi,.ib{display:grid;place-items:center;width:34px;height:34px;border-radius:10px;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.2);flex:none}
.ib.big{width:46px;height:46px;border-radius:12px}
.lbl{font-size:10px;letter-spacing:.08em;text-transform:uppercase;color:rgba(200,212,255,.6);padding:14px 8px 4px}
.nv{display:flex;align-items:center;gap:10px;padding:9px 10px;border-radius:10px;text-decoration:none;font-size:13px;color:rgba(230,236,255,.85);border:1px solid transparent}
.nv:hover{background:rgba(255,255,255,.08)}
.nv.on{background:rgba(255,255,255,.16);border-color:rgba(255,255,255,.18);color:#fff}
.who{margin-top:auto;display:flex;align-items:center;gap:10px;padding:12px 6px 2px;border-top:1px solid rgba(255,255,255,.14)}
.who div{flex:1;min-width:0;line-height:1.25}
.who b{display:block;font-size:12px;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.av{display:grid;place-items:center;width:30px;height:30px;border-radius:50%;background:rgba(255,255,255,.16);border:1px solid rgba(255,255,255,.22);font-size:12px;font-weight:600;flex:none}
.lo{padding:6px;border-radius:8px;display:grid;place-items:center;opacity:.8}.lo:hover{background:rgba(255,255,255,.12);opacity:1}
.panel{border-radius:20px;border:1px solid rgba(255,255,255,.16);background:rgba(8,12,100,.28);backdrop-filter:blur(10px);display:flex;flex-direction:column;min-width:0}
.top{display:flex;justify-content:space-between;align-items:center;padding:14px 22px;border-bottom:1px solid rgba(255,255,255,.12);font-size:12px}
.crumbs{display:flex;align-items:center;gap:8px;color:rgba(230,236,255,.85)}.crumbs a{text-decoration:none}
.body{padding:22px;flex:1;min-width:0}

/* ---------- page headings ---------- */
.ptitle{display:flex;justify-content:space-between;align-items:flex-start;gap:12px;margin-bottom:18px}
.ptitle h1{font-size:26px;font-weight:700}
.sub{font-size:13px;color:rgba(215,224,255,.75);margin-top:4px}
.pill{display:inline-flex;align-items:center;gap:6px;padding:5px 12px;border-radius:999px;border:1px solid rgba(255,255,255,.2);background:rgba(255,255,255,.08);font-size:12px;white-space:nowrap}
.phead{display:flex;align-items:center;gap:14px;margin-bottom:16px}
.phead>div{flex:1;min-width:0}
.phead h1{font-size:24px;font-weight:700;line-height:1.1}
.trow{display:flex;justify-content:space-between;align-items:baseline;gap:10px;margin-bottom:14px}
.trow h2{font-size:17px;font-weight:500}
.hint{font-size:13px;color:rgba(215,224,255,.7);margin-bottom:14px}
.between{display:flex;justify-content:space-between;align-items:center;gap:10px}
.ch{display:flex;align-items:center;gap:10px;margin-bottom:14px}
.ch h3{font-size:16px;font-weight:600}
.ch.between{justify-content:space-between}

/* ---------- projects home ---------- */
.two{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1.1fr);gap:14px}
.two .glass{margin-bottom:0}
.newp .f{margin-bottom:12px}
.frow{display:flex;justify-content:space-between;align-items:center;margin-top:18px}
.tile{display:flex;flex-direction:column;gap:6px;text-decoration:none;padding:16px;border-radius:16px;border:1px solid rgba(255,255,255,.2);background:rgba(255,255,255,.07);margin-bottom:12px;transition:background .15s}
.tile:hover{background:rgba(255,255,255,.13)}
.tile h4{font-size:20px;font-weight:700;margin-top:8px}
.role{display:flex;align-items:center;gap:6px;font-size:12px;color:rgba(215,224,255,.8)}
.prow{display:flex;justify-content:space-between;font-size:11px;color:rgba(215,224,255,.8);margin-top:10px}
.open{display:flex;justify-content:space-between;align-items:center;margin-top:12px;padding-top:12px;border-top:1px solid rgba(255,255,255,.14);font-size:12px;color:#7dd3fc}
.strip{display:flex;align-items:center;gap:14px;margin:14px 0 0;padding:14px 18px;border-radius:14px}
.strip b{display:block;font-size:13px;font-weight:600}

/* ---------- dashboard ---------- */
.stats{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:12px;margin-bottom:14px}
.stat{margin:0;padding:14px}
.stat .sh{display:flex;justify-content:space-between;align-items:center;font-size:11px;color:rgba(215,224,255,.8)}
.stat b{display:block;font-size:30px;font-weight:400;margin-top:10px}
.prog{display:flex;align-items:center;gap:18px}
.pg{flex:1;min-width:0;display:flex;flex-direction:column;gap:8px}
.ring{position:relative;width:66px;height:66px;display:grid;place-items:center;font-size:15px;flex:none}
.ring:before{content:'';position:absolute;inset:0;border-radius:50%;background:conic-gradient(#9db8ff calc(var(--p)*1%),rgba(255,255,255,.14) 0);-webkit-mask:radial-gradient(farthest-side,transparent calc(100% - 5px),#000 calc(100% - 4px));mask:radial-gradient(farthest-side,transparent calc(100% - 5px),#000 calc(100% - 4px))}
.two2{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}
.two2 .glass{margin-bottom:0}
.mrow{display:grid;grid-template-columns:22px 110px 1fr 28px;align-items:center;gap:10px;padding:9px 0;font-size:12px}
.mrow b{text-align:right;font-weight:500}
.foot{display:flex;align-items:center;gap:8px;margin-top:16px;font-size:11px;color:rgba(200,212,255,.6)}
.bar{height:6px;border-radius:99px;background:rgba(255,255,255,.14);overflow:hidden}
.bar i{display:block;height:100%;background:linear-gradient(90deg,#7dd3fc,#a5b4fc);border-radius:99px}
.bar.thin{height:4px}
.bt{display:block;margin-top:6px}

/* ---------- board / lists ---------- */
.kan{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px}
.col{background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.14);border-radius:14px;padding:12px;min-height:130px}
.col h4{font-size:13px;margin-bottom:10px;font-weight:600}
.kc{background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.16);border-radius:12px;padding:10px;margin-bottom:10px;font-size:13px}
.kc a{color:#c7d6ff;display:block;margin:6px 0 2px}
.mv{margin-top:8px}.mv button{padding:2px 10px;margin-right:4px}
.qa{display:flex;gap:8px;flex-wrap:wrap;align-items:center}
.qa input[name=title]{flex:1;min-width:220px;width:auto}
.qa select{width:auto}
.pills{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:14px}
.pills a{padding:7px 14px;border-radius:999px;font-size:13px;text-decoration:none;border:1px solid rgba(255,255,255,.18);background:rgba(255,255,255,.06)}
.pills a:hover{background:rgba(255,255,255,.12)}
.pills a.on{background:rgba(140,110,255,.55);border-color:rgba(190,170,255,.6)}
table{width:100%;border-collapse:collapse}
th,td{text-align:left;padding:10px 8px;border-bottom:1px solid rgba(255,255,255,.1);font-size:13px}
th{color:rgba(200,212,255,.65);font-weight:500}
td a{color:#c7d6ff}
.tag{font-size:11px;padding:2px 9px;border-radius:999px;background:rgba(255,255,255,.14);white-space:nowrap}
.critical{background:rgba(248,113,113,.38)}.high{background:rgba(251,146,60,.38)}.medium{background:rgba(250,204,21,.25)}.bug{background:rgba(250,204,21,.3)}
.s-todo,.s-planned{background:rgba(255,255,255,.16)}.s-in_progress,.s-active{background:rgba(96,165,250,.4)}.s-review{background:rgba(250,204,21,.32)}.s-done,.s-completed{background:rgba(74,222,128,.35)}
.fgrid{display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:12px}
.f{margin-bottom:12px}
.card-h{font-size:16px;font-weight:600;margin-bottom:12px}

/* ---------- tablet / mobile ---------- */
@media(max-width:900px){
  body{background-attachment:scroll}
  .shell{grid-template-columns:minmax(0,1fr);gap:10px;padding:10px;min-height:100vh}
  /* sidebar becomes a sticky, horizontally scrollable top bar */
  .side{position:sticky;top:8px;z-index:30;height:auto;flex-direction:row;align-items:center;gap:6px;padding:8px 10px;overflow-x:auto;overflow-y:hidden;-webkit-overflow-scrolling:touch;scrollbar-width:none}
  .side::-webkit-scrollbar{display:none}
  .side>*{flex:none}
  .brand{padding:0 6px 0 2px;font-size:0}
  .lbl{display:none}
  .nv{white-space:nowrap;padding:8px 12px}
  .who{margin:0 0 0 auto;padding:0 0 0 8px;border-top:0;border-left:1px solid rgba(255,255,255,.14)}
  .who div{display:none}
  .two,.two2{grid-template-columns:minmax(0,1fr)}
  .stats{grid-template-columns:repeat(2,minmax(0,1fr))}
  .kan{grid-template-columns:repeat(2,minmax(0,1fr))}
  .glass{overflow-x:auto}
  th,td{white-space:nowrap}
}
@media(max-width:600px){
  input,select,textarea{font-size:16px} /* stops iOS zoom-on-focus */
  .shell{padding:8px;gap:8px}
  .top{padding:12px 14px}
  .top>div:last-child{display:none}
  .crumbs{min-width:0}
  .crumbs span{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
  .body{padding:14px}
  .ptitle,.phead{flex-wrap:wrap}
  .ptitle h1{font-size:22px}
  .phead h1{font-size:20px}
  .trow{flex-direction:column;align-items:flex-start;gap:2px}
  .glass{padding:14px;border-radius:16px}
  .stat b{font-size:24px}
  .prog{gap:12px}
  .mrow{grid-template-columns:20px 82px 1fr 24px;gap:8px}
  .kan{grid-template-columns:minmax(0,1fr)}
  .fgrid{grid-template-columns:minmax(0,1fr)}
  .qa{flex-direction:column;align-items:stretch}
  .qa input,.qa select,.qa button{width:100%!important;min-width:0!important;flex:none}
  .qa button{justify-content:center}
  form.inline{display:block}
  form.inline select{width:100%;max-width:none;margin-bottom:8px}
  .pills{flex-wrap:nowrap;overflow-x:auto;padding-bottom:4px}
  .pills a{flex:none}
  .tile h4{font-size:18px}
  .auth-frame{margin:8px;padding:24px 12px;min-height:calc(100vh - 16px)}
  .acard,.auth-frame .note{width:100%;max-width:320px}
}
</style></head>
<body class="<?= me() ? 'app' : 'auth' ?>">
<?php if (!me()): ?>
<div class="auth-frame">
  <div class="logo"><?= logo() ?></div>
  <?php if (!in_array($p, ['register', 'forgot', 'reset'])): ?><p class="welcome">Hi, Welcome to PMITS!</p><?php endif; ?>
  <?php if ($err) echo "<div class='note err'>" . h($err) . "</div>"; ?>
  <?php if ($msg) echo "<div class='note ok'>" . h($msg) . "</div>"; ?>
  <?php if ($p == 'register'): ?>
  <form class="acard" method="post" action="index.php?p=register">
    <h3>Register</h3>
    <input type="hidden" name="a" value="register">
    <input name="name" placeholder="Full name" required>
    <input name="email" type="email" placeholder="Email" required>
    <input name="password" type="password" placeholder="Password" required minlength="6">
    <button>Register</button>
  </form>
  <p class="alink">Already have an account? <a href="index.php">Login</a></p>

  <?php elseif ($p == 'forgot'): ?>
  <form class="acard" method="post" action="index.php?p=forgot">
    <h3>Forgot Password</h3>
    <small style="text-align:center">Enter your account email and we'll send a 6-digit reset code.</small>
    <input type="hidden" name="a" value="forgot">
    <input name="email" type="email" placeholder="Email" required autocomplete="email">
    <button>Send Reset Code</button>
  </form>
  <p class="alink"><a href="index.php">Back to Login</a></p>

  <?php elseif ($p == 'reset'):
    $resetAuth = $_SESSION['password_reset'] ?? null;
    $resetVerified = is_array($resetAuth) && !empty($resetAuth['user_id']) && (int)($resetAuth['expires'] ?? 0) >= time();
    if (!$resetVerified && is_array($resetAuth)) { unset($_SESSION['password_reset']); }
    $resetEmail = (string)($_SESSION['reset_email'] ?? '');
    if ($resetVerified): ?>
  <form class="acard" method="post" action="index.php?p=reset">
    <h3>New Password</h3>
    <small style="text-align:center">Code confirmed. Choose a new password for your account.</small>
    <input type="hidden" name="a" value="reset_password">
    <input name="password" type="password" placeholder="New password" required minlength="6" autocomplete="new-password">
    <input name="password2" type="password" placeholder="Confirm new password" required minlength="6" autocomplete="new-password">
    <button>Update Password</button>
  </form>
    <?php elseif ($resetEmail !== ''): ?>
  <form class="acard" method="post" action="index.php?p=reset">
    <h3>Confirm Reset Code</h3>
    <small style="text-align:center">Paste the 6-digit code sent to your email. It expires in 10 minutes.</small>
    <input type="hidden" name="a" value="verify_reset_code">
    <input type="hidden" name="email" value="<?= h($resetEmail) ?>">
    <input name="code" type="text" inputmode="numeric" autocomplete="one-time-code" placeholder="6-digit reset code" required minlength="6" maxlength="6" pattern="[0-9]{6}">
    <button>Confirm Code</button>
  </form>
  <p class="alink"><a href="index.php?p=forgot">Request a new code</a></p>
    <?php else: ?>
  <div class="note err">Request a reset code before entering it.</div>
  <p class="alink"><a href="index.php?p=forgot">Request a reset code</a></p>
    <?php endif; ?>

  <?php else: ?>
  <form class="acard" method="post">
    <h3>Login</h3>
    <input type="hidden" name="a" value="login">
    <input name="email" type="email" placeholder="Email" required>
    <input name="password" type="password" placeholder="Password" required>
    <button>Login</button>
  </form>
  <p class="alink"><a href="index.php?p=forgot">Forgot password?</a></p>
  <p class="alink">Don't have an account? <a href="index.php?p=register">Register</a></p>
  <?php endif; ?>
</div>
<?php else: ?>
<div class="shell">
  <aside class="side">
    <div class="brand"><span class="bi"><?= ic('layers', 18) ?></span>Issue Tracker</div>
    <div class="lbl">Workspace</div>
    <a class="nv<?= $p != 'project' ? ' on' : '' ?>" href="index.php"><?= ic('folder', 16) ?>Your Projects</a>
    <?php if ($role): ?>
      <div class="lbl"><?= h($proj['name']) ?></div>
      <?php foreach ($TABS as $k => [$lab, $icn]): ?>
        <a class="nv<?= ($t == $k || ($t == 'item' && $k == 'items')) ? ' on' : '' ?>" href="index.php?p=project&id=<?= $pid ?>&t=<?= $k ?>"><?= ic($icn, 16) ?><?= h($lab) ?></a>
      <?php endforeach; ?>
    <?php endif; ?>
    <div class="who">
      <span class="av"><?= h(strtoupper(mb_substr(me()['name'], 0, 1))) ?></span>
      <div><b><?= h(me()['name']) ?></b><small>Workspace</small></div>
      <a class="lo" href="index.php?p=logout" title="Logout"><?= ic('logout', 16) ?></a>
    </div>
  </aside>

  <section class="panel">
    <div class="top">
      <div class="crumbs"><?= ic('folder', 15) ?><a href="index.php">Projects</a><?php if ($role): ?><?= ic('chev', 14) ?><span><?= h($proj['name']) ?></span><?php endif; ?></div>
      <div>Hi, <?= h(me()['name']) ?></div>
    </div>
    <div class="body">
<?php
if ($err) echo "<div class='note err'>" . h($err) . "</div>";
if ($msg) echo "<div class='note ok'>" . h($msg) . "</div>";

/* ================= PROJECTS HOME ================= */
if ($p == 'home') {
  $ps = q("SELECT p.*, m.role, (SELECT COUNT(*) FROM items i WHERE i.project_id=p.id) total, (SELECT COUNT(*) FROM items i WHERE i.project_id=p.id AND i.status='done') done FROM projects p JOIN project_members m ON m.project_id=p.id WHERE m.user_id=? ORDER BY p.id DESC", [me()['id']])->fetchAll();
  $n = count($ps); ?>
  <div class="ptitle">
    <div><h1>Projects</h1><p class="sub">A clear space for your next idea. Create, organize, and track.</p></div>
    <span class="pill"><?= $n ?> project<?= $n == 1 ? '' : 's' ?></span>
  </div>
  <div class="two">
    <form class="glass newp" method="post">
      <div class="ch"><span class="ib"><?= ic('plus', 16) ?></span><h3>New Project</h3></div>
      <input type="hidden" name="a" value="new_project">
      <div class="f"><label>Project Name</label><input name="name" placeholder="Project Name" required></div>
      <div class="f"><label>Description</label><textarea name="description" rows="5" placeholder="Description"></textarea></div>
      <div class="frow"><small>Make it happen.</small><button><?= ic('plus', 15) ?>Create Project</button></div>
    </form>
    <div class="glass">
      <div class="ch between"><h3>Your Projects</h3><?= ic('folder', 16) ?></div>
      <?php foreach ($ps as $r): $pp = pct($r['done'], $r['total']); ?>
      <a class="tile" href="index.php?p=project&id=<?= (int)$r['id'] ?>">
        <div class="between"><span class="ib"><?= ic('layers', 16) ?></span><?= ic('arrow-up-right', 15) ?></div>
        <h4><?= h($r['name']) ?></h4>
        <?php if (trim((string)$r['description']) !== ''): ?><p class="sub"><?= h($r['description']) ?></p><?php endif; ?>
        <div class="role"><?= ic('user', 13) ?><?= h($RL[$r['role']] ?? $r['role']) ?></div>
        <div class="prow"><span>Overall Progress</span><span><?= (int)$r['done'] ?> / <?= (int)$r['total'] ?> (<?= $pp ?>%)</span></div>
        <div class="bar"><i style="width:<?= $pp ?>%"></i></div>
        <div class="open"><span>Open project</span><?= ic('arrow-right', 15) ?></div>
      </a>
      <?php endforeach;
      if (!$ps) echo "<p class='sub'>No projects yet. Create your first one on the left.</p>"; ?>
    </div>
  </div>
  <div class="glass strip">
    <?= ic('layers', 18) ?>
    <div><b>From the big picture to the smallest issue.</b><small>Open <?= $ps ? h($ps[0]['name']) : 'a project' ?> to view your dashboard, board, backlog, sprints, and team.</small></div>
  </div>
<?php
/* ================= PROJECT ================= */
} elseif ($p == 'project') {
  if (!$role) {
    echo "<div class='note err'>No access to this project.</div>";
  } else {
    $cap = $CAP[$t];
    echo "<div class='phead'><span class='ib big'>" . ic('layers', 22) . "</span><div><h1>" . h($proj['name']) . "</h1><p class='sub'>" . h($proj['description']) . "</p></div><span class='pill'>" . ic('user', 13) . " " . h($RL[$role]) . "</span></div>";
    echo "<div class='trow'><h2>" . h($cap[0]) . "</h2><small>" . h($cap[1]) . "</small></div>";

    /* ===== DASHBOARD ===== */
    if ($t == 'overview') {
      $sc = q("SELECT status, COUNT(*) FROM items WHERE project_id=? GROUP BY status", [$pid])->fetchAll(PDO::FETCH_KEY_PAIR);
      $tc = q("SELECT type, COUNT(*) FROM items WHERE project_id=? GROUP BY type", [$pid])->fetchAll(PDO::FETCH_KEY_PAIR);
      $pc = q("SELECT priority, COUNT(*) FROM items WHERE project_id=? AND status<>'done' GROUP BY priority", [$pid])->fetchAll(PDO::FETCH_KEY_PAIR);
      $total = array_sum($sc); $done = (int)($sc['done'] ?? 0); $openN = array_sum($pc);
      $ob = (int)q("SELECT COUNT(*) FROM items WHERE project_id=? AND type='bug' AND status<>'done'", [$pid])->fetchColumn();
      $pr = pct($done, $total);

      echo "<div class='stats'>";
      foreach ($ST as $k => $v) echo "<div class='glass stat'><div class='sh'><span>$v</span>" . ic($STI[$k], 16) . "</div><b>" . (int)($sc[$k] ?? 0) . "</b></div>";
      echo "<div class='glass stat'><div class='sh'><span>Open Bugs</span>" . ic('bug', 16) . "</div><b>$ob</b></div></div>";

      echo "<div class='glass prog'><div class='ring' style='--p:$pr'><span>$pr%</span></div><div class='pg'><div class='between'><b>Overall Progress</b><small>$done / $total ($pr%)</small></div><div class='bar'><i style='width:$pr%'></i></div><small>" . ($total ? "$done of $total items are done." : "No issues yet. Progress will appear as work moves forward.") . "</small></div></div>";

      echo "<div class='two2'><div class='glass'><div class='ch between'><h3>By Type</h3><small>$total items</small></div>";
      foreach ($TY as $k => $v) { $c = (int)($tc[$k] ?? 0); $w = pct($c, $total); echo "<div class='mrow'>" . ic($TYI[$k], 15) . "<span>$v</span><div class='bar thin'><i style='width:$w%'></i></div><b>$c</b></div>"; }
      echo "</div><div class='glass'><div class='ch between'><h3>Open Items by Priority</h3><small>$openN items</small></div>";
      foreach ($PR as $k => $v) { $c = (int)($pc[$k] ?? 0); $w = pct($c, $openN); echo "<div class='mrow'>" . ic($PRI[$k], 15) . "<span>$v</span><div class='bar thin'><i style='width:$w%'></i></div><b>$c</b></div>"; }
      echo "</div></div>";
      echo "<div class='foot'>" . ic('info', 14) . "All metrics reflect the current project. " . ($total ? "$total item" . ($total == 1 ? '' : 's') . " in " . h($proj['name']) . "." : "No issues have been added to " . h($proj['name']) . ".") . "</div>";

    /* ===== KANBAN BOARD ===== */
    } elseif ($t == 'board') {
      $sf = $_GET['sprint'] ?? ''; if (!is_string($sf)) $sf = '';
      $ex = ''; $ep = [];
      if ($sf === 'none') $ex = ' AND i.sprint_id IS NULL';
      elseif (ctype_digit($sf)) { $ex = ' AND i.sprint_id=?'; $ep = [(int)$sf]; }
      else $sf = '';
      $bopts = ['' => 'All items', 'none' => 'Backlog (no sprint)']; foreach ($sprints as $s) $bopts[$s['id']] = $s['name'];
      $keys = array_keys($ST);
      echo "<p class='hint'>Each card is one piece of work. Use the arrows to move it: To Do → In Progress → In Review → Done.</p>";
      echo "<form method='get' class='glass' style='display:flex;align-items:center;gap:10px'><input type='hidden' name='p' value='project'><input type='hidden' name='id' value='$pid'><input type='hidden' name='t' value='board'><span>Show:</span><select name='sprint' style='width:auto' onchange='this.form.submit()'>" . opts($bopts, $sf) . "</select></form><div class='kan'>";
      foreach ($ST as $k => $v) {
        $rows = q("SELECT i.*, u.name an FROM items i LEFT JOIN users u ON u.id=i.assignee_id WHERE i.project_id=? AND i.status=?$ex ORDER BY FIELD(i.priority,'critical','high','medium','low'), i.id", array_merge([$pid, $k], $ep))->fetchAll();
        $i = array_search($k, $keys); $pv = $keys[$i - 1] ?? null; $nx = $keys[$i + 1] ?? null;
        echo "<div class='col'><h4>$v (" . count($rows) . ")</h4>";
        foreach ($rows as $r) {
          echo "<div class='kc'>" . tag($r['type'], $TY[$r['type']]) . " " . tag($r['priority'], $PR[$r['priority']]) . "<a href='index.php?p=project&id=$pid&t=item&item={$r['id']}'>#{$r['id']} " . h($r['title']) . "</a><small>" . h($r['an'] ?: 'Unassigned') . "</small>
          <form method='post' class='mv'><input type='hidden' name='a' value='move'><input type='hidden' name='pid' value='$pid'><input type='hidden' name='iid' value='{$r['id']}'><input type='hidden' name='sprint' value='" . h($sf) . "'>"
          . ($pv ? "<button class='gray' name='status' value='$pv' title='Move to {$ST[$pv]}'>◀</button>" : '')
          . ($nx ? "<button class='gray' name='status' value='$nx' title='Move to {$ST[$nx]}'>▶</button>" : '') . "</form></div>";
        }
        if (!$rows) echo "<small>No items</small>";
        echo "</div>";
      }
      echo "</div>";

    /* ===== BACKLOG & ISSUES ===== */
    } elseif ($t == 'items') {
      $ty = $_GET['type'] ?? ''; if (!is_string($ty) || !array_key_exists($ty, $TY)) $ty = '';
      $cnt = q("SELECT type, COUNT(*) FROM items WHERE project_id=? GROUP BY type", [$pid])->fetchAll(PDO::FETCH_KEY_PAIR);
      echo "<p class='hint'>Add an item below, then click its title to assign it to someone or to a sprint.</p>";
      echo "<form class='glass qa' method='post'><input type='hidden' name='a' value='save_item'><input type='hidden' name='pid' value='$pid'><input type='hidden' name='iid' value='0'>
      <input name='title' placeholder='What needs to be done? (e.g. As a student, I want to log in)' required>
      <select name='type' title='Type'>" . opts($TY, $ty ?: 'task') . "</select>
      <select name='priority' title='Priority'>" . opts($PR, 'medium') . "</select>
      <button>" . ic('plus', 15) . "Add</button></form>";
      echo "<div class='pills'><a class='" . ($ty == '' ? 'on' : '') . "' href='index.php?p=project&id=$pid&t=items'>All (" . array_sum($cnt) . ")</a>";
      foreach ($TYP as $k => $v) echo "<a class='" . ($ty == $k ? 'on' : '') . "' href='index.php?p=project&id=$pid&t=items&type=$k'>$v (" . (int)($cnt[$k] ?? 0) . ")</a>";
      echo "</div>";
      $rows = q("SELECT i.*, u.name an, s.name sn FROM items i LEFT JOIN users u ON u.id=i.assignee_id LEFT JOIN sprints s ON s.id=i.sprint_id WHERE i.project_id=?" . ($ty ? " AND i.type=?" : '') . " ORDER BY i.id DESC", $ty ? [$pid, $ty] : [$pid])->fetchAll();
      echo "<div class='glass' style='overflow-x:auto'><table><tr><th>#</th><th>Title</th><th>Type</th><th>Priority</th><th>Status</th><th>Assigned to</th><th>Sprint</th></tr>";
      foreach ($rows as $r) echo "<tr><td>{$r['id']}</td><td><a href='index.php?p=project&id=$pid&t=item&item={$r['id']}'>" . h($r['title']) . "</a></td><td>" . $TY[$r['type']] . "</td><td>" . tag($r['priority'], $PR[$r['priority']]) . "</td><td>" . tag('s-' . $r['status'], $ST[$r['status']]) . "</td><td>" . h($r['an']) . "</td><td>" . h($r['sn']) . "</td></tr>";
      echo "</table>" . (!$rows ? "<p class='sub' style='margin-top:10px'>No items yet. Add one above.</p>" : '') . "</div>";

    /* ===== EDIT ITEM ===== */
    } elseif ($t == 'item') {
      $iid = (int)($_GET['item'] ?? 0);
      $it = $iid ? q("SELECT * FROM items WHERE id=? AND project_id=?", [$iid, $pid])->fetch() : false;
      if (!$it) {
        echo "<div class='note err'>Item not found.</div>";
      } else {
        $g = fn($k, $d = '') => h($it[$k] ?? $d);
        echo "<form class='glass' method='post'><div class='card-h'>Edit item #$iid</div><input type='hidden' name='a' value='save_item'><input type='hidden' name='pid' value='$pid'><input type='hidden' name='iid' value='$iid'>
        <div class='f'><label>Title</label><input name='title' value='" . $g('title') . "' required></div>
        <div class='f'><label>Description (details, acceptance criteria, steps to reproduce a bug)</label><textarea name='description' rows='4'>" . $g('description') . "</textarea></div>
        <div class='fgrid'>
          <div class='f'><label>Type</label><select name='type'>" . opts($TY, $it['type']) . "</select></div>
          <div class='f'><label>Priority</label><select name='priority'>" . opts($PR, $it['priority']) . "</select></div>
          <div class='f'><label>Status</label><select name='status'>" . opts($ST, $it['status']) . "</select></div>
          <div class='f'><label>Assigned to</label><select name='assignee_id'>" . opts($mopts, $it['assignee_id'] ?? '') . "</select></div>
          <div class='f'><label>Sprint</label><select name='sprint_id'>" . opts($sopts, $it['sprint_id'] ?? '') . "</select></div>
          <div class='f'><label>Release</label><select name='release_id'>" . opts($ropts, $it['release_id'] ?? '') . "</select></div>
        </div>
        <button>Save</button> &nbsp; <a href='index.php?p=project&id=$pid&t=items'>Cancel</a></form>";
        if ($mgr) echo "<form method='post' onsubmit=\"return confirm('Delete this item?')\"><input type='hidden' name='a' value='delete_item'><input type='hidden' name='pid' value='$pid'><input type='hidden' name='iid' value='$iid'><button class='red'>Delete item</button></form>";
      }

    /* ===== SPRINTS ===== */
    } elseif ($t == 'sprints') {
      echo "<p class='hint'>A sprint is a short work period (1–4 weeks). Add items to a sprint, then track them on the Kanban Board.</p>";
      if ($mgr) echo "<form class='glass' method='post'><div class='card-h'>New Sprint</div><input type='hidden' name='a' value='new_sprint'><input type='hidden' name='pid' value='$pid'>
      <div class='fgrid'><div class='f'><label>Name</label><input name='name' placeholder='Sprint 1' required></div><div class='f'><label>Goal</label><input name='goal' placeholder='What should we finish?'></div>
      <div class='f'><label>Start date</label><input type='date' name='start_date'></div><div class='f'><label>End date</label><input type='date' name='end_date'></div></div><button>Create Sprint</button></form>";
      $free = q("SELECT id,title FROM items WHERE project_id=? AND sprint_id IS NULL AND status<>'done' ORDER BY id DESC", [$pid])->fetchAll();
      $fo = ['' => '— choose an item to add —']; foreach ($free as $x) $fo[$x['id']] = "#{$x['id']} " . $x['title'];
      foreach ($sprints as $s) {
        $its = q("SELECT id,title,type,priority,status FROM items WHERE sprint_id=? AND project_id=? ORDER BY FIELD(priority,'critical','high','medium','low'), id", [$s['id'], $pid])->fetchAll();
        $c = count($its); $n = array_count_values(array_column($its, 'status')); $d = $n['done'] ?? 0;
        $ob = count(array_filter($its, fn($x) => $x['type'] == 'bug' && $x['status'] != 'done'));
        $days = $s['end_date'] ? (int)ceil((strtotime($s['end_date']) - time()) / 86400) : null;
        echo "<div class='glass'><div class='card-h'>" . h($s['name']) . " " . tag('s-' . $s['status'], ucfirst($s['status'])) . "</div>";
        echo "<p class='sub' style='margin:0 0 12px'>" . h($s['goal']) . "<br>" . h($s['start_date']) . " → " . h($s['end_date']) . ($days !== null && $s['status'] == 'active' ? ($days >= 0 ? " · $days days left" : " · past end date") : '') . "</p>";
        echo "<b>Progress report</b>" . bar($d, $c) . "<p style='margin:6px 0 12px'><small>To Do: " . ($n['todo'] ?? 0) . " · In Progress: " . ($n['in_progress'] ?? 0) . " · In Review: " . ($n['review'] ?? 0) . " · Done: $d · Open bugs: $ob</small></p>";
        if ($its) {
          echo "<table><tr><th>#</th><th>Title</th><th>Type</th><th>Priority</th><th>Status</th><th></th></tr>";
          foreach ($its as $x) echo "<tr><td>{$x['id']}</td><td><a href='index.php?p=project&id=$pid&t=item&item={$x['id']}'>" . h($x['title']) . "</a></td><td>" . $TY[$x['type']] . "</td><td>" . tag($x['priority'], $PR[$x['priority']]) . "</td><td>" . tag('s-' . $x['status'], $ST[$x['status']]) . "</td>
            <td><form method='post' class='inline'><input type='hidden' name='a' value='assign'><input type='hidden' name='pid' value='$pid'><input type='hidden' name='iid' value='{$x['id']}'><input type='hidden' name='field' value='sprint'><input type='hidden' name='val' value=''><button class='sm gray'>Remove</button></form></td></tr>";
          echo "</table>";
        } else echo "<p class='sub'>No items in this sprint yet.</p>";
        echo "<p style='margin-top:12px'><form method='post' class='inline'><input type='hidden' name='a' value='assign'><input type='hidden' name='pid' value='$pid'><input type='hidden' name='field' value='sprint'><input type='hidden' name='val' value='{$s['id']}'><select name='iid'>" . opts($fo) . "</select> <button class='sm'>Add to sprint</button></form></p>";
        if ($mgr && $s['status'] != 'completed') {
          $nx = $s['status'] == 'planned' ? 'active' : 'completed';
          echo "<form method='post' class='inline'><input type='hidden' name='a' value='sprint_state'><input type='hidden' name='pid' value='$pid'><input type='hidden' name='sid' value='{$s['id']}'><input type='hidden' name='status' value='$nx'><button style='margin-top:10px'>" . ($nx == 'active' ? 'Start Sprint' : 'Complete Sprint') . "</button></form>";
        }
        echo "</div>";
      }
      if (!$sprints) echo "<p class='sub'>No sprints yet." . ($mgr ? " Create one above." : " Ask a Manager to create one.") . "</p>";

    /* ===== RELEASES ===== */
    } elseif ($t == 'releases') {
      echo "<p class='hint'>A release is a version you deliver (like v1.0.0). Add the items that go into it and watch it reach 100%.</p>";
      if ($mgr) echo "<form class='glass' method='post'><div class='card-h'>New Release</div><input type='hidden' name='a' value='new_release'><input type='hidden' name='pid' value='$pid'>
      <div class='fgrid'><div class='f'><label>Version</label><input name='version' placeholder='v1.0.0' required></div><div class='f'><label>Release date</label><input type='date' name='release_date'></div></div>
      <div class='f'><label>Release notes</label><textarea name='notes' rows='2' placeholder='What is new in this version?'></textarea></div><button>Create Release</button></form>";
      $free = q("SELECT id,title FROM items WHERE project_id=? AND release_id IS NULL ORDER BY id DESC", [$pid])->fetchAll();
      $fo = ['' => '— choose an item to add —']; foreach ($free as $x) $fo[$x['id']] = "#{$x['id']} " . $x['title'];
      foreach ($rels as $r) {
        $its = q("SELECT id,title,type,status FROM items WHERE release_id=? AND project_id=? ORDER BY id", [$r['id'], $pid])->fetchAll();
        $c = count($its); $d = count(array_filter($its, fn($x) => $x['status'] == 'done'));
        echo "<div class='glass'><div class='card-h'>" . h($r['version']) . " <small>" . h($r['release_date']) . "</small> " . ($c && $d == $c ? tag('s-done', 'Ready to release') : tag('s-todo', 'In progress')) . "</div>";
        if (trim((string)$r['notes']) !== '') echo "<p class='sub' style='margin:0 0 10px'>" . nl2br(h($r['notes'])) . "</p>";
        echo bar($d, $c);
        if ($its) {
          echo "<table style='margin-top:10px'><tr><th>#</th><th>Title</th><th>Type</th><th>Status</th><th></th></tr>";
          foreach ($its as $x) echo "<tr><td>{$x['id']}</td><td><a href='index.php?p=project&id=$pid&t=item&item={$x['id']}'>" . h($x['title']) . "</a></td><td>" . $TY[$x['type']] . "</td><td>" . tag('s-' . $x['status'], $ST[$x['status']]) . "</td>
            <td><form method='post' class='inline'><input type='hidden' name='a' value='assign'><input type='hidden' name='pid' value='$pid'><input type='hidden' name='iid' value='{$x['id']}'><input type='hidden' name='field' value='release'><input type='hidden' name='val' value=''><button class='sm gray'>Remove</button></form></td></tr>";
          echo "</table>";
        } else echo "<p class='sub' style='margin-top:10px'>No items in this release yet.</p>";
        echo "<p style='margin-top:12px'><form method='post' class='inline'><input type='hidden' name='a' value='assign'><input type='hidden' name='pid' value='$pid'><input type='hidden' name='field' value='release'><input type='hidden' name='val' value='{$r['id']}'><select name='iid'>" . opts($fo) . "</select> <button class='sm'>Add to release</button></form></p></div>";
      }
      if (!$rels) echo "<p class='sub'>No releases yet." . ($mgr ? " Create one above." : " Ask a Manager to create one.") . "</p>";

    /* ===== TEAM ===== */
    } elseif ($t == 'team') {
      echo "<p class='hint'><b>Manager:</b> full control (sprints, releases, team, delete). <b>Developer / Tester:</b> add and update items and move cards.</p>";
      echo "<div class='glass'><table><tr><th>Name</th><th>Email</th><th>Role</th></tr>";
      foreach ($members as $m) echo "<tr><td>" . h($m['name']) . "</td><td>" . h($m['email']) . "</td><td>" . $RL[$m['role']] . "</td></tr>";
      echo "</table></div>";
      if ($mgr) echo "<form class='glass qa' method='post'><input type='hidden' name='a' value='add_member'><input type='hidden' name='pid' value='$pid'><input type='email' name='email' style='flex:1;min-width:220px;width:auto' placeholder='Registered email of the new member' required> <select name='role'>" . opts($RL, 'developer') . "</select> <button>" . ic('plus', 15) . "Add Member</button></form>";

    /* ===== ACTIVITY ===== */
    } elseif ($t == 'activity') {
      $rows = q("SELECT a.created_at, a.action, u.name FROM activity_log a JOIN users u ON u.id=a.user_id WHERE a.project_id=? ORDER BY a.id DESC LIMIT 100", [$pid])->fetchAll();
      echo "<div class='glass' style='overflow-x:auto'><table><tr><th>When</th><th>Who</th><th>What happened</th></tr>";
      foreach ($rows as $r) echo "<tr><td>" . h($r['created_at']) . "</td><td>" . h($r['name']) . "</td><td>" . h($r['action']) . "</td></tr>";
      echo "</table>" . (!$rows ? "<p class='sub' style='margin-top:10px'>No activity yet.</p>" : '') . "</div>";
    }
  }
}
?>
    </div>
  </section>
</div>
<?php endif; ?>
</body></html>