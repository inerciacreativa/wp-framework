<?php

namespace ic\Framework\Settings;

use ic\Framework\Settings\Page\NetworkPage;
use ic\Framework\Settings\Page\OptionsPage;
use ic\Framework\Settings\Page\SitePage;
use ic\Framework\Support\Options;
use ic\Framework\Support\Str;

/**
 * Class Settings
 *
 * @package ic\Framework\Settings
 *
 * @method static OptionsPage optionsGeneral(Options $options)
 * @method static OptionsPage optionsWriting(Options $options)
 * @method static OptionsPage optionsReading(Options $options)
 * @method static OptionsPage optionsDiscussion(Options $options)
 * @method static OptionsPage optionsPermalink(Options $options)
 * @method static OptionsPage optionsMedia(Options $options)
 * @method static SitePage siteOptions($id, Options $options, $pageTitle, $menuTitle = '')
 * @method static SitePage siteDashboard($id, Options $options, $pageTitle, $menuTitle = '')
 * @method static SitePage sitePosts($id, Options $options, $pageTitle, $menuTitle = '')
 * @method static SitePage sitePages($id, Options $options, $pageTitle, $menuTitle = '')
 * @method static SitePage siteMedia($id, Options $options, $pageTitle, $menuTitle = '')
 * @method static SitePage siteComments($id, Options $options, $pageTitle, $menuTitle = '')
 * @method static SitePage siteThemes($id, Options $options, $pageTitle, $menuTitle = '')
 * @method static SitePage sitePlugins($id, Options $options, $pageTitle, $menuTitle = '')
 * @method static SitePage siteUsers($id, Options $options, $pageTitle, $menuTitle = '')
 * @method static SitePage siteManagement($id, Options $options, $pageTitle, $menuTitle = '')
 * @method static NetworkPage networkSettings($id, Options $options, $pageTitle, $menuTitle = '')
 * @method static NetworkPage networkDashboard($id, Options $options, $pageTitle, $menuTitle = '')
 * @method static NetworkPage networkSites($id, Options $options, $pageTitle, $menuTitle = '')
 * @method static NetworkPage networkUsers($id, Options $options, $pageTitle, $menuTitle = '')
 * @method static NetworkPage networkThemes($id, Options $options, $pageTitle, $menuTitle = '')
 * @method static NetworkPage networkPlugins($id, Options $options, $pageTitle, $menuTitle = '')
 * @method static NetworkPage networkUpdates($id, Options $options, $pageTitle, $menuTitle = '')
 */
class Settings
{

	public const MANAGEMENT = 'tools.php';

	public const OPTIONS = 'options-general.php';

	public const THEMES = 'themes.php';

	public const PLUGINS = 'plugins.php';

	public const USERS = 'users.php';

	public const DASHBOARD = 'index.php';

	public const POSTS = 'edit.php';

	public const PAGES = 'edit.php?post_type=page';

	public const MEDIA = 'upload.php';

	public const COMMENTS = 'edit-comments.php';

	public const SETTINGS = 'settings.php';

	public const SITES = 'sites.php';

	public const UPDATES = 'update-core.php';

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
	protected static $siteParents = [
		'options'    => self::OPTIONS,
		'dashboard'  => self::DASHBOARD,
		'posts'      => self::POSTS,
		'pages'      => self::PAGES,
		'media'      => self::MEDIA,
		'comments'   => self::COMMENTS,
		'themes'     => self::THEMES,
		'plugins'    => self::PLUGINS,
		'users'      => self::USERS,
		'management' => self::MANAGEMENT,
	];

	/**
	 * @var array The possible page parents for a network page.
	 */
	protected static $networkParents = [
		'settings'  => self::SETTINGS,
		'dashboard' => self::DASHBOARD,
		'sites'     => self::SITES,
		'users'     => self::USERS,
		'themes'    => self::THEMES,
		'plugins'   => self::PLUGINS,
		'updates'   => self::UPDATES,
	];

	/**
	 * NativePage constructor.
	 *
	 * @param string  $id
	 * @param Options $options
	 *
	 * @return OptionsPage
	 */
	protected static function options(string $id, Options $options): OptionsPage
	{
		return new OptionsPage($id, $options);
	}

	/**
	 * SitePage constructor.
	 *
	 * @param string  $parent
	 * @param string  $id
	 * @param Options $options
	 * @param string  $pageTitle
	 * @param string  $menuTitle
	 *
	 * @return SitePage
	 */
	protected static function site(string $parent, string $id, Options $options, string $pageTitle, string $menuTitle = ''): SitePage
	{
		return new SitePage($parent, $id, $options, $pageTitle, $menuTitle);
	}

	/**
	 * NetworkPage constructor.
	 *
	 * @param string  $parent
	 * @param string  $id
	 * @param Options $options
	 * @param string  $pageTitle
	 * @param string  $menuTitle
	 *
	 * @return NetworkPage
	 */
	protected static function network(string $parent, string $id, Options $options, string $pageTitle, string $menuTitle = ''): NetworkPage
	{
		return new NetworkPage($parent, $id, $options, $pageTitle, $menuTitle);
	}

	/**
	 * Dynamic Settings API page constructor.
	 *
	 * @param string $name
	 * @param array  $arguments
	 *
	 * @return SettingsPage
	 *
	 * @throws \InvalidArgumentException
	 */
	public static function __callStatic(string $name, array $arguments): SettingsPage
	{
		[$type, $page] = explode('_', Str::snake($name));

		if ($type === 'options') {
			if (!array_key_exists($page, self::$optionPages)) {
				throw new \InvalidArgumentException("'$page' is not a valid settings page.");
			}

		} else if (\in_array($type, ['site', 'network'], false)) {
			$variable = $type . 'Parents';
			$parents  = self::$$variable;

			if (!array_key_exists($page, $parents)) {
				throw new \InvalidArgumentException(sprintf('"%s" is not a valid "%s" parent page.', $page, $type));
			}

			$page = $parents[$page];
		} else {
			throw new \InvalidArgumentException(sprintf('"%s" is not a valid settings type.', $type));
		}

		array_unshift($arguments, $page);

		return \call_user_func_array([__CLASS__, $type], $arguments);
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
		return array_key_exists($page, self::$optionPages) && \in_array($section, self::$optionPages[$page], false);
	}

}