<?php

use AmoCRM\Exceptions\AmoCRMoAuthApiException;

include_once __DIR__ . '/../src/bootstrap.php';

if (!isset($_GET['code'])) {
    echo '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Title</title>
</head>
<body>

</body>
<script
        class="amocrm_oauth"
        charset="utf-8"
        data-client-id="' . $_ENV['CLIENT_ID'] . '"
        data-title="Button"
        data-compact="false"
        data-class-name="className"
        data-color="default"
        data-state="state"
        data-error-callback="functionName"
        data-mode="post_message"
        src="https://www.amocrm.ru/auth/button.min.js"
></script>
</html>';
    die;
}

try {
    $token = getClient()->getOAuthClient()->getAccessTokenByCode($_GET['code']);
    file_put_contents('../tmp/code.txt', json_encode($token->jsonSerialize()));
} catch (AmoCRMoAuthApiException $e) {
    die((string)$e);
}