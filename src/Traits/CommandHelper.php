<?php

namespace Koverae\KoveraeBuilder\Traits;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

trait CommandHelper
{
    protected function isModule()
    {
        return !empty($this->option('module'));
    }

    protected function isForce()
    {
        return $this->option('force') === true;
    }

    protected function isInline()
    {
        return $this->option('inline') === true;
    }

    protected function isType()
    {
        // Get the type of component
        $type = $this->option('type');
        
        // Ensure a valid component type is passed (optional validation)
        $validTypes = ['table', 'form', 'navbar', 'cart', 'modal', 'map'];
        if ($type && !in_array($type, $validTypes)) {
            $this->error("Invalid component type '{$type}'. Valid types are: " . implode(', ', $validTypes));
            return false;
        }
    }


    protected function getTypeInfo()
    {
        $type = null;
        // Get the type of component
        if($this->option('type')){
            $type = $this->option('type');
        }

        return $type;
    }

    protected function ensureDirectoryExists($path)
    {
        if (! File::isDirectory(dirname($path))) {
            File::makeDirectory(dirname($path), 0777, $recursive = true, $force = true);
        }
    }

    protected function getModule()
    {
        $moduleName = $this->argument('module');

        if (! $module = $this->laravel['modules']->find($moduleName)) {
            $this->line("<options=bold,reverse;fg=red> WHOOPS! </> 😳 \n");
            $this->line("<fg=red;options=bold>The {$moduleName} module not found.</>");

            return null;
        }

        return $module;
    }

    protected function getModuleName()
    {
        return $this->module->getName();
    }

    protected function getModuleLowerName()
    {
        return $this->module->getLowerName();
    }

    protected function getModulePath()
    {
        $path = $this->module->getPath();

        return strtr($path, ['\\' => '/']);
    }

    protected function getModuleNamespace()
    {
        return config('modules.namespace', 'Modules');
    }

    protected function getModuleLivewireNamespace()
    {
        $moduleLivewireNamespace = config('modules-livewire.namespace', 'Http\\Livewire');

        return $moduleLivewireNamespace;
    }

    protected function getNamespace($classPath)
    {
        $classPath = Str::contains($classPath, '/') ? '/'.$classPath : '';
        
        // Determine the type of component: form, table, map, ...
        $type = ucfirst($this->getTypeInfo());

        $prefix = $this->getModuleNamespace().'\\'.$this->module->getName().'\\'.$this->getModuleLivewireNamespace().'\\'.$type;

        return (string) Str::of($classPath)
            ->beforeLast('/')
            ->prepend($prefix)
            ->replace(['/'], ['\\']);
    }

    protected function getModuleLivewireViewDir()
    {
        $moduleLivewireViewDir = config('modules-livewire.view', 'Resources/views/livewire');

        return $this->getModulePath().'/'.$moduleLivewireViewDir;
    }

    protected function checkClassNameValid()
    {
        if (! $this->isClassNameValid($name = $this->component->class->name)) {
            $this->line("<options=bold,reverse;fg=red> WHOOPS! </> 😳 \n");
            $this->line("<fg=red;options=bold>Class is invalid:</> {$name}");

            return false;
        }

        return true;
    }

    protected function checkReservedClassName()
    {
        if ($this->isReservedClassName($name = $this->component->class->name)) {
            $this->line("<options=bold,reverse;fg=red> WHOOPS! </> 😳 \n");
            $this->line("<fg=red;options=bold>Class is reserved:</> {$name}");

            return false;
        }

        return true;
    }

    protected function isClassNameValid($name)
    {
        return (new \Livewire\Features\SupportConsoleCommands\Commands\MakeCommand())->isClassNameValid($name);
    }

    protected function isReservedClassName($name)
    {
        return (new \Livewire\Features\SupportConsoleCommands\Commands\MakeCommand())->isReservedClassName($name);
    }

}
 