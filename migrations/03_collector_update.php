<?php

class CollectorUpdate extends Migration {
    public function up()
    {
        $cfg = Config::get();

        // remove unsecure field
        $cfg->delete('SIDDATA_Collector_Fields');

        // remove deprecated field
        $cfg->delete('SIDDATA_collect_limit');

        // add start year
        $cfg->create('SIDDATA_Collector_startyear',
            [
                'value' => '',
                'type' => 'string',
                'range' => 'global',
                'section' => 'SIDDATA',
                'description' => 'Jahreszahl im Format "YYYY" des frühsten Jahres, aus dem Daten zu sammeln sind. Ist dieses Feld leer, so wird mit dem Start-Datum der frühsten Veranstaltung in der Datenbank gestartet.'
            ]
        );

        // add end year
        $cfg->create('SIDDATA_Collector_endyear',
            [
                'value' => '',
                'type' => 'string',
                'range' => 'global',
                'section' => 'SIDDATA',
                'description' => 'Jahreszahl im Format "YYYY" des frühsten Jahres, aus dem KEINE Daten mehr zu sammeln sind. Ist dieses Feld leer, so wird bis zum Start-Datum der letzten Veranstaltung + 6 Monate gesammelt.'
            ]
        );

        // add iteration number
        $cfg->create('SIDDATA_Collector_iteration',
            [
                'value' => 0,
                'type' => 'integer',
                'range' => 'global',
                'section' => 'SIDDATA',
                'description' => 'Dieser Index wird intern zum Management des iterativen Sammelns von Daten verwendet und kann i. d. R. ignoriert werden.'
            ]
        );
    }

    public function down()
    {
        $cfg = Config::get();

        $cfg->delete('SIDDATA_Collector_startyear');
        $cfg->delete('SIDDATA_Collector_endyear');
        $cfg->delete('SIDDATA_Collector_iteration');

        $cfg->create('SIDDATA_Collector_Fields',
            [
                'value' => '',
                'type' => 'string',
                'range' => 'global',
                'section' => 'SIDDATA',
                'description' => 'Liste von Feldern, die eine öffentliche Veranstaltung klassifizieren (z. B. "VeranstaltungsNummer" in Osnabrück). Die Feld-IDs werden durch Kommata getrennt. Es werden nur Veranstaltungen gesammelt, bei denen diese Felder Werte enthalten.'
            ]
        );

        // Do not add SIDDATA_Collector_Fields again. This field is a security issue!
    }
}
