<?php

class CronjobOffsetConfig extends Migration {
    public function up()
    {
        $cfg = Config::get();
        $cfg->create('SIDDATA_Collector_offset',
            [
                'value' => 6,
                'type' => 'integer',
                'range' => 'global',
                'section' => 'SIDDATA',
                'description' => 'Das Zeitinterval, in welchem der Collector-Cronjob pro Iteration nach Veranstaltungen und Terminen sucht. Kommt es beim Ausführen des Cronjobs zu Fehlern, probieren Sie, diesen Wert herabzusetzen.'
            ]
        );
    }

    public function down()
    {
        $cfg = Config::get();
        $cfg->delete('SIDDATA_Collector_offset');
    }
}
