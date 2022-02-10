<section class="siddata-questionnaire">
    <form action="<?= $controller->link_for('siddata/questionnaire/'.$goal['id'].'/'.$context_route) ?>" method="POST" class="default">
        <fieldset>
            <legend>
                <header>
                    <span class="siddata-element-title">
                        <?= _('Fragebogen: ') . htmlReady($goal['goal']) ?>
                    </span>
                    <span class="siddata-element-options">
                        <a href="<?= $controller->link_for('siddata/delete_goal_confirm/' . $goal['id'] . '/' . $context_route) ?>" data-dialog="size=auto;" title="Fragebogen und angegebene Antworten löschen">
                            <?= Icon::create('trash', Icon::ROLE_CLICKABLE) ?>
                        </a>
                        <a href="" class="siddata-goal-collapse" id="siddata-goal-collapse-expand_<?= $goal['id'] ?>" title="Ausklappen" hidden>
                            <?= Icon::create('arr_1left', Icon::ROLE_CLICKABLE) ?>
                        </a>
                        <a href="" class="siddata-goal-collapse" id="siddata-goal-collapse-collapse_<?= $goal['id'] ?>" title="Einklappen">
                            <?= Icon::create('arr_1down', Icon::ROLE_CLICKABLE) ?>
                        </a>
                    </span>
                </header>
            </legend>
            <div class="siddata-goal-content" id="siddata-goal-content_<?= $goal['id'] ?>">
                <?php
                $activities = $goal->getActivities(true);
                ?>
                <? if (empty($activities)): ?>
                <p>
                    In diesem Fragebogen gibt es keine offenen Fragen mehr.
                </p>
                <p>
                    Die angegebenen Antworten sind links oder im Drei-Punkte-Menü unter "Abgeschlossen" einsehbar.
                </p>
                <? else: ?>
                    <? foreach($activities as $activity): ?>
                        <? if ($activity['type'] == 'question'): ?>
                            <? $q = $activity->getQuestion(); ?>
                            <div class="siddata-questionnaire-item">
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
                                    <textarea class="siddata-activity-question-textanswer" name="siddata-questionnaire-answer_<?= $goal['id'] . '['.$activity['id'].']' ?>" required placeholder="<?= empty($activity['answers'])? 'Hier kann eine Antwort eintragen werden ...': htmlReady(reset($activity['answers'])) ?>"></textarea>
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
                                            <option value="<?= $index ?>" <?= $activity['answers'] == $answer? 'selected': '' ?>><?= htmlReady($answer) ?></option>
                                        <? endforeach; ?>
                                    </select>
                                    <div class="siddata-questionnaire-nolikert-dummy"></div>
                                <? elseif ($q->getType() == 'checkbox'): ?>
                                    <? foreach($q->getSelectionAnswers() as $index => $answer): ?>
                                        <label for="siddata-answer_<?= $activity['id']?>[<?=$index?>]" class="siddata-activity-question-checkboxanswer">
                                            <div class="siddata-checkbox">
                                                <input type="checkbox" name="siddata-questionnaire-answer_<?= $goal['id'] . '['.$activity['id'].'][' . $index . ']' ?>" value="<?= $index ?>" id="siddata-answer_<?= $activity['id']?>[<?=$index?>]"
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
                            </div>
                        <? endif; ?>
                    <? endforeach; ?>
                    <? if ($_SESSION['SIDDATA_view'] == 'all'): ?>
                        <footer>
                            <?= Studip\Button::createAccept('Abschicken') ?>
                        </footer>
                    <? endif; ?>
                <? endif; ?>
            </div>
        </fieldset>
    </form>
</section>
