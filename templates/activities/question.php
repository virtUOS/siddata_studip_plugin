<?php
$question = $activity->getQuestion();
?>

<div class="siddata-activity-question">
    <p>
        <?= htmlFormatReady($question->getText()) ?>
    </p>
    <? if ($question->getType() == 'selection'): ?>
        <? if (count($question->getSelectionAnswers()) > 7): ?>
            <select class="siddata-activity-question-selectanswer" id="siddata-answer_<?= $activity['id'] ?>" name="siddata-answer_<?= $activity['id'] ?>" required>
                <option value="" disabled selected>Bitte auswählen...</option>
                <? foreach($question->getSelectionAnswers() as $index => $answer): ?>
                    <option value="<?= htmlReady($answer) ?>"><?= htmlReady($answer) ?></option>
                <? endforeach; ?>
            </select>
        <? else: ?>
            <? foreach($question->getSelectionAnswers() as $index => $answer): ?>
                <label for="siddata-answer_<?= $activity['id'] . $index ?>" class="siddata-activity-question-radioanswer">
                    <div class="siddata-checkbox">
                        <input type="radio" id="siddata-answer_<?= $activity['id'] . $index ?>" name="siddata-answer_<?= $activity['id'] ?>" value="<?= htmlReady($answer) ?>"
                            <?= (is_array($activity['answers']) && in_array($answer, $activity['answers'])) ? 'checked' : '' ?> required>
                    </div>
                    <div class="siddata-checkbox-label">
                        <?= htmlReady($answer) ?>
                    </div>
                </label>
                <br>
            <? endforeach; ?>
        <? endif; ?>
    <? elseif($question->getType() == 'text'): ?>
        <textarea class="siddata-activity-question-textanswer" id="siddata-answer_<?= $activity['id'] ?>" name="siddata-answer_<?= $activity['id'] ?>" required placeholder="Hier kann eine Antwort eintragen werden ..."></textarea>
    <? elseif($question->getType() == 'checkbox'): ?>
        <? foreach($question->getSelectionAnswers() as $index => $answer): ?>
            <label for="siddata-answer_<?= $activity['id'] ?>[<?=$index?>]" class="siddata-activity-question-checkboxanswer">
                <div class="siddata-checkbox">
                    <input type="checkbox" name="siddata-answer_<?= $activity['id'] ?>[<?=$index?>]" value="<?= $index ?>" id="siddata-answer_<?= $activity['id'] ?>[<?=$index?>]"
                           <?= (is_array($activity['answers']) && in_array($answer, $activity['answers'])) ? 'checked' : '' ?> >
                </div>
                <div class="siddata-checkbox-label">
                    <?= htmlReady($answer) ?>
                </div>
            </label>
            <br>
        <? endforeach; ?>
    <? elseif ($question->getType() == 'multitext'): ?>
        <? if (!$activity['inactive']): ?>
        <div class="siddata-multitext-answers">
            <input type="text" id="siddata-answer_<?= $activity['id'] ?>" name="siddata-answer_<?= $activity['id'] ?>[0]">
            <?= Studip\Button::create('Noch eine Antwort', '', ['class' => 'siddata-add-answer-button', 'id' => 'siddata-add-answer-button_'.$activity['id']]) ?>
        </div>
        <? else: ?>
        <ul>
            <? foreach ($activity['answers'] as $answer): ?>
                <li><?= htmlReady($answer) ?></li>
            <? endforeach; ?>
        </ul>
        <? endif; ?>
    <? elseif ($question->getType() == 'auto_completion'): ?>
        <?php
        $search = new SiddataAnswerSearch($question->getSelectionAnswers());
        echo QuickSearch::get("siddata-answer_".$activity['id'], $search)->setInputStyle("width: 240px")->render();
        ?>
    <? elseif ($question->getType() == 'date' || $question->getType() == 'datetime'): ?>
        <div class="siddata-activity-question-date">
            <label for="siddata-answer_<?= $activity['id'] ?>[date]" class="siddata-activity-question-date-answer">
                <div class="siddata-question-date-label">
                    Datum:
                </div>
                <div class="siddata-question-date-answer">
                    <input type="text" class="siddata-activity-question-dateanswer" id="siddata-answer_<?= $activity['id'] ?><?= $question->getType() == 'datetime' ? '[date]' : '' ?>"
                           name="siddata-answer_<?= $activity['id'] ?><?= $question->getType() == 'datetime' ? '[date]' : '' ?>" data-date-picker>
                </div>
            </label>
            <? if ($question->getType() == 'datetime'): ?>
                <br>
                <label for="siddata-answer_<?= $activity['id'] ?>[time]" class="siddata-activity-question-date-answer">
                    <div class="siddata-question-date-label">
                        Zeit:
                    </div>
                    <div class="siddata-question-date-answer">
                        <input type="text" class="siddata-activity-question-timeanswer" id="siddata-answer_<?= $activity['id'] ?>[time]" name="siddata-answer_<?= $activity['id'] ?>[time]" data-time-picker>
                    </div>
                </label>
            <? endif; ?>
        </div>
    <? endif; ?>
</div>


