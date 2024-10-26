<?php

namespace Koverae\KoveraeBuilder\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\File;
use Koverae\KoveraeBuilder\Traits\ComponentParser;

class MakePageCommand extends Command
{
    use ComponentParser;
    protected $signature = 'koverae:make-page {component} {--inline}';
    protected $description = 'Create a new page for Koverae Builder.';
    protected $files;

    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }

    public function handle(): int
    {
        // Extract component path and class
        $component = Str::studly($this->argument('component'));
        $path = $this->getPath($component);
        $viewPath = $this->getViewPath($component);

        // Generate directory and class if they do not exist
        if ($this->files->exists($path)) {
            $this->error("Component [{$component}] already exists!");
            return 0;
        }

        $this->makeDirectory($path);
        $this->makeDirectory($viewPath);

        // Create the class file and view file
        $this->files->put($path, $this->getStubContent($component));
        $this->files->put($viewPath, $this->getViewContent());

        // Display the class, view, and tag
        $this->displayComponentInfo($component);

        return 0;
    }

    protected function getPath(string $component): string
    {
        $componentPath = str_replace('/', DIRECTORY_SEPARATOR, $component);
        return app_path("Livewire/Page/{$componentPath}.php");
    }

    protected function getViewPath(string $component): string
    {
        // Replace slashes with DIRECTORY_SEPARATOR for the correct nested structure
        $componentPath = str_replace('/', DIRECTORY_SEPARATOR, $component);
        // Generate the kebab-cased file name
        $fileName = Str::kebab(str_replace('/', '-', $component)); // Change slashes to hyphens for the filename
        return resource_path("views/livewire/page/{$componentPath}.blade.php"); // Keep the nested structure
    }

    protected function makeDirectory(string $path): void
    {
        if (!$this->files->isDirectory(dirname($path))) {
            $this->files->makeDirectory(dirname($path), 0755, true);
        }
    }

    protected function getStubContent(string $component): string
    {
        // $namespace = 'App\\Livewire\\Page\\' . str_replace('/', '\\', $component);
        $namespace = $this->getNamespace($component);
        $class = Str::afterLast($component, '/');
        $viewName = 'livewire.page.' . Str::kebab($component); // Ensure correct view name for stubs

        return str_replace(
            ['{{namespace}}', '{{class}}', '{{viewName}}'],
            [$namespace, $class, $viewName],
            $this->files->get($this->getStubPath())
        );
    }

    protected function getNamespace(string $component): string
    {
        $componentParts = explode('/', $component);
        array_pop($componentParts); // Remove the class name part
        $namespace = implode('\\', $componentParts);
    
        return "App\\Livewire\\Page" . ($namespace ? "\\" . $namespace : "");
    }
    
    /**
     * Get the path to the stub file.
     *
     * @return string
     */
    protected function getStubPath(): string
    {
        return __DIR__ . '/stubs/page/page.stub';
    }

    protected function getViewContent(): string
    {
        return "<div>\n    <!-- Koverae Page Content -->\n</div>";
    }

    protected function displayComponentInfo(string $component)
    {
        // Kebab-cased view path for Livewire conventions
        $slug = Str::kebab(str_replace('/', '-', $component)); // Change slashes to hyphens
        
        // Class path formatted to match nested directories
        $classPath = "App/Livewire/Page/" . str_replace('/', '/', $component);

        // View path in the correct nested format
        $viewPath = "resources/views/livewire/page/" . str_replace('/', DIRECTORY_SEPARATOR, $component) . ".blade.php";

        // Tag format for Livewire component
        $tag = "<livewire:page.{$slug} />";

        // Display the results in the console
        $this->line("<options=bold,reverse;fg=green> COMPONENT CREATED </> 🤙🏿 \n");
        $this->line("<options=bold;fg=green>CLASS:</> {$classPath}");
        $this->line("<options=bold;fg=green>VIEW:</> {$viewPath}");
        $this->line("<options=bold;fg=green>TAG:</> {$tag}");
    }

}