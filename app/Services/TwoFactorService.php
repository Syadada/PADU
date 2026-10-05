<?php

namespace App\Services;

class TwoFactorService
{
    private static string $base32Chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    /**
     * Buat 16-karakter Secret Key Base32 acak standar RFC 6238
     */
    public static function generateSecret(int $length = 16): string
    {
        $secret = '';
        $max = strlen(self::$base32Chars) - 1;
        for ($i = 0; $i < $length; $i++) {
            $secret .= self::$base32Chars[random_int(0, $max)];
        }
        return $secret;
    }

    /**
     * Verifikasi kode 6-digit TOTP pengguna dengan toleransi jendela waktu drift (discrepancy)
     */
    public static function verifyCode(string $secret, string $code, int $discrepancy = 1): bool
    {
        $code = trim($code);
        if (strlen($code) !== 6 || !ctype_digit($code)) {
            return false;
        }

        $currentTimeSlice = (int)floor(time() / 30);

        for ($i = -$discrepancy; $i <= $discrepancy; $i++) {
            $calculatedCode = self::calculateCode($secret, $currentTimeSlice + $i);
            if (hash_equals($calculatedCode, $code)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Hitung 6-digit TOTP untuk slice waktu tertentu
     */
    public static function calculateCode(string $secret, int $timeSlice): string
    {
        $secretBinary = self::base32Decode($secret);
        if ($secretBinary === false) {
            return '';
        }

        // Pack 64-bit integer big-endian (RFC 4226 / RFC 6238)
        $data = pack('N*', 0) . pack('N*', $timeSlice);
        $hash = hash_hmac('sha1', $data, $secretBinary, true);

        $offset = ord($hash[19]) & 0x0F;
        $binary =
            ((ord($hash[$offset]) & 0x7F) << 24) |
            ((ord($hash[$offset + 1]) & 0xFF) << 16) |
            ((ord($hash[$offset + 2]) & 0xFF) << 8) |
            (ord($hash[$offset + 3]) & 0xFF);

        $otp = $binary % 1000000;
        return str_pad((string)$otp, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Generate URI standar otpauth:// untuk pemindaian aplikasi autentikator (Google Authenticator, Aegis, dll)
     */
    public static function getProvisioningUri(string $company, string $userEmail, string $secret): string
    {
        $label = rawurlencode($company) . ':' . rawurlencode($userEmail);
        $issuer = rawurlencode($company);
        return "otpauth://totp/{$label}?secret={$secret}&issuer={$issuer}&algorithm=SHA1&digits=6&period=30";
    }

    /**
     * Buat Kunci Pemulihan Darurat Fisik (Master Recovery Key) 24-karakter dengan format:
     * PADU-XXXX-XXXX-XXXX-XXXX
     */
    public static function generateEmergencyRecoveryKey(): string
    {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $blocks = [];
        for ($b = 0; $b < 4; $b++) {
            $part = '';
            for ($c = 0; $c < 4; $c++) {
                $part .= $chars[random_int(0, strlen($chars) - 1)];
            }
            $blocks[] = $part;
        }
        return 'PADU-' . implode('-', $blocks);
    }

    /**
     * Decode string Base32 menjadi biner mentah
     */
    private static function base32Decode(string $b32): string|false
    {
        $b32 = strtoupper(str_replace('=', '', $b32));
        $lut = [];
        for ($i = 0; $i < strlen(self::$base32Chars); $i++) {
            $lut[self::$base32Chars[$i]] = $i;
        }

        $binary = '';
        for ($i = 0; $i < strlen($b32); $i++) {
            $char = $b32[$i];
            if (!isset($lut[$char])) {
                return false;
            }
            $binary .= str_pad(decbin($lut[$char]), 5, '0', STR_PAD_LEFT);
        }

        $bytes = '';
        $len = strlen($binary);
        for ($i = 0; $i + 8 <= $len; $i += 8) {
            $bytes .= chr(bindec(substr($binary, $i, 8)));
        }

        return $bytes;
    }
}
