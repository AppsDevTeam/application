<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004, 2011 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Application\Diagnostics;

use Nette,
	Nette\Application\Routers,
	Nette\Application\UI\Presenter, // templates
	Nette\Diagnostics\Debugger;



/**
 * Routing debugger for Debug Bar.
 *
 * @author     David Grudl
 */
class RoutingPanel extends Nette\Object implements Nette\Diagnostics\IBarPanel
{
	/** @var Nette\Application\IRouter */
	private $router;

	/** @var Nette\Http\IRequest */
	private $httpRequest;

	/** @var array */
	private $routers = array();

	/** @var Nette\Application\Request */
	private $request;



	public function __construct(Nette\Application\IRouter $router, Nette\Http\IRequest $httpRequest)
	{
		$this->router = $router;
		$this->httpRequest = $httpRequest;
	}



	/**
	 * Renders tab.
	 * @return string
	 */
	public function getTab()
	{
		$this->analyse($this->router);
		ob_start();
		require __DIR__ . '/templates/RoutingPanel.tab.phtml';
		return ob_get_clean();
	}



	/**
	 * Renders panel.
	 * @return string
	 */
	public function getPanel()
	{
		ob_start();
		require __DIR__ . '/templates/RoutingPanel.panel.phtml';
		return ob_get_clean();
	}



	/**
	 * Analyses simple route.
	 * @param  Nette\Application\IRouter
	 * @return void
	 */
	private function analyse($router)
	{
		if ($router instanceof Routers\RouteList) {
			foreach ($router as $subRouter) {
				$this->analyse($subRouter);
			}
			return;
		}

		$request = $router->match($this->httpRequest);
		$matched = $request === NULL ? 'no' : 'may';
		if ($request !== NULL && empty($this->request)) {
			$this->request = $request;
			$matched = 'yes';
		}

		$this->routers[] = array(
			'matched' => $matched,
			'class' => get_class($router),
			'defaults' => $router instanceof Routers\Route || $router instanceof Routers\SimpleRouter ? $router->getDefaults() : array(),
			'mask' => $router instanceof Routers\Route ? $router->getMask() : NULL,
			'request' => $request,
		);
	}

}
