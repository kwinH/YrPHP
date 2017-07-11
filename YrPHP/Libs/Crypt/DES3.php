<?php
namespace YrPHP\Crypt;
class DES3 implements ICrypt
{
    public $key;
    public $iv;

    function __construct()
    {
        $this->key = C('cryptKey');
        $this->iv = C('cryptIv');
    }

    function encrypt($input)
    {
        $size = mcrypt_get_block_size(MCRYPT_3DES, MCRYPT_MODE_ECB);
        $input = $this->pkcs5_pad($input, $size);
        $this->key = str_pad($this->key, 24, '0');
        $td = mcrypt_module_open(MCRYPT_3DES, '', MCRYPT_MODE_ECB, '');
        if ($this->iv == '') {
            $this->iv = @mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
        }

        @mcrypt_generic_init($td, $this->key, $this->iv);
        $data = mcrypt_generic($td, $input);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        return base64_encode($data);
    }

    function decrypt($encrypted)
    {
        $encrypted = base64_decode($encrypted);
        $this->key = str_pad($this->key, 24, '0');
        $td = mcrypt_module_open(MCRYPT_3DES, '', MCRYPT_MODE_ECB, '');
        if ($this->iv == '') {
            $this->iv = @mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
        }

        $ks = mcrypt_enc_get_key_size($td);
        @mcrypt_generic_init($td, $this->key, $this->iv);
        $decrypted = mdecrypt_generic($td, $encrypted);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        return $this->pkcs5_unpad($decrypted);
    }

    function pkcs5_pad($text, $blocksize)
    {
        $pad = $blocksize - (strlen($text) % $blocksize);
        return $text . str_repeat(chr($pad), $pad);
    }

    function pkcs5_unpad($text)
    {
        $pad = ord($text{strlen($text) - 1});
        if ($pad > strlen($text)) {
            return false;
        }
        if (strspn($text, chr($pad), strlen($text) - $pad) != $pad) {
            return false;
        }
        return substr($text, 0, -1 * $pad);
    }

    function PaddingPKCS7($data)
    {
        $block_size = mcrypt_get_block_size(MCRYPT_3DES, MCRYPT_MODE_ECB);
        $padding_char = $block_size - (strlen($data) % $block_size);
        return $data . str_repeat(chr($padding_char), $padding_char);
    }
}

//$des = new DES3();

//echo $ret = $des->encrypt("asdfghjasdfgjghfdsfhmhgfdgf") . "\n";
//echo $des->decrypt($ret) . "\n";
?>