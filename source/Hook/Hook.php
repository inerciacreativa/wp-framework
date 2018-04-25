<?php

namespace ic\Framework\Hook;

use ic\Framework\Support\Singleton;
use ic\Framework\Support\Data;
use ic\Framework\Support\Arr;

/**
 * Class Hooks
 *
 * @package ic\Framework\Hook
 * @method static Hook getInstance()
 */
class Hook extends Singleton
{

    /**
     * @var object
     */
    private $object;

    /**
     * @var string
     */
    private $class;

    /**
     * @var ActionInterface[]
     */
    private $actions = [];

    /**
     * @param object $object
     *
     * @return static
     */
    public static function bind($object)
    {
        $hooks         = static::getInstance();
        $hooks->object = $object;
        $hooks->class  = get_class($object);

        return $hooks;
    }

    /**
     * @return static
     */
    public static function unbind()
    {
        $hooks         = static::getInstance();
        $hooks->object = null;
        $hooks->class  = '';

        return $hooks;
    }

    /**
     * The format of the IDs is '$namespace.$hook.$callback'.
     *
     * If only one segment is passed it's assumed to be '$callback'.
     * If two segments are passed it's assumed to be '$hook.$callback'-
     *
     * @param string $target
     *
     * @return ActionInterface[]
     */
    public function get($target)
    {
        $segments = explode('.', $target);

        if (count($segments) === 1) {
            array_unshift($segments, '*');
        }

        if (count($segments) === 2) {
            if ($this->isBounded()) {
                array_unshift($segments, $this->class);
            } else {
                array_unshift($segments, 'global');
            }
        }

        $actions = Data::get($this->actions, $segments, []);

        if (!is_array($actions)) {
            $actions = [$actions];
        } elseif (!empty($actions)) {
            $actions = array_values(array_filter($actions));
        }

        return $actions;
    }

    /**
     * @param ActionInterface $action
     * @param bool            $overwrite
     *
     * @return $this
     */
    public function set(ActionInterface $action, $overwrite = true)
    {
        Data::set($this->actions, $action->getId(), $action, $overwrite);

        return $this;
    }

    /**
     * @param string          $hook
     * @param string|callable $callback
     * @param array           $parameters
     *
     * @return ActionInterface|null
     */
    public function getAction($hook, $callback, array $parameters = [])
    {
        if ($callback instanceof \Closure) {
            return new ClosureAction($hook, $callback, $parameters);
        } elseif ($this->isBounded($callback)) {
            return new BoundedAction($hook, $this->object, $callback, $parameters);
        } elseif (is_callable($callback)) {
            return new UnboundedAction($hook, $callback, $parameters);
        }

        return null;
    }

    /**
     * @param string $callback
     *
     * @return bool
     */
    protected function isBounded($callback = null)
    {
        return $this->object !== null && ($callback === null || (is_string($callback) && method_exists($this->object, $callback)));
    }

    /**
     * @param string          $hook       The name of the event
     * @param string|callable $callback   The method to be run
     * @param array           $parameters {
     *
     * @type string           $target     The name of the filter or action to hook
     * @type int              $priority   Used to specify the order of execution
     * @type int              $arguments  The number of arguments the method accepts
     * @type callable|bool    $status     Whether the event should be executed or not
     * }
     *
     * @return $this
     */
    public function on($hook, $callback, array $parameters = [])
    {
        $parameters = array_merge(['enabled' => true], $parameters);

        if ($action = $this->getAction($hook, $callback, $parameters)) {
            $this->set($action);
        }

        return $this;
    }

    /**
     * @param string          $hook
     * @param string|callable $callback
     * @param array|int       $parameters
     *
     * @return $this
     */
    public function off($hook, $callback, $parameters = 10)
    {
        if (is_int($parameters)) {
            $parameters = ['priority' => $parameters, 'enabled' => false];
        } else {
            $parameters = array_merge($parameters, ['enabled' => false]);
        }

        if ($action = $this->getAction($hook, $callback, $parameters)) {
            $this->set($action);
        }

        return $this;
    }

    /**
     * @param string          $hook
     * @param string|callable $callback
     * @param array           $parameters
     *
     * @return $this
     */
    public function before($hook, $callback, array $parameters = [])
    {
        return $this->on($hook, $callback, array_merge($parameters, ['priority' => -1]));
    }

    /**
     * @param string          $hook
     * @param string|callable $callback
     * @param array           $parameters
     *
     * @return $this
     */
    public function after($hook, $callback, array $parameters = [])
    {
        return $this->on($hook, $callback, array_merge($parameters, ['priority' => 999999]));
    }

    /**
     * @param string|array    $hook
     * @param string|callable $callback
     *
     * @return $this
     */
    public function capture($hook, $callback)
    {
        if ($this->isBounded($callback) && ($action = new CaptureAction($hook, $this->object, $callback))) {
            $this->set($action);
        }

        return $this;
    }

    /**
     * @param string          $file
     * @param string|callable $callback
     * @param array           $parameters
     *
     * @return $this
     */
    public function activation($file, $callback, array $parameters = [])
    {
        $hook = 'activate_' . plugin_basename($file);

        return $this->on($hook, $callback, $parameters);
    }

    /**
     * @param string          $file
     * @param string|callable $callback
     * @param array           $parameters
     *
     * @return $this
     */
    public function deactivation($file, $callback, array $parameters = [])
    {
        $hook = 'deactivate_' . plugin_basename($file);

        return $this->on($hook, $callback, $parameters);
    }

    /**
     * @param string $hook
     * @param mixed  $parameters
     */
    public function fire($hook, $parameters)
    {
        call_user_func_array('do_action', func_get_args());
    }

    /**
     * @param string $hook
     * @param mixed  $parameters
     *
     * @return mixed
     */
    public function apply($hook, $parameters)
    {
        return call_user_func_array('apply_filters', func_get_args());
    }

    /**
     * @param string|array $target
     *
     * @return $this
     */
    public function enable($target)
    {
        $actions = $this->get($target);

        foreach ($actions as $action) {
            $action->enable();
        }

        return $this;
    }

    /**
     * @param string|array $target
     *
     * @return $this
     */
    public function disable($target)
    {
        $actions = $this->get($target);

        foreach ($actions as $action) {
            $action->disable();
        }

        return $this;
    }

    /**
     * @return ActionInterface[]
     */
    public function all()
    {
        return Arr::flatten($this->actions);
    }

    /**
     * @param $target
     *
     * @return array
     */
    public function inspect($target)
    {
        $report  = [];
        $actions = $this->get($target);

        foreach ($actions as $action) {
            $report[$action->getId()] = $action->isEnabled();
        }

        return $report;
    }

}