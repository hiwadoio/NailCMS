<?php
declare(strict_types=1);

/** @var array<int, array<string, mixed>> $reviews_list */
$reviews_list = $reviews_list ?? [];
?>
            <div class="tpl-reviews" id="reviews-list" role="list">
<?php foreach ($reviews_list as $review): ?>
<?php include dirname(__DIR__) . '/partials/review-card.php'; ?>
<?php endforeach; ?>
            </div>
