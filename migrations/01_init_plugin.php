<?php

require_once 'plugins_packages/virtUOS/SiddataPlugin/lib/SiddataCronjob.php';

class InitPlugin extends Migration {
    public function up()
    {
        $cfg = Config::get();

        $cfg->create('SIDDATA_Brain_URL',
            [
                'value' => 'https://brain.siddata.de/backend/api/',
                'type' => 'string',
                'range' => 'global',
                'section' => 'SIDDATA',
                'description' => 'URL der REST-Schnittstelle. Der Produktivserver läuft unter "https://brain.siddata.de/backend/api/".'
            ]
        );

        $cfg->create('SIDDATA_Proxy_URL',
            [
                'value' => '',
                'type' => 'string',
                'range' => 'global',
                'section' => 'SIDDATA',
                'description' => 'URL eines optionalen Proxys vor der REST-Schnittstelle'
            ]
        );

        $cfg->create('SIDDATA_Proxy_Port',
            [
                'value' => 80,
                'type' => 'integer',
                'range' => 'global',
                'section' => 'SIDDATA',
                'description' => 'Port eines optionalen Proxys vor der REST-Schnittstelle'
            ]
        );

        $cfg->create('SIDDATA_Debug_Info',
            [
                'value' => False,
                'type' => 'boolean',
                'range' => 'global',
                'section' => 'SIDDATA',
                'description' => 'Ein-/Ausschalten von Debugging-Informationen'
            ]
        );

        if (!$cfg['SIDDATA_crypt']) {
            $cfg->create('SIDDATA_crypt',
                [
                    'value' => '',
                    'type' => 'string',
                    'range' => 'global',
                    'section' => 'SIDDATA',
                    'description' => 'Dieses Feld wird zur Identifikation von Siddata-Nutzern benötigt. Wird dieses Feld geändert oder gelöscht, so können Nutzer nicht mehr auf ihre Siddata-Daten zugreifen.'
                ]
            );
        }

        $mail_tag = '<a href="mailto:virtuos+siddata@uos.de">virtuos+siddata@uos.de</a>';
        $cfg->create('SIDDATA_Error_Message',
            [
                'value' => 'Bitte das Siddata-Team per E-Mail kontaktieren ('.$mail_tag.'). Vielen Dank!',
                'type' => 'string',
                'range' => 'global',
                'section' => 'SIDDATA',
                'description' => 'Diese Nachricht sieht ein Siddata-Nutzer, wenn ein Fehler innerhalb des Plugins auftritt. Es bietet sich an, hier eine Email-Adresse zu hinterlegen.'
            ]
        );

        $cfg->create('SIDDATA_origin',
            [
                'value' => '',
                'type' => 'string',
                'range' => 'global',
                'section' => 'SIDDATA',
                'description' => 'Dieses Feld wird zur Identifikation von Siddata-Nutzern benötigt. Genauer identifiziert es die Stud.IP-Installation, in die der Nutzer eingeloggt ist. Wird dieses Feld geändert oder gelöscht, so können Nutzer nicht mehr auf ihre Siddata-Daten zugreifen.'
            ]
        );

        $cfg->create('SIDDATA_nav',
            [
                'value' => True,
                'type' => 'boolean',
                'range' => 'global',
                'section' => 'SIDDATA',
                'description' => 'Hier wird festgelegt, ob das Siddata-Plugin in der Hauptnavigation auftauchen soll.'
            ]
        );

        $cfg->create('SIDDATA_api_key',
            [
                'value' => '',
                'type' => 'string',
                'range' => 'global',
                'section' => 'SIDDATA',
                'description' => 'Dieser Schlüssel wird zur Authentifizierung der Stud.IP-Instanz gegenüber Siddata benötigt. Der Schlüssel muss vom virtUOS angefordert und händisch hier eingetragen werden.'
            ]
        );

        $cfg->create('SIDDATA_collect_limit',
            [
                'value' => 2000,
                'type' => 'integer',
                'range' => 'global',
                'section' => 'SIDDATA',
                'description' => 'Die maximale Anzahl die von Siddata gesammelt werden soll. Ist dieser Wert < 0, gibt es kein Limit.'
            ]
        );

        SiddataCronjob::register()->schedulePeriodic(0,2)->activate();
    }

    public function down()
    {
        $cfg = Config::get();
        $cfg->delete('SIDDATA_Brain_URL');
        $cfg->delete('SIDDATA_Proxy_URL');
        $cfg->delete('SIDDATA_Proxy_Port');
        $cfg->delete('SIDDATA_Debug_Info');
        $cfg->delete('SIDDATA_Error_Message');
        $cfg->delete('SIDDATA_origin');
        $cfg->delete('SIDDATA_nav');
        $cfg->delete('SIDDATA_api_key');
        $cfg->delete('SIDDATA_collect_limit');
        SiddataCronjob::unregister();
    }
}
