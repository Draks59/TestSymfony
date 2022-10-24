<?php
namespace App\Service;

use DateTimeImmutable;

class JWTService
{
    // Generer le token

    /**
     * génération du JWT
     * @param array $header
     * @param array $payload
     * @param string $secret
     * @param int $validity
     * @return string
     */
    
    public function generate(array $header, array $payload, string $secret, int $validity = 3600 ): string
    {
        if($validity <= 0){
            return "";
        }
        
        $now = new DateTimeImmutable();
        $exp = $now->getTimestamp() + $validity;

        $payload['iat'] = $now->getTimestamp();
        $payload['exp'] = $exp;

        // Encod en base64

        $base64Header = base64_encode(json_encode($header));
        $base64Payload = base64_encode(json_encode($payload));

        // "nettoyage" des valeur encodées (retrait des + / et =)
        $base64Header = str_replace(['+', '/', '='], ['-', '_', '']
        , $base64Header);
        $base64Payload = str_replace(['+', '/', '='], ['-', '_', '']
        , $base64Payload);

        // Generation de la signature
        $secret = base64_encode($secret);

        $signature = hash_hmac('sha256', $base64Header . '.' . $base64Payload, $secret, true);
        $base64Signature = base64_encode($signature);

        $base64Signature = str_replace(['+', '/', '='], ['-', '_', '']
        , $base64Signature);

        // on crée le token

        $jwt = $base64Header . '.' . $base64Payload . '.' . $base64Signature;

        return $jwt;
    }

    public function isValid(string $token): bool
    {
        return preg_match(
            '/^[a-zA-Z0-9\-\_\=]+\.[a-zA-Z0-9\-\_\=]+\.[a-zA-Z0-9\-\_\=]+$/',
            $token
        ) === 1;
    } // https://youtu.be/UrJUn2EL07U?t=3483 reprendre a la maison
}