<?php declare(strict_types = 1);

namespace Tests;

require_once __DIR__ . '/Support/IntegrationFunctionStubs.php';

use Symfony\Component\VarDumper\VarDumper;
use Testo\Assert;
use Testo\Test;

final class AppTest
{
	private const APP_FILE = __DIR__ . '/../src/Sources/VarDumper/app.php';

	#[Test]
	public function it_blocks_direct_access_without_smf(): void
	{
		$output = [];
		$status = null;

		exec(PHP_BINARY . ' ' . escapeshellarg(self::APP_FILE), $output, $status);

		Assert::same($status, 0);
		Assert::same(implode("\n", $output), 'No direct access...');
	}

	#[Test]
	public function it_registers_all_integration_hooks_on_bootstrap(): void
	{
		$this->includeApp();

		$hooks = $this->hookCalls();

		Assert::count($hooks, 4);
		Assert::same($hooks[0], [
			'hook'      => 'integrate_load_theme',
			'callback'  => 'Bugo\\VarDumper\\Integration::loadTheme#',
			'permanent' => false,
			'file'      => $this->integrationFile(),
		]);
		Assert::same($hooks[1], [
			'hook'      => 'integrate_admin_areas',
			'callback'  => 'Bugo\\VarDumper\\Integration::adminAreas#',
			'permanent' => false,
			'file'      => $this->integrationFile(),
		]);
		Assert::same($hooks[2], [
			'hook'      => 'integrate_admin_search',
			'callback'  => 'Bugo\\VarDumper\\Integration::adminSearch#',
			'permanent' => false,
			'file'      => $this->integrationFile(),
		]);
		Assert::same($hooks[3], [
			'hook'      => 'integrate_modify_modifications',
			'callback'  => 'Bugo\\VarDumper\\Integration::modifyModifications#',
			'permanent' => false,
			'file'      => $this->integrationFile(),
		]);
	}

	#[Test]
	public function it_uses_the_dark_theme_and_hides_string_lengths_by_default(): void
	{
		$this->includeApp();

		ob_start();
		VarDumper::dump('abc');
		$output = (string) ob_get_clean();

		Assert::string($output)->contains('background-color:#18171B');
		Assert::string($output)->contains('title="3 characters">abc</span>"');
		Assert::string($output)->notContains('(3) "<span class=sf-dump-str');
	}

	#[Test]
	public function it_can_enable_light_theme_and_string_lengths_from_settings(): void
	{
		$this->includeApp([
			'vd_show_string_length' => true,
			'vd_use_light_theme'    => true,
		]);

		ob_start();
		VarDumper::dump('abc');
		$output = (string) ob_get_clean();

		Assert::string($output)->contains('background:none');
		Assert::string($output)->contains('(3) "<span class=sf-dump-str title="3 characters">abc</span>"');
	}

	private function includeApp(array $settings = []): void
	{
		$GLOBALS['integration_test_state'] = ['functions' => []];

		if (! defined('SMF')) {
			define('SMF', 1);
		}

		global $modSettings;
		$modSettings = $settings;

		require self::APP_FILE;
	}

	private function integrationFile(): string
	{
		return (string) realpath(__DIR__ . '/../src/Sources/VarDumper/Integration.php');
	}

	private function hookCalls(): array
	{
		return $GLOBALS['integration_test_state']['functions']['add_integration_function'] ?? [];
	}
}
