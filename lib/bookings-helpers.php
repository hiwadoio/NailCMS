<?php
declare(strict_types=1);

const BOOKING_STATUS_NEW = 'new';
const BOOKING_STATUS_IN_PROGRESS = 'in_progress';
const BOOKING_STATUS_COMPLETED = 'completed';
const BOOKING_STATUS_CANCELLED = 'cancelled';

function booking_statuses(): array
{
    return [
        BOOKING_STATUS_NEW => 'Новая',
        BOOKING_STATUS_IN_PROGRESS => 'В процессе',
        BOOKING_STATUS_COMPLETED => 'Выполнена',
        BOOKING_STATUS_CANCELLED => 'Отменена',
    ];
}

function booking_status_label(string $status): string
{
    return booking_statuses()[$status] ?? 'Новая';
}

function booking_is_valid_status(string $status): bool
{
    return isset(booking_statuses()[$status]);
}

function booking_format_visit_date(string $date): string
{
    return format_ru_date($date, '—');
}

function booking_format_submitted_at(string $iso): string
{
    $iso = trim($iso);
    if ($iso === '') {
        return '—';
    }

    try {
        $dt = new DateTimeImmutable($iso);
    } catch (Exception) {
        return $iso;
    }

    return $dt->format('d.m.Y H:i');
}

function booking_status_badge_class(string $status): string
{
    return match ($status) {
        BOOKING_STATUS_IN_PROGRESS => 'is-progress',
        BOOKING_STATUS_COMPLETED => 'is-done',
        BOOKING_STATUS_CANCELLED => 'is-cancelled',
        default => 'is-new',
    };
}
