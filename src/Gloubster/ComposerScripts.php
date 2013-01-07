<?php

namespace Gloubster;

class ComposerScripts
{
    public static function postInstallCmd()
    {
        echo "Executing post install command";
        $cwd = getcwd();
        chdir(__DIR__ . '/../../');
        system(__DIR__ . "/../../init.sh");
        chdir($cwd);
    }
    
    public static function postPackageInstall()
    {
        echo "Executing post package install";
        $cwd = getcwd();
        chdir(__DIR__ . '/../../');
        system(__DIR__ . "/../../init.sh");
        chdir($cwd);
    }
}
