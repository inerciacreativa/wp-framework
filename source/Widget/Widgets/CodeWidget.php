<?php

namespace ic\Framework\Widget\Widgets;

use ic\Framework\Widget\Widget;
use ic\Framework\Widget\WidgetForm;
use ic\Framework\Support\Arr;
use ic\Framework\Html\Tag;

/**
 * Class CodeWidget
 *
 * @package ic\Framework\Widget\Widgets
 */
class CodeWidget extends Widget
{

    /**
     * @inheritdoc
     */
    public function id()
    {
        return 'code2';
    }

    /**
     * @inheritdoc
     */
    public function name()
    {
        return __('ic Framework / Code', 'ic-framework');
    }

    /**
     * @inheritdoc
     */
    public function description()
    {
        return __('Arbitrary text, HTML, or PHP Code', 'ic-framework');
    }

    /**
     * @inheritdoc
     */
    /*
    protected function frontend(array $instance, array $arguments)
    {
        ob_start();
        eval('?>' . Arr::get($instance, 'code'));
        $code = ob_get_clean();

        if (Arr::get($instance, 'autop')) {
            $code = wpautop($code);
        }

        return $code;
    }
    */

    /**
     * @inheritdoc
     */
    protected function backend(array $instance, WidgetForm $form)
    {
        return [
            Tag::p($form->textarea('code', '', ['label' => __('Code:', 'ic-framework')])),
            Tag::p($form->checkbox('autop', 1, false, ['label' => __('Automatically add paragraphs', 'ic-framework')])),
        ];
    }

}