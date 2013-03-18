<?php
namespace ApptSimpleAuth;

use ApptSimpleAuth\Acl;

use Doctrine\Common\Persistence\ObjectRepository;

class AclService
{
    /**
     * @var string
     */
    protected $defaultAcl;

    /**
     * @var ObjectRepository
     */
    protected $repository;

    /**
     * @param ObjectRepository $repository
     */
    public function __construct(ObjectRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @return ObjectRepository
     */
    protected function getRepository()
    {
        return $this->repository;
    }

    /**
     * @param string $defaultAcl
     */
    public function setDefaultAcl($defaultAcl)
    {
        $this->defaultAcl = $defaultAcl;
    }

    /**
     * @return string
     */
    public function getDefaultAcl()
    {
        return $this->defaultAcl;
    }

    public function getAcl($name = false)
    {
        if ( ! $name ) {
            $name = $this->getDefaultAcl();
        }

        return $this->getRepository()->find($name);
    }

    /**
     * @param string | bool $name
     * @param bool $get
     *
     * @return Acl
     */
    public function createAcl($name = false, $get = true)
    {
        if ( $get ) {
            if ( $acl = $this->getAcl($name) ) {
                return $acl;
            }
        }

        if ( ! $name ) {
            $name = $this->getDefaultAcl();
        }

        $class = $this->getRepository()->getClassName();

        $acl = new $class($name);

        return $acl;
    }
}