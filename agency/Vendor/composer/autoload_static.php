<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit16154c197c51814563cbbaafc2504957
{
    public static $prefixLengthsPsr4 = array (
        'B' => 
        array (
            'Braintree\\' => 10,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Braintree\\' => 
        array (
            0 => __DIR__ . '/..' . '/braintree/braintree_php/lib/Braintree',
        ),
    );

    public static $prefixesPsr0 = array (
        'B' => 
        array (
            'Braintree' => 
            array (
                0 => __DIR__ . '/..' . '/braintree/braintree_php/lib',
            ),
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit16154c197c51814563cbbaafc2504957::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit16154c197c51814563cbbaafc2504957::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInit16154c197c51814563cbbaafc2504957::$prefixesPsr0;

        }, null, ClassLoader::class);
    }
}