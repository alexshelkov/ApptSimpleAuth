<?php
namespace ApptSimpleAuth;

use SimpleAcl\Rule as SimpleAclRule;

class Rule extends SimpleAclRule
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @codeCoverageIgnore
     *
     * @param $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @codeCoverageIgnore
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }
}