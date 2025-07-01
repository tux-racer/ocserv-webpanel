<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Panel</title>
    <link href="/panel/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="/panel/assets/css/bootstrap-icons.css" rel="stylesheet">
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
            <?php if (!empty($login_error)): ?>
                <div class='alert alert-danger mt-3'><?= htmlspecialchars($login_error) ?></div>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>