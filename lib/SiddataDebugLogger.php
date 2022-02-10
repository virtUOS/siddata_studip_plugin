<?php


/**
 * Class SiddataDebugLogger
 *
 * @author Niklas Dettmer <ndettmer@uos.de>
 * @author Sebastian Osada <sebastian.osada@uni-osnabrueck.de>
 * @author Dennis Benz <dbenz@uni-osnabrueck.de>
 */
class SiddataDebugLogger
{
    /**
     * @var SiddataCrypt
     */
    private static $crypter;

    /**
     * Storing debugging information to show in debug-template
     *
     * @param array $data data to be shown - only parts will be printed
     */
    public static function log($data) {
        if (is_null(self::$crypter)) {
            self::$crypter = new SiddataCrypt();
        }
        // encrypt user id
        $uid = User::findCurrent()->id;
        $crypted_uid = self::$crypter->std_encrypt($uid);
        $tempDir = $GLOBALS['TMP_PATH'].'/siddata/debug';

        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0777, true);
        }

        $queue = new SplQueue();
        if (file_exists($tempDir.'/'.$crypted_uid)) {
            $debug_data = file_get_contents($tempDir.'/'.$crypted_uid);
            $queue->unserialize($debug_data);
            while ($queue->count() > 9) {
                $queue->dequeue();
            }
        }
        $queue->enqueue($data);
        file_put_contents($tempDir.'/'.$crypted_uid, $queue->serialize());
    }

    /**
     * Get stored log data
     *
     * @return false|string|null
     */
    public static function getLog() {
        if (is_null(self::$crypter)) {
            self::$crypter = new SiddataCrypt();
        }
        $uid = User::findCurrent()->id;
        $crypted_uid = self::$crypter->std_encrypt($uid);
        $tempDir = $GLOBALS['TMP_PATH'].'/'.'siddata/debug';
        if (file_exists($tempDir.'/'.$crypted_uid)) {
            $debug_data = file_get_contents($tempDir.'/'.$crypted_uid);
            file_put_contents($tempDir.'/'.$crypted_uid, '');

            return $debug_data;
        }
        return null;
    }


    /**
     * Dump
     *
     * @param string $data string to put into new file
     * @param string $file_extension can be any file extension with leading characters
     * @param string $callingClassName Name of the calling class
     */
    public static function dataDump($data, $file_extension='default.txt', $callingClassName='SiddataDebugLogger') {
        // replace characters to comply with filesystem constraints
        $file_extension = mb_ereg_replace("([^\w\s\d\~,;\[\]\(\).])", '-', $file_extension);
        $callingClassName = mb_ereg_replace("([^\w\s\d\~,;\[\]\(\).])", '-', $callingClassName);

        $dateTime = date("D-d-M-Y_H-i-s", time());

        // let vairables be filled
        if (empty($callingClassName)) {
            $callingClassName = 'SiddataDebugLogger';
        }
        if (empty($file_extension)) {
            $file_extension='default.txt';
        }

        $dirname = $GLOBALS['TMP_PATH'] . '/siddata/dumps/' . $callingClassName.'/' ;
        // create path if not existing
        if (!file_exists($dirname)) {
            mkdir($dirname, 0777, true);
        }

        $filename = $dirname . "/" . $dateTime . "_" . $file_extension;

        // add trailing increasing number if file already present
        $i = 0;
        while (file_exists($filename)) {
            $filename = $dirname . "/" . $dateTime . "_".$i++. $file_extension;
        }

        file_put_contents($filename, $data);

    }
}
