<?php
namespace App\Support;
class ApiToken
{
    public static function secret(): string
    {
        return (string) env('API_SECRET', '');
    }
    public static function ttl(): int
    {
        return (int) env('API_TOKEN_TTL', 60);
    }
    public static function sign($timestamp): string
    {
        return hash_hmac('sha256', (string) $timestamp, self::secret());
    }
    public static function generate(int $timestamp = null): string
    {
        $timestamp = $timestamp ?? time();
        return $timestamp . '.' . self::sign($timestamp);
    }
    public static function verify(?string $token): bool
    {
        $secret = self::secret();
        if ($secret === '' || !is_string($token) || strpos($token, '.') === false) {
            return false;
        }
        [$timestamp, $signature] = explode('.', $token, 2);
        if (!ctype_digit($timestamp) || $signature === '') {
            return false;
        }
        if (abs(time() - (int) $timestamp) > self::ttl()) {
            return false;
        }
        return hash_equals(self::sign($timestamp), $signature);
    }
}
