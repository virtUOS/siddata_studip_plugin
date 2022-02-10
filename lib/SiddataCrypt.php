<?php


/**
 * Class SiddataCrypt
 *
 * @author Niklas Dettmer <ndettmer@uos.de>
 * @author Sebastian Osada <sebastian.osada@uni-osnabrueck.de>
 */
class SiddataCrypt
{
    /**
     * @var SiddataCrypt
     */
    private $crypt;

    /**
     * SiddataCrypt constructor.
     */
    public function __construct()
    {
        $config = Config::get();
        if(!empty($config['SIDDATA_crypt'])) {
            $this->crypt = $config['SIDDATA_crypt'];
        }

        if (!empty($config['SIDDATA_KEY']) and !empty($config['SIDDATA_IV'])) {
            $this->siddata_key = $config['SIDDATA_KEY'];
            $this->siddata_iv = $config['SIDDATA_IV'];
        }
    }

    /**
     * @return string
     */
    function getCrypt() {
        if (!empty($this->crypt)) {
            return $this->crypt;
        }

        $pwdHash = new PasswordHash(30, false);
        $random = $pwdHash->get_random_bytes(256);
        $this->crypt = $pwdHash->gensalt_private($random);

        $config = Config::get();
        $config->store('SIDDATA_crypt', $this->crypt);

        return $this->crypt;
    }

    /**
     * @param $val string value to be encrypted
     * @return string encrypted value
     */
    function std_encrypt($val) {
        return hash("sha256", $val . $this->getCrypt());
    }

    /**
     * Encrypt using configurable siddata_key variable
     * @param $message string message to encrypt
     * @return false|string
     */
    function symmetric_encrypt($message) {
        $message = str_pad($message, 16 * ceil(strlen($message) / 16));
        return openssl_encrypt(
            $message,
            'aes-128-cbc',
            $this->siddata_key,
            0,
            $this->siddata_iv
        );
    }

    /**
     * Decrypt using configurable siddata_key variable
     * @param $message string encrypted message to decrypt
     * @return string
     */
    function symmetric_decrypt($message) {
        return trim(
            openssl_decrypt(
                $message,
                'aes-128-cbc',
                $this->siddata_key,
                0,
                $this->siddata_iv
            )
        );
    }
}
