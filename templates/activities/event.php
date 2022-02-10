<? if (isset($activity['date']) or isset($activity['place'])): ?>
    <p>
        <?= isset($activity['date'])? "Datum: <strong>" . date("d.m.y", htmlReady($activity['date'])) . "</strong>" : "" ?>
        <?= $activity['place']? "<br>Ort: <strong>" . htmlReady($activity['place']) . "</strong>": "" ?>
    </p>
<? endif; ?>
<? if (isset($activity['description'])): ?>
    <div class="siddata-activity-description" id="siddata-activity-description_<?= $activity['id'] ?>"><?= htmlFormatReady(['description']) ?></div>
    <div class="siddata-activity-description-toggle" id="siddata-activity-description-toggle_<?= $activity['id'] ?>">
        <a id="siddata-activity-showmore_<?= $activity['id'] ?>" class="siddata-activity-description-showmore" hidden>mehr <?= Icon::create('arr_1left', Icon::ROLE_CLICKABLE, ['id' => 'siddata-toggle-icon-left_'.$activity['id']]) ?></a>
        <a id="siddata-activity-showless_<?= $activity['id'] ?>" class="siddata-activity-description-showless" hidden>weniger <?= Icon::create('arr_1up', Icon::ROLE_CLICKABLE, ['id' => 'siddata-toggle-icon-up_'.$activity['id']]) ?></a>
    </div>
<? endif; ?>

<? if ($url = $activity->getEvent()->getUrl()): ?>
    <p></p>
    <p>
        <a href="<?= htmlReady($url) ?>">Zum Termin ...</a>
    </p>
<? endif; ?>

