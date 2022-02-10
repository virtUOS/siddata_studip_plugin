<div class="siddata-dialog">
    <? if ($remote_data['study']): ?>
        <label for="siddata-studydata">Studiengangsdaten:</label>
        <ul id="siddata-studydata">
            <?php
            foreach ($remote_data['study'] as $study) {
                echo "<li>";
                if ($study['subject_name']) {
                    echo htmlReady($study['subject_name']);
                } else {
                    echo "Unbekanntes Fach";
                }
                if ($study['degree_name']) {
                    echo ", " . htmlReady($study['degree_name']);
                }
                if ($study['semester']) {
                    echo ", im " . htmlReady($study['semester']) . ". Semester";
                }
                echo "</li>";
            }
            ?>
        </ul>
    <? endif; ?>
    <? if ($remote_data['courses']): ?>
        <label for="siddata-courses">Belegte Kurse:</label>
        <ul id="siddata-courses">
            <?php
            foreach ($remote_data['courses'] as $course){
                echo "<li>";
                echo htmlReady($course['course_name']);
                if ($course['start_semester_name'] and $course['end_semester_name'] and $course['start_semester_name'] != $course['end_semester_name']) {
                    echo htmlReady(" vom " . $course['start_semester_name'] . " bis zum " . $course['end_semester_name']);
                } else if ($course['start_semester_name']) {
                    echo " im " . htmlReady($course['start_semester_name']);
                }
                echo "</li>";
            }
            ?>
        </ul>
    <? endif; ?>
    <? if ($remote_data['gender']): ?>
        <label for="siddata-gender">Geschlecht:</label>
        <div id="siddata-gender"><?= $this->genders[$remote_data['gender']] ?></div>
    <? endif ?>
</div>