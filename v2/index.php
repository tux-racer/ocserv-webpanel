<?php
// ocserv-panel.php
session_start();

function isLoggedIn()
{
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

function authenticate($user, $pass)
{
    $file = "/etc/ocserv/paneladmin";
    if (!file_exists($file)) return false;
    $lines = file($file, FILE_IGNORE_NEW_LINES);
    foreach ($lines as $line) {
        list($fuser, $fpass) = explode(':', $line, 2);
        if ($user === $fuser && password_verify($pass, $fpass)) {
            return true;
        }
    }
    return false;
}

if (isset($_POST['login'])) {
    $user = $_POST['login_user'] ?? '';
    $pass = $_POST['login_pass'] ?? '';
    if (authenticate($user, $pass)) {
        $_SESSION['logged_in'] = true;
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } else {
        error_log("PHP message: user authentication failure for : Password Mismatch");
        $login_error = "Login gagal.";
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

if (!isLoggedIn()) {
    echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Panel</title>
    <link href="/panel/bootstrap.min.css" rel="stylesheet">
    <link href="/panel/bootstrap-icons.css" rel="stylesheet">
    <style>
         body {
             background: linear-gradient(135deg, #e0e7ff 0%, #f8fafc 100%);
             min-height: 100vh;
         }
         .main-card {
             border-radius: 1.5rem;
             box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.15);
             background: rgba(255,255,255,0.95);
         }
         .panel-header {
             background: linear-gradient(90deg, #6366f1 0%, #60a5fa 100%);
             color: #fff;
             border-radius: 1.5rem 1.5rem 0 0;
             padding: 2rem 2rem 1rem 2rem;
             box-shadow: 0 4px 16px 0 rgba(99,102,241,0.10);
         }
         .panel-header h3 {
             font-weight: 700;
             letter-spacing: 1px;
         }
         .card {
             border-radius: 1rem;
         }
         .form-label {
             font-weight: 500;
         }
         .btn {
             font-weight: 500;
             letter-spacing: 0.5px;
         }
     </style>
</head>
<body>
<div class="container py-5">
    <div class="main-card mx-auto shadow" style="max-width: 400px;">
        <div class="panel-header text-center mb-0">
            <i class="bi bi-shield-lock-fill fs-1 mb-2"></i>
            <h3 class="mb-0">Login Admin</h3>
        </div>
        <form method="post" class="card p-4 shadow-none border-0 mb-0 bg-transparent">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="login_user" class="form-control" required autofocus>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="login_pass" class="form-control" required>
            </div>
            <button type="submit" name="login" class="btn btn-primary w-100"><i class="bi bi-box-arrow-in-right"></i> Login</button>
        </form>
HTML;
    if (isset($login_error)) {
        echo "<div class='alert alert-danger mt-3'>{$login_error}</div>";
    }
    echo <<<HTML
    </div>
</div>
</body>
</html>
HTML;
    exit;
}

function runCommand($cmd)
{
    $output = null;
    $status = null;
    exec($cmd . " 2>&1", $output, $status);
    return ['output' => $output, 'status' => $status];
}

function listUsers()
{
    $users = [];
    $passwdFile = "/etc/ocserv/passwd";
    if (file_exists($passwdFile)) {
        $lines = file($passwdFile, FILE_IGNORE_NEW_LINES);
        foreach ($lines as $line) {
            $parts = explode(":", $line);
            if (!empty($parts[0])) {
                $users[] = $parts[0];
            }
        }
    }
    return $users;
}

$userList = listUsers();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $password = $_POST['password'] ?? '';
    $username_raw = $_POST['username'] ?? '';

    if (empty($username_raw)) {
        $username_raw = $_POST['user_select'] ?? '';
    }

    $username = preg_replace('/[^a-zA-Z0-9._-]/', '', $username_raw);

    switch ($action) {
        case 'add_user':
            if (!empty($password) && !empty($username)) {
                $cmd = "echo -e \"$password\\n$password\" | sudo /usr/bin/ocpasswd -c /etc/ocserv/passwd $username";
                $result = runCommand($cmd);
                runCommand("sudo chmod 644 /etc/ocserv/passwd");
            } else {
                $result = ['output' => ['Username dan Password tidak boleh kosong.'], 'status' => 1];
            }
            break;
        case 'delete_user':
            if (!empty($username)) {
                $result = runCommand("sudo /usr/bin/ocpasswd -d -c /etc/ocserv/passwd $username");
                runCommand("sudo chmod 644 /etc/ocserv/passwd");
            } else {
                $result = ['output' => ['Username harus dipilih untuk menghapus.'], 'status' => 1];
            }
            break;
        case 'disconnect_user':
            $result = runCommand("sudo /usr/bin/occtl disconnect user $username");
            break;
        case 'show_users':
            $result = runCommand("sudo /usr/bin/occtl show users");
            break;
        case 'show_user':
            $result = runCommand("sudo /usr/bin/occtl show user $username");
            break;
        case 'list_all_users':
            $result = ['output' => $userList, 'status' => 0];
            break;
        default:
            $result = ['output' => ['Unknown action'], 'status' => 1];
    }
    $userList = listUsers();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ocserv Admin Panel</title>
    <link href="/panel/bootstrap.min.css" rel="stylesheet">
    <link href="/panel/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #e0e7ff 0%, #f8fafc 100%);
            min-height: 100vh;
        }

        .main-card {
            border-radius: 1.5rem;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.15);
            background: rgba(255, 255, 255, 0.95);
        }

        .panel-header {
            background: linear-gradient(90deg, #6366f1 0%, #60a5fa 100%);
            color: #fff;
            border-radius: 1.5rem 1.5rem 0 0;
            padding: 2rem 2rem 1rem 2rem;
            box-shadow: 0 4px 16px 0 rgba(99, 102, 241, 0.10);
        }

        .panel-header h1 {
            font-weight: 700;
            letter-spacing: 1px;
        }

        .btn i {
            margin-right: 6px;
        }

        .list-group-item {
            border-radius: 0.5rem;
            margin-bottom: 0.5rem;
        }

        .card {
            border-radius: 1rem;
        }

        .form-label {
            font-weight: 500;
        }

        .btn {
            font-weight: 500;
            letter-spacing: 0.5px;
        }
    </style>
</head>

<body>
    <div class="container py-5">
        <div class="main-card mx-auto shadow" style="max-width: 1200px;">
            <div class="panel-header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <i class="bi bi-shield-lock-fill fs-2 me-2"></i>
                    <h1 class="mb-0">Ocserv Admin Panel</h1>
                </div>
                <a href="?logout=1" class="btn btn-outline-light"><i class="bi bi-box-arrow-right"></i>Logout</a>
            </div>
            <div class="row g-0">
                <!-- Left Column: Form & Output -->
                <div class="col-md-7 p-4">
                    <form method="post" class="card p-4 shadow-none border-0 mb-4 bg-transparent">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" name="username" id="username" class="form-control" placeholder="Masukkan username baru">
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password (untuk tambah user)</label>
                            <input type="password" name="password" id="password" class="form-control" placeholder="Masukkan password">
                        </div>
                        <div class="mb-3">
                            <label for="user_select" class="form-label">Atau pilih dari daftar user</label>
                            <select class="form-select" name="user_select" id="user_select">
                                <option value="">-- Pilih User --</option>
                                <?php foreach ($userList as $user): ?>
                                    <option value="<?php echo htmlspecialchars($user); ?>"><?php echo htmlspecialchars($user); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="d-grid gap-2">
                            <button name="action" value="add_user" class="btn btn-success"><i class="bi bi-person-plus"></i>Tambah User</button>
                            <button name="action" value="delete_user" class="btn btn-danger"><i class="bi bi-person-dash"></i>Hapus User</button>
                            <button name="action" value="disconnect_user" class="btn btn-warning text-white"><i class="bi bi-plug"></i>Disconnect User</button>
                            <button name="action" value="show_users" class="btn btn-primary"><i class="bi bi-people"></i>Cek User Online</button>
                            <button name="action" value="show_user" class="btn btn-info text-white"><i class="bi bi-person-badge"></i>Cek Info User</button>
                            <!-- <button name="action" value="list_all_users" class="btn btn-secondary"><i class="bi bi-list-ul"></i>Tampilkan Semua User</button> -->
                        </div>
                    </form>
                    <?php if (!empty($result)): ?>
                        <div class="card shadow p-3 mb-4 bg-light border-0">
                            <h5 class="mb-3"><i class="bi bi-terminal"></i> Output:</h5>
                            <pre class="mb-0"><?php echo htmlspecialchars(implode("\n", $result['output'])); ?></pre>
                        </div>
                    <?php endif; ?>
                </div>
                <!-- Right Column: User List -->
                <div class="col-md-5 p-4 border-start">
                    <?php if (!empty($userList)): ?>
                        <div class="card shadow p-3 mb-4 bg-light border-0">
                            <h5 class="mb-3"><i class="bi bi-people-fill"></i> Daftar User Terdaftar:</h5>
                            <ul class="list-group list-group-flush">
                                <?php foreach ($userList as $user): ?>
                                    <li class="list-group-item d-flex align-items-center"><i class="bi bi-person-circle me-2 text-primary"></i><?php echo htmlspecialchars($user); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>

</html>