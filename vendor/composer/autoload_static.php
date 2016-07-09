<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit1f62c6bc2e0f724407af9772d2c64312
{
    public static $prefixLengthsPsr4 = array (
        'A' => 
        array (
            'Abraham\\TwitterOAuth\\' => 21,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Abraham\\TwitterOAuth\\' => 
        array (
            0 => __DIR__ . '/..' . '/abraham/twitteroauth/src',
        ),
    );

    public static $prefixesPsr0 = array (
        'M' => 
        array (
            'Madcoda' => 
            array (
                0 => __DIR__ . '/..' . '/madcoda/php-youtube-api/src',
            ),
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit1f62c6bc2e0f724407af9772d2c64312::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit1f62c6bc2e0f724407af9772d2c64312::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInit1f62c6bc2e0f724407af9772d2c64312::$prefixesPsr0;

        }, null, ClassLoader::class);
    }
}