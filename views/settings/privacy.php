<div class="siddata-settings siddata">
    <div class="siddata-settings-text">
        <h1>Nutzungsbedingungen</h1>
        <p>
            Der Siddata-Studienassistent ist ein Prototyp, der im Forschungsprojekt "Siddata" an drei Universitäten in den Ländern Bremen und Niedersachsen entwickelt wird:
        </p>
        <blockquote>
            <a href="https://www.siddata.de/" target="_blank">Verbundprojekt zur Studienindividualisierung durch digitale, datengestützte Assistenten (SIDDATA)</a>
        </blockquote>
        <p>
            Wenn du Siddata nutzen möchtest, werden Daten von dir verschlüsselt zu einem zentralen Server unter Verwendung eines Pseudonyms gesendet, verarbeitet und gespeichert. Weitere Informationen und die Ansprechpersonen findest du in den <a href="<?= $controller->link_for('faq') ?>">"FAQ"</a> sowie in der <a href="https://www.uni-osnabrueck.de/serviceseiten/datenschutz.html" target="_blank">Datenschutzerklärung&nbsp;der&nbsp;Universität&nbsp;Osnabrück</a>.
        </p>
        <p>
            <strong>Um den Siddata-Studienassistenten nutzen zu können, musst du unten der Übertragung, Verarbeitung und Speicherung dieser Daten zustimmen.</strong>
            Im Rahmen des Siddata-Projekts werden wir Daten erheben, verarbeiten und speichern, welche die Grundlage der Empfehlungen des Siddata-Studienassistenten sein werden und unsere Forschung unterstützen. In diesem Fall sind unsere Absichten bei der Erhebung, Verarbeitung und Speicherung von Daten:
        </p>
        <ul>
            <li>die Unterstützung von Studierenden beim Erreichen ihrer Studienziele,</li>
            <li>die Unterstützung der Studierenden bei der Selbstkontrolle und Selbstregulation,</li>
            <li>die Unterstützung der Studierenden bei der Individualisierung ihres Lernverhaltens,</li>
            <li>die Evaluation und Verbesserung des Assistenten durch die Analyse von Daten über die Nutzung von Siddata-Funktionen und -Aktionen (Name der Funktion/Aktion, zusätzliche Eingaben zur Funktion/Aktion sowie Datum und Uhrzeit der Funktions-/Aktionsnutzung). Funktionen sind zum Beispiel "Fachliche Interessen", "Lernorganisation", "Evaluation". Aktionen sind zum Beispiel "Löschen einer Empfehlung", "Beantwortung einer Frage", "Pausieren einer Empfehlung", "Abgabe von Feedback".</li>
            <li>die Untersuchung von Forschungsfragen zur Nutzung und Wirksamkeit des Assistenten durch die Analyse von Daten über die Nutzung von Siddata-Funktionen und -Aktionen und zusätzlichen freiwilligen Befragungen.</li>
        </ul>
        <p>
            Uns ist es wichtig, dich zu informieren, welche deiner Daten wir zu welchem Zweck speichern und dass du ausdrücklich damit einverstanden bist. Das SIDDATA-Projekt gewährleistet, dass alle Mitarbeiter:innen, die an der Entwicklung und Wartung des Assistenten beteiligt sind, die einschlägigen datenschutzrechtlichen Vorschriften kennen und beachten.
        </p>
        <p>
            Das SIDDATA-Projekt gewährleistet, dass alle Mitarbeiter*innen, die an der Entwicklung und Wartung des Assistenten beteiligt sind, die einschlägigen datenschutzrechtlichen Vorschriften kennen und beachten.
        </p>
        <p>
            Eine genaue Auflistung über Umfang, Zweck, Sichtbarkeit und Zeitspanne der personenbezogenen Daten, die gespeichert werden, sowie Ihre Rechte hinsichtlich Datenschutzgrundverordnung (DSGVO) finden Sie in den <a href="<?= $controller->link_for('settings/privacy_policy') ?>">Datenschutzbestimmungen</a>.
        </p>
    </div>

    <div>
        <form class="default siddata-terms-form" action="<?= $controller->link_for('settings/terms') ?>" method="POST">
            <?= CSRFProtection::tokenTag() ?>

            <fieldset>
                <legend>Einverständniserklärung</legend>
                <p>
                    Mit deiner freiwilligen Einverständniserklärung wird ein für dich generiertes Pseudonym an den Siddata-Server an der Universität Osnabrück übertragen, verarbeitet und gespeichert, woraufhin die ersten Empfehlungen für dich erstellt werden. Alle weiteren von dir angegebenen Daten erfolgen freiwillig und werden ebenfalls an den Siddata-Server an der Universität Osnabrück übertragen, verarbeitet und gespeichert.
                </p>
                <p>
                    Im Zuge der Transparenz und Nachvollziehbarkeit der Forschung veröffentlichen wir Forschungsdaten. Dies geschieht voraussichtlich über das <a href="https://www.fdz.dzhw.eu/de" target="_blank">Forschungsdatenzentrum des DZHW</a>. Dabei werden alle Daten wie z.&nbsp;B. Daten über die Nutzung von Siddata-Funktionen und -Aktionen und somit z. B. eingegebene Ziele, Feedback zu Empfehlungen und Daten, die du in Siddata angibst, in strikt anonymisierter, das heißt nicht auf einzelne Personen zurückführbarer Form veröffentlicht. Für eine solche Veröffentlichung gelten strenge Datenschutzauflagen, die unabhängig kontrolliert werden. Mit deiner Einverständniserklärung stimmst du einer solchen Veröffentlichung zu und hilfst der Forschung.
                </p>
                <p>
                    Deine Daten, die du während der Nutzung angibst, sind nur für dich sichtbar und können mit deinem Pseudonym durch das System und im Rahmen des Siddata-Projekts für die oben genannten Absichten genutzt werden. Stimmst du später explizit zu, können diese Daten darüber hinaus auch mit anderen Nutzenden geteilt werden, um z.&nbsp;B. Kontakte herzustellen, Vorschläge für Lerngruppen zu machen, etc.
                </p>
                <p>
                    Die Nutzungsbedingungen von Stud.IP bleiben von dieser Erklärung unberührt und bleiben bestehen.
                </p>
                <p>
                    <strong>Diese Einverständniserklärung kannst du jederzeit mit Wirkung für die Zukunft per E-Mail an <a href="mailto:virtuos+siddata@uos.de">virtuos+siddata@uos.de</a> widerrufen.</strong> Deine Daten werden aufgrund deines Widerrufs unverzüglich vom Siddata-Server an der Universität Osnabrück gelöscht und es entstehen dir keine Nachteile. Nicht widerrufene Daten werden automatisch nach Abschluss der Datensammlungs- und Auswertungsphase des Projekts gelöscht. Der Widerruf bezieht sich nicht auf die Stud.IP Nutzungsbedingungen. Bezüglich der Widerrufserklärung wirkt sich diese nur auf die zukünftige Nutzung der Daten aus. Bisherige Nutzungen können aufgrund deines Widerrufs nicht rückgängig gemacht werden.
                </p>


                <label>
                    <input type="checkbox" name="terms_accepted" value="1" <?= $terms_accepted ? 'checked disabled' : '' ?> required>
                    <strong>Ich habe die Hinweise zum <a href="<?= $controller->link_for('settings/privacy_policy') ?>">Datenschutz</a> gelesen und erkläre mich mit den Nutzungsbedingungen einverstanden.</strong>
                </label>

                <p>
                    Im Verlauf der Nutzung des Studienassistenten kannst du weitere Daten, wie z.&nbsp;B. Studiengangsinformationen und Informationen über bereits besuchte Lehrveranstaltungen, für die genannte Nutzung durch Siddata freigeben und/oder mit anderen Nutzenden teilen, um zum Beispiel in der "Get Together"-Funktion personalisiertere Kontaktvorschläge zu erhalten.
                </p>

                <? if (!$terms_accepted): ?>
                    <footer>
                        <?= Studip\Button::createAccept('Abschicken', ['disabled' => true, 'title'=> 'Setze den Haken bei der Einverständniserklärung, um fortzufahren.']) ?>
                    </footer>
                <? endif; ?>
            </fieldset>
        </form>
    </div>
    <? if ($terms_accepted): ?>
        <div>
            <blockquote><a href="<?= $controller->link_for('settings/privacy_data') ?>">Weitere Daten einstellen</a></blockquote>
        </div>
    <? endif; ?>
    <?php
    if ($controller->plugin->debug) {
        $debug_template = $this->factory->open('debug');
        echo $debug_template->render();
    }
    ?>
</div>
