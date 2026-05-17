<?php
declare(strict_types=1);

if (!isset($empty_message) || $empty_message === '') {
    return;
}

$empty_variant = (string) ($empty_variant ?? 'default');
$empty_hint = trim((string) ($empty_hint ?? ''));
$empty_action_label = trim((string) ($empty_action_label ?? ''));
$empty_action_href = trim((string) ($empty_action_href ?? ''));
$empty_action_id = trim((string) ($empty_action_id ?? ''));
$allowedVariants = ['default', 'services', 'reviews'];
if (!in_array($empty_variant, $allowedVariants, true)) {
    $empty_variant = 'default';
}
$hasLinkAction = $empty_action_label !== '' && $empty_action_href !== '';
$hasButtonAction = $empty_action_label !== '' && $empty_action_id !== '';
?>
            <div class="tpl-section-empty tpl-section-empty--<?= e($empty_variant) ?>" role="status">
              <div class="tpl-section-empty__icon" aria-hidden="true">
<?php if ($empty_variant === 'reviews'): ?>
                <svg viewBox="0 0 24 24" width="28" height="28" focusable="false"><path fill="none" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round" d="M5 9.5c0-3 3.1-5.5 7-5.5s7 2.5 7 5.5v4.8L16.5 18H8.2L5 17V9.5Z"/><path fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" d="M8.5 18.2h7"/><path fill="currentColor" d="m10.2 9.8.55 1.65.55-1.65H12l-.95 2.75h-1.1L9 9.8h1.2Zm3.1 0 .4 1.2h1.15l-1 0.75.4 1.2-.95-.7-.95.7.4-1.2-1-.75h1.15l.4-1.2Z"/></svg>
<?php elseif ($empty_variant === 'services'): ?>
                <svg viewBox="0 0 24 24" width="28" height="28" focusable="false"><rect x="5" y="3" width="14" height="18" rx="2" fill="none" stroke="currentColor" stroke-width="1.5"/><path fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" d="M9 8h6M9 12h6M9 16h4"/></svg>
<?php else: ?>
                <svg viewBox="0 0 24 24" width="28" height="28" focusable="false"><circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="1.5"/><path fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" d="M12 8v5M12 16h.01"/></svg>
<?php endif; ?>
              </div>
              <p class="tpl-section-empty__title"><?= e($empty_message) ?></p>
<?php if ($empty_hint !== ''): ?>
              <p class="tpl-section-empty__hint"><?= e($empty_hint) ?></p>
<?php endif; ?>
<?php if ($hasLinkAction): ?>
              <p class="tpl-section-empty__action">
                <a class="tpl-btn" href="<?= e($empty_action_href) ?>"><?= e($empty_action_label) ?></a>
              </p>
<?php elseif ($hasButtonAction): ?>
              <p class="tpl-section-empty__action">
                <button type="button" class="tpl-btn" id="<?= e($empty_action_id) ?>"><?= e($empty_action_label) ?></button>
              </p>
<?php endif; ?>
            </div>
