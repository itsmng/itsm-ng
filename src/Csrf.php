<?php

namespace itsmng;

class Csrf {

    static function generate() {
        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;
        $_SESSION['csrf_token_time'] = time() + 350;
        return $token;
    }

    static function verify() {
        if (isset($_SESSION['csrf_token'])
            && isset($_POST['csrf_token'])
            && $_SESSION['csrf_token'] === $_POST['csrf_token']) {
                if (time() >= $_SESSION['csrf_token_time']) {
                    return false;
                }
                unset($_SESSION['csrf_token']);
                unset($_SESSION['csrf_token_time']);
                return true;
        }
        return false;
    }
}