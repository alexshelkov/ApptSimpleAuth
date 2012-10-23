<?php
namespace ApptSimpleAuth;

use SimpleAcl\Role\RoleAggregate;
use Doctrine\Common\Collections\ArrayCollection;

class User extends RoleAggregate
{
    protected $password;

    protected $email;

    public function __construct()
    {
        $this->objects = new ArrayCollection();
    }

    protected function removeObjects()
    {
        $this->objects->clear();
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setPassword($password)
    {
        $this->password = $password;
    }

    public function getPassword()
    {
        return $this->password;
    }
}
