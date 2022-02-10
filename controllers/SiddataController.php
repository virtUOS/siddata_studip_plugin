<?php

abstract class SiddataControllerAbstract extends PluginController
{
    /**
     * Displays an error or success message
     * @param array $response response data
     * @param string $error_details additional error information
     * @param string|null $default_success default success message
     * @param string|null $default_error default error message
     */
    protected function processResponse($response, $error_details='', $default_success=null, $default_error=null) {
        if ($response['http_code'] == 200) {
            if ($msg = $response['response']) {
                $this->plugin->postSuccess($msg);
            } else {
                if ($default_success) {
                    $this->plugin->postSuccess($default_success);
                } else {
                    $this->plugin->postSuccess();
                }
            }
        } else {
            if ($msg = $response['response']) {
                $this->plugin->postError($msg . $error_details);
            } else {
                if ($default_error) {
                    $this->plugin->postError($default_error);
                } else {
                    $this->plugin->postError();
                }
            }
        }
    }

    /**
     * Dump information for debugging purposes
     * @param string $json json formatted string
     * @param string $data_type
     */
    protected function jsonDump($json, $data_type='default') {
        $dirname = $GLOBALS['STUDIP_BASE_PATH'] . '/public/plugins_packages/virtUOS/SiddataPlugin/dumps';
        $filename = $dirname . "/" . date("D-d-M-Y_H-i-s_", time()) . "_" . $data_type . "_" .  "dump.json";
        if (!file_exists($dirname)) {
            mkdir($dirname);
        } else {
            if (file_exists($filename)) {
                unlink($filename);
            }
        }
        file_put_contents($filename, $json);

    }
}
