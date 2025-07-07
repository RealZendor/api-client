<?php

session_start();
require_once __DIR__ . '/../vendor/autoload.php';

use StudioIntern\Io\RequestHelper;
use StudioIntern\Api\ApiConfig;

// Initialize config if not already done in the calling script
if (!is_a($Config, 'StudioIntern\Api\ApiConfig')) {
    $Config = new ApiConfig();
}

$pars = [
    'endpoint'          => [FILTER_SANITIZE_ADD_SLASHES],
    'anrede'            => [FILTER_SANITIZE_ADD_SLASHES],
    'titel'             => [FILTER_SANITIZE_ADD_SLASHES],
    'vorname'           => [FILTER_SANITIZE_ADD_SLASHES],
    'nachname'          => [FILTER_SANITIZE_ADD_SLASHES],
    'adresse'           => [FILTER_SANITIZE_ADD_SLASHES],
    'adresszusatz'      => [FILTER_SANITIZE_ADD_SLASHES],
    'plz'               => [FILTER_SANITIZE_ADD_SLASHES],
    'ort'               => [FILTER_SANITIZE_ADD_SLASHES],
    'telefon'           => [FILTER_SANITIZE_ADD_SLASHES],
    'mobil'             => [FILTER_SANITIZE_ADD_SLASHES],
    'email'             => [FILTER_SANITIZE_EMAIL],
    'bemerkungen'       => [FILTER_SANITIZE_ADD_SLASHES],
    'ist_schueler'      => [FILTER_SANITIZE_NUMBER_INT],
    'geburtstag'        => [FILTER_SANITIZE_ADD_SLASHES],
    'notify_error'      => [FILTER_SANITIZE_EMAIL],
    'no_custos'         => [FILTER_SANITIZE_NUMBER_INT],
    'sch_vorname'        => [FILTER_SANITIZE_ADD_SLASHES, FILTER_FORCE_ARRAY],
    'sch_nachname'       => [FILTER_SANITIZE_ADD_SLASHES, FILTER_FORCE_ARRAY],
    'sch_geb'            => [FILTER_SANITIZE_ADD_SLASHES, FILTER_FORCE_ARRAY],
];

if (isset($_POST['do']) && $_POST['do'] === 'send_api_request') {
    $post_data = RequestHelper::filterInput($pars);
    include_once 'do/send_api_request.php';
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Tester - Create User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/styles.css">
</head>

<body>
    <div class="container mt-5">

        <?php require_once 'navbar.php'; ?>

        <h1 class="mb-4">API Tester</h1>
        <h2>Create User</h2>

        <?php require_once 'includes/messages.php'; ?>

        <div class="row">
            <div class="col-md-6">
                <form id="apiForm" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" class="mb-4">
                    <input type="hidden" name="do" value="send_api_request">
                    <input type="hidden" name="endpoint" value="/pub/customer/register">

                    <div class="mb-3">
                        <label for="salutation" class="form-label">Salutation</label>
                        <select class="form-select" id="salutation" name="anrede" required maxlength="12">
                            <option value="Herr">Herr</option>
                            <option value="Frau">Frau</option>
                            <option value="k.A.">k.A.</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="title" class="form-label">Title</label>
                        <input type="text" class="form-control" id="title" name="titel" maxlength="12">
                    </div>

                    <div class="mb-3">
                        <label for="firstName" class="form-label">First Name</label>
                        <input type="text" class="form-control" id="firstName" name="vorname" required maxlength="64">
                    </div>

                    <div class="mb-3">
                        <label for="lastName" class="form-label">Last Name</label>
                        <input type="text" class="form-control" id="lastName" name="nachname" required maxlength="48">
                    </div>

                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <input type="text" class="form-control" id="address" name="adresse" maxlength="64">
                    </div>

                    <div class="mb-3">
                        <label for="addressAddition" class="form-label">Address Addition</label>
                        <input type="text" class="form-control" id="addressAddition" name="adresszusatz" maxlength="64">
                    </div>

                    <div class="mb-3">
                        <label for="postalCode" class="form-label">Postal Code</label>
                        <input type="text" class="form-control" id="postalCode" name="plz" maxlength="8">
                    </div>

                    <div class="mb-3">
                        <label for="city" class="form-label">City</label>
                        <input type="text" class="form-control" id="city" name="ort" maxlength="64">
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="tel" class="form-control" id="phone" name="telefon">
                    </div>

                    <div class="mb-3">
                        <label for="mobile" class="form-label">Mobile</label>
                        <input type="tel" class="form-control" id="mobile" name="mobil" required>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required maxlength="255">
                    </div>

                    <div class="mb-3">
                        <label for="schueler" class="form-label">Schüler</label>
                        <select class="form-select" id="schueler" name="ist_schueler" onchange="toggleSchuelerFields()">
                            <option value="1">Kunde = Schüler</option>
                            <option value="2">Anmeldung für Kind(er)</option>
                        </select>
                    </div>
                <div id="if_schueler_is_1" class="d-none">
                    <div class="mb-3">
                        <label class="form-label">Geburtstag</label>
                        <input type="date" class="form-control" id="geburtstag" name="geburtstag">
                    </div>
                </div>

                <div id="if_schueler_is_2" class="d-none">
                    <div class="mb-3">
                        <label class="form-label">Schüler 1 Vorname</label>
                        <input type="text" class="form-control" id="sch_vorname" name="sch_vorname[]" maxlength="64">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Schüler 1 Nachname</label>
                        <input type="text" class="form-control" id="sch_nachname" name="sch_nachname[]" maxlength="48">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Schüler 1 Geburtstag</label>
                        <input type="date" class="form-control" id="sch_geb" name="sch_geb[]">
                    </div>


                    <div class="mb-3">
                        <label class="form-label">Schüler 2 Vorname</label>
                        <input type="text" class="form-control" id="sch_vorname" name="sch_vorname[]" maxlength="64">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Schüler 2 Nachname</label>
                        <input type="text" class="form-control" id="sch_nachname" name="sch_nachname[]" maxlength="48">
                    </div>

                    <div class="mb-3">
                        <label for="sch_geb" class="form-label">Schüler 2 Geburtstag</label>
                        <input type="date" class="form-control" id="sch_geb" name="sch_geb[]">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="notify_error" class="form-label">E-Mail-Adresse für Fehler-Benachrichtigung</label>
                    <input type="email" class="form-control" id="notify_error" name="notify_error" maxlength="255">
                </div>

                <div class="mb-3">
                    <label for="customer_message" class="form-label">Bemerkungen</label>
                    <textarea class="form-control" id="customer_message" name="customer_message" rows="3" maxlength="512"></textarea>
                </div>

                <div class="mb-3">
                    <label for="no_custos" class="form-label">KEIN Spam-Check (Custos-Service)</label>
                    <input type="checkbox" class="form-check-input" id="no_custos" name="no_custos" value="1">
                </div>

                <button type="submit" class="btn btn-primary">Kunden erzeugen</button>
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

    <script>
        function toggleSchuelerFields() {
            const schueler = document.getElementById('schueler').value;
            const if_schueler_is_2 = document.getElementById('if_schueler_is_2');
            if (schueler == 2) {
                if_schueler_is_2.classList.remove('d-none');
                if_schueler_is_1.classList.add('d-none');
            } else {
                if_schueler_is_2.classList.add('d-none');
                if_schueler_is_1.classList.remove('d-none');
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            toggleSchuelerFields();
            
        });
    </script>
</body>

</html>