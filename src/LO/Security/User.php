<?php
namespace LO\Security;

use Symfony\Component\Security\Core\User\UserInterface;

class User implements UserInterface{
    private $id;
    private $roles;
    private $pass;
    private $token;
    private $expire;
    
    public function getId(){
        return $this->id;
    }

    public function getRoles(){
        return is_array($this->roles)? $this->roles: explode(',', $this->roles);
    }

    public function getPassword(){
        return $this->pass;
    }

    public function getSalt(){
        return null;
    }

    public function getUsername(){
        return $this->token;
    }
    
    public function getTimeExpire(){
        return $this->expire;
    }

    public function eraseCredentials(){

    }

    public function setFromArray(array $data){
        foreach($data as $k => $v){
            if(property_exists($this, $k)){
                $this->$k = $v;
            }
        }

        return $this;
    }
}