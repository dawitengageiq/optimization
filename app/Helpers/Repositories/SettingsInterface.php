<?php

namespace App\Helpers\Repositories;

interface SettingsInterface
{
    public function getValue($code);

    public function getkeyByCode();
}
