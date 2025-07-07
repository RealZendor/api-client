<?php if (!empty($meta)) : ?>
<h5>Response Meta</h5>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Key</th>
            <th>Value</th>
        </tr>
    </thead>
    <tbody>
    <?php
    if (is_array($meta)) {
        foreach ($meta as $key => $value) {
            if (is_array($value)) {
                echo '<tr><td>' . $key . '</td><td><pre>' . print_r($value, true) . '</pre></td></tr>';
            } else {
                echo '<tr><td>' . $key . '</td><td>' . $value . '</td></tr>';
            }
        }
    } else {
        echo '<tr><td>Meta</td><td>' . $meta . '</td></tr>';
    }
    ?>
    </tbody>
</table>
<?php endif; ?>