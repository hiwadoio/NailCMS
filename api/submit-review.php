<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/bootstrap.php';
require_once dirname(__DIR__) . '/lib/captcha.php';

security_require_public_form();

$author = trim((string) ($_POST['author'] ?? ''));
$service = trim((string) ($_POST['service'] ?? ''));
$text = trim((string) ($_POST['text'] ?? ''));
$rating = (float) ($_POST['rating'] ?? 0);
$captchaAnswer = (int) ($_POST['captcha'] ?? 0);
$honeypot = trim((string) ($_POST['website'] ?? ''));

if (security_honeypot_filled($honeypot)) {
    security_json_response(['ok' => true, 'message' => 'Спасибо! Отзыв отправлен на модерацию.']);
}

if (!captcha_verify_review($captchaAnswer)) {
    security_json_response(['ok' => false, 'message' => 'Неверный ответ на проверочный вопрос.'], 422);
}

if (mb_strlen($author, 'UTF-8') < 2) {
    security_json_response(['ok' => false, 'message' => 'Укажите, как к вам обращаться (минимум 2 символа).'], 422);
}

if (mb_strlen($text, 'UTF-8') < 10) {
    security_json_response(['ok' => false, 'message' => 'Напишите отзыв подробнее (минимум 10 символов).'], 422);
}

security_require_service_choice($service, $services);

if ($rating < 1 || $rating > 5) {
    security_json_response(['ok' => false, 'message' => 'Оценка должна быть от 1 до 5.'], 422);
}

$rating = round($rating * 2) / 2;

if (!data_add_pending_review([
    'author' => $author,
    'service' => $service,
    'text' => $text,
    'rating' => $rating,
    'date' => date('Y-m-d'),
])) {
    security_json_response(['ok' => false, 'message' => 'Не удалось сохранить отзыв. Попробуйте позже.'], 500);
}

security_json_response([
    'ok' => true,
    'message' => 'Спасибо! Отзыв отправлен на модерацию и появится на сайте после проверки.',
]);
