<?php

$course = $activity->getCourse();

?>
<? if (empty($course->getDescription())) : ?>
    <p>Für diesen Kurs gibt es keine Beschreibung.</p>
<? else: ?>
    <div class="siddata-activity-description" id="siddata-activity-description_<?= $activity['id'] ?>"><?= htmlFormatReady($course->getDescription()) ?></div>
    <div class="siddata-activity-description-toggle" id="siddata-activity-description-toggle_<?= $activity['id'] ?>">
        <a id="siddata-activity-showmore_<?= $activity['id'] ?>" class="siddata-activity-description-showmore" hidden>mehr <?= Icon::create('arr_1left', Icon::ROLE_CLICKABLE, ['id' => 'siddata-toggle-icon-left_'.$activity['id']]) ?></a>
        <a id="siddata-activity-showless_<?= $activity['id'] ?>" class="siddata-activity-description-showless" hidden>weniger <?= Icon::create('arr_1up', Icon::ROLE_CLICKABLE, ['id' => 'siddata-toggle-icon-up_'.$activity['id']]) ?></a>
    </div>
<? endif; ?>
<?php
if ($activity['times_rooms']) {
    echo "<p>";
    echo "<strong>" . _("Zeit / Veranstaltungsort") . "</strong>" . "<br>";
    echo htmlReady($activity['times_rooms']);
    echo "</p>";
}
if ($activity['next_date']) {
    echo "<p>";
    echo "<strong>" . _("Nächster Termin") . "</strong>" . "<br>";
    echo htmlReady($activity['next_date']);
    echo "</p>";
}
if ($activity['first_date']) {
    echo "<p>";
    echo "<strong>" . _("Erster Termin") . "</strong>" . "<br>";
    echo htmlReady($activity['first_date']);
    echo "</p>";
}
if ($activity['lecturers']) {
    echo "<p>";
    echo "<strong>" . _("Lehrende") . "</strong>" . "<br>";
    echo implode(", ", htmlReady($activity['lecturers']));
    echo "</p>";
}
?>
<? if ($url = $activity->getCourse()->getUrl()): ?>
    <p></p>
    <p>
        <a href="<?= htmlReady($url) ?>">Zum Kurs ...</a>
    </p>
<? endif; ?>
