<?php
namespace ApptSimpleAuthTest;

use PHPUnit_Framework_TestCase;

use ApptSimpleAuth\EventListener;
use Zend\EventManager\EventManager;
use Zend\EventManager\SharedEventManager;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\Http\RouteMatch;

class EvenListenerTest extends PHPUnit_Framework_TestCase
{
    public function testAttachDeAttach()
    {
        $sem = new SharedEventManager();
        $em = new EventManager();

        $el = new EventListener();

        $em->attachAggregate($el);
        $sem->attachAggregate($el);

        $this->assertContains('dispatch', $sem->getEvents('Zend\Stdlib\DispatchableInterface'));
        $this->assertContains('dispatch.error', $em->getEvents());

        $el->detach($em);
        $el->detachShared($sem);

        $this->assertEquals(0, count($em->getEvents()));
        $this->assertEquals(0, count($sem->getEvents('Zend\Stdlib\DispatchableInterface')));
    }

    public function testNoRouteMatch()
    {
        $sem = new SharedEventManager();

        $el = new EventListener();

        $sem->attachAggregate($el);

        $event = new MvcEvent();
        $em = new EventManager();

        $em->setSharedManager($sem);
        $em->setIdentifiers(array('Zend\Stdlib\DispatchableInterface'));

        $r = $em->trigger('dispatch', $event);

        $this->assertNull($r->last());
    }

    public function testNoController()
    {
        $sem = new SharedEventManager();

        $el = new EventListener();

        $sem->attachAggregate($el);

        $event = new MvcEvent();
        $routeMatch = new RouteMatch(array());

        $event->setRouteMatch($routeMatch);

        $em = new EventManager();

        $em->setSharedManager($sem);
        $em->setIdentifiers(array('Zend\Stdlib\DispatchableInterface'));

        $r = $em->trigger('dispatch', $event);

        $this->assertNull($r->last());
    }

    public function testNoControllerInControllerManager()
    {
        $sem = new SharedEventManager();

        $el = new EventListener();

        $sem->attachAggregate($el);

        $event = new MvcEvent();
        $routeMatch = new RouteMatch(array('controller' => 'test'));

        $cl = $this->getMock('Zend\Mvc\Controller\PluginManager');
        $cl->expects($this->once())->method('has')->will($this->returnValue(false));

        $ml = $this->getMock('Zend\ModuleManager\ModuleManager', array(), array(), '', false);
        $ml->expects($this->once())->method('getModule')->will($this->returnValue(false));

        $sm = $this->getMock('Zend\ServiceManager\ServiceManager');
        $sm->expects($this->exactly(2))->
            method('get')->
            with($this->logicalOr(
                $this->equalTo('ControllerLoader'),
                $this->equalTo('ModuleManager')
            ))->
            will($this->returnCallback(function($a) use ($ml, $cl) {
                return (($a == 'ModuleManager') ? $ml : $cl);
            }));

        $app = $this->getMock('Zend\Mvc\Application', array(), array(), '', false);
        $app->expects($this->any())->method('getServiceManager')->will($this->returnValue($sm));

        $event->setRouteMatch($routeMatch);
        $event->setApplication($app);

        $em = new EventManager();

        $em->setSharedManager($sem);
        $em->setIdentifiers(array('Zend\Stdlib\DispatchableInterface'));

        $r = $em->trigger('dispatch', $event);

        $this->assertNull($r->last());
    }
}