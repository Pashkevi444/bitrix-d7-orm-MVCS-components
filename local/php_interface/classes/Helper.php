<?php

use Bitrix\Main\Application;
use Bitrix\Main\Web\Cookie;

/**
 * Helper class for various utility methods.
 */
class Helper
{
    /**
     * Set a cookie with a randomly generated value if it doesn't already exist.
     *
     * @param string $cookieName Name of the cookie to set.
     * @return string|null The cookie value if set, otherwise null.
     */
    public static function setCookie(string $cookieName): ?string
    {
        if (self::getCookie($cookieName)) {
            return null;
        }

        $hashedData = self::generateRandomHash();
        $application = Application::getInstance();
        $context = $application->getContext();
        $cookie = new Cookie($cookieName, $hashedData, time() + 60 * 60 * 24 * 60);
        $cookie->setSpread(Cookie::SPREAD_DOMAIN);
        $cookie->setHttpOnly(false);
        $context->getResponse()->addCookie($cookie);
        $context->getResponse()->writeHeaders("");

        return $hashedData;
    }

    /**
     * Generate a random hash string.
     *
     * @param int $length Length of the generated hash.
     * @return string The generated random hash.
     */
    public static function generateRandomHash(int $length = 32): string
    {
        return bin2hex(random_bytes($length / 2));
    }

    /**
     * Retrieve the value of a specific cookie.
     *
     * @param string $cookieName Name of the cookie to retrieve.
     * @return string|null The value of the cookie if it exists, otherwise null.
     */
    public static function getCookie(string $cookieName): ?string
    {
        $application = Application::getInstance();
        return $application->getContext()->getRequest()->getCookie($cookieName) ?: null;
    }

    /**
     * Get the correct plural form of a word based on the number.
     *
     * @param int $n The number to check.
     * @param string $form1 Singular form.
     * @param string $form2 Plural form for 2-4.
     * @param string $form3 Plural form for 5 or more.
     * @return string The correct form of the word.
     */
    public static function pluralForm(int $n, string $form1, string $form2, string $form3): string
    {
        $n = abs($n) % 100;
        $n1 = $n % 10;
        if ($n > 10 && $n < 20) {
            return $form3;
        }
        if ($n1 > 1 && $n1 < 5) {
            return $form2;
        }
        if ($n1 === 1) {
            return $form1;
        }
        return $form3;
    }
}
