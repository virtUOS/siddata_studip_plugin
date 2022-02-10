<?php

class InitialCollect extends Migration {
    public function up()
    {
        $cfg = Config::get();
        $old_val = $cfg['SIDDATA_Brain_URL'];
        $cfg->delete('SIDDATA_Brain_URL');
        $cfg->create('SIDDATA_Brain_URL',
            [
                'value' => $old_val,
                'type' => 'string',
                'range' => 'global',
                'section' => 'SIDDATA',
                'description' => 'URL der REST-Schnittstelle. Der Produktivserver läuft unter 
                    "https://brain.siddata.de/backend/api/". 
                    WICHTIG: Nachdem diese Variable korrekt konfiguriert wurde, bitte den SiddataCollector-Cronjob
                    einmal so oft ausführen, bis er "Übertragung abgeschlossen" meldet.'
            ]
        );
    }

    public function down()
    {
        $cfg = Config::get();
        $old_val = $cfg['SIDDATA_Brain_URL'];
        $cfg->delete('SIDDATA_Brain_URL');
        $cfg->create('SIDDATA_Brain_URL',
            [
                'value' => $old_val,
                'type' => 'string',
                'range' => 'global',
                'section' => 'SIDDATA',
                'description' => 'URL der REST-Schnittstelle. Der Produktivserver läuft unter 
                    "https://brain.siddata.de/backend/api/".'
            ]
        );
    }
}