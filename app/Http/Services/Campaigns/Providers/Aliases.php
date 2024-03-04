<?php

namespace App\Http\Services\Campaigns\Providers;

use Illuminate\Foundation\AliasLoader;

class Aliases extends AliasLoader
{
    /**
     * alias list container where facade is required.
     */
    protected $aliases = [];

    /**
     * Alias loader instance.
     */
    protected $loader;

    /**
     * Instantiate.
     */
    public function __construct()
    {
        if (class_exists('Illuminate\Foundation\AliasLoader')) {
            $this->loader = AliasLoader::getInstance();
        }
    }

    /**
     * Set aliases.
     */
    public function set(array $aliases)
    {
        if (count($this->aliases) == 0) {
            $this->aliases = array_merge($this->aliases, $aliases);
        }
    }

    /**
     * Create aliases for the dependency.
     *
     * @param  Illuminate\Foundation\Application  $app
     */
    public function registers(
        // \Illuminate\Foundation\Application $app
    ) {
        if (count($this->aliases) == 0) {
            return;
        }

        collect($this->aliases)->each(function ($facade, $alias) {
            $this->loader->alias($alias, $facade);
        });
    }

    /**
     * Create aliases for the dependency.
     */
    public function registerAlias(array $alias, array $facade)
    {
        $this->loader->alias($alias, $facade);
    }
}
