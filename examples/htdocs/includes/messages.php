<?php if (!empty($_ERROR)) : ?>
    <div class="alert alert-danger">
        <?php
        foreach ($_ERROR as $error) {
            echo '<p>' . $error . '</p>';
        }
        unset($_ERROR);
        ?>
    </div>
<?php endif; ?>

<?php if (!empty($_MESSAGE)) : ?>
    <div class="alert alert-success">
        <?php
        foreach ($_MESSAGE as $message) {
            echo '<p>' . $message . '</p>';
        }
        unset($_MESSAGE);
        ?>
    </div>
<?php endif; ?>

<?php if (!empty($_WARNING)) : ?>
    <div class="alert alert-warning">
        <?php
        foreach ($_WARNING as $warning) {
            echo '<p>' . $warning . '</p>';
        }
        unset($_WARNING);
        ?>
    </div>
<?php endif; ?>