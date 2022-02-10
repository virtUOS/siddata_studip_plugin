<?php
if ($activity['og']) {
    echo "<p></p><div class='siddata-activity-og'>";
    echo $activity['og'];
    echo "</div></p>";
}
?>
<? if (isset($activity['description'])): ?>
    <div class="siddata-activity-description" id="siddata-activity-description_<?= $activity['id'] ?>"><?= htmlFormatReady($activity['description']) ?></div>
    <div class="siddata-activity-description-toggle" id="siddata-activity-description-toggle_<?= $activity['id'] ?>">
        <a id="siddata-activity-showmore_<?= $activity['id'] ?>" class="siddata-activity-description-showmore" hidden>mehr <?= Icon::create('arr_1left', Icon::ROLE_CLICKABLE, ['id' => 'siddata-toggle-icon-left_'.$activity['id']]) ?></a>
        <a id="siddata-activity-showless_<?= $activity['id'] ?>" class="siddata-activity-description-showless" hidden>weniger <?= Icon::create('arr_1up', Icon::ROLE_CLICKABLE, ['id' => 'siddata-toggle-icon-up_'.$activity['id']]) ?></a>
    </div>
<? endif; ?>
<? if ($activity['url'] and !$activity['og']): ?>
    <p></p>
    <p>
        Zusätzliche Informationen unter: <a href="<?= htmlReady($activity['url']) ?>" target="_blank"><?= htmlReady($activity['url_text'])? : htmlReady($activity['url']) ?></a>
    </p>
<? endif; ?>

