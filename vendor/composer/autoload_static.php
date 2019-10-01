<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit8c0efe031536de027b20a32f10a7a684
{
    public static $prefixLengthsPsr4 = array (
        'D' => 
        array (
            'DragonBe\\Vies\\' => 14,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'DragonBe\\Vies\\' => 
        array (
            0 => __DIR__ . '/..' . '/dragonbe/vies/src/Vies',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit8c0efe031536de027b20a32f10a7a684::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit8c0efe031536de027b20a32f10a7a684::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}