<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/bootstrap.php';
require_once dirname(__DIR__) . '/lib/captcha.php';

security_require_public_form();

$name = trim((string) ($_POST['name'] ?? ''));
$phone = trim((string) ($_POST['phone'] ?? ''));
$email = trim((string) ($_POST['email'] ?? ''));
$service = trim((string) ($_POST['service'] ?? ''));
$visitDate = trim((string) ($_POST['date'] ?? ''));
$comment = trim((string) ($_POST['comment'] ?? ''));
$captchaAnswer = (int) ($_POST['captcha'] ?? 0);
$honeypot = trim((string) ($_POST['website'] ?? ''));

if (security_honeypot_filled($honeypot)) {
    security_json_response(['ok' => true, 'message' => 'Заявка принята. Мы перезвоним для подтверждения записи.']);
}

if (!captcha_verify_booking($captchaAnswer)) {
    security_json_response(['ok' => false, 'message' => 'Неверный ответ на проверочный вопрос.'], 422);
}

if (mb_strlen($name, 'UTF-8') < 2) {
    security_json_response(['ok' => false, 'message' => 'Укажите имя (минимум 2 символа).'], 422);
}

$phoneDigits = preg_replace('/\D+/', '', $phone) ?? '';
if (strlen($phoneDigits) < 10) {
    security_json_response(['ok' => false, 'message' => 'Укажите корректный номер телефона.'], 422);
}

if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    security_json_response(['ok' => false, 'message' => 'Укажите корректный email.'], 422);
}

security_require_service_choice($service, $services);

if ($visitDate !== '') {
    $dt = DateTimeImmutable::createFromFormat('Y-m-d', $visitDate);
    if ($dt === false || $dt->format('Y-m-d') !== $visitDate) {
        security_json_response(['ok' => false, 'message' => 'Укажите корректную дату визита.'], 422);
    }
}

if (mb_strlen($comment, 'UTF-8') > 2000) {
    security_json_response(['ok' => false, 'message' => 'Комментарий слишком длинный.'], 422);
}

if (!data_add_booking([
    'name' => $name,
    'phone' => $phone,
    'email' => $email,
    'service' => $service,
    'visit_date' => $visitDate,
    'comment' => $comment,
])) {
    security_json_response(['ok' => false, 'message' => 'Не удалось сохранить заявку. Попробуйте позже.'], 500);
}

security_json_response([
    'ok' => true,
    'message' => 'Заявка принята. Мы перезвоним для подтверждения записи.',
]);
