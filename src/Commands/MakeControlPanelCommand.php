<?php

namespace Koverae\KoveraeBuilder\Commands;

use Illuminate\Support\Str;

class MakeControlPanelCommand extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'koverae:make-panel {component}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new control panel component for Koverae UI Builder.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $component = Str::studly($this->argument('component'));
        $path = $this->getPath($component);

        if ($this->files->exists($path)) {
            $this->error("Component [{$component}] already exists!");
            return 0;
        }

        $this->makeDirectory($path);
        $this->files->put($path, $this->getStubContent($component));

        // Display the class, view, and tag
        $this->displayComponentInfo($component);

        return 0;
    }

    /**
     * Get the destination path where the component should be created.
     *
     * @param string $component
     * @return string
     */
    protected function getPath(string $component): string
    {
        $componentPath = str_replace('/', DIRECTORY_SEPARATOR, $component);
        return app_path("Livewire/Navbar/ControlPanel/{$componentPath}.php");
    }

    /**
     * Get the stub content for the new component class.
     *
     * @param string $component
     * @return string
     */
    protected function getStubContent(string $component): string
    {
        $namespace = $this->getNamespace($component);
        $class = Str::afterLast($component, '/');
        
        return str_replace(
            ['{{namespace}}', '{{class}}'],
            [$namespace, $class],
            $this->files->get($this->getStubPath())
        );
    }

    protected function getNamespace(string $component): string
    {
        $componentParts = explode('/', $component);
        array_pop($componentParts); // Remove the class name part
        $namespace = implode('\\', $componentParts);
    
        return "App\\Livewire\\Navbar\\ControlPanel" . ($namespace ? "\\" . $namespace : "");
    }

    /**
     * Get the path to the stub file.
     *
     * @return string
     */
    protected function getStubPath(): string
    {
        return __DIR__ . '/stubs/panel/control-panel.stub';
    }

    /**
     * Display the class, view, and tag.
     *
     * @param string $component
     */
    protected function displayComponentInfo(string $component)
    {
        // Class path formatted to match nested directories
        $classPath = "App/Livewire/Navbar/ControlPanel/" . str_replace('/', '/', $component);

        $slug = preg_replace('/\.-/', '.',  Str::kebab(str_replace('/', '.', $component)));
        // Tag format for Livewire component
        $tag = "<livewire:navbar.control-panel.{$slug} />";

        // Display the results in the console
        $this->line("<options=bold,reverse;fg=green> COMPONENT CREATED </> 🤙🏿 \n");
        $this->line("<options=bold;fg=green>CLASS:</> {$classPath}");
        $this->line("<options=bold;fg=green>TAG:</> {$tag}");
    }

}