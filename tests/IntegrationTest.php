<?php declare(strict_types = 1);

namespace Tests;

require_once __DIR__ . '/Support/IntegrationFunctionStubs.php';

use Bugo\VarDumper\Integration;
use Bugo\VarDumper\RedirectExit;
use Testo\Assert;
use Testo\Test;

final class IntegrationTest
{
	#[Test]
	public function it_registers_all_expected_hooks(): void
	{
		$this->resetEnvironment();

		(new Integration)->hooks();

		Assert::same($GLOBALS['integration_test_state']['functions']['add_integration_function'], [
			[
				'hook'      => 'integrate_load_theme',
				'callback'  => 'Bugo\\VarDumper\\Integration::loadTheme#',
				'permanent' => false,
				'file'      => $this->integrationFile(),
			],
			[
				'hook'      => 'integrate_admin_areas',
				'callback'  => 'Bugo\\VarDumper\\Integration::adminAreas#',
				'permanent' => false,
				'file'      => $this->integrationFile(),
			],
			[
				'hook'      => 'integrate_admin_search',
				'callback'  => 'Bugo\\VarDumper\\Integration::adminSearch#',
				'permanent' => false,
				'file'      => $this->integrationFile(),
			],
			[
				'hook'      => 'integrate_modify_modifications',
				'callback'  => 'Bugo\\VarDumper\\Integration::modifyModifications#',
				'permanent' => false,
				'file'      => $this->integrationFile(),
			],
		]);
	}

	#[Test]
	public function it_loads_theme_css_with_default_font_size(): void
	{
		$this->resetEnvironment(
			context: ['current_action' => 'forum'],
		);

		(new Integration)->loadTheme();

		Assert::count($GLOBALS['integration_test_state']['functions']['addInlineCss'], 1);
		Assert::string($GLOBALS['integration_test_state']['functions']['addInlineCss'][0])->contains('font-size: 1rem !important;');
		Assert::null($GLOBALS['integration_test_state']['functions']['loadLanguage'] ?? null);
	}

	#[Test]
	public function it_loads_language_on_admin_pages_and_uses_custom_font_size(): void
	{
		$this->resetEnvironment(
			context: ['current_action' => 'admin'],
			modSettings: ['vd_font_size' => '1.25rem'],
		);

		(new Integration)->loadTheme();

		Assert::string($GLOBALS['integration_test_state']['functions']['addInlineCss'][0])->contains('font-size: 1.25rem !important;');
		Assert::same($GLOBALS['integration_test_state']['functions']['loadLanguage'], ['VarDumper']);
	}

	#[Test]
	public function it_adds_the_admin_area_entry(): void
	{
		$this->resetEnvironment(txt: ['vd_title' => 'Var Dumper']);
		$admin_areas = ['config' => ['areas' => ['modsettings' => ['subsections' => []]]]];

		(new Integration)->adminAreas($admin_areas);

		Assert::same($admin_areas['config']['areas']['modsettings']['subsections']['var_dumper'], ['Var Dumper']);
	}

	#[Test]
	public function it_registers_settings_for_admin_search(): void
	{
		$this->resetEnvironment();
		$language_files = [];
		$include_files = [];
		$settings_search = [];
		$integration = new Integration;

		$integration->adminSearch($language_files, $include_files, $settings_search);

		Assert::count($settings_search, 1);
		Assert::same($settings_search[0][0][0], $integration);
		Assert::same($settings_search[0][0][1], 'settings');
		Assert::same($settings_search[0][1], 'area=modsettings;sa=var_dumper');
	}

	#[Test]
	public function it_registers_the_var_dumper_subaction(): void
	{
		$this->resetEnvironment();
		$subActions = [];
		$integration = new Integration;

		$integration->modifyModifications($subActions);

		Assert::same($subActions['var_dumper'][0], $integration);
		Assert::same($subActions['var_dumper'][1], 'settings');
	}

	#[Test]
	public function it_returns_settings_config_and_initializes_default_font_size(): void
	{
		$this->resetEnvironment(
			context: ['admin_menu_name' => 'menu'],
			txt: ['vd_title' => 'Var Dumper', 'settings' => 'Settings'],
		);

		$config = (new Integration)->settings(true);

		Assert::same($config, [
			['text', 'vd_font_size'],
			['check', 'vd_show_string_length'],
			['check', 'vd_use_light_theme'],
		]);
		Assert::same($GLOBALS['context']['page_title'], 'Var Dumper');
		Assert::same($GLOBALS['context']['settings_title'], 'Settings');
		Assert::same($GLOBALS['context']['post_url'], 'https://example.test/index.php?action=admin;area=modsettings;save;sa=var_dumper');
		Assert::same($GLOBALS['integration_test_state']['functions']['updateSettings'], [
			['vd_font_size' => '1rem'],
		]);
		Assert::null($GLOBALS['integration_test_state']['functions']['prepareDBSettingContext'] ?? null);
	}

	#[Test]
	public function it_prepares_the_settings_context_when_not_saving(): void
	{
		$this->resetEnvironment(
			context: ['admin_menu_name' => 'menu'],
			txt: [
				'vd_title'       => 'Var Dumper',
				'settings'       => 'Settings',
				'vd_description' => 'Debug variables easily.',
			],
			modSettings: ['vd_font_size' => '0.9rem'],
		);

		$result = (new Integration)->settings();

		Assert::null($result);
		Assert::same($GLOBALS['context']['menu']['tab_data']['description'], 'Debug variables easily.');
		Assert::same($GLOBALS['integration_test_state']['functions']['prepareDBSettingContext'], [[
			['text', 'vd_font_size'],
			['check', 'vd_show_string_length'],
			['check', 'vd_use_light_theme'],
		]]);
		Assert::null($GLOBALS['integration_test_state']['functions']['updateSettings'] ?? null);
	}

	#[Test]
	public function it_saves_settings_and_redirects_when_requested(): void
	{
		$this->resetEnvironment(
			context: ['admin_menu_name' => 'menu'],
			txt: [
				'vd_title'       => 'Var Dumper',
				'settings'       => 'Settings',
				'vd_description' => 'Debug variables easily.',
			],
			modSettings: ['vd_font_size' => '1rem'],
			get: ['save' => '1'],
		);

		try {
			(new Integration)->settings();
			Assert::fail('settings() must redirect after saving.');
		} catch (RedirectExit $redirect) {
			Assert::same($redirect->getMessage(), 'action=admin;area=modsettings;sa=var_dumper');
		}

		Assert::same($GLOBALS['integration_test_state']['functions']['checkSession'], [true]);
		Assert::same($GLOBALS['integration_test_state']['functions']['saveDBSettings'], [[
			['text', 'vd_font_size'],
			['check', 'vd_show_string_length'],
			['check', 'vd_use_light_theme'],
		]]);
		Assert::same($GLOBALS['integration_test_state']['functions']['clean_cache'], [true]);
		Assert::same($GLOBALS['integration_test_state']['functions']['redirectexit'], [
			'action=admin;area=modsettings;sa=var_dumper',
		]);
		Assert::null($GLOBALS['integration_test_state']['functions']['prepareDBSettingContext'] ?? null);
	}

	private function resetEnvironment(
		array $context = [],
		array $txt = [],
		array $modSettings = [],
		array $get = [],
	): void {
		if (! defined('SMF')) {
			define('SMF', 1);
		}

		$GLOBALS['integration_test_state'] = ['functions' => []];
		$GLOBALS['context'] = $context;
		$GLOBALS['txt'] = $txt;
		$GLOBALS['scripturl'] = 'https://example.test/index.php';
		$GLOBALS['modSettings'] = $modSettings;
		$_GET = $get;
	}

	private function integrationFile(): string
	{
		return (string) realpath(__DIR__ . '/../src/Sources/VarDumper/Integration.php');
	}
}
