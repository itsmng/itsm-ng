<?php

namespace itsmng;

class Csrf
{
    public static function generate()
    {
        $token = bin2hex(random_bytes(32));
        $_SESSION['_glpi_csrf_token'] = $token;
        $_SESSION['csrf_token_time'] = time() + 3600;
        return $token;
    }

    public static function verify()
    {
        if (isset($_SESSION['_glpi_csrf_token'])
            && isset($_POST['_glpi_csrf_token'])
            && $_SESSION['_glpi_csrf_token'] === $_POST['_glpi_csrf_token']) {
            if (time() >= $_SESSION['csrf_token_time']) {
                return false;
            }
            unset($_SESSION['_glpi_csrf_token']);
            unset($_SESSION['csrf_token_time']);
            return true;
        }
        return false;
    }
}
