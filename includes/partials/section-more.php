<?php
declare(strict_types=1);

if (empty($more_href) || empty($more_label)) {
    return;
}

$more_meta = trim((string) ($more_meta ?? ''));
$more_variant = (string) ($more_variant ?? 'default');
$more_eyebrow = trim((string) ($more_eyebrow ?? ''));
$allowedVariants = ['default', 'services', 'reviews'];
if (!in_array($more_variant, $allowedVariants, true)) {
    $more_variant = 'default';
}
if ($more_eyebrow === '' && $more_variant === 'services') {
    $more_eyebrow = 'Каталог';
}
if ($more_eyebrow === '' && $more_variant === 'reviews') {
    $more_eyebrow = 'Отзывы гостей';
}
?>
            <div class="tpl-section-more tpl-section-more--<?= e($more_variant) ?>">
              <a class="tpl-section-more__link" href="<?= e($more_href) ?>">
                <span class="tpl-section-more__content">
<?php if ($more_eyebrow !== ''): ?>
                  <span class="tpl-section-more__eyebrow"><?= e($more_eyebrow) ?></span>
<?php endif; ?>
                  <span class="tpl-section-more__label"><?= e($more_label) ?></span>
<?php if ($more_meta !== ''): ?>
                  <span class="tpl-section-more__meta"><?= e($more_meta) ?></span>
<?php endif; ?>
                </span>
                <span class="tpl-section-more__arrow" aria-hidden="true">
                  <svg viewBox="0 0 20 20" width="20" height="20" focusable="false"><path fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" d="M8 5 13 10 8 15" /></svg>
                </span>
              </a>
            </div>
