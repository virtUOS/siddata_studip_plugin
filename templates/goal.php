<?php
// choose goal template
$template_path = 'goals/activity_stream';
if ($goal->getType() == 'form') {
    $template_path = 'goals/questionnaire';
}

$template = $controller->factory->open($template_path);
$template->set_attribute('goal', $goal);
$template->set_attribute('factory', $this->factory);
$template->set_attribute('controller', $controller);

if (!isset($context_route)) {
    $context_route = 'recommender/' . $goal->getRecommender()->getId();
}

$template->set_attribute('context_route', $context_route);
?>
<div class="siddata-goal">
    <?= $template->render() ?>
</div>
