<?php
namespace LO\Security;

use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;

class CryptDigestPasswordEncoder implements PasswordEncoderInterface{
    public function encodePassword($raw, $salt){
        return password_hash($raw, PASSWORD_BCRYPT, ['salt' => $salt]);
    }

    public function isPasswordValid($encoded, $raw, $salt){
        return password_verify($raw, $encoded);
    }

} 