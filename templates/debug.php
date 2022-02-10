<?php
$debug_data = SiddataDebugLogger::getLog();
$queue = new SplQueue();
if (!empty($debug_data)) {
    $queue->unserialize($debug_data);
    $idx = 0;
}
?>

<fieldset>
    <legend>DEBUG: Requests an das Backend</legend>
    <div class="siddata-accordion">
        <? while(!$queue->isEmpty()): ?>
            <? $request = $queue->dequeue(); ?>
            <h3>
                Request <?= $idx ?>
            </h3>
            <div>
                <ul>
                    <? foreach($request as $key => $value): ?>
                        <li>
                        <? if ($key == 'response_body'): ?>
                            <?= $key . ": " ?>
                            <? echo var_dump(json_decode(htmlspecialchars(html_entity_decode($value), ENT_NOQUOTES, 'utf-8'), true)); ?>
                        <? elseif ($key == 'fields'): ?>
                            <?= $key . ": " ?>
                            <? foreach ($value as $field): ?>
                                <? echo var_dump(json_decode(htmlspecialchars(html_entity_decode($field), ENT_NOQUOTES, 'utf-8'), true)) ?>
                            <? endforeach; ?>
                        <? else: ?>
                            <? if (is_array($value)): ?>
                                <?= $key ?>:<ul>
                                <? foreach($value as $key_ => $value_): ?>
                                    <li><?= $key_ . ": " . preg_replace("/api_key=(.*?)&/", "api_key=SECRET&", $value_) ?></li>
                                <? endforeach; ?>
                                </ul>
                            <? else: ?>
                                <?= $key . ": " . preg_replace("/api_key=(.*?)&/", "api_key=SECRET&", $value) ?>
                            <? endif; ?>
                        <? endif; ?>
                        </li>
                    <? endforeach; ?>
                </ul>
            </div>
            <? $idx++; ?>
        <? endwhile; ?>
    </div>
</fieldset>

<?php
$_SESSION['Siddata_debug_data'] = $queue->serialize();
