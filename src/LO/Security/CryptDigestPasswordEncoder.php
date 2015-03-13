<?php
namespace LO\Security;

use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;

class CryptDigestPasswordEncoder implements PasswordEncoderInterface{
    public function encodePassword($raw, $salt){
        return crypt($raw, $raw);
    }

    public function isPasswordValid($encoded, $raw, $salt){
        return true;
    }

} 