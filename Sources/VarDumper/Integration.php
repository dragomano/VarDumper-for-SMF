<?php

declare(strict_types = 1);

/**
 * Integration.php
 *
 * @package VarDumper
 * @link https://github.com/dragomano/VarDumper-for-SMF
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2021—2022 Bugo
 * @license https://opensource.org/licenses/MIT The MIT License
 *
 * @version 0.2
 */

namespace Bugo\VarDumper;

if (! defined('SMF'))
	die('No direct access...');

class Integration
{
	public function hooks()
	{
		add_integration_function('integrate_load_theme', __CLASS__ . '::loadTheme#', false, __FILE__);
		add_integration_function('integrate_admin_areas', __CLASS__ . '::adminAreas#', false, __FILE__);
		add_integration_function('integrate_admin_search', __CLASS__ . '::adminSearch#', false, __FILE__);
		add_integration_function('integrate_modify_modifications', __CLASS__ . '::modifyModifications#', false, __FILE__);
	}

	public function loadTheme()
	{
		global $modSettings, $context;

		addInlineCss('
		pre.sf-dump {
			font-size: ' . (empty($modSettings['vd_font_size']) ? '1rem' : $modSettings['vd_font_size']) . ' !important;
			max-height: 300px;
			overflow: auto !important;
		}');

		if ($context['current_action'] === 'admin')
			loadLanguage('VarDumper');
	}

	public function adminAreas(array &$admin_areas)
	{
		global $txt;

		$admin_areas['config']['areas']['modsettings']['subsections']['var_dumper'] = array($txt['vd_title']);
	}

	public function adminSearch(array &$language_files, array &$include_files, array &$settings_search)
	{
		$settings_search[] = array(array($this, 'settings'), 'area=modsettings;sa=var_dumper');
	}

	public function modifyModifications(array &$subActions)
	{
		$subActions['var_dumper'] = array($this, 'settings');
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
		$context[$context['admin_menu_name']]['tab_data']['tabs']['var_dumper'] = array('description' => $txt['vd_description']);

		if (empty($modSettings['vd_font_size']))
			updateSettings(array('vd_font_size' => '1rem'));

		$config_vars = array(
			array('text', 'vd_font_size'),
			array('check', 'vd_show_string_length'),
			array('check', 'vd_use_light_theme'),
		);

		if ($return_config)
			return $config_vars;

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
