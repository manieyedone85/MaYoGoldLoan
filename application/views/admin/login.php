<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login &middot; Aurum Finance</title>
    <link rel="icon" type="image/jpeg" href="<?php echo base_url('assets/images/logo-aurum-fab.jpg'); ?>">
    <style>
        :root {
            --brand-dark: #8a5a35;
            --brand-mid: #c9975f;
            --brand-light: #f3d9b1;
            --bg-cream: #f7ede1;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background: radial-gradient(circle at 15% 20%, #fdf3e7 0%, var(--bg-cream) 55%, #f3e4d2 100%);
            padding: 24px;
        }
        .login-card {
            width: 100%;
            max-width: 420px;
            background: #fffdfb;
            border-radius: 28px;
            box-shadow: 0 30px 60px -20px rgba(138, 90, 53, 0.25);
            padding: 40px 36px 28px;
            text-align: center;
        }
        .logo-wrap {
            width: 92px;
            height: 92px;
            border-radius: 50%;
            background: #fff;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            box-shadow: 0 8px 20px -6px rgba(138, 90, 53, 0.35);
        }
        .logo-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        h1.title {
            font-size: 28px;
            font-weight: 700;
            color: var(--brand-dark);
            margin: 0 0 6px;
        }
        p.subtitle {
            color: #a3897a;
            font-size: 14px;
            margin: 0 0 28px;
        }
        .alert-error {
            background: #fdecec;
            color: #b3423a;
            border-radius: 12px;
            padding: 10px 14px;
            font-size: 13px;
            margin-bottom: 18px;
            text-align: left;
        }
        form { text-align: left; }
        .form-group { margin-bottom: 16px; }
        .form-control {
            width: 100%;
            padding: 14px 18px;
            border: 1px solid #eee0d2;
            border-radius: 16px;
            font-size: 14px;
            background: #fffefd;
            color: #4a3a2c;
            outline: none;
            transition: border-color .15s ease;
        }
        .form-control::placeholder { color: #c9b6a4; }
        .form-control:focus { border-color: var(--brand-mid); }
        .password-wrap { position: relative; }
        .password-wrap .form-control { padding-right: 46px; }
        .toggle-password {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #b39a86;
            padding: 4px;
            display: flex;
        }
        .row-between {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin: 4px 0 22px;
            font-size: 13px;
        }
        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #5c4a3a;
        }
        .remember-me input {
            width: 16px;
            height: 16px;
            accent-color: var(--brand-mid);
        }
        .forgot-link {
            color: var(--brand-mid);
            font-weight: 600;
            text-decoration: none;
        }
        .forgot-link:hover { text-decoration: underline; }
        .btn-signin {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 16px;
            background: linear-gradient(180deg, var(--brand-light), var(--brand-mid));
            color: #5a3a1e;
            font-weight: 700;
            font-size: 15px;
            cursor: pointer;
            transition: filter .15s ease;
        }
        .btn-signin:hover { filter: brightness(0.97); }
        .footer-rights {
            margin-top: 26px;
            font-size: 12px;
            color: #c9b6a4;
        }
    </style>
</head>
<body>
<div class="login-card">
    <a href="<?php echo base_url(); ?>" class="logo-wrap">
        <img src="<?php echo base_url('assets/images/logo-aurum.png'); ?>" alt="Aurum Finance">
    </a>
    <h1 class="title">Welcome back</h1>
    <p class="subtitle">Sign in to manage employees, customers, loans &amp; reports</p>

    <?php if (! empty($error)): ?>
        <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" action="<?php echo base_url('admin/login'); ?>">
        <div class="form-group">
            <input type="text" name="email" class="form-control" placeholder="Email address" required autofocus>
        </div>
        <div class="form-group password-wrap">
            <input type="password" name="password" id="password" class="form-control" placeholder="Password" required>
            <button type="button" class="toggle-password" onclick="togglePassword()" aria-label="Toggle password visibility">
                <svg id="eye-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                    <circle cx="12" cy="12" r="3"></circle>
                </svg>
            </button>
        </div>
        <div class="row-between">
            <label class="remember-me">
                <input type="checkbox" name="remember">
                Remember me
            </label>
            <a href="#" class="forgot-link">Forgot password?</a>
        </div>
        <button type="submit" class="btn-signin">Sign in</button>
    </form>

    <div class="footer-rights">
        &copy; <?php echo date('Y'); ?> Aurum Finance. All rights reserved.
    </div>
</div>
<script>
    function togglePassword() {
        var input = document.getElementById('password');
        input.type = input.type === 'password' ? 'text' : 'password';
    }
</script>
</body>
</html>
