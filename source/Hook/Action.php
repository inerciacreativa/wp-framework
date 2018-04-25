<?php

namespace ic\Framework\Hook;

use ic\Framework\Support\Arr;

/**
 * Class Action
 *
 * @package ic\Framework\Hook
 */
abstract class Action implements ActionInterface
{

    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $hook;

    /**
     * @var callable
     */
    protected $callback;

    /**
     * @var int
     */
    protected $arguments;

    /**
     * @var int
     */
    protected $priority;

    /**
     * @var bool
     */
    protected $enabled = false;

    /**
     * Hook constructor.
     *
     * @param string   $hook
     * @param callable $callback
     * @param array  $parameters {
     *
     * @type int     $priority
     * @type int     $arguments
     * @type bool    $enabled
     *                             }
     */
    public function __construct($hook, callable $callback, array $parameters = [])
    {
        $this->hook      = $hook;
        $this->callback  = $callback;
        $this->priority  = (int)Arr::value($parameters, 'priority', 10);
        $this->arguments = (int)Arr::value($parameters, 'arguments', 1);

        if (Arr::value($parameters, 'enabled', true)) {
            $this->enable();
        } else {
            $this->disable();
        }
    }

    /**
     * Calls the real method, closure or function.
     *
     * @return mixed
     */
    public function __invoke()
    {
        return call_user_func_array($this->callback, array_slice(func_get_args(), 0, $this->arguments));
    }

    /**
     * @param string $namespace
     * @param string $id
     */
    protected function setId($namespace, $id)
    {
        $this->id = sprintf('%s.%s.%s', $namespace, $this->hook, $id);
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getHook()
    {
        return $this->hook;
    }

    /**
     * @inheritdoc
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @inheritdoc
     */
    public function enable()
    {
        if (!$this->enabled) {
            add_filter($this->hook, $this, $this->priority, $this->arguments);
            $this->enabled = true;
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function disable()
    {
        if ($this->enabled) {
            remove_filter($this->hook, $this, $this->priority);
            $this->enabled = false;
        }

        return $this;
    }

}