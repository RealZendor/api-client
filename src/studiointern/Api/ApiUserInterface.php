<?php

namespace studiointern\Api;

interface ApiUserInterface
{
    public function __construct(ApiConfig $config);
    public function getToken(): string;
    public function login(string $username, string $password): bool;
    public function getExpiration(): ?\DateTime;
    public function isLoggedIn(): bool;
}
