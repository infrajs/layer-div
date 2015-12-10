<?php

//Свойство div

namespace infrajs\layer\div;

use infrajs\controller\Controller;
use infrajs\controller\Each;

class div
{
	public static function init()
	{
		
	}
	public static function divtpl(&$layer)
	{
		if (!isset($layer['divtpl'])) {
			return;
		}
		$layer['div'] = infra_template_parse(array($layer['divtpl']), $layer);
	}
	public static function divcheck(&$layer)
	{

		$start = false;
		if (Controller::run(Controller::$layers, function (&$l) use (&$layer, &$start) {//Пробежка не по слоям на ветке, а по всем слоям обрабатываемых после.. .то есть и на других ветках тоже
			if (!$start) {
				if (Each::isEqual($layer, $l)) {
					$start = true;
				}

				return;
			}
			if (@$l['div'] !== @$layer['div']) return; //ищим совпадение дивов впереди
			if (Controller::is('show', $l)) {

				Controller::isSaveBranch($layer, Controller::isParent($l, $layer));
				return true;//Слой который дальше показывается в томже диве найден
			}
		})) {

			return false;
		}
	}
}
