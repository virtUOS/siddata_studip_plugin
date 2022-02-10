<div class="siddata">
    <? if ($recommender and is_array($recommender->getGoals())): ?>
        <? if (count($recommender->getAllActivities($_SESSION['SIDDATA_view'])) == 0): ?>
            <? if ($_SESSION['SIDDATA_view'] != 'all'): ?>
                <fieldset>
                    <p>
                        <strong>Hier gibt es nichts zu sehen.</strong><br>Momentan wird dir hier nichts angezeigt, da du offenbar bereits alle Activities dieser Funktion abgeschlossen, pausiert oder verworfen hast. Wenn du sie einsehen und ggf. wieder aufnehmen möchtest, findest du sie im Menü links oder mobil im Drei-Punkte-Menü unter <strong>Ansichten</strong>.
                    </p>
                    <p>
                        <a href='<?= $controller->link_for('siddata/change_view/all/index/') ?>'>Zu den offenen Empfehlungen...</a>
                    </p>
                </fieldset>
            <? endif; ?>
        <? else: ?>
            <? $activities = array_values($recommender->getGoals())[0]->getActivities(true); ?>
            <? if (is_array($activities) and !empty($activities)): ?>
                <? foreach(SiddataActivity::sortActivities($activities) as $activity): ?>
                    <? if ($activity['answer_type'] != 'likert'): ?>
                        <form class="default siddata-activity-form" method="POST" action="<?= $controller->link_for('siddata/activity/'. $activity['id']) ?>" <?= $activity['type'] == 'person'? ' enctype="multipart/form-data"': '' ?>>
                            <?php
                            $template = $this->factory->open('activity');
                            $template->set_attribute('activity', $activity);
                            $template->set_attribute('controller', $controller);
                            $template->set_attribute('mode', $_SESSION['SIDDATA_view']);
                            $template->set_attribute('sem_view', false);
                            $template->set_attribute('context_route', 'index/'.$recommender->getId());
                            echo $template->render();
                            ?>
                        </form>
                    <? endif; ?>
                <? endforeach; ?>
            <? endif; ?>
        <? endif; ?>
    <? endif; ?>
    <? if($enabledRecommenders > 1): ?>
        <? if($_SESSION['SIDDATA_view'] == 'all'): ?>
        <ul class="boxed-grid">
            <? foreach (Navigation::getItem('/siddata/assistant') as $name => $nav): ?>
                <? $tileRecommender = array_values(array_filter($recommender_objs, function($r) use($nav) { return $r->getName() == $nav->getTitle(); }))[0] ?>
                <? if ($nav->isVisible() && $name != 'index'): ?>
                    <li class="siddata-tile <?= ($tileRecommender and !empty($tileRecommender->getColorTheme())) ? "siddata-tile-color-".$tileRecommender->getColorTheme() : "" ?>">
                        <a href="<?= URLHelper::getLink($nav->getURL()) ?>">
                            <h3>
                                <? if ($nav->getImage()): ?>
                                    <?= $nav->getImage()->asImg(false, $nav->getLinkAttributes()) ?>
                                <? endif; ?>
                                <?= htmlReady($nav->getTitle()) ?>
                            </h3>
                            <p>
                                <?= htmlReady($nav->getDescription()) ?>
                            </p>
                            <? if ($nav->getBadgeNumber() > 0): ?>
                                <div class="siddata-numberCircle"><?= $nav->getBadgeNumber() ?></div>
                            <? endif; ?>
                        </a>
                    </li>
                <? endif; ?>
            <? endforeach; ?>
            <!--
                this is pretty ugly but we need to spawn some empty elements so that the
                last row of the flex grid won't be messed up if the boxes don't line up
            -->
            <li></li><li></li><li></li>
            <li></li><li></li><li></li>
        </ul>
        <? endif; ?>
    <? endif; ?>
    <?php
    if ($controller->plugin->debug) {
        $debug_template = $this->factory->open('debug');
        echo $debug_template->render();
    }
    ?>
</div>
