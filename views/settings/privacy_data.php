<div class="siddata-settings siddata">
    <h1>Deine Datenfreigaben</h1>
    <div id="siddata-shareddata">
        <form class="default" action="<?= $controller->link_for('settings/data') ?>" method="POST">
            <fieldset <?= !$terms_accepted ? 'disabled' : '' ?>>
                <legend>Welche weiteren Daten möchtest du teilen?</legend>

                <? if ((is_array($studip_data['studycourses']) && count($studip_data['studycourses']) > 0) ||
                    (is_array($studip_data['institutes']) &&  count($studip_data['institutes']) > 0)): ?>
                    <table class="default">
                        <tr>
                            <th><strong>Studiendaten</strong></th>
                            <th><label class="undecorated"><input type="checkbox" title="Alles auswählen" data-proxyfor=":checkbox[class=siddata-share-studydata-brain]"> Mit Siddata</label></th>
                            <th><label class="undecorated"><input type="checkbox" title="Alles auswählen" data-proxyfor=":checkbox[class=siddata-share-studydata-social]"> Mit anderen Nutzenden</label></th>
                        </tr>
                        <? if (count($studip_data['studycourses']) > 0): ?>
                            <tr>
                                <td colspan="3">
                                    <div class="siddata-settings-labelcell">
                                        <strong>Studiengänge</strong>
                                    </div>
                                </td>
                            </tr>
                        <? endif; ?>
                        <? foreach($studip_data['studycourses'] as $sc) : ?>
                            <?php
                            if (is_array($saved_studycourses)) {
                                $saved_sc = array_values(array_filter($saved_studycourses['data'], function ($obj) use ($sc) {
                                    return $obj['attributes']['studip_id'] == $sc['id'];
                                }))[0];
                            }
                            ?>
                            <tr>
                                <td colspan="3">
                                    <p class="siddata-table-li1">
                                        <?= htmlReady($sc['degree']['name']." ".$sc['subject']['name']." ".$sc['semester'].". Semester") ?>
                                    </p>
                                </td>
                            </tr>
                            <? foreach(array('degree' =>'Abschluss', 'subject' => 'Fach', 'semester' => 'Semester') as $p_id => $p_name): ?>
                                <tr>
                                    <td>
                                        <p class="siddata-table-li2"><?=$p_name?> <?=tooltipicon('Die Angabe der Studiengangsdaten (Abschluss, Fach, Semester) kann die Empfehlungen in manchen Funktionen stärker individualisieren und dient statistischen Zwecken zur Darstellung der Forschungsergebnisse in anonymisierter Form.')?></p>
                                    </td>
                                    <td>
                                        <input type="checkbox" id="siddata-share-sc-<?=$p_id?>-brain_<?= $sc['id'] ?>" name="siddata-share-sc-<?=$p_id?>-brain_<?= $sc['id'] ?>" title="<?=$p_name?> dieses Studiengangs teilen" class="siddata-share-studydata-brain" <?= $saved_sc['attributes']['share_'.$p_id.'_brain']? 'checked': '' ?>>
                                    </td>
                                    <td>
                                        <input type="checkbox" id="siddata-share-sc-<?=$p_id?>-social_<?= $sc['id'] ?>" name="siddata-share-sc-<?=$p_id?>-social_<?= $sc['id'] ?>" title="<?=$p_name?> dieses Studiengangs teilen" class="siddata-share-studydata-social" <?= $saved_sc['attributes']['share_'.$p_id.'_social']? 'checked': '' ?>>
                                    </td>
                                </tr>
                            <? endforeach; ?>
                        <? endforeach; ?>
                        <? if (count($studip_data['institutes']) > 0): ?>
                            <tr>
                                <td colspan="3">
                                    <div class="siddata-settings-labelcell">
                                        <strong>Fachbereiche</strong>
                                        <?=tooltipicon('Die Angabe des Fachbereichs kann die Empfehlungen in manchen Funktionen stärker individualisieren und dient statistischen Zwecken zur Darstellung der Forschungsergebnisse in anonymisierter Form.')?>
                                    </div>
                                </td>
                            </tr>
                        <? endif; ?>
                        <? foreach($studip_data['institutes'] as $institute) : ?>
                            <tr>
                                <td>
                                    <p class="siddata-table-li1"><?= htmlReady($institute['name']) ?></p>
                                </td>
                                <td>
                                    <input type="checkbox" id="siddata-share-institute-brain_<?= $institute['id'] ?>" name="siddata-share-institute-brain_<?= $institute['id'] ?>" title="Diesen Fachbereich teilen" class="siddata-share-studydata-brain" <?= (is_array($saved_inst_ids_brain) && in_array($institute['id'], $saved_inst_ids_brain)) ? 'checked': '' ?>>
                                </td>
                                <td>
                                    <input type="checkbox" id="siddata-share-institute-social_<?= $institute['id'] ?>" name="siddata-share-institute-social_<?= $institute['id'] ?>" title="Diesen Fachbereich teilen" class="siddata-share-studydata-social" <?= (is_array($saved_inst_ids_social) && in_array($institute['id'], $saved_inst_ids_social)) ? 'checked': '' ?>>
                                </td>
                            </tr>
                        <? endforeach; ?>
                    </table>
                    <br><br>
                <? endif; ?>

                <? if (is_array($studip_data['courses']) && count($studip_data['courses']) > 0): ?>
                    <table class="default">
                        <tr>
                            <th><strong>belegte Kurse</strong> <?=tooltipicon('Die Angabe der Kursdaten kann die Empfehlungen in manchen Funktionen stärker individualisieren und dient statistischen Zwecken zur Darstellung der Forschungsergebnisse in anonymisierter Form.')?></th>
                            <th><label class="undecorated"><input type="checkbox" title="Alles auswählen" data-proxyfor=":checkbox[class=siddata-share-courses-brain]"> Mit Siddata</label></th>
                            <th><label class="undecorated"><input type="checkbox" title="Alles auswählen" data-proxyfor=":checkbox[class=siddata-share-courses-social]"> Mit anderen Nutzenden</label></th>
                        </tr>
                        <? foreach($studip_data['courses'] as $course) : ?>
                            <tr>
                                <td title="Kurse, in die ich in Stud.IP eingetragen bin">
                                    <p class="siddata-table-li1"><?= htmlReady($course['name']) ?></p>
                                </td>
                                <td>
                                    <input type="checkbox" id="siddata-share-course-brain_<?= $course['id'] ?>" name="siddata-share-course-brain_<?= $course['id'] ?>" title="Diesen Kurs teilen" class="siddata-share-courses-brain" <?= (is_array($saved_course_ids_brain) && in_array($course['id'], $saved_course_ids_brain)) ? 'checked': '' ?>>
                                </td>
                                <td>
                                    <input type="checkbox" id="siddata-share-course-social_<?= $course['id'] ?>" name="siddata-share-course-social_<?= $course['id'] ?>" title="Diesen Kurs teilen" class="siddata-share-courses-social" <?= (is_array($saved_course_ids_social) && in_array($course['id'], $saved_course_ids_social)) ? 'checked': '' ?>>
                                </td>
                            </tr>
                        <? endforeach; ?>
                    </table>
                    <br><br>
                <? endif; ?>

                <table class="default">
                    <tr>
                        <th><strong>Sonstige Daten</strong></th>
                        <th><label class="undecorated"><input type="checkbox" title="Alles auswählen" data-proxyfor=":checkbox[class=siddata-share-other-brain]"> Mit Siddata</label></th>
                        <th><label class="undecorated"><input type="checkbox" title="Alles auswählen" data-proxyfor=":checkbox[class=siddata-share-other-social]"> Mit anderen Nutzenden</label></th>
                    </tr>
                    <tr>
                        <td>
                            <div class="siddata-settings-labelcell">
                                <strong>Geschlecht: </strong>
                                <?= htmlReady($studip_data['gender']) ?>
                                <?=tooltipicon('Die Angabe des Geschlechts kann die Empfehlungen in manchen Funktionen stärker individualisieren und dient statistischen Zwecken zur Darstellung der Forschungsergebnisse in anonymisierter Form.')?>
                            </div>
                        </td>
                        <td>
                            <input type="checkbox" id="siddata-share-gender-brain" name="siddata-share-gender-brain" <?= $_SESSION['SIDDATA_share']['gender']['brain']? "checked": "" ?> title="Mein Geschlecht teilen" class="siddata-share-other-brain" <?= $saved_gender_brain_set? 'checked': '' ?>>
                        </td>
                        <td>
                            <input type="checkbox" id="siddata-share-gender-social" name="siddata-share-gender-social" <?= $_SESSION['SIDDATA_share']['gender']['social']? "checked": "" ?> title="Mein Geschlecht teilen" class="siddata-share-other-social" <?= $saved_gender_social_set? 'checked': '' ?>>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="siddata-settings-labelcell">
                                <strong>Nutzungsverhalten</strong>
                                <?=tooltipicon('Die Angabe des Nutzungsverhaltens dient statistischen Zwecken zur Darstellung der Forschungsergebnisse in anonymisierter Form.')?>
                            </div>
                        </td>
                        <td colspan="2">
                            <input type="checkbox" id="siddata-share-usage-brain" name="siddata-share-usage-data" <?= $saved_data_donation? "checked": "" ?> title="Welche Siddata-Recommender werden wann genutzt?" class="siddata-share-other-brain">
                        </td>
                    </tr>
                </table>
                <!-- forward collected data to next route -->
                <p>
                    Deine Entscheidungen kannst du auf dieser Seite (Siddata > Einstellungen > Datenfreigabe) jederzeit ändern.
                </p>
                <textarea hidden type="text" name="siddata-studip-data"><?= SiddataDataManager::json_encode($studip_data) ?></textarea>
                <?= $first_run ? '<input type="hidden" id="first_run" name="first_run" value="1">' : '' ?>
                <footer>
                    <?= Studip\Button::createAccept($first_run ? 'Abschicken und zum Studienassistenten' : 'Abschicken' ) ?>
                    <?= Studip\LinkButton::create($first_run ? 'Direkt zum Studienassistenten' : 'Zum Studienassistenten', $controller->link_for('siddata')) ?>
                </footer>
            </fieldset>
        </form>
    </div>
    <?php
    if ($controller->plugin->debug) {
        $debug_template = $this->factory->open('debug');
        echo $debug_template->render();
    }
    ?>
</div>
