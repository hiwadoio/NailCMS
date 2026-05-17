<?php

declare(strict_types=1);

function review_initials(string $author): string
{
    $author = trim($author);
    if ($author === '') {
        return '?';
    }

    $parts = preg_split('/\s+/u', $author) ?: [];
    $initials = '';

    foreach ($parts as $part) {
        if ($part === '') {
            continue;
        }
        $initials .= mb_strtoupper(mb_substr($part, 0, 1, 'UTF-8'), 'UTF-8');
        if (mb_strlen($initials, 'UTF-8') >= 2) {
            break;
        }
    }

    return $initials !== '' ? $initials : mb_strtoupper(mb_substr($author, 0, 1, 'UTF-8'), 'UTF-8');
}

function review_format_date(string $date): string
{
    return format_ru_date($date, $date);
}

function review_score_label(float $rating): string
{
    if (abs($rating - round($rating)) < 0.01) {
        return (string) (int) round($rating);
    }

    return str_replace('.', ',', number_format($rating, 1, '.', ''));
}

function review_star_class(int $index, float $rating): string
{
    $filled = $rating >= $index;
    $half = !$filled && $rating >= $index - 0.5;

    if ($filled) {
        return 'is-filled';
    }
    if ($half) {
        return 'is-half';
    }

    return '';
}

function review_stars_html(float $rating): string
{
    $html = '';
    for ($i = 1; $i <= 5; $i++) {
        $class = review_star_class($i, $rating);
        $html .= '<span class="tpl-review-stars__item ' . $class . '" aria-hidden="true">★</span>';
    }

    return $html;
}

function reviews_average_rating(array $items): float
{
    if ($items === []) {
        return 0;
    }

    $sum = 0.0;
    foreach ($items as $item) {
        $sum += (float) ($item['rating'] ?? 0);
    }

    return round($sum / count($items), 1);
}

function reviews_count_label(int $count): string
{
    $mod10 = $count % 10;
    $mod100 = $count % 100;

    if ($mod100 >= 11 && $mod100 <= 14) {
        return $count . ' отзывов';
    }

    if ($mod10 === 1) {
        return $count . ' отзыв';
    }

    if ($mod10 >= 2 && $mod10 <= 4) {
        return $count . ' отзыва';
    }

    return $count . ' отзывов';
}
