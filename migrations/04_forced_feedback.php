<?php

class ForcedFeedback extends Migration {
    public function up()
    {
        $cfg = Config::get();
        $cfg->create('SIDDATA_forced_feedback',
            [
                'value' => False,
                'type' => 'boolean',
                'range' => 'global',
                'section' => 'SIDDATA',
                'description' => 'Falls ausgewählt, wird bei der Finalisierung einer Empfehlung Feedback vom Nutzer verlangt (weiterhin optional, jedoch Erinnerung bei jeder Finalisierung). Andernfalls geben Nutzer Feedback nur über Eigeninitiative ab.'
            ]
        );
    }

    public function down()
    {
        $cfg = Config::get();
        $cfg->delete('SIDDATA_forced_feedback');
    }
}
