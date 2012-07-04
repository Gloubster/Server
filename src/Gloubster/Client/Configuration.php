<?php

namespace Gloubster\Client;

use Gloubster\Configuration as MainConfiguration;

class Configuration extends MainConfiguration
{
    public function __construct($json)
    {
        parent::__construct($json, array(file_get_contents(__DIR__. '/../../../ressources/configuration.schema.json')));
    }
}
