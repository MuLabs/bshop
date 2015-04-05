<?php
namespace Mu\Bundle\Bshop;

use Mu\App;
use Mu\Kernel;

class Bundle extends Kernel\Bundle\Core
{
	/**
	 * @return string
	 */
	public function getMainPath()
	{
		return dirname(__FILE__);
	}

	public function initialize()
	{
	}
}