<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/captcha.php';
require_once dirname(__DIR__) . '/lib/security.php';

security_require_api_get();

$captcha = captcha_create_booking();

security_json_response([
    'ok' => true,
    'question' => $captcha['question'],
]);
