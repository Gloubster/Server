<?php

namespace Gloubster;

class ComposerScripts
{
    public static function postPackageInstall()
    {
        $cwd = getcwd();
        chdir(__DIR__ . '/../../');
        system(__DIR__ . "/../../init.sh");
        chdir($cwd);
    }
}
