<?php

namespace Phplc\Core;

class Config
{
    /** 
     * @var string 
     */
    public const ROOT_PATH = '%root%';
    
    /** 
     * @var string[] 
     */
    public array $rootDirСontainsFiles = ['composer.json', '.git'];

    public string $commandSeverAddr = '127.0.0.1:9191';

    /** 
     * @var array<string, string> 
     */
    public array $retain = [
        'dbPath' => '%root%/default.db',
        'table' => 'retain_property',
    ];

    /** 
     * @var array<string, string> 
     */
    public array $logging = [
        'dbPath' => '%root%/default.db',
        'table' => 'logging_property',
        'period_s' => "60",
    ];

    /** 
     * @var array<string, string> 
     */
    public array $errorLog = [
        'dbPath' => '%root%/default.db',
        'table' => 'error_log',
    ];

    public function build(): void
    {
        $this->buildRootPath();
    }

    protected function buildRootPath(): void
    {
        $rootDir = $this->searchRootDir();

        foreach ((array)$this as $key => $value) {
            if (is_array($value)) {
                /** @var mixed $vValue */
                foreach ($value as $vKey => $vValue) {
                    if (!is_string($vValue)) {
                        continue;
                    }
                    if (is_int(strpos($vValue, static::ROOT_PATH))) {
                        $value[$vKey] = str_replace(static::ROOT_PATH, $rootDir, $vValue);
                        $this->$key = $value;
                    }
                }
            }

            if (!is_string($value)) {
                continue;
            }
            if (is_int(strpos($value, static::ROOT_PATH))) {
                $this->$key = str_replace(static::ROOT_PATH, $rootDir, $value);
            }
        }
    }

    protected function searchRootDir(): string
    {
        $dir = __DIR__;
        while ($dir !== '/') {
            $scan = scandir($dir);
            foreach ($scan as $fileName) {
                if (in_array($fileName, $this->rootDirСontainsFiles)) {
                    return $dir;
                }
            }
            $dir = dirname($dir);
        }
        return '/';
    }
}

