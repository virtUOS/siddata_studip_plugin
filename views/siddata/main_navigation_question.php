<div class="siddata-dialog">
    <p>
        Möchtest du Siddata in der Hauptnavigation anzeigen, um so schneller auf die Assistenzfunktionen zugreifen zu können? <br>
        Die Entscheidung kannst du später unter <a href='<?= $controller->link_for("profile_settings/index") ?>'>"Profil > Einstellungen > Siddata"</a> wieder ändern und auf Siddata von deinem Profil aus zugreifen.
    </p>
    <footer data-dialog-button>
        <?= Studip\LinkButton::createAccept('Ja', $controller->link_for('siddata/main_navigation_question/1/')) ?>
        <?= Studip\LinkButton::create('Nein', $controller->link_for('siddata/main_navigation_question/0/')) ?>
    </footer>
</div>
