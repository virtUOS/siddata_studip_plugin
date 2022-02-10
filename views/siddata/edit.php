<div class="siddata-dialog">
    <form class="default" method="POST" action="<?= $controller->link_for('siddata/edit_submit/' . $activity['id'] . '/' . $context_route . (isset($rec_id)? '/' . $rec_id: '')) ?>">
        <fieldset id="siddata-edit-notes">
            <legend>Notizen</legend>
            <p>Hier kann ich meine eigenen Notizen zu dieser Empfehlung speichern.</p>
            <input type="text" name="siddata-note-input" id="siddata-note-input" placeholder="Hier Notizen eingeben ...">
            <? if (!empty($activity['notes'])): ?>
            <ul>
                <? foreach ($activity['notes'] as $note): ?>
                <li><?= $note ?></li>
                <? endforeach; ?>
            </ul>
            <? endif; ?>
        </fieldset>
        <fieldset id="siddata-date">
            <legend>Ablaufdatum</legend>
            <p>
                Achtung! Wenn das Ablaufdatum nach hinten verlegt wird, könnte es passieren, dass ich den Termin verpasse!
            </p>
            <input type="text" id="ende" name="ende" data-date-picker <?= $activity['duedate']? 'value="'.date("d.m.Y", $activity['duedate']).'"': '' ?>>
        </fieldset>
        <footer data-dialog-button>
            <?= Studip\Button::createAccept('Abschicken') ?>
            <?= Studip\Button::createCancel('Abbrechen') ?>
        </footer>
    </form>
</div>
