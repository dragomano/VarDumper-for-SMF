<?php declare(strict_types = 1);

namespace Bugo\VarDumper;

use RuntimeException;

$GLOBALS['integration_test_state'] ??= [
	'functions' => [],
];

function add_integration_function(string $hook, string $callback, bool $permanent, string $file): void
{
	$GLOBALS['integration_test_state']['functions']['add_integration_function'][] = [
		'hook'      => $hook,
		'callback'  => $callback,
		'permanent' => $permanent,
		'file'      => $file,
	];
}

function addInlineCss(string $css): void
{
	$GLOBALS['integration_test_state']['functions']['addInlineCss'][] = $css;
}

function loadLanguage(string $language): void
{
	$GLOBALS['integration_test_state']['functions']['loadLanguage'][] = $language;
}

function updateSettings(array $settings): void
{
	$GLOBALS['integration_test_state']['functions']['updateSettings'][] = $settings;
}

function checkSession(): void
{
	$GLOBALS['integration_test_state']['functions']['checkSession'][] = true;
}

function saveDBSettings(array $settings): void
{
	$GLOBALS['integration_test_state']['functions']['saveDBSettings'][] = $settings;
}

function clean_cache(): void
{
	$GLOBALS['integration_test_state']['functions']['clean_cache'][] = true;
}

function redirectexit(string $url): never
{
	$GLOBALS['integration_test_state']['functions']['redirectexit'][] = $url;

	throw new RedirectExit($url);
}

function prepareDBSettingContext(array $config_vars): void
{
	$GLOBALS['integration_test_state']['functions']['prepareDBSettingContext'][] = $config_vars;
}

final class RedirectExit extends RuntimeException {}
