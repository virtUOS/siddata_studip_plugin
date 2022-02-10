<div class="siddata">
    <h1>Häufig gestellte Fragen zum Siddata-Studienassistenten</h1>


    <p>
        Hier gibt es Erklärungen zu Begriffen und Antworten auf einige häufig gestellte Fragen.<br>
        Falls diese dir nicht helfen, steht dir das Siddata-Team gerne zur Verfügung. <br>
        Schreibe dazu eine E-Mail an: <a href="mailto:virtuos+siddata@uni-osnabrueck.de">virtuos+siddata@uni-osnabrueck.de</a>.
    </p>
    <p>
        Bei dem Siddata-Studienassistenzsystem handelt es sich um einen Prototyp, den wir mit deiner Unterstützung weiterentwickeln.
        Weitere Informationen zum Forschungsprojekt "Siddata" findest du unter:
    </p>
    <blockquote>
        <a href="https://www.siddata.de/" target="_blank">Verbundprojekt zur Studienindividualisierung durch digitale, datengestützte Assistenten - www.siddata.de</a>
    </blockquote>

    <h2  class="anchor" id="siddata-faq-basic"><?=htmlReady($sectionAnchors['siddata-faq-basic'])?></h2>

    <h3>Was ist ein Recommender?</h3>
    <p>
        Die einzelnen Funktionen in Siddata werden intern als Recommender bezeichnet, da ihre Kernaufgabe darin besteht, auf dich zugeschnittene Empfehlungen zu generieren.
    </p>

    <h3>Was sind Activities?</h3>
    <p>
        Activities unterscheiden sich durch die Art der Aktionen, die du ausführen kannst. Aktuell gibt es drei Arten von Activities.
    </p>

    <h3>Welche Arten von Activities gibt es?</h3>
    <p>
        Informationen und Empfehlungen werden dir in Form von Boxen angezeigt. Diese werden als Activities (Aktivitäten) bezeichnet, weil sie dich dazu motivieren sollen, etwas zu unternehmen, das dich auf deinem Weg weiterbringt.
    </p>
    <ul>
        <li>Informationen & To-dos enthalten Hinweise für dich. Du kannst sie ausblenden, indem du auf den Button „OK“ klickst.</li>
        <li>Fragen zu dir und deinen Interessen zielen darauf ab, für dich passende Empfehlungen zu generieren. Wenn du eine Frage beantwortest, wird sie als „abgeschlossen“ markiert und ausgeblendet.</li>
        <li>Ressourcen verweisen auf externe Informationsquellen im Internet. Du kannst die Ressource durch Klick auf „Aufrufen“ in einem neuen Brower-Tab öffnen. Durch Klick auf „OK“ wird die Activity als „abgeschlossen“ markiert und ausgeblendet.</li>
    </ul>


    <h2  class="anchor" id="siddata-faq-usage"><?=htmlReady($sectionAnchors['siddata-faq-usage'])?></h2>

    <h3>Muss ich offene Activities bearbeiten?</h3>
    <p>
        Nein, offene Activities musst du nicht direkt bearbeiten. Wenn du dies zu einem späteren Zeitpunkt tun möchtest, kannst du eine Activity pausieren. Wenn du eine Activity gar nicht bearbeiten möchtest, kannst du sie verwerfen. Du tust dies, indem du auf das entsprechende Icon oben rechts in der Activity klickst.
    </p>

    <h3>Was passiert mit den Activities, nachdem sie abgeschlossen, pausiert oder verworfen und somit ausgeblendet wurden?</h3>
    <p>
        Du kannst alle deine abgeschlossenen, pausierten oder verworfenen Activities jederzeit einsehen und auf Wunsch auch wieder aufnehmen.  Hierzu klickst du im Menü links oder mobil im „Drei-Punkte“-Menü unten unter „Ansichten“ auf die jeweilige Kategorie. Um eine Activity wiederherzustellen, klicke dort auf „<?= Icon::create('refresh') ?> Wieder&nbsp;aufnehmen“.
        <br>
        In den Ansichten siehst du jeweils die zur aktuellen Funktion gehörenden Activities. Wenn du also eine bestimmte Activity nicht siehst, musst du ggf. zuerst in die dazugehörige Funktion wechseln.
    </p>

    <h3>Ich habe eine Empfehlung verworfen oder pausiert. Wo finde ich sie wieder?</h3>
    <p>
        Jede verworfene Empfehlung ist unter der Ansicht „<a href="<?= $controller->link_for('siddata/change_view/discarded/index') ?>">Verworfen</a>“ einsehbar und kann dort über die Schaltfläche „<?= Icon::create('refresh') ?> Wieder&nbsp;aufnehmen“ wieder aufgenommen werden. <br>
        Entsprechend können pausierte Empfehlungen unter „<a href="<?= $controller->link_for('siddata/change_view/snoozed/index') ?>">Pausiert</a>“ und bereits erledigte unter „<a href="<?= $controller->link_for('siddata/change_view/done/index') ?>">Abgeschlossen</a>“ eingesehen und wieder aufgenommen werden.
    </p>

    <h3>Meine Empfehlungen sind verschwunden. Wo finde ich sie wieder?</h3>
    <p>
        Vielleicht ist eine Ansicht (im Menü links oder mobil im „Drei-Punkte“-Menü) ausgewählt, unter der es keine Empfehlungen gibt. Alle aktiven Empfehlungen finden sich unter der Ansicht „<a href="<?= $controller->link_for('siddata/change_view/all/index') ?>">Offen</a>“.
    </p>

    <h2  class="anchor" id="siddata-faq-data-privacy"><?=htmlReady($sectionAnchors['siddata-faq-data-privacy'])?></h2>

    <h3>Bei der Benutzung werden meine Daten „verschlüsselt zu einem zentralen Server unter Verwendung eines Pseudonyms gesendet, verarbeitet und gespeichert.“ Was ist darunter zu verstehen?</h3>
    <p>
        Damit Siddata auf dich zugeschnittene Empfehlungen und Dienste anbieten kann, müssen bestimmte Daten verarbeitet und gespeichert werden. Dies geschieht auf dem zentralen Siddata-Server, der sich an der Universität Osnabrück befindet. Damit deine Daten beim Transport zum Server und zurück nicht eingesehen werden können, werden sie immer verschlüsselt übertragen. Darüber hinaus werden deine Daten unter Verwendung eines Pseudonyms gespeichert. Dies bedeutet, dass bei der Kommunikation mit dem Siddata-Server Daten in der Form ausgetauscht werden, dass ein Rückschluss auf deine Person erschwert wird. So werden z.&nbsp;B. weder dein Name noch deine Matrikelnummer gesendet. Stattdessen wird ein individuell erstelltes Pseudonym verwendet.<br>
        Du kannst deine Einverständniserklärung unter „<a href="<?= $controller->link_for('settings/privacy') ?>">Einstellungen > Datenschutz</a>“ ansehen.<br>
        Weitere Daten, die du teilst, änderst du unter „<a href="<?= $controller->link_for('settings/privacy_data') ?>">Einstellungen > Datenfreigabe</a>“.
    </p>

    <h3>Wer ist für den Datenschutz des zentralen Siddata-Servers verantwortlich?</h3>
    <p>
        Verantwortliche gem. Art. 4 Abs. 7 EU-Datenschutz-Grundverordnung (DS-GVO) ist die Universität Osnabrück,<br>
    </p>
    <blockquote>
        vertreten durch die Präsidentin Prof. Dr. Susanne Menzel-Riedl<br>
        Neuer Graben 29 / Schloss<br>
        49074 Osnabrück<br>
        Telefon: +49 541 969 0<br>
        <a href="mailto:virtuos+siddata@uni-osnabrueck.de">virtuos+siddata@uni-osnabrueck.de</a>.
    </blockquote>
    <p>
        Unseren Datenschutzbeauftragten des zentralen Siddata-Servers erreichst du unter:
    </p>
    <blockquote>
        Dipl.-Kfm. Björn Voitel<br>
        Nelson-Mandela-Straße 4<br>
        49076 Osnabrück<br>
        Telefon: +49 541 969 7880 <br>
        <a href="mailto:datenschutzbeauftragter@uni-osnabrueck.de">datenschutzbeauftragter@uni-osnabrueck.de</a>.
    </blockquote>
    <br><br><br>
</div>
