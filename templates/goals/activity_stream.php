<? if ($controller->goalQuantity > 1 and $goal->isVisible()): ?>
<fieldset class="siddata-element">
        <div class="siddata-element-header">
            <legend>
                <span class="siddata-element-title">
                    <?php
                    echo htmlReady($goal['goal']);
                    ?>
                </span>
            </legend>
            <div class="siddata-element-options">
                <span>
                        <a href="<?= $controller->link_for('siddata/delete_goal_confirm/' . $goal['id'] . '/' . $context_route) ?>" data-dialog="size=auto;" title="Ziel löschen">
                            <?= Icon::create('trash', Icon::ROLE_INFO_ALT) ?>
                        </a>
                        <? if(count($goal->getActivities($_SESSION['SIDDATA_view'])) > 0): ?>
                        <a href="" class="siddata-goal-collapse" id="siddata-goal-collapse-expand_<?= $goal['id'] ?>" title="Ausklappen" hidden>
                            <?= Icon::create('arr_1left', Icon::ROLE_INFO_ALT) ?>
                        </a>
                        <a href="" class="siddata-goal-collapse" id="siddata-goal-collapse-collapse_<?= $goal['id'] ?>" title="Einklappen">
                            <?= Icon::create('arr_1down', Icon::ROLE_INFO_ALT) ?>
                        </a>
                        <? endif; ?>
                </span>
            </div>
        </div>
<? endif; ?>
    <div class="siddata-goal-content" id="siddata-goal-content_<?= $goal['id'] ?>">
        <div>
            <div id="siddata-goal-properties">
                <? foreach($goal['properties'] as $property) : ?>
                    <form class="siddata-goal-property-form" method="POST" action="<?= $controller->link_for('siddata/goal/' . $property['id'] . '/' . $goal['list']->getId()) ?>">
                        <p id="siddata-goal-property_<?= $property['id'] ?>" class="siddata-goal-property">
                            <?= htmlReady($property['key']) ?>: <?= htmlReady($property['value'])
                            . ($property['editable']? (" <a id='siddata-edit-property-button_".$property['id']."' class='siddata-edit-property-button'>"
                                . Icon::create('edit', Icon::ROLE_CLICKABLE) . "</a>"): '') ?>
                        </p>
                        <p id="siddata-edit-goal-property-p_<?= $property["id"] ?>" hidden class="siddata-goal-edit-property">
                            <?= htmlReady($property["key"]) ?>: <input id="siddata-edit-goal-property_<?= $property["id"] ?>" type="text" value="<?= htmlReady($property['value']) ?>" name="siddata-edit-goal-property_<?= htmlReady($property["id"]) ?>">
                            <a id="siddata-edit-property-close-button_<?= $property['id'] ?>" class="siddata-edit-property-button"><?= Icon::create('decline', Icon::ROLE_CLICKABLE) ?></a>
                        </p>
                    </form>
                <? endforeach; ?>
            </div>
        </div>
        <div class="<?= $goal->getType() == 'carousel' ? "siddata-goal-carousel" : "" ?>">
            <?php
            $batch_buffer = [];
            foreach(SiddataActivity::sortActivities($goal->getActivities(true)) as $activity) {

                // collect activities for form
                if (isset($activity['form'])) {
                    // activity belongs to a batch
                    if (empty($batch_buffer)) {
                        // activity is the first one of it's batch
                        $batch_buffer = [$activity];
                    } else {
                        // buffer is not empty
                        if (end($batch_buffer)["form"] == $activity["form"]) {
                            // activity is not the first one of it's batch
                            $batch_buffer[] = $activity;
                        } else {
                            // old batch ends, new batch starts
                            $contains_person = SiddataDataManager::array_cointains_activity_of_type($batch_buffer, 'person');
                            echo '<form action="'.$controller->link_for('siddata/questionnaire/'.$goal['id'].'/'.$context_route).'" method="POST" class="default" '.($contains_person? 'enctype="multipart/form-data"' : '' ).'>';
                            $controller->render_form($batch_buffer, $goal, $factory, $controller, $context_route);
                            echo '</form>';
                            $batch_buffer = [$activity];
                        }
                    }
                } else {
                    // activity does not belong to a batch
                    if (!empty($batch_buffer)) {
                        // a batch is still pending
                        $contains_person = SiddataDataManager::array_cointains_activity_of_type($batch_buffer, 'person');
                        echo '<form action="'.$controller->link_for('siddata/questionnaire/'.$goal['id'].'/'.$context_route).'" method="POST" class="default" '.($contains_person? 'enctype="multipart/form-data"' : '' ).'>';
                        $controller->render_form($batch_buffer, $goal, $factory, $controller, $context_route);
                        echo '</form>';
                        $batch_buffer = [];
                    }
                    echo '<form class="default siddata-activity-form" method="POST" action="'.$controller->link_for('siddata/activity/'. $activity['id'] .'/'. $context_route).'"'.($activity['type'] == 'person'? ' enctype="multipart/form-data"': '').'>';
                    $controller->render_activity($activity, $factory, $controller, $context_route);
                    echo '</form>';
                }
            }
            if (!empty($batch_buffer)) {
                // a batch is still pending
                $contains_person = SiddataDataManager::array_cointains_activity_of_type($batch_buffer, 'person');
                echo '<form action="'.$controller->link_for('siddata/questionnaire/'.$goal['id'].'/'.$context_route).'" method="POST" class="default" '.($contains_person? 'enctype="multipart/form-data"' : '' ).'>';
                $controller->render_form($batch_buffer, $goal, $factory, $controller, $context_route);
                echo '</form>';
            }
            ?>
        </div>
    </div>
<? if ($controller->goalQuantity > 1 and $goal->isVisible()): ?>
</fieldset>
<? endif; ?>
