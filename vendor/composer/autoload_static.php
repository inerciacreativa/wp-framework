<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit639d59e8f1cc96528f72dc4c5d20eb2f
{
    public static $prefixLengthsPsr4 = array (
        'i' => 
        array (
            'ic\\Framework\\' => 13,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'ic\\Framework\\' => 
        array (
            0 => __DIR__ . '/../..' . '/source',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit639d59e8f1cc96528f72dc4c5d20eb2f::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit639d59e8f1cc96528f72dc4c5d20eb2f::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}