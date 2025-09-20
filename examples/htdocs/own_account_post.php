<?php

use studiointern\Api\ApiOauthUser;
use studiointern\Api\ApiConfig;
use studiointern\Io\RequestHelper;

session_start();

require_once __DIR__ . '/../vendor/autoload.php';

$Config = new ApiConfig();
$Config->setEndpoint('/own/account');

$pars = [
    'kid'               => [FILTER_SANITIZE_ADD_SLASHES],
    'pmt_type'          => [FILTER_SANITIZE_ADD_SLASHES],
    'pmt_amount'        => [FILTER_SANITIZE_ADD_SLASHES],
    'pmt_vat'           => [FILTER_SANITIZE_ADD_SLASHES],
    'pmt_description'   => [FILTER_SANITIZE_ADD_SLASHES],
    'pmt_date'          => [FILTER_SANITIZE_ADD_SLASHES],
];

if (isset($_POST['do']) && $_POST['do'] === 'send_api_request') {
    $post_data = RequestHelper::filterInput($pars);
    if (empty($post_data['pmt_date'])) {
        $post_data['pmt_date'] = date('Y-m-d');
    }

    include_once 'do/send_api_request.php';
}

$_WARNING = [];

$oauth_user = new ApiOauthUser($Config);
if (true === $oauth_user->isLoggedIn()) {
    $expiration = $oauth_user->getExpiration();
    if (null !== $expiration) {
        $expires_in = round(($expiration->getTimestamp() - time()) / 60);
        if ($expires_in < 5) {
            $_WARNING[] = 'Ihr Token läuft in ' . $expires_in . ' Minuten ab. Bitte <a href="/login.php?redirect=' . rawurlencode($_SERVER['REQUEST_URI']) . '">loggen Sie sich hier ein</a>.';
        }
    }
} else {
    $_WARNING[] = 'Sie sind nicht eingeloggt. Dieser Request wird nicht ausgeführt. Bitte <a href="/login.php?redirect=' . rawurlencode($_SERVER['REQUEST_URI']) . '">loggen Sie sich hier ein</a>.';
}

?><!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Tester - Create Account Booking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/styles.css">
</head>
<body>
    <div class="container mt-5">
        <?php require_once 'navbar.php'; ?>

        <h1 class="mb-4">API Tester</h1>
        <h2>Create Account Booking</h2>

        <?php require_once 'includes/messages.php'; ?>

        <div class="row">
            <div class="col-md-6">

                <form id="bookingForm" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" class="mb-4">
                    <input type="hidden" name="do" value="send_api_request">
                    <input type="hidden" name="endpoint" value="/own/account">
                    
                    <div class="mb-3">
                        <label for="kid" class="form-label">Customer ID</label>
                        <input type="text" class="form-control" id="kid" name="kid" value="<?php echo $_POST['kid']; ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="pmt_type" class="form-label">Payment Type</label>
                        <select class="form-control" id="pmt_type" name="pmt_type" value="<?php echo $_POST['pmt_type']; ?>" required>
                            <option value="debit">Forderung</option>
                            <option value="payment">Zahlung</option>
                            <option value="credit">Gutschrift</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="pmt_amount" class="form-label">Amount</label>
                        <input type="number" step="0.01" class="form-control" id="pmt_amount" name="pmt_amount" value="<?php echo $_POST['pmt_amount']; ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="pmt_vat" class="form-label">VAT Rate (%)</label>
                        <select class="form-control" id="pmt_vat" name="pmt_vat" value="<?php echo $_POST['pmt_vat']; ?>" required>
                            <option value="0">0</option>
                            <option value="7">7</option>
                            <option value="19">19</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="pmt_description" class="form-label">Description</label>
                        <input type="text" class="form-control" id="pmt_description" name="pmt_description" value="<?php echo $_POST['pmt_description']; ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="pmt_date" class="form-label">Booking Date</label>
                        <input type="date" class="form-control" id="pmt_date" name="pmt_date" value="<?php echo $_POST['pmt_date']; ?>">
                    </div>

                    <button type="submit" class="btn btn-primary">Create Booking</button>

                </form>

            </div>

            <div class="col-md-6">

            <?php if (isset($data)) : ?>
                <div class="response-container">
                    <h3>Response</h3>
                    <pre><?php print_r($data); ?></pre>
                </div>
            <?php endif; ?>

            </div>
        </div>

    </div>

</body>
</html>