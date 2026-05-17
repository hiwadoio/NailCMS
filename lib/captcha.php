<?php

declare(strict_types=1);

require_once __DIR__ . '/public-session.php';

const REVIEW_CAPTCHA_KEY = 'review_captcha_answer';
const BOOKING_CAPTCHA_KEY = 'booking_captcha_answer';

function captcha_create(string $sessionKey): array
{
    public_session_start();

    $a = random_int(2, 9);
    $b = random_int(2, 9);
    $_SESSION[$sessionKey] = $a + $b;

    return [
        'question' => $a . ' + ' . $b,
    ];
}

function captcha_verify(string $sessionKey, int $answer): bool
{
    public_session_start();

    if (!isset($_SESSION[$sessionKey])) {
        return false;
    }

    $expected = (int) $_SESSION[$sessionKey];
    unset($_SESSION[$sessionKey]);

    return $answer === $expected;
}

function captcha_create_review(): array
{
    return captcha_create(REVIEW_CAPTCHA_KEY);
}

function captcha_verify_review(int $answer): bool
{
    return captcha_verify(REVIEW_CAPTCHA_KEY, $answer);
}

function captcha_create_booking(): array
{
    return captcha_create(BOOKING_CAPTCHA_KEY);
}

function captcha_verify_booking(int $answer): bool
{
    return captcha_verify(BOOKING_CAPTCHA_KEY, $answer);
}
