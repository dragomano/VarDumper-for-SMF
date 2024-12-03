<?php declare(strict_types = 1);

/**
 * @package VarDumper
 * @link https://github.com/dragomano/VarDumper-for-SMF
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2021—2024 Bugo
 * @license https://opensource.org/licenses/MIT The MIT License
 */

namespace Bugo\VarDumper;

if (! defined('SMF'))
	die('No direct access...');

class Integration
{
	public function hooks(): void
	{
		add_integration_function('integrate_load_theme', __CLASS__ . '::loadTheme#', false, __FILE__);
		add_integration_function('integrate_admin_areas', __CLASS__ . '::adminAreas#', false, __FILE__);
		add_integration_function('integrate_admin_search', __CLASS__ . '::adminSearch#', false, __FILE__);
		add_integration_function('integrate_modify_modifications', __CLASS__ . '::modifyModifications#', false, __FILE__);
	}

	public function loadTheme(): void
	{
		global $modSettings, $context;

		addInlineCss('
		pre.sf-dump {
			font-size: ' . (empty($modSettings['vd_font_size']) ? '1rem' : $modSettings['vd_font_size']) . ' !important;
			max-height: 300px;
			overflow: auto !important;
		}');

		$context['current_action'] === 'admin' && loadLanguage('VarDumper');
	}

	public function adminAreas(array &$admin_areas): void
	{
		global $txt;

		$admin_areas['config']['areas']['modsettings']['subsections']['var_dumper'] = [$txt['vd_title']];
	}

	public function adminSearch(array &$language_files, array &$include_files, array &$settings_search): void
	{
		$settings_search[] = [[$this, 'settings'], 'area=modsettings;sa=var_dumper'];
	}

	public function modifyModifications(array &$subActions): void
	{
		$subActions['var_dumper'] = [$this, 'settings'];
	}

	/**
	* @return void|array
	*/
	public function settings(bool $return_config = false)
	{
		global $context, $txt, $scripturl, $modSettings;

		$context['page_title']     = $txt['vd_title'];
		$context['settings_title'] = $txt['settings'];
		$context['post_url'] = $scripturl . '?action=admin;area=modsettings;save;sa=var_dumper';

		if (empty($modSettings['vd_font_size'])) {
			updateSettings(['vd_font_size' => '1rem']);
		}

		$config_vars = [
			['text', 'vd_font_size'],
			['check', 'vd_show_string_length'],
			['check', 'vd_use_light_theme'],
		];

		if ($return_config)
			return $config_vars;

		$context[$context['admin_menu_name']]['tab_data']['description'] = $txt['vd_description'];

		// Saving?
		if (isset($_GET['save'])) {
			checkSession();

			$save_vars = $config_vars;
			saveDBSettings($save_vars);

			clean_cache();
			redirectexit('action=admin;area=modsettings;sa=var_dumper');
		}

		prepareDBSettingContext($config_vars);
	}
}
