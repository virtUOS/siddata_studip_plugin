<?php
if ($activity['og']) {
    echo "<p></p><div class='siddata-activity-og'>";
    echo $activity['og'];
    echo "</div></p>";
}

$resource = $activity->getResource();
?>
<? if (empty($resource->getDescription())): ?>
    <p>Für diese Activity gibt es keine Beschreibung.</p>
<? else: ?>
    <div class="siddata-activity-description" id="siddata-activity-description_<?= $activity['id'] ?>"><?= htmlFormatReady($resource->getDescription()) ?></div>
    <div class="siddata-activity-description-toggle" id="siddata-activity-description-toggle_<?= $activity['id'] ?>">
        <a id="siddata-activity-showmore_<?= $activity['id'] ?>" class="siddata-activity-description-showmore" hidden>mehr <?= Icon::create('arr_1left', Icon::ROLE_CLICKABLE, ['id' => 'siddata-toggle-icon-left_'.$activity['id']]) ?></a>
        <a id="siddata-activity-showless_<?= $activity['id'] ?>" class="siddata-activity-description-showless" hidden>weniger <?= Icon::create('arr_1up', Icon::ROLE_CLICKABLE, ['id' => 'siddata-toggle-icon-up_'.$activity['id']]) ?></a>
    </div>
<? endif; ?>
<? if ($resource->getFormat()): ?>
    <p></p>
    <p>
        Format: <?= htmlReady($resource->getFormat()) ?>
    </p>
<? endif; ?>
<? if ($resource->getUrl()): ?>
    <? if (!$resource->isIframe()): ?>
        <p></p>
        <p>
            Zur Website: <a href="<?= $controller->link_for('siddata/show_resource/'.$activity['id']) ?>" target="_blank"><?= htmlReady($activity['url_text'])? : htmlReady($resource->getUrl()) ?></a>
        </p>
    <? else: ?>
        <p></p>
        <div class="siddata-activity-iframe" id="siddata-activity-iframe_<?= $activity['id'] ?>">
            <iframe src="<?= htmlReady($resource->getUrl()) ?>" title="<?= htmlReady($resource->getTitle()) ?>" allowfullscreen
                sandbox="allow-forms allow-popups allow-pointer-lock allow-same-origin allow-scripts"></iframe>
        </div>
    <? endif; ?>
<? endif; ?>

