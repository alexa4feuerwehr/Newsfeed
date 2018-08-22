<?php
/**
 * Created by PhpStorm.
 * User: manuelgeil
 * Date: 10.12.16
 * Time: 13:40
 */

namespace Newsfeed\View;

use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\ViewModel;


class LayoutListener
	extends AbstractListenerAggregate
{
	/**
	 * @var array
	 */
	protected $layoutSegments = [];

	/**
	 * @param array $layoutSegments
	 */
	function __construct(array $layoutSegments = [])
	{
		$this->layoutSegments = $layoutSegments;
	}

	/**
	 * Attach to an event manager
	 *
	 * @param EventManagerInterface $events
	 * @param int                   $priority
	 */
	public function attach(EventManagerInterface $events, $priority = -100)
	{
		$this->listeners[] = $events->attach(
			MvcEvent::EVENT_RENDER,
			[$this, 'renderLayoutSegments'],
			$priority
		);
	}

	/**
	 * Listen to the "render" event and render additional layout segments
	 *
	 * @param  MvcEvent $objEvent
	 *
	 * @return null
	 */
	public function renderLayoutSegments(MvcEvent $objEvent)
	{
	    //
		$objRouteMatched = $objEvent->getRouteMatch();
        $strController = $objRouteMatched ? $objRouteMatched->getParam('controller') : null;
        $strAction = $objRouteMatched ? $objRouteMatched->getParam('action') : null;
        //
        if(in_array($strController , [\Newsfeed\Controller\Login::class,\Newsfeed\Controller\Setup::class]))
        {
            $objEvent->getViewModel()->setTemplate('layout/login');
        }
        else if(
            ($strController == \Newsfeed\Controller\Dashboard::class && in_array($strAction, ['checkAmazonLinking']))
        )
        {
            $objEvent->getViewModel()->setTemplate('layout/empty');
        }
	}

}
