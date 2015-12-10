<?php
namespace infrajs\controller;
use infrajs\path\Path;
use infrajs\event\Event;
use infrajs\template\Template;

/**
 * div, divs, divtpl
 *
 **/
Path::req('*controller/infra.php');
Event::handler('oninit', function () {
	Run::runAddKeys('divs');
	
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
	if (!isset($layer['divtpl'])) return;
	$layer['div'] = Template::parse(array($layer['divtpl']), $layer);
}, 'div:env,config,external');

Event::handler('layer.isshow', function (&$layer) {
	if (empty($layer['div'])&&!empty($layer['parent'])) return false;
	//Такой слой игнорируется, события onshow не будет, но обработка пройдёт дальше у других дивов
	$start = false;
	if (Run::exec(Controller::$layers, function (&$l) use (&$layer, &$start) {//Пробежка не по слоям на ветке, а по всем слоям обрабатываемых после.. .то есть и на других ветках тоже
		if (!$start) {
			if (Each::isEqual($layer, $l)) {
				$start = true;
			}

			return;
		}
		if (@$l['div'] !== @$layer['div']) return; //ищим совпадение дивов впереди
		if (Controller::fire('layer.isshow', $l)) {
			$layer['is_save_branch'] = Layer::isParent($l, $layer);
			return true;//Слой который дальше показывается в томже диве найден
		}
	})) {
		return false;
	}
}, 'div:is');