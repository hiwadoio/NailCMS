<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/bootstrap.php';
require __DIR__ . '/includes/header.php';
?>
<main class="tpl-main" id="main-content">
      <?php
      require __DIR__ . '/includes/slider.php';
      require __DIR__ . '/includes/main.php';
      ?>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>