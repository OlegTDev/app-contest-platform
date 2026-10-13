<?php

declare(strict_types=1);

namespace App\Service;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtService
{
    public function __construct(private readonly string $secretKey)
    {}

    public function decode(string $token): \stdClass
    {
        return JWT::decode($token, new Key($this->secretKey, 'HS256'));
    }
}
