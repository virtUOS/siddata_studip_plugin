<div class="siddata">
    <div>
        <?php
        if ($recommender_objs != null and !empty($recommender_objs)) {
            foreach ($recommender_objs as $recommender) {
                if (isset($recommender)) {
                    if (count($recommender->getAllActivities($_SESSION['SIDDATA_view'])) == 0) {
                    } else {
                        echo "<div>";
                        echo "<h1>" . $recommender->getName() . "</h1>";
                        foreach($recommender->getGoals() as $id => $goal){
                            $template = $this->factory->open('goal');
                            $template->set_attribute('goal', $goal);
                            $template->set_attribute('factory', $this->factory);
                            $template->set_attribute('controller', $controller);
                            $template->set_attribute('context_route', 'all');
                            echo $template->render();
                        }
                        echo "</div>";
                    }
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
