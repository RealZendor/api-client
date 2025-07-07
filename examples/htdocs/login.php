<?php
session_start();

require_once __DIR__ . '/../vendor/autoload.php';

use StudioIntern\Api\ApiConfig;
use StudioIntern\Api\ApiOauthUser;

$Config = new ApiConfig();
$_WARNING = [];

if (isset($_POST['do']) && $_POST['do'] === 'login') {
    include_once 'do/login.php';
}

$oauth_user = new ApiOauthUser($Config);
if (true === $oauth_user->isLoggedIn()) {
    $_MESSAGE[] = 'Sie sind eingeloggt.';
    $expiration = $oauth_user->getExpiration();
    if (null !== $expiration) {
        $expires_in = round(($expiration->getTimestamp() - time()) / 60);
        $_MESSAGE[] = 'Ihr Token läuft bei Inaktivität in ' . $expires_in . ' Minuten ab.*';
    }
} else {
    $_WARNING[] = 'Sie sind nicht eingeloggt.';
}

?><!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Tester - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/styles.css">
</head>
<body>
    <div class="container mt-5">
        <?php require_once 'navbar.php'; ?>

        <h1 class="mb-4">API Tester</h1>
        <h2>Login</h2>

        <?php require_once 'includes/messages.php'; ?>

        <div class="row">
            <div class="col-md-6">
                <?php if (false === $oauth_user->isLoggedIn()) : ?>
                <form id="bookingForm" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" class="mb-4">
                    <input type="hidden" name="do" value="login">
                    <input type="hidden" name="redirect" value="<?php echo $_GET['redirect'] ?? ''; ?>">
                    
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>

                    <button type="submit" class="btn btn-primary">Login</button>

                </form>
                <?php endif; ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <p class="text-muted">
                        * Das Login-Token wird automatisch erneuert, wenn Sie innerhalb von 
                        <?php echo ApiOauthUser::TOKEN_GAP_TIME / 60 ?> Minuten vor Ablauf eine 
                        Aktion ausführen, für die das Token benötigt wird.
                    </p>
                </div>
            </div>
        </div>

    </div>
</body>
</html>