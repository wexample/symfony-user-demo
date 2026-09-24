<?php

namespace Wexample\SymfonyUserDemo\Traits;

use Wexample\SymfonyHelpers\Traits\BundleClassTrait;
use Wexample\SymfonyUserDemo\WexampleSymfonyUserDemoBundle;

trait SymfonyUserDemoBundleClassTrait
{
    use BundleClassTrait;

    public static function getBundleClassName(): string
    {
        return WexampleSymfonyUserDemoBundle::class;
    }
}
