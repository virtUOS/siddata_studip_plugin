<?php

class CollectorConditions extends Migration {
    public function up()
    {
        $cfg = Config::get();
        $cfg->create('SIDDATA_Collector_Fields',
            [
                'value' => '',
                'type' => 'string',
                'range' => 'global',
                'section' => 'SIDDATA',
                'description' => 'Liste von Feldern, die eine öffentliche Veranstaltung klassifizieren (z. B. "VeranstaltungsNummer" in Osnabrück). Die Feld-IDs werden durch Kommata getrennt. Es werden nur Veranstaltungen gesammelt, bei denen diese Felder Werte enthalten.'
            ]
        );
    }

    public function down()
    {
        $cfg = Config::get();
        $cfg->delete('SIDDATA_Collector_Fields');
    }
}
