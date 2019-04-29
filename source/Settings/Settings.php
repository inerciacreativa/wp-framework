<?php

namespace ic\Framework\Settings;

use BadMethodCallException;
use ic\Framework\Data\Options;
use ic\Framework\Settings\Page\Page;
use ic\Framework\Settings\Page\PageSimple;
use ic\Framework\Settings\Page\PageTabbed;
use ic\Framework\Support\Str;
use InvalidArgumentException;

/**
 * Class Settings
 *
 * @package ic\Framework\Settings
 *
 * @method static PageSimple optionsGeneral(Options $options)
 * @method static PageSimple optionsWriting(Options $options)
 * @method static PageSimple optionsReading(Options $options)
 * @method static PageSimple optionsDiscussion(Options $options)
 * @method static PageSimple optionsPermalink(Options $options)
 * @method static PageSimple optionsMedia(Options $options)
 *
 *
 * @method static PageSimple siteOptions(Options $options, string $title, array $config = [])
 * @method static PageSimple siteDashboard(Options $options, string $title, array $config = [])
 * @method static PageSimple sitePosts(Options $options, string $title, array $config = [])
 * @method static PageSimple sitePages(Options $options, string $title, array $config = [])
 * @method static PageSimple siteMedia(Options $options, string $title, array $config = [])
 * @method static PageSimple siteComments(Options $options, string $title, array $config = [])
 * @method static PageSimple siteThemes(Options $options, string $title, array $config = [])
 * @method static PageSimple sitePlugins(Options $options, string $title, array $config = [])
 * @method static PageSimple siteUsers(Options $options, string $title, array $config = [])
 * @method static PageSimple siteManagement(Options $options, string $title, array $config = [])
 *
 * @method static PageTabbed siteTabbedOptions(Options $options, string $title, array $config = [])
 * @method static PageTabbed siteTabbedDashboard(Options $options, string $title, array $config = [])
 * @method static PageTabbed siteTabbedPosts(Options $options, string $title, array $config = [])
 * @method static PageTabbed siteTabbedPages(Options $options, string $title, array $config = [])
 * @method static PageTabbed siteTabbedMedia(Options $options, string $title, array $config = [])
 * @method static PageTabbed siteTabbedComments(Options $options, string $title, array $config = [])
 * @method static PageTabbed siteTabbedThemes(Options $options, string $title, array $config = [])
 * @method static PageTabbed siteTabbedPlugins(Options $options, string $title, array $config = [])
 * @method static PageTabbed siteTabbedUsers(Options $options, string $title, array $config = [])
 * @method static PageTabbed siteTabbedManagement(Options $options, string $title, array $config = [])
 *
 *
 * @method static PageSimple networkSettings(Options $options, string $title, array $config = [])
 * @method static PageSimple networkDashboard(Options $options, string $title, array $config = [])
 * @method static PageSimple networkSites(Options $options, string $title, array $config = [])
 * @method static PageSimple networkUsers(Options $options, string $title, array $config = [])
 * @method static PageSimple networkThemes(Options $options, string $title, array $config = [])
 * @method static PageSimple networkPlugins(Options $options, string $title, array $config = [])
 * @method static PageSimple networkUpdates(Options $options, string $title, array $config = [])
 *
 * @method static PageTabbed networkTabbedSettings(Options $options, string $title, array $config = [])
 * @method static PageTabbed networkTabbedDashboard(Options $options, string $title, array $config = [])
 * @method static PageTabbed networkTabbedSites(Options $options, string $title, array $config = [])
 * @method static PageTabbed networkTabbedUsers(Options $options, string $title, array $config = [])
 * @method static PageTabbed networkTabbedThemes(Options $options, string $title, array $config = [])
 * @method static PageTabbed networkTabbedPlugins(Options $options, string $title, array $config = [])
 * @method static PageTabbed networkTabbedUpdates(Options $options, string $title, array $config = [])
 */
class Settings
{

	public const PAGE_MANAGEMENT = 'tools.php';

	public const PAGE_OPTIONS = 'options-general.php';

	public const PAGE_THEMES = 'themes.php';

	public const PAGE_PLUGINS = 'plugins.php';

	public const PAGE_USERS = 'users.php';

	public const PAGE_DASHBOARD = 'index.php';

	public const PAGE_POSTS = 'edit.php';

	public const PAGE_PAGES = 'edit.php?post_type=page';

	public const PAGE_MEDIA = 'upload.php';

	public const PAGE_COMMENTS = 'edit-comments.php';

	public const PAGE_SETTINGS = 'settings.php';

	public const PAGE_SITES = 'sites.php';

	public const PAGE_UPDATES = 'update-core.php';

	public const MENU_SITE = 'admin_menu';

	public const MENU_NETWORK = 'network_admin_menu';

	/**
	 * @var array The possible option pages with the default sections.
	 */
	protected static $optionPages = [
		'general'    => ['default'],
		'writing'    => ['default', 'post_via_email'],
		'reading'    => ['default'],
		'discussion' => ['default', 'avatars'],
		'media'      => ['default', 'embeds', 'uploads'],
		'permalink'  => ['optional'],
	];

	/**
	 * @var array The possible page parents for a site page.
	 */
	protected static $sitePages = [
		'options'    => self::PAGE_OPTIONS,
		'dashboard'  => self::PAGE_DASHBOARD,
		'posts'      => self::PAGE_POSTS,
		'pages'      => self::PAGE_PAGES,
		'media'      => self::PAGE_MEDIA,
		'comments'   => self::PAGE_COMMENTS,
		'themes'     => self::PAGE_THEMES,
		'plugins'    => self::PAGE_PLUGINS,
		'users'      => self::PAGE_USERS,
		'management' => self::PAGE_MANAGEMENT,
	];

	/**
	 * @var array The possible page parents for a network page.
	 */
	protected static $networkPages = [
		'settings'  => self::PAGE_SETTINGS,
		'dashboard' => self::PAGE_DASHBOARD,
		'sites'     => self::PAGE_SITES,
		'users'     => self::PAGE_USERS,
		'themes'    => self::PAGE_THEMES,
		'plugins'   => self::PAGE_PLUGINS,
		'updates'   => self::PAGE_UPDATES,
	];

	/**
	 * @param string  $parent
	 * @param Options $options
	 * @param string  $title
	 * @param array   $config
	 *
	 * @return PageSimple
	 */
	protected static function simple(string $parent, Options $options, string $title, array $config = []): PageSimple
	{
		return new PageSimple($parent, $options, $title, $config);
	}

	/**
	 * @param string  $parent
	 * @param Options $options
	 * @param string  $title
	 * @param array   $config
	 *
	 * @return PageTabbed
	 */
	protected static function tabbed(string $parent, Options $options, string $title, array $config = []): PageTabbed
	{
		return new PageTabbed($parent, $options, $title, $config);
	}

	/**
	 * Dynamic Settings API page constructor.
	 *
	 * @param string $name
	 * @param array  $arguments
	 *
	 * @return Page
	 *
	 * @throws InvalidArgumentException
	 */
	public static function __callStatic(string $name, array $arguments): Page
	{
		[$type, $method, $page] = self::parseCall($name);

		if ($type === 'options') {
			$parameters = self::parseOptionsParameters($page, $arguments);
		} else {
			$parameters = self::parsePageParameters($type, $page, $arguments);
		}

		return call_user_func_array([__CLASS__, $method], $parameters);
	}

	/**
	 * Parses the static call and return an array with:
	 * - type: options, site or network
	 * - method: simple or tabbed
	 * - page: the parent page
	 *
	 * @param string $name
	 *
	 * @return array
	 */
	private static function parseCall(string $name): array
	{
		$parts = explode('_', Str::snake($name));

		if (!in_array(count($parts), [2, 3], true)) {
			throw new BadMethodCallException(sprintf('"%s" is not a valid settings call.', $name));
		}

		$type = array_shift($parts);

		if (!in_array($type, ['options', 'site', 'network'])) {
			throw new BadMethodCallException(sprintf('"%s" is not a valid settings type (options, site or network).', $type));
		}

		if (count($parts) === 1) {
			$method = 'simple';
		} else {
			$method = array_shift($parts);

			if (!in_array($method, ['simple', 'tabbed'])) {
				throw new BadMethodCallException(sprintf('"%s" is not a valid settings method (simple or tabbed).', $type));
			}

			if ($method === 'tabbed' && $type === 'options') {
				throw new BadMethodCallException('The options type cannot be tabbed.');
			}
		}

		$page = $parts[0];

		return [$type, $method, $page];
	}

	/**
	 * @param string $page
	 * @param array  $arguments
	 *
	 * @return array
	 */
	private static function parseOptionsParameters(string $page, array $arguments): array
	{
		if (!array_key_exists($page, self::$optionPages)) {
			throw new InvalidArgumentException("'$page' is not a valid options page.");
		}

		if (count($arguments) !== 1 || !($arguments[0] instanceof Options)) {
			throw new InvalidArgumentException('There must be only one argument, and must be an Options instance.');
		}

		return [
			self::PAGE_OPTIONS, // parent
			$arguments[0],      // options,
			'',                 // title
			['id' => $page],    // config
		];
	}

	/**
	 * @param string $type
	 * @param string $page
	 * @param array  $arguments
	 *
	 * @return array
	 */
	private static function parsePageParameters(string $type, string $page, array $arguments): array
	{
		$parents = self::getParents($type);

		if (!array_key_exists($page, $parents)) {
			throw new InvalidArgumentException(sprintf('"%s" is not a valid "%s" parent page.', $page, $type));
		}

		if (count($arguments) < 2) {
			throw new InvalidArgumentException('Incorrect number of arguments.');
		}

		[$options, $title] = $arguments;

		if (!($options instanceof Options)) {
			throw new InvalidArgumentException('The options argument should be an Options instance.');
		}

		$parent         = $parents[$page];
		$config         = $arguments[3] ?? [];
		$config['menu'] = ($type === 'site') ? self::MENU_SITE : self::MENU_NETWORK;

		return [
			$parent,
			$options,
			$title,
			$config,
		];
	}

	/**
	 * @param string $type
	 *
	 * @return array
	 */
	protected static function getParents(string $type): array
	{
		$parents = $type . 'Pages';

		return self::$$parents;
	}

	/**
	 * @param string $page
	 *
	 * @return string
	 */
	public static function getDefaultSection(string $page): string
	{
		if (array_key_exists($page, self::$optionPages)) {
			return reset(self::$optionPages[$page]);
		}

		return 'default';
	}

	/**
	 * @param string $page
	 * @param string $section
	 *
	 * @return bool
	 */
	public static function isDefaultSection(string $page, string $section): bool
	{
		return array_key_exists($page, self::$optionPages) && in_array($section, self::$optionPages[$page], false);
	}

}