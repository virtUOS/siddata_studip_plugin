<form method="post" action="<?= $controller->link_for('profile_settings/store') ?>" class="default">
    <?= CSRFProtection::tokenTag() ?>

    <fieldset>
        <legend><?= _('Siddata-Einstellungen') ?></legend>

        <label>
            <input type="checkbox" name="main_nav_enabled" value="1"
                <? if ($main_nav_enabled) echo 'checked'; ?>> Siddata-Icon in der Hauptnavigation
        </label>
        <a href="<?= $controller->link_for("siddata") ?>" style="<?= Icon::create('link-intern', Icon::ROLE_CLICKABLE)->asCSS(15) ?>; padding-left: 20px; background-repeat: no-repeat; background-position: 0 -1px;">Zum Siddata-Studienassistent</a>
        <br>
        <a href="<?= $controller->link_for("settings") ?>" style="<?= Icon::create('link-intern', Icon::ROLE_CLICKABLE)->asCSS(15) ?>; padding-left: 20px; background-repeat: no-repeat; background-position: 0 -1px;">Weitere Siddata-Einstellungen</a>
    </fieldset>

    <footer>
        <?= \Studip\Button::createAccept(_("Übernehmen")) ?>
    </footer>
</form>
