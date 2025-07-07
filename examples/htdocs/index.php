<?php

session_start();

require_once __DIR__ . '/../vendor/autoload.php';

use StudioIntern\Api\ApiConfig;

if (isset($_GET['do']) && $_GET['do'] === 'send_api_request') {
    $post_data = [];
    foreach ($_GET as $key => $value) {
        if ('do' == $key) {
            continue;
        }
        $post_data[$key] = filter_input(INPUT_GET, $key, FILTER_SANITIZE_ADD_SLASHES);
    }
    include_once 'do/send_api_request.php';
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Tester - Simple GET Endpoints</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/styles.css">

    <script>
        const $endpoint = '<?php echo htmlspecialchars($_GET['endpoint'] ?? ''); ?>';

        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('endpoint').value = $endpoint;
        });
    </script>

    <style>
        pre {
            background-color: #000;
            color: #ccc;
            padding: 1rem;
            border-radius: 0.5rem;
        }

        td pre {
            background-color: #fff;
            color: #000;
            padding: 1rem;
            border-radius: 0.5rem;
        }
    </style>
</head>

<body>
    <div class="container mt-5">

        <?php require_once 'navbar.php'; ?>

        <h1 class="mb-4">API Tester</h1>
        <h2>Einige GET Endpoints</h2>

        <div class="row">
            <div class="col-md-12">
                <?php require_once 'includes/messages.php'; ?>
            </div>
        </div>
    
        <div class="row">
            <div class="col-md-4">
                <h3>Send Request</h3>
                
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="GET" id="apiForm" class="mb-4">
                    <input type="hidden" name="do" value="send_api_request">
                    <div class="mb-3">
                        <label for="endpoint" class="form-label">Endpoint</label>
                        <select class="form-select" id="endpoint" name="endpoint" required>
                            <option value="">Select an endpoint...</option>
                            <?php
                            foreach (ApiConfig::ENDPOINTS as $endpoint => $endpoint_data) {
                                if ('GET' == $endpoint_data[0]) {
                                    echo '<option value="' . $endpoint . '">' . $endpoint_data[1] . ' (' . $endpoint . ')</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="params" class="form-label">Query Parameters (optional)</label>
                        <input type="text" class="form-control" id="params" name="params" value="<?php echo htmlspecialchars($_GET['params'] ?? ''); ?>" placeholder="e.g. /123">
                        <p class="text-muted">
                            Die Art des Query-Parameters hängt von dem Endpoint ab.
                        </p>
                        <ul class="text-muted">
                            <li>
                                <strong>/own/account</strong>: <code>/[id_des_eintrags]</code>
                            </li>
                            <li>
                                <strong>/own/customer</strong>: <code>/[id_des_kunden]</code>
                            </li>
                            <li>
                                <strong>/own/student</strong>: <code>/[id_des_schülers]</code>
                            </li>
                        </ul>
                        <p>
                            Andere Endpoints können evtl. Parameter der Form <code>?key=value</code> haben. Für die Endpoints <code>/own/customer</code> und <code>/own/student</code> 
                            steht eine Pagination zur Verfügung.
                        </p>
                        <ul>
                            <li>
                                <code>per_page=10</code> (Anzahl der Einträge pro Seite)
                            </li>
                            <li>
                                <code>page=2</code> (Seite 2)
                            </li>
                        </ul>
                        <p>
                            Beispiel: <code>?per_page=10&page=2</code> (10 Einträge pro Seite, Seite 2)
                        </p>
                    </div>

                    <button type="submit" class="btn btn-primary">Send Request</button>
                </form>
            </div>
        
            <div class="col-md-8">
                <h3>Response Data</h3>
                <pre><?php echo htmlspecialchars(print_r($data, true)); ?></pre>
                <?php require_once 'includes/headers.php'; ?>
                <?php require_once 'includes/meta.php'; ?>
            </div>
        </div>

    </div>

</body>

</html>