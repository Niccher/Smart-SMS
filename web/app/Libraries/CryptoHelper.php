<?php

namespace App\Libraries;

class CryptoHelper
{
    private string $cipherAlgo = "AES-128-CBC";
    private string $cryptKey;
    private string $cryptIv;

    public function __construct()
    {
        $this->cryptKey = (string) env('MPESA_CRYPT_KEY', 'a:r2yt>N3_\\Py,f=');
        $this->cryptIv  = (string) env('MPESA_CRYPT_IV', '[M[@_w[F4a>yQsJW');
    }

    public function decode_content($value)
    {
        if (strlen($value) < 16) {
            throw new \RuntimeException("Payload too short to extract IV");
        }

        $options = OPENSSL_RAW_DATA;

        // Attempt AES-256-GCM decryption (12-byte IV header + 16-byte Auth Tag footer)
        if (strlen($value) > 28) {
            $ivGcm = substr($value, 0, 12);
            $tag = substr($value, -16);
            $ciphertextGcm = substr($value, 12, -16);

            $gcmDec = @openssl_decrypt($ciphertextGcm, 'AES-256-GCM', $this->cryptKey, $options, $ivGcm, $tag);
            if ($gcmDec !== false) {
                log_message('debug', 'Decrypted payload using AES-256-GCM');
                return $gcmDec;
            }
        }

        // Fallback to legacy AES-128-CBC decryption
        $iv = substr($value, 0, 16);
        $ciphertext = substr($value, 16);

        $dec_val = openssl_decrypt($ciphertext, $this->cipherAlgo, $this->cryptKey, $options, $iv);

        if ($dec_val === false) {
            $error = openssl_error_string();
            log_message('error', 'OpenSSL decrypt error: ' . $error);
            throw new \RuntimeException("OpenSSL decrypt failed: " . $error);
        }

        log_message('debug', 'Decrypted payload using AES-128-CBC fallback');
        return $dec_val;
    }

    public function encode_content($value)
    {
        $iv = openssl_random_pseudo_bytes(12);
        $options = OPENSSL_RAW_DATA;
        $tag = '';

        $enc_val = openssl_encrypt($value, 'AES-256-GCM', $this->cryptKey, $options, $iv, $tag);

        return $iv . $enc_val . $tag;
    }
}
