<?php
/**
 * 
 * This file is part of the Aura Project for PHP.
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 */
namespace Aura\Http;

/**
 * 
 * Collection of Cookie objects.
 * 
 * @package Aura.Http
 * 
 */
class Cookies extends Request\Cookies
{
    /**
     * 
     * Sends the cookies using `setcookie()`.
     * 
     * @return void
     * 
     */
    public function send()
    {
        foreach ($this->list as $cookie) {
            setcookie(
                $cookie->getName(),
                $cookie->getValue(),
                $cookie->getExpire(),
                $cookie->getPath(),
                $cookie->getDomain(),
                $cookie->getSecure(),
                $cookie->getHttpOnly()
            );
        }
    }
}
