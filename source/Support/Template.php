<?php

namespace ic\Framework\Support;

use ic\Framework\Debug\Debug;
use ic\Framework\Hook\HookDecorator;

/**
 * Class Template
 *
 * @package ic\Framework\Support
 */
class Template
{

    use HookDecorator;

    protected $template;

    /**
     * @param string $type      The name of the file template.
     * @param string $path      The absolute path to the default file template.
     * @param array  $arguments An array of variables to be used in the template.
     * @param string $directory The directory in the theme.
     *
     * @return string
     */
    public static function render($type, $path, array $arguments = [], $directory = 'templates')
    {
        $template = new static();

        if ($template->locate($type, $path, $directory)) {
            return $template->load($arguments);
        }

        return '';
    }

    /**
     * Require the template file.
     *
     * @param array $arguments
     *
     * @return string
     */
    public function load(array $arguments = [])
    {
        if (!empty($arguments)) {
            extract($arguments, EXTR_SKIP);
        }

        ob_start();

        include $this->template;

        return ob_get_clean();
    }

    /**
     * Locate a file template and return the path.
     *
     * @param string $type
     * @param string $path
     * @param string $directory
     *
     * @throws \RuntimeException
     *
     * @return string
     */
    protected function locate($type, $path, $directory)
    {
        $type      = preg_replace('|[^a-z0-9-]+|', '', $type);
        $templates = $this->templates($type, $path, $directory);

        foreach ($templates as $template) {
            if (file_exists($template)) {
                $this->template = $template;

                return true;
            }
        }

        Debug::error(sprintf('Cannot locate the template type "%s"', $type));

        return false;
    }

    /**
     * Build an array with all possible templates.
     *
     * @param string $type
     * @param string $base
     * @param string $directory
     *
     * @return array
     */
    protected function templates($type, $base, $directory)
    {
        $file   = $type . '.php';
        $parent = get_template_directory();
        $child  = get_stylesheet_directory();

        if (!empty($directory)) {
            $directory = trailingslashit($directory);
        }

        $templates = array_unique([
            $child . '/' . $directory . $file,
            $child . '/' . $file,
            $parent . '/' . $directory . $file,
            $parent . '/' . $file,
            $base . '/' . $directory . $file,
        ]);

        $templates = $this->setHook()->apply('ic_framework_templates_' . $type, $templates);

        return $templates;
    }

}