<?php
$spec_template = $controller->factory->open('activities/' . $activity['type']);
$spec_template->set_attribute('activity', $activity);
$spec_template->set_attribute('controller', $controller);
$spec_template->set_attribute('description', $description);
$feedback_path = $controller->plugin->getPluginURL() . '/assets/images/feedback/';
$feedbackImgSize = 28;

// feedback is required if we have no numerical feedback yet, feedback size for this activity is valid and forced feedback is activate
$feedback_required =  !isset($activity['feedback_value']) && $activity['feedback_size'] > 1 && $_SESSION['SIDDATA_forced_feedback'] == true;
?>

<section class="siddata-activity<?= isset($activity['color_theme']) ? " siddata-activity-color-".htmlReady($activity['color_theme']) : "" ?><?= $activity['type'] == "iframe" ? " siddata-iframe-activity" : "" ?><?= $activity['inactive'] ? " siddata-inactive" : "" ?>" id="siddata-activity_<?= $activity['id'] ?>">

<? if ($activity['status'] == 'done' || $activity['status'] == 'snoozed' || $activity['status'] == 'discarded'): ?>
    <fieldset disabled class="siddata-fieldset-activity">
<? else : ?>
    <fieldset class="siddata-fieldset-activity">
<? endif; ?>
        <legend>
            <header class="siddata-element-header <?= $activity['inactive'] ? "siddata-inactive-header" : "" ?>">

                <span class="siddata-header-provider-circle left">
                        <img src="<?= Icon::create($activity['type_icon'], ["title" => $activity['display_type']])->asImagePath()?>" class="siddata-header-icon" alt="<?= $activity['display_type'] ?>" title="<?= $activity['display_type'] ?>">
                </span>

                <span class="siddata-element-title">
                    <?= htmlReady($activity['title']) ?>
                </span>
                <span class="siddata-element-options">
                    <? if (!$activity['inactive'] and $activity['status'] != 'immortal'): ?>
                        <?= "<a href='".$controller->link_for('siddata/snooze/'.$activity['id'] . '/' . $context_route)."' title='Pausieren'>". Icon::create('pause', Icon::ROLE_CLICKABLE) . "</a>"; ?>
                        <?= "<a href='".$controller->link_for('siddata/discard/'.$activity['id'] . '/' . $context_route)."' title='Verwerfen'>". Icon::create('decline', Icon::ROLE_CLICKABLE) . "</a>"; ?>
                    <? endif; ?>
                </span>
            </header>
        </legend>

        <? if ($activity['status'] == 'done' || $activity['status'] == 'snoozed' || $activity['status'] == 'discarded'): ?>
        <div class="siddata-activity-body-grey" id="siddata-activity-body-<?= $activity['id'] ?>">
        <? else: ?>
        <div class="siddata-activity-body" id="siddata-activity-body-<?= $activity['id'] ?>">
        <? endif; ?>
            <? if (isset($activity['image'])): ?>
                <div class="siddata-activity-image">
                    <?= $activity['image'] ?>
                </div>

                <div class="siddata-activity-specific">
                    <?= $spec_template->render(); ?>
                </div>
                <? if (!empty($activity['notes'])): ?>
                    <a class="siddata-activity-shownotes" id="siddata-activity-shownotes-<?= $activity['id'] ?>"><?= Icon::create('arr_1left') ?>Notizen anzeigen</a>
                    <a class="siddata-activity-hidenotes" id="siddata-activity-hidenotes-<?= $activity['id'] ?>" hidden><?= Icon::create('arr_1down') ?>Notizen verstecken</a>
                    <div class="siddata-notes" hidden id="siddata-activity-notes-<?= $activity['id'] ?>">
                        <ul>
                            <? foreach ($activity['notes'] as $note): ?>
                                <li><?= htmlReady($note) ?></li>
                            <? endforeach; ?>
                        </ul>
                    </div>
                <? endif; ?>

            <? else: ?>
                <div class="siddata-activity-specific">
                    <?= $spec_template->render(); ?>
                </div>
                <? if ($activity['dueDate']): ?>
                    <p>
                        Verfügbar bis: <b <?= $activity['missed']? 'class="siddata-activity-missed"': '' ?>><?= date("d.m.y H:i", htmlReady($activity['dueDate'])) ?> Uhr</b>
                    </p>
                <? endif; ?>
                <? if (!empty($activity['notes'])): ?>
                    <a class="siddata-activity-shownotes" id="siddata-activity-shownotes_<?= $activity['id'] ?>"><?= Icon::create('arr_1left') ?>Notizen anzeigen</a>
                    <a class="siddata-activity-hidenotes" id="siddata-activity-hidenotes_<?= $activity['id'] ?>" hidden><?= Icon::create('arr_1down') ?>Notizen verstecken</a>
                    <div class="siddata-notes" hidden id="siddata-activity-notes_<?= $activity['id'] ?>">
                        <ul>
                            <? foreach ($activity['notes'] as $note): ?>
                                <li><?= htmlReady($note) ?></li>
                            <? endforeach; ?>
                        </ul>
                    </div>
                <? endif; ?>
            <? endif; ?>
        </div>

        <? if ($activity['inactive']): ?>
            <div class="siddata-activity-footer" id="siddata-activity-footer-<?= $activity['id'] ?>">
                <span>
                    <strong><?= $activity['display_type'] ?> <?= $activity['display_status'] ?>.</strong>
                </span>
                <? if ($activity->isRestorable()): ?>
                    <div>
                        <?= "<a href='".$controller->link_for('siddata/reactivate/'.$activity['id'] . '/' . $context_route)."' title='Wieder aufnehmen'>". Icon::create('refresh', Icon::ROLE_CLICKABLE) . " Wieder aufnehmen</a>"?>
                    </div>
                <? endif; ?>
            </div>
        <? endif; ?>
        <div class="siddata-activity-footer-submit" id="siddata-activity-feedback-<?= $activity['id'] ?>">
            <? if (!$activity['inactive']):?>
                <? if ($activity['feedback_size'] > 1): ?>
                    <div id="siddata-feedback-options_<?= $activity['id'] ?>" class="siddata-feedback-options">
                        <span class="siddata-feedback-options-block">
                            <? for ($i = $activity['feedback_size']; $i > 0; $i--): ?>
                                <label tabindex="0">
                                    <input type="radio" value=<?= $i ?> name="siddata-feedback-<?= $activity['id'] ?>" <?= $feedback_required? 'required': '' ?>>
                                    <?= Icon::create($feedback_path . $activity->getFeedbackNames()[$i].'.svg', Icon::ROLE_CLICKABLE,
                                        [
                                            "id" => "siddata-feedback-".$activity->getFeedbackNames()[$i]."_".$activity["id"],
                                            "class" => "siddata-feedback-option ". $activity->getFeedbackNames()[$i],
                                            "title" => $activity->getFeedbackTitle($activity->getFeedbackNames()[$i])
                                        ])->asImg($feedbackImgSize)  ?>
                                </label>
                            <? endfor; ?>
                            <label tabindex="0">
                                <a href="" class="siddata-feedback-text-button" id="siddata-feedback-text-button_<?= $activity['id'] ?>">
                                    <?= Icon::create('comment', Icon::ROLE_CLICKABLE,
                                        ["id" => "siddata-feedback-text_".$activity["id"],
                                            "class" => "siddata-feedback-text",
                                            "title" => "Einen Kommentar verfassen"
                                        ])->asImg($feedbackImgSize-4) ?>
                                </a>
                            </label>
                        </span>
                        <div class="siddata-feedback-text-div" id="siddata-feedback-text-div_<?= $activity['id'] ?>" hidden>
                            <label for="siddata-feedback-text-input">
                                Bitte teile uns mehr Feedback zu dieser Aktivität mit:
                                <textarea id="siddata-feedback-text-input" name="siddata-feedback-text-input_<?= $activity['id'] ?>"><?= htmlReady($activity['feedback_text'])? : '' ?></textarea>
                            </label>
                        </div>
                    </div>
                <? endif; ?>
            <? endif; ?>
        </div>
        <? if (!$activity['inactive'] and $activity->hasButton()): ?>
            <div class="siddata-activity-footer-submit" id="siddata-activity-footer-<?= $activity['id'] ?>">
                <? if ($activity['type'] == 'question'): ?>
                    <?= Studip\Button::createAccept(htmlReady($activity['button_text'])? : 'Abschicken', null, []); ?>
                <? else: ?>
                    <?= Studip\Button::createAccept(htmlReady($activity['button_text'])? : 'OK', 'siddata-done', ['value' => $activity['id']]); ?>
                <? endif; ?>
                <? if (($activity['type'] == 'resource') && (!empty($activity->getResource()->getUrl()))): ?>
                    <?= Studip\LinkButton::create('Aufrufen', $controller->link_for('siddata/show_resource/'.$activity['id']),['target'=>'_blank', 'title'=>htmlReady($activity->getResource()->getUrl())]); ?>
                <? endif; ?>
                <? if (($activity['type'] == 'course') && (!empty($activity->getCourse()->getUrl()))): ?>
                    <?= Studip\LinkButton::create('Aufrufen', htmlReady($activity->getCourse()->getUrl()),['target'=>'_blank', 'title'=>htmlReady($activity->getCourse()->getUrl())]); ?>
                <? endif; ?>
            </div>
        <? endif;?>
    </fieldset>

</section>

