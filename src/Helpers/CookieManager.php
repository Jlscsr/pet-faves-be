<?php

namespace App\Helpers;

use App\Helpers\ResponseHelper;

class CookieManager
{
    private $is_secure;
    private $is_http_only;
    private $cookie_name;
    private $samesite;
    private $domain;

    public function __construct()
    {
        // Determine if the scheme is HTTPS
        $this->is_secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ||
            (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

        // Set SameSite based on scheme
        $this->samesite = $this->is_secure ? 'None' : 'Strict';

        // Cookie settings
        $this->is_http_only = true;
        $this->cookie_name = 'pfvs_acc_tk';
        $this->domain = $this->is_local() ? 'pet-faves-be.local' : 'serene-chamber-22766-e2c42f887fde.herokuapp.com';
    }

    /**
     * Determine if the environment is local.
     *
     * @return bool  
     */
    private function is_local(): bool
    {
        return $_SERVER['HTTP_HOST'] === 'pet-faves-be.local';
    }

    /**
     * Sets a cookie with the given token and expiry date.
     *
     * @param string $token The token to set in the cookie.
     * @param int $expiry_date The expiry date of the cookie in Unix timestamp format.
     * @return void
     */
    public function setCookiHeader(string $token, string $expiry_date)
    {
        $this->resetCookieHeader();

        setcookie($this->cookie_name, $token, [
            'expires' => $expiry_date,
            'path' => '/',
            'domain' => $this->domain,
            'secure' => $this->is_secure,
            'httponly' => $this->is_http_only,
            'samesite' => $this->samesite,
        ]);
    }

    /**
     * Resets the cookie header by deleting the cookie with the specified name.
     *
     * @return void
     */
    public function resetCookieHeader()
    {
        setcookie($this->cookie_name, '', [
            'expires' => time() - 3600,
            'path' => '/',
            'domain' => $this->domain,
            'secure' => $this->is_secure,
            'httponly' => $this->is_http_only,
            'samesite' => $this->samesite,
        ]);
    }

    /**
     * Extracts the access token from the cookie header.
     *
     * @return string|null The access token extracted from the cookie header, or null if not found.
     */
    public function extractAccessTokenFromCookieHeader(): ?string
    {
        $headers = getallheaders();

        if (isset($headers['Cookie'])) {
            $cookie = $headers['Cookie'];
            parse_str(str_replace('; ', '&', $cookie), $cookies);
            return $cookies[$this->cookie_name] ?? null;
        }

        return null;
    }

    /**
     * Validates the presence of the cookie in the headers.
     *
     * @return array|bool Returns true if the cookie is found, or an error array otherwise.
     */
    public function validateCookiePresence(): array|bool
    {
        $headers = getallheaders();

        if (!isset($headers['Cookie']) || strpos($headers['Cookie'], $this->cookie_name . '=') === false) {
            return ['status' => 'failed', 'message' => 'Cookie not found'];
        }

        return ['status' => 'success'];
    }
}
