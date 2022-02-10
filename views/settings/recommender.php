<div class="siddata-settings siddata">
    <div class="siddata-settings-text">
        <h1>Deine Siddata-Funktionen</h1>

        <p>
            Siddata besteht aus verschiedenen Funktionsmodulen, die dir zu unterschiedlichen Themen Dinge empfehlen.
            Hier kannst du auswählen, welche dieser Funktionen du nutzen möchtest und welche nicht.
        </p>
    </div>
    <div>
        <form class="default" action="<?= $controller->link_for('settings/post_recommender') ?>" method="POST">
            <fieldset>
                <legend>Welche Funktionen möchtest du nutzen?</legend>
                <ul class="boxed-grid">
                    <? if (isset($available_recommenders)): ?>
                        <? foreach ($available_recommenders as $recommender): ?>
                            <? if ($recommender['attributes']['name'] != 'Startseite'): ?>
                                <li>
                                    <h3><?= htmlReady($recommender['attributes']['name']) . " " . tooltipIcon($recommender['attributes']['data_info']) ?></h3>
                                    <label class="siddata-switch" title="Die '<?=htmlReady($recommender['attributes']['name'])?>'-Funktion nutzen">
                                        <input type="checkbox" name="siddata-activate-rec[]" value="<?= $recommender['id'] ?>" <?= in_array($recommender['id'], $activated_recommender_ids)? 'checked': '' ?>>
                                        <span class="siddata-slider"></span>
                                    </label>
                                    <p><?= htmlReady($recommender['attributes']['description']) ?></p>
                                </li>
                            <? endif; ?>
                        <? endforeach; ?>
                    <? endif; ?>
                </ul>
                <footer>
                    <?= Studip\Button::createAccept('Abschicken') ?>
                    <?= Studip\LinkButton::create('Zum Studienassistenten', $controller->link_for('siddata')) ?>
                </footer>
            </fieldset>
        </form>
    </div>
    <?php
    if ($controller->plugin->debug) {
        $debug_template = $this->factory->open('debug');
        echo $debug_template->render();
    }
    ?>
</div>
