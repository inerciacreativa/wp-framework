<?php

namespace ic\Framework\Settings;

use ic\Framework\Debug\Debug;
use ic\Framework\Support\Options;
use ic\Framework\Settings\Page\OptionsPage;
use ic\Framework\Settings\Page\NetworkPage;
use ic\Framework\Settings\Page\SitePage;
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

    const MANAGEMENT = 'tools.php';
    const OPTIONS    = 'options-general.php';
    const THEMES     = 'themes.php';
    const PLUGINS    = 'plugins.php';
    const USERS      = 'users.php';
    const DASHBOARD  = 'index.php';
    const POSTS      = 'edit.php';
    const PAGES      = 'edit.php?post_type=page';
    const MEDIA      = 'upload.php';
    const COMMENTS   = 'edit-comments.php';
    const SETTINGS   = 'settings.php';
    const SITES      = 'sites.php';
    const UPDATES    = 'update-core.php';

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
    protected static function options($id, Options $options)
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
    protected static function site($parent, $id, Options $options, $pageTitle, $menuTitle = '')
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
    protected static function network($parent, $id, Options $options, $pageTitle, $menuTitle = '')
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
     */
    public static function __callStatic($name, $arguments)
    {
        list($type, $page) = explode('_', Str::snake($name));

        if ($type === 'options') {
            if (!array_key_exists($page, self::$optionPages)) {
                Debug::error(sprintf('"%s" is not a valid "options" page.', $page), static::class);

                $page = reset(array_keys(self::$optionPages));
            }

        } elseif (in_array($type, ['site', 'network'], false)) {
            $variable = $type . 'Parents';
            $parents  = self::$$variable;

            if (!array_key_exists($page, $parents)) {
                Debug::error(sprintf('"%s" is not a valid "%s" parent page.', $page, $type), static::class);

                $page = reset($parents);
            } else {
                $page = $parents[$page];
            }
        } else {
            Debug::error(sprintf('"%s" is not a valid settings type.', $type), 'Settings');

            return null;
        }

        array_unshift($arguments, $page);

        return call_user_func_array([__CLASS__, $type], $arguments);
    }

    /**
     * @param string $page
     *
     * @return string
     */
    public static function getDefaultSection($page)
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
    public static function isDefaultSection($page, $section)
    {
        return array_key_exists($page, self::$optionPages) && in_array($section, self::$optionPages[$page], false);
    }

}