<?php
namespace ApptSimpleAuth\Acl\Object;

use RecursiveIterator as SplIterator;
use SimpleAcl\Object;
use Doctrine\Common\Collections\Collection;

class RecursiveIterator implements SplIterator
{
    /**
     * @var Object[]|Collection
     */
    protected $objects = array();

    public function current()
    {
        if ( $this->objects instanceof Collection ) {
            return $this->objects->current();
        } else {
            return current($this->objects);
        }
    }

    public function next()
    {
        if ( $this->objects instanceof Collection ) {
            return $this->objects->next();
        } else {
            return next($this->objects);
        }
    }

    public function key()
    {
        if ( $this->objects instanceof Collection ) {
            $key = $this->objects->key();
        } else {
            $key = key($this->objects);
        }

        if ( is_null($key) ) {
            return null;
        }

        return $this->current()->getName();
    }

    public function valid()
    {
        return $this->key() !== null;
    }

    public function rewind()
    {
        if ( $this->objects instanceof Collection ) {
            return $this->objects->first();
        } else {
            return reset($this->objects);
        }
    }

    public function hasChildren()
    {
        if ( is_null($this->key()) ) {
            return false;
        }

        $object = $this->current();

        return count($object->getChildren()) > 0;
    }

    public function getChildren()
    {
        $object = $this->current();
        $children = $object->getChildren();

        return new RecursiveIterator($children);
    }

    public function __construct($objects)
    {
        $this->objects = $objects;
    }
}