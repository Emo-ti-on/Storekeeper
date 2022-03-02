<?php

namespace Storekeeper;

class Config
{
    protected static array $configs = [];

    // Global configuration
    protected static string $configFolder;
    protected static string $separator = '.';

    // Data
    protected array $origin = [];
    protected array $current;

    // Names
    protected string $name;
    protected string $filepath;

    /**
     * @param string $pathToFolder
     * @return void
     */
    public static function setConfigFolder(string $pathToFolder)
    {
        static::$configFolder = $pathToFolder;
        if (substr($pathToFolder, -1) !== DIRECTORY_SEPARATOR) {
            static::$configFolder .= DIRECTORY_SEPARATOR;
        }
    }

    /**
     * @param string $separator
     * @return void
     */
    public static function setSeparator(string $separator)
    {
        static::$separator = $separator;
    }

    public function __construct(string $name, array $data = [])
    {
        $this->name = $name;
        $this->filepath = static::$configFolder . $name . '.php';

        $this->getConfig($data);
    }

    /**
     * Turns the parts of string separated by $separator into array
     * @param string $map
     */
    protected function parseMap(&$map, &$finalIndex)
    {
        $map = explode(static::$separator, $map);
        $finalIndex = array_pop($map);
    }

    /**
     * @param array $data
     * @return void
     */
    protected function getConfig(array $data = [])
    {
        // If current config was not created
        if (!key_exists($this->name, static::$configs)) {
            // Get data from file
            if (file_exists($this->filepath) && is_readable($this->filepath))
                static::$configs[$this->name] = include $this->filepath;
            else static::$configs[$this->name] = [];
        }

        static::$configs[$this->name] = array_merge(static::$configs[$this->name] ?? [], $data);

        $this->origin = static::$configs[$this->name];
        $this->current = &static::$configs[$this->name];
    }

    /**
     * @param string $map
     * @param $value
     * @return void
     */
    public function set(string $map, $value)
    {
        $this->parseMap($map, $finalIndex);

        $point = &$this->current;
        foreach ($map as $index) {
            if (!isset($point[$index])) $point[$index] = [];
            $point = &$point[$index];
        }

        $point[$finalIndex] = $value;
    }

    /**
     * Removes data by $map
     * @param string $map
     * @return void
     * @throws \Exception
     */
    public function unset(string $map): void
    {
        if (!$this->contains($map)) {
            throw new \Exception('value: \'' . $map . '\' does not exists');
        }

        $this->parseMap($map, $finalIndex);

        $point = &$this->current;
        foreach ($map as $index) {
            $point = &$point[$index];
        }

        unset($point[$finalIndex]);
    }

    /**
     * Get data by $map
     * @param string $map
     * @return array|mixed
     */
    public function get(string $map = '')
    {
        if ($map === '') return $this->current;
        if (!$this->contains($map)) {
            throw new \Exception('value: \'' . $map . '\' Does not exists');
        }

        $this->parseMap($map, $finalIndex);

        $point = &$this->current;
        foreach ($map as $index) {
            $point = &$point[$index];
        }

        return $point[$finalIndex];
    }

    /**
     * Delete config file
     * @return void
     */
    public function delete()
    {
        unlink($this->filepath);
    }

    /**
     * Write into config file
     * @return void
     */
    public function save()
    {
        file_put_contents($this->filepath, (new Writer())->write($this->current));
    }

    /**
     * Remove all data from config
     * @return void
     */
    public function truncate()
    {
        $this->current = [];
    }

    /**
     * Returns config to it original state
     * @return void
     */
    public function reset()
    {
        $this->current = $this->origin;
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->current);
    }

    /**
     * @return bool
     */
    public function isNotExists(): bool
    {
        return !file_exists($this->filepath);
    }

    /**
     * Checks if config contains current index
     * @param string $map
     * @return bool
     */
    public function contains(string $map): bool
    {
        if (strlen($map) < 1) {
            throw new \Error('$map must contains at least 1 character');
        }

        $this->parseMap($map, $finalIndex);

        $point = &$this->current;
        foreach ($map as $index) {
            if (!isset($point[$index])) return false;
            $point = &$point[$index];
        }

        return isset($point[$finalIndex]);
    }
}
