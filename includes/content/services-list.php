<?php
declare(strict_types=1);

/** @var array<int, array<string, mixed>> $services_list */
$services_list = $services_list ?? [];
?>
            <div class="tpl-services" role="list">
<?php foreach ($services_list as $service): ?>
<?php include dirname(__DIR__) . '/partials/service-card.php'; ?>
<?php endforeach; ?>
            </div>
