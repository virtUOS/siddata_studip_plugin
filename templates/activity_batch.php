<?php
$sample_activity = reset($activities);
$batch_id = htmlReady($sample_activity['form']);
$goal = $sample_activity['goal'];

$feedback_path = $controller->plugin->getPluginURL() . '/assets/images/feedback/';
$feedbackImgSize = 28;

// feedback is required if we have no numerical feedback yet, feedback size for this activity is valid and forced feedback is activate
$feedback_required =  !isset($sample_activity['feedback_value']) && $sample_activity['feedback_size'] > 1 && $_SESSION['SIDDATA_forced_feedback'] == true;
?>

<section class="siddata-questionnaire siddata-activity<?= isset($sample_activity['color_theme']) ? " siddata-activity-color-".htmlReady($sample_activity['color_theme']) : "" ?><?= $sample_activity['inactive'] ? " siddata-inactive" : "" ?>" id="siddata-activity_<?= htmlReady($sample_activity['id']) ?>">
    <fieldset class="siddata-fieldset-activity">
        <legend>
            <header class="siddata-element-header <?= htmlReady($sample_activity['inactive']) ? "siddata-inactive-header" : "" ?>">

                <span class="siddata-header-provider-circle left">
                        <img src="<?= Icon::create('archive3', ["title" => $sample_activity['display_type']])->asImagePath()?>" class="siddata-header-icon" alt="<?=$sample_activity['display_type']?>" title="<?=$sample_activity['display_type']?>">
                </span>

                <span class="siddata-element-title">
                    <?= htmlReady($sample_activity['title']) ?>
                </span>
                <span class="siddata-element-options">
                <? if (!$sample_activity['inactive'] and $sample_activity['status'] != 'immortal'): ?>
                    <?= "<a href='".$controller->link_for('siddata/snooze_batch/'.$goal['id'] . '/' .$batch_id . '/' . $context_route)."' title='Pausieren'>". Icon::create('pause', Icon::ROLE_CLICKABLE) . "</a>"; ?>
                    <?= "<a href='".$controller->link_for('siddata/discard_batch/'.$goal['id'] . '/' .$batch_id . '/' . $context_route)."' title='Verwerfen'>". Icon::create('decline', Icon::ROLE_CLICKABLE) . "</a>"; ?>
                <? endif; ?>
                </span>
            </header>
        </legend>
        <div class="siddata-batch-content" id="siddata-batch-content_<?= $goal['id'] ?>_<?= $batch_id ?>">
            <? if (empty($activities)): ?>
            <p>
                In diesem Fragebogen gibt es keine offenen Fragen mehr.
            </p>
            <p>
                Die angegebenen Antworten sind links oder im Drei-Punkte-Menü unter "Abgeschlossen" einsehbar.
            </p>
            <? else: ?>
                <? foreach($activities as $activity): ?>
                    <div class="siddata-questionnaire-item">
                    <? if ($activity['type'] == 'question'): ?>
                        <?
                        $q = $activity->getQuestion();
                        $answers = $activity->getAnswers();
                        ?>
                            <?= $q->getText() ?>
                            <? if ($q->getType() == 'likert'): ?>
                                <div class="siddata-activity-question-likertanswers">
                                    <ul>
                                        <? foreach($q->getSelectionAnswers() as $index => $answer): ?>
                                            <li><label>
                                                    <input type="radio" name="siddata-questionnaire-answer_<?= $goal['id'] . '['.$activity['id'].']' ?>"
                                                           value="<?= $index ?>" required <?= $answer == $activity['answers'][0]? 'checked': '' ?>>
                                                    <?= htmlReady($answer) ?>
                                                </label></li>
                                        <? endforeach; ?>
                                    </ul>
                                </div>
                            <? elseif ($q->getType() == 'text'): ?>
                                <textarea class="siddata-activity-question-textanswer" name="siddata-questionnaire-answer_<?= $goal['id'] . '['.$activity['id'].']' ?>" required placeholder="<?= empty($activity['answers'])? 'Hier kann eine Antwort eintragen werden ...': htmlReady(reset($activity['answers'])) ?>"> <?= !empty($answers)? htmlReady($answers[0]): "" ?> </textarea>
                                <div class="siddata-questionnaire-nolikert-dummy"></div>
                            <? elseif ($q->getType() == 'auto_completion'): ?>
                                <?php
                                $search = new SiddataAnswerSearch($q->getSelectionAnswers());
                                echo QuickSearch::get("siddata-questionnaire-answer_".$goal['id'].'['.$activity['id'].']', $search)->setInputStyle("width: 240px")->setAttributes(['required' => true])->render();
                                ?>
                                <div class="siddata-questionnaire-nolikert-dummy"></div>
                            <? elseif ($q->getType() == 'selection'): ?>
                                <select class="siddata-activity-question-selectanswer" name="siddata-questionnaire-answer_<?= $goal['id'] . '['.$activity['id'].']' ?>" required>
                                    <option value="" disabled <?= empty($activity['answers'])? 'selected' : ''?>>Bitte auswählen...</option>
                                    <? foreach($q->getSelectionAnswers() as $index => $answer): ?>
                                        <option value="<?= $index ?>" <?= $activity['answers'] == [$answer]? 'selected': '' ?>><?= htmlReady($answer) ?></option>
                                    <? endforeach; ?>
                                </select>
                                <div class="siddata-questionnaire-nolikert-dummy"></div>
                            <? elseif ($q->getType() == 'checkbox'): ?>
                                <? foreach($q->getSelectionAnswers() as $index => $answer): ?>
                                    <label for="siddata-answer_<?= $activity['id'] ?>[<?=$index?>]" class="siddata-activity-question-checkboxanswer">
                                        <div class="siddata-checkbox">
                                            <input type="checkbox" name="siddata-questionnaire-answer_<?= $goal['id'] . '['.$activity['id'].'][' . $index . ']' ?>" value="<?= $index ?>" id="siddata-answer_<?= $activity['id'] ?>[<?=$index?>]"
                                                <?= (is_array($activity['answers']) && in_array($answer, $activity['answers'])) ? 'checked' : '' ?> >
                                        </div>
                                        <div class="siddata-checkbox-label">
                                            <?= htmlReady($answer) ?>
                                        </div>
                                    </label>
                                <? endforeach; ?>
                                <div class="siddata-questionnaire-nolikert-dummy"></div>
                            <? elseif ($q->getType() == 'multitext'): ?>
                                <? if (!$activity['inactive']): ?>
                                    <div class="siddata-multitext-answers">
                                        <input type="text" id="siddata-answer_<?= $activity['id'] ?>" name="siddata-questionnaire-answer_<?= $goal['id'] . '['.$activity['id'].'][0]'?>" required>
                                        <?= Studip\Button::create('Noch eine Antwort', '', ['class' => 'siddata-add-answer-button-questionnaire', 'id' => 'siddata-add-answer-button_'.$goal['id'].'_'.$activity['id']]) ?>
                                    </div>
                                <? else: ?>
                                    <ol title="Meine Antworten">
                                        <? foreach ($activity['answers'] as $answer): ?>
                                            <li><?= htmlReady($answer) ?></li>
                                        <? endforeach; ?>
                                    </ol>
                                <? endif; ?>
                                <div class="siddata-questionnaire-nolikert-dummy"></div>
                            <? elseif ($q->getType() == 'date' || $q->getType() == 'datetime'): ?>
                                <label for="siddata-answer_<?= $activity['id'] ?>[date]" class="siddata-activity-question-date-answer">
                                    <div class="siddata-question-date-label">
                                        Datum:
                                    </div>
                                    <div class="siddata-question-date-answer">
                                        <input type="text" class="siddata-activity-question-dateanswer" id="siddata-answer_<?= $activity['id'] ?>"
                                               name="siddata-questionnaire-answer_<?= $goal['id'] . '['.$activity['id'].']'?><?= $q->getType() == 'datetime' ? '[date]' : '' ?>" data-date-picker>
                                    </div>
                                </label>
                                <? if ($q->getType() == 'datetime'): ?>
                                    <label for="siddata-answer_<?= $activity['id'] ?>[time]" class="siddata-activity-question-date-answer">
                                        <div class="siddata-question-date-label">
                                            Zeit:
                                        </div>
                                        <div class="siddata-question-date-answer">
                                            <input type="text" class="siddata-activity-question-timeanswer" id="siddata-answer_<?= $activity['id'] ?>" name="siddata-questionnaire-answer_<?= $goal['id'] . '['.$activity['id'].'][time]'?>" data-time-picker>
                                        </div>
                                    </label>
                                <? endif; ?>
                                <div class="siddata-questionnaire-nolikert-dummy"></div>
                            <? endif; ?>
                    <? else: ?>
                        <? if ($activity['image']): ?>
                            <div class="siddata-activity-image">
                                <?= $activity['image'] ?>
                            </div>
                        <? endif; ?>
                        <div class="siddata-activity-specific">
                            <?php
                            $spec_template = $controller->factory->open('activities/' . $activity['type']);
                            $spec_template->set_attribute('activity', $activity);
                            $spec_template->set_attribute('controller', $controller);
                            echo $spec_template->render();
                            ?>
                            <input type="checkbox" name="siddata-questionnaire-answer_<?= $goal['id'] . '['.$activity['id'].'][' . $index . ']' ?>" value="<?= $index ?>" id="siddata-answer_<?= htmlReady($activity['id']) ?>[<?=$index?>]" checked hidden>
                        </div>
                    <? endif; ?>
                    </div>
                <? endforeach; ?>
                <? if ($_SESSION['SIDDATA_view'] == 'all'): ?>
                    <div class="siddata-activity-footer-submit" id="siddata-activity-feedback-<?= $goal['id'].$sample_activity['form'] ?>">
                        <? if (!$sample_activity['inactive']):?>
                            <? if ($sample_activity['feedback_size'] > 1): ?>
                                <div id="siddata-feedback-options_<?= $goal['id'].$sample_activity['form'] ?>" class="siddata-feedback-options">
                                    <span class="siddata-feedback-options-block">
                                        <? for ($i = $sample_activity['feedback_size']; $i > 0; $i--): ?>
                                            <label tabindex="0">
                                                <input type="radio" value=<?= $i ?> name="siddata-batch-feedback-<?= $goal['id'].$sample_activity['form'] ?>" <?= $feedback_required? 'required': '' ?>>
                                                <?= Icon::create($feedback_path . $sample_activity->getFeedbackNames()[$i].'.svg', Icon::ROLE_CLICKABLE,
                                                    [
                                                        "id" => "siddata-feedback-".$sample_activity->getFeedbackNames()[$i]."_".$goal['id'].$sample_activity['form'],
                                                        "class" => "siddata-feedback-option ". $sample_activity->getFeedbackNames()[$i],
                                                        "title" => $sample_activity->getFeedbackTitle($sample_activity->getFeedbackNames()[$i])
                                                    ])->asImg($feedbackImgSize)  ?>
                                            </label>
                                        <? endfor; ?>
                                        <label tabindex="0">
                                            <a href="" class="siddata-feedback-text-button" id="siddata-feedback-text-button_<?= $goal['id'].$sample_activity['form'] ?>">
                                                <?= Icon::create('comment', Icon::ROLE_CLICKABLE,
                                                    ["id" => "siddata-feedback-text_".$goal['id'].$sample_activity['form'],
                                                        "class" => "siddata-feedback-text",
                                                        "title" => "Einen Kommentar verfassen"
                                                    ])->asImg($feedbackImgSize-4) ?>
                                            </a>
                                        </label>
                                    </span>
                                    <div class="siddata-feedback-text-div" id="siddata-feedback-text-div_<?= $goal['id'].$sample_activity['form'] ?>" hidden>
                                        <label for="siddata-feedback-text-input">
                                            Bitte teile uns mehr Feedback zu dieser Aktivität mit:
                                            <textarea id="siddata-feedback-text-input" name="siddata-batch-feedback-text-input_<?= $goal['id'].$sample_activity['form'] ?>"><?= htmlReady($sample_activity['feedback_text'])? : '' ?></textarea>
                                        </label>
                                    </div>
                                </div>
                            <? endif; ?>
                        <? endif; ?>
                    </div>
                    <? if (!$sample_activity['inactive'] and $sample_activity->hasButton()): ?>
                        <div class="siddata-activity-footer-submit" id="siddata-activity-footer-<?= $goal['id'].$sample_activity['form'] ?>">
                            <?= Studip\Button::createAccept(htmlReady($sample_activity['button_text'])?: 'Abschicken') ?>
                        </div>
                    <? endif;?>
                <? elseif ($sample_activity['inactive']): ?>
                    <div class="siddata-activity-footer" id="siddata-activity-footer-<?= $sample_activity['id'] ?>">
                        <span>
                            <strong><?= $sample_activity['display_type'] ?> <?= $sample_activity['display_status'] ?>.</strong>
                        </span>
                        <? if ($activity->isRestorable()): ?>
                            <div>
                                <?= "<a href='".$controller->link_for('siddata/reactivate_batch/'.$goal['id'] . '/' . $sample_activity['form'] . '/' .$context_route)."' title='Wieder aufnehmen'>". Icon::create('refresh', Icon::ROLE_CLICKABLE) . " Wieder aufnehmen</a>"?>
                            </div>
                        <? endif; ?>
                    </div>
                <? endif; ?>
            <? endif; ?>
        </div>
    </fieldset>
</section>
