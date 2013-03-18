<?php
namespace ApptSimpleAuth;

use SimpleAcl\Role\RoleAggregate;
use Doctrine\Common\Collections\ArrayCollection;

use SimpleAcl\Role;

use Zend\Crypt\Password\Bcrypt;
use Zend\Crypt\Password\PasswordInterface;

class User extends RoleAggregate
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $password;

    /**
     * @var string
     */
    protected $email;

    /**
     * @var PasswordInterface
     */
    protected $passwordEncrypt;

    public function __construct()
    {
        $this->objects = new ArrayCollection();
        $this->setId(uniqid());
    }

    /**
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param PasswordInterface $passwordEncrypt
     */
    public function setPasswordEncrypt(PasswordInterface $passwordEncrypt)
    {
        $this->passwordEncrypt = $passwordEncrypt;
    }

    /**
     * @return PasswordInterface
     */
    public function getPasswordEncrypt()
    {
        if ( ! $this->passwordEncrypt ) {
            $this->setPasswordEncrypt(new Bcrypt());
        }

        return $this->passwordEncrypt;
    }

    public function removeRoles()
    {
        $this->objects->clear();
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $this->getPasswordEncrypt()->create($password);
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     *
     * @return bool
     */
    public function verifyPassword($password)
    {
        return $this->getPasswordEncrypt()->verify($password, $this->getPassword());
    }
}