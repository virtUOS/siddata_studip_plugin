<?php
$person = $activity->getPerson();
?>

<div class="siddata-activity-person">

<? if ($person->isEditable()): ?>

    <? if (!$activity['inactive']): ?>
        <p>
            <label class="file-upload">
                <input type="file" accept="image/png,image/jpeg,image/gif" name="siddata-person-image_<?= $activity['id'] ?>">
                <?= _('Neues Profilbild hochladen') ?>
            </label>
            <? if ($activity['image'] != null and $activity['image'] != Avatar::getNobody()->getImageTag(Avatar::MEDIUM)): ?>
            <label>
                <input type="checkbox" name="siddata-person-delete-image_<?= $activity['id'] ?>">
                <?= _('Profilbild löschen') ?>
            </label>
            <? endif; ?>
        </p>
    <? endif; ?>

    <p>
        <label for="siddata-person-firstname_<?= $activity['id'] ?>" title="Vorname" class="siddata-activity-person-input">
            Vorname:
            <input type="text" name="siddata-person-firstname_<?= $activity['id'] ?>" id="siddata-person-firstname_<?= $activity['id'] ?>" value="<?= htmlReady($person->getFirstName()) ? : "" ?>">
        </label>
        <label for="siddata-person-secondname_<?= $activity['id'] ?>" title="Nachname" class="siddata-activity-person-input">
            Nachname:
            <input type="text" name="siddata-person-secondname_<?= $activity['id'] ?>" id="siddata-person-secondname_<?= $activity['id'] ?>" value="<?= htmlReady($person->getSurname()) ? : "" ?>">
        </label>
    </p>

    <p>
        <label for="siddata-person-description_<?= $activity['id'] ?>" title="Beschreibung" class="siddata-activity-person-input">
            Beschreibung:
            <textarea name="siddata-person-description_<?= $activity['id'] ?>" id="siddata-person-description_<?= $activity['id'] ?>" <?= (!$person->getDescription()? _('placeholder="Beschreibe dich selbst. Wofür interessierst du dich?"') : "") ?>><?= htmlReady($person->getRawDescription())? : "" ?></textarea>
        </label>
    </p>

    <p>
        <label for="siddata-person-email_<?= $activity['id'] ?>" title="E-Mail" class="siddata-activity-person-input">
            E-Mail:
            <input type="email" name="siddata-person-email_<?= $activity['id'] ?>" id="siddata-person-email_<?= $activity['id'] ?>" value="<?= htmlReady($person->getEmail()) ? : "" ?>" required>
        </label>
    </p>

<? else: ?>

    <h1><?= htmlReady($person->getName()) ?></h1>

    <p>
        <?= htmlFormatReady($person->getDescription()) ?>
    </p>

<? if(strlen($person->getRecommendationReason()) > 0): ?>
    <p>
        <?= _('Grund der Empfehlung:'); ?> <?= htmlReady($person->getRecommendationReason()) ?>
    </p>
<? endif; ?>

<? if(filter_var($person->getURL(), FILTER_VALIDATE_URL, array('flags' => null))): ?>
    <p>
        URL: <a target="_blank" href="<?= htmlReady($person->getURL()) ?>"><?= htmlReady($person->getURL()) ?></a>
    </p>
<? endif; ?>

<? if(filter_var($person->getEmail(), FILTER_VALIDATE_EMAIL)): ?>
    <p>
        Mail: <a id="siddata-activity-email_<?= $activity['id'] ?>" class="siddata-activity-email" target="_blank" href="mailto:<?= htmlReady($person->getEmail()) ?>" hidden><?= htmlReady($person->getEmail()) ?></a>
        <a id="siddata-activity-email-show_<?= $activity['id'] ?>" href="<?= $controller->link_for('siddata/show_email/'.$activity['id']) ?>" class="siddata-activity-email-show">
            anzeigen <?= Icon::create('visibility-visible', Icon::ROLE_CLICKABLE) ?>
        </a>
    </p>
<? endif; ?>

<?endif;?>

</div>
