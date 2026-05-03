<?php

namespace App\Support;

use Illuminate\Support\Facades\Crypt;

class MathCaptcha
{
    /**
     * Generate a new math CAPTCHA challenge.
     */
    public static function generate(): array
    {
        $a = random_int(2, 15);
        $b = random_int(2, 15);
        $answer = $a + $b;

        $token = Crypt::encryptString(json_encode([
            'answer' => $answer,
            'expires_at' => now()->addMinutes(15)->timestamp,
        ]));

        return [
            'question' => "What is {$a} + {$b}?",
            'token' => $token,
        ];
    }

    /**
     * Verify a CAPTCHA answer against its token.
     */
    public static function verify(?string $token, ?string $answer): bool
    {
        if (empty($token) || $token === '' || empty($answer) || $answer === '') {
            return false;
        }

        try {
            $data = json_decode(Crypt::decryptString($token), true);

            if (! is_array($data) || ! isset($data['answer'], $data['expires_at'])) {
                return false;
            }

            if (now()->timestamp > (int) $data['expires_at']) {
                return false;
            }

            return (int) $answer === (int) $data['answer'];
        } catch (\Exception $e) {
            return false;
        }
    }
}
