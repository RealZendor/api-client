<?php if (!empty($headers)) : ?>
<h5>Response Headers</h5>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Header</th>
            <th>Value</th>
        </tr>
    </thead>
    <tbody>
    <?php
    foreach ($headers as $header => $value) {
        echo '<tr><td>' . $header . '</td><td>' . join(', ', $value) . '</td></tr>';
    }
    ?>
    </tbody>
</table>
<?php endif; ?>