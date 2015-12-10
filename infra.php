<?php
namespace infrajs\controller;
use infrajs\path\Path;
use infrajs\event\Event;
use infrajs\layer\div\Div;

Event::handler('oninit', function () {
	Controller::runAddKeys('divs');
	
	External::add('divs', function (&$now, $ext) {//Если уже есть пропускаем
		if (!$now) {
			$now = array();
		}
		foreach ($ext as $i => $v) {
			if (isset($now[$i])) {
				continue;
			}
			$now[$i] = array();
			Each::fora($ext[$i], function (&$l) use (&$now, $i) {
				array_push($now[$i], array('external' => $l));
			});
		}

		return $now;
	});
}, 'div:layer');
Event::handler('layer.oncheck', function (&$layer) {
	//В onchange слоя может не быть див// Это нужно чтобы в external мог быть определён div перед тем как наследовать div от родителя
	if (@!$layer['div'] && @$layer['parent']) {
		$layer['div'] = $layer['parent']['div'];
	}
}, 'div');

Event::handler('layer.oncheck', function (&$layer) {
	//Без этого не показывается окно cо стилями.. только его заголовок..
	Each::forx($layer['divs'], function (&$l, $div) {
		if (@!$l['div']) {
			$l['div'] = $div;
		}
	});
}, 'div');


Event::handler('layer.oncheck', function (&$layer) {
	Div::divtpl($layer);
}, 'div:env,config,external');

Event::handler('layer.isshow', function (&$layer) {
	if (empty($layer['div'])&&!empty($layer['parent'])) return false;
	//Такой слой игнорируется, события onshow не будет, но обработка пройдёт дальше у других дивов
	return Div::divcheck($layer);
}, 'div:is');