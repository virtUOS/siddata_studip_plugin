<div class="siddata-dialog">
    <p>
        Bist du sicher, dass du löschen möchtest?
    </p> <p>
        Empfehlungen und Fragen des Siddata-Studienassistenten und angegebene Antworten zu diesem Element werden unwiderruflich gelöscht.
    </p>
    <footer data-dialog-button>
        <?= Studip\LinkButton::createAccept('Ja', $controller->link_for('siddata/delete_goal/' . $goal_id . '/' . $context_route . '/' . $rec_id)) ?>
        <?= Studip\LinkButton::createCancel('Nein', $controller->link_for('siddata/' . $context_route . '/' . $rec_id)) ?>
    </footer>
</div>
