<?php

namespace Koverae\KoveraeBuilder\Commands;

use Illuminate\Console\Command;
use Koverae\KoveraeBuilder\Traits\ComponentParser;
use Illuminate\Support\Str;
use Illuminate\Filesystem\Filesystem;

abstract class BaseCommand extends Command
{
    use ComponentParser;

    protected $signature = '';
    protected $description = '';
    protected $files;

    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }

    abstract protected function handle() : int;

    abstract protected function getPath(string $component) : string;

    protected function getViewPath(string $component) : string{
        return '';
    }
    
    protected function makeDirectory(string $path): void
    {
        if (!$this->files->isDirectory(dirname($path))) {
            $this->files->makeDirectory(dirname($path), 0755, true);
        }
    }

    abstract protected function getStubContent(string $component) : string;

    protected function getViewContent(string $component) : string{
        return '';
    }

    abstract protected function getNamespace(string $component) : string;

    abstract protected function getStubPath() : string;

    protected function getViewStubPath() : string{
        return '';
    }

    abstract protected function displayComponentInfo(string $component);

    
}
