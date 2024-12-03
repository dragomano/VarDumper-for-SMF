<?php declare(strict_types = 1);

/**
 * @package VarDumper
 * @link https://github.com/dragomano/VarDumper-for-SMF
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2021—2024 Bugo
 * @license https://opensource.org/licenses/MIT The MIT License
 */

if (! defined('SMF'))
	die('No direct access...');

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/Integration.php';

use Bugo\VarDumper\Integration;
use Symfony\Component\VarDumper\VarDumper;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;
use Symfony\Component\VarDumper\Dumper\AbstractDumper;

global $modSettings;

VarDumper::setHandler(function ($var) use ($modSettings) {
	$cloner = new VarCloner();
	$dumper = empty($modSettings['vd_show_string_length'])
		? new HtmlDumper()
		: new HtmlDumper(null, null, AbstractDumper::DUMP_STRING_LENGTH);
	$dumper->setTheme(empty($modSettings['vd_use_light_theme']) ? 'dark' : 'light');
	$dumper->dump($cloner->cloneVar($var));
});

$app = new Integration;
$app->hooks();
