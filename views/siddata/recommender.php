<div class="siddata">
    <h1><?= isset($recommender) ? $recommender->getName() : "" ?></h1>
    <div>
        <?php
        if (isset($recommender)) {
            if (count($recommender->getAllActivities($_SESSION['SIDDATA_view'])) == 0) {
                echo "<fieldset><p><strong>Hier gibt es nichts zu sehen.</strong><br>Momentan wird dir hier nichts angezeigt, da du offenbar bereits alle Activities dieser Funktion erledigt, pausiert oder verworfen hast. Wenn du sie einsehen und ggf. wieder aufnehmen möchtest, findest du sie im Menü links oder mobil im Drei-Punkte-Menü unter <strong>Ansichten</strong>.</p>";
                if ($_SESSION['SIDDATA_view'] == 'all') {
                    echo "<p><a href='".$controller->link_for('siddata/index')."'>Zur Startseite</a></p>";
                } else {
                    echo "<p><a href='".$controller->link_for('siddata/change_view/all/recommender/'.$recommender->getId())."'>Zu den offenen Empfehlungen...</a></p>";
                }
                echo "</fieldset>";
            } else {
                foreach($recommender->getGoals() as $id => $goal){
                    $template = $this->factory->open('goal');
                    $template->set_attribute('goal', $goal);
                    $template->set_attribute('factory', $this->factory);
                    $template->set_attribute('controller', $controller);
                    echo $template->render();
                }
            }
        }
        ?>
    </div>
    <?php
    if ($controller->plugin->debug) {
        $debug_template = $this->factory->open('debug');
        echo $debug_template->render();
    }
    ?>
</div>
