<?php
$resource = $activity->getResource();
?>

<div class="siddata-activity-iframe" id="siddata-activity-iframe_<?= $activity['id'] ?>">
    <iframe id="iiframe" src="<?= htmlReady($resource->getUrl()) ?>" title="<?= htmlReady($resource->getTitle()) ?>" allowfullscreen
            sandbox="allow-forms allow-popups allow-pointer-lock allow-same-origin allow-scripts"></iframe>
</div>
