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
    if (is_array($headers)) {
        foreach ($headers as $header => $value) {
            echo '<tr><td>' . $header . '</td><td>' . join(', ', $value) . '</td></tr>';
        }
    } else {
        echo '<tr><td>Headers</td><td>' . $headers . '</td></tr>';
    }
    ?>
    </tbody>
</table>
<?php endif; ?>