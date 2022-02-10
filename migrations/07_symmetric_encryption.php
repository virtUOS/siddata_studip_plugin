<?php

class SymmetricEncryption extends Migration {
    public function up()
    {
        $cfg = Config::get();
        $cfg->create('SIDDATA_KEY',
            [
                'type' => 'string',
                'range' => 'global',
                'section' => 'SIDDATA',
                'description' => "Dieser Schlüssel wird für die Ver- und Entschlüsselung von Klartexten für die
                    Kommunkation mit dem Backend verwendet. Er wird von Siddata definiert und sollte ihnen vom
                    Siddata-Team zugeteilt worden sein. \n Falls dies nicht der Fall ist wenden Sie sich bitte an
                    siddata@uos.de",
                'default' => 'BITTE AENDERN!',
            ]
        );
        $cfg->create('SIDDATA_IV',
            [
                'type' => 'string',
                'range' => 'global',
                'section' => 'SIDDATA',
                'description' => "Diese Variable wird für die Ver- und Entschlüsselung von Klartexten für die
                    Kommunkation mit dem Backend verwendet. Sie wird von Siddata definiert und sollte ihnen vom
                    Siddata-Team zugeteilt worden sein. \n Falls dies nicht der Fall ist wenden Sie sich bitte an
                    siddata@uos.de",
                'default' => 'BITTE AENDERN!',
            ]
        );
    }

    public function down()
    {
        $cfg = Config::get();
        $cfg->delete('SIDDATA_KEY');
        $cfg->delete('SIDDATA_IV');
    }
}
