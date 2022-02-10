<div class="siddata">
    <form method="POST" action="<?= $controller->link_for('siddata/activity_submit') ?>" class="default">
        <div>
            <label for="siddata-activity-title-input">Titel*:
                <input type="text" id="siddata-activity-title-input" name="siddata-activity-title-input" required>
            </label>

            <label for="ende">Zu erledigen bis:
                <input type="text" id="ende" name="ende" data-datetime-picker>
            </label>

            <label for="siddata-activity-description-input">Beschreibung*:
                <textarea id="siddata-activity-description-input" name="siddata-activity-description-input" required></textarea>
            </label>

            <? if(count($goals) > 1): ?>
            <label>
                <select id="siddata-activity-goal-input" name="siddata-activity-goal-input" required>
                    <? foreach($goals as $goal): ?>
                        <option value="<?= $goal['id'] ?>"><?= $goal['goal'] ?></option>
                    <? endforeach; ?>
                </select>
                <? elseif (count($goals) == 1): ?>
                <input type="text" id="siddata-activity-goal-input" name="siddata-activity-goal-input" value="<?= array_keys($goals)[0] ?>" hidden>
                <? endif; ?>
            </label>
        </div>

        <div id="siddata-footnote">
            <p>* Diese Felder müssen ausgefüllt werden.</p>
        </div>

        <footer data-dialog-button>
            <?= Studip\Button::createAccept("Abschicken") ?>
            <?= Studip\LinkButton::createCancel("Abbrechen", null, ["data-dialog" => "close"]) ?>
        </footer>
    </form>
</div>
