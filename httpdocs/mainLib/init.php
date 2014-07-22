<?php
require_once(dirname(__FILE__).'/packages/configurator/configurator.class.inc.php');

function cmfGetInitObject() {
	return cmfcConfigurator::factory('standAloneMultiAutoV1',array());
}