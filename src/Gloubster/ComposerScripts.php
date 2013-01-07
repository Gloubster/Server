<?php

namespace Gloubster;

class ComposerScripts
{
    public static function postInstallCmd()
    {
        $cwd = getcwd();
        chdir(__DIR__ . '/../../');
        system(sprintf('/usr/bin/env sh %s', realpath(__DIR__ . "/../../init.sh")));
        chdir($cwd);
    }
}
