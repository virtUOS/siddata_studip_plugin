<div class="siddata-dialog">
    <p>
        Bist du sicher, dass du löschen möchtest?
    </p> <p>
        Alle berechneten Empfehlungen und Fragen des Siddata-Studienassistenten und angegebene Antworten werden unwiderruflich gelöscht.
        Außerdem wird Siddata aus deiner Hauptnavigation entfernt. Du kannst Siddata jederzeit wieder zur Hauptnavigation hinzufügen. Dies kannst du in deinen Profileinstellugen tun. <br>
        Sobald du den Siddata-Studienassistenten wieder aufrufst wird für dich ein neues Siddata-Konto erstellt. Deine alten Daten bleiben gelöscht.
    </p>
    <footer data-dialog-button>
        <?= Studip\LinkButton::createAccept('Ja', $controller->link_for('settings/delete_student')) ?>
        <?= Studip\LinkButton::createCancel('Nein', $controller->link_for('settings/index')) ?>
    </footer>
</div>
