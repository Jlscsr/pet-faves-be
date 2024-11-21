<?php

namespace App\Helpers;

use App\Helpers\ResponseHelper;

class CookieManager
{
    private $is_secure;
    private $is_http_only;
    private $cookie_name;
    private $samesite;

    public function __construct()
    {
        $this->is_secure = $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https' ? true : false;
        $this->is_http_only = true;
        $this->cookie_name = 'pfvs_acc_tk';
        $this->samesite = $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https' ? 'None' : 'Strict';
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
            'domain' => 'serene-chamber-22766-e2c42f887fde.herokuapp.com',  // Specify the domain if needed
            'secure' => $this->is_secure,  // Set Secure for HTTPS requests only
            'httponly' => $this->is_http_only,
            'samesite' => $this->samesite,
        ]);
    }

    /**
     * Resets the cookie header by deleting the cookie with the specified name.
     *
     * This function sets the cookie with the name specified in the `$cookie_name` property to an empty value,
     * with an expiry date set to one hour ago. The cookie is set to be accessible only on the current domain,
     * and it is flagged as secure if the `$is_secure` property is set to true. The cookie is also flagged as HTTP-only
     * if the `$is_http_only` property is set to true.
     *
     * @return void
     */
    public function resetCookieHeader()
    {
        setcookie($this->cookie_name, '', time() - 3600, '/', '', $this->is_secure, $this->is_http_only,);
    }

    /**
     * Extracts the access token from the cookie header.
     *
     * This function retrieves all the headers using the `getallheaders()` function and
     * validates the presence of the cookie using the `validateCookiePressence()` method.
     * It then extracts the access token from the cookie header by removing the prefix
     * "tcg_access_token=". The extracted token is returned.
     *
     * @return string The access token extracted from the cookie header.
     */
    public function extractAccessTokenFromCookieHeader()
    {
        $headers = getallheaders();

        $this->validateCookiePressence();

        $token = $headers['Cookie'];
        $token = str_replace("pfvs_acc_tk=", "", $token);

        return $token;
    }

    /**
     * Validates the presence of the cookie in the headers.
     *
     * This function retrieves all the headers using `getallheaders()`,
     * checks if the 'Cookie' header is set, and sends an error response
     * with a 400 status code if the cookie header is missing.
     *
     * @throws void
     * @return void
     */
    public function validateCookiePressence()
    {
        $headers = getallheaders();

        if (!isset($headers['Cookie'])) {
            ResponseHelper::sendErrorResponse('Missing Cookie Header', 400);
            exit;
        }
    }
}
