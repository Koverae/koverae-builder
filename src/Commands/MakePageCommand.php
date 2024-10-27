<?php

namespace Koverae\KoveraeBuilder\Commands;

use Illuminate\Support\Str;

class MakePageCommand extends BaseCommand
{
    protected $signature = 'koverae:make-page {component} {--inline}';
    protected $description = 'Create a new page for Koverae Builder.';

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
        $this->files->put($viewPath, $this->getViewContent($component));

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
        $componentPath = preg_replace('/\/-/', '/', strtolower(preg_replace('/(?<!^)(?=[A-Z])/', '-', $component)));
        
        return resource_path("views/livewire/page/{$componentPath}.blade.php"); // Keep the nested structure
    }

    protected function getStubContent(string $component): string
    {
        $namespace = $this->getNamespace($component);
        $class = Str::afterLast($component, '/');
        $viewName = 'livewire.page.' . preg_replace('/\.-/', '.',  Str::kebab(str_replace('/', '.', $component))); // Ensure correct view name for stubs

        return str_replace(
            ['{{namespace}}', '{{class}}', '{{viewName}}'],
            [$namespace, $class, $viewName],
            $this->files->get($this->getStubPath())
        );
    }

    protected function getViewContent(string $component): string
    {
        $class = Str::afterLast($component, '/');

        return str_replace(
            ['{class}'],
            [$class],
            $this->files->get($this->getViewStubPath())
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
    /**
     * Get the path to the stub file.
     *
     * @return string
     */
    protected function getViewStubPath(): string
    {
        return __DIR__ . '/stubs/page/view.stub';
    }

    protected function displayComponentInfo(string $component)
    {
        // Kebab-cased view path for Livewire conventions
        $slug = preg_replace('/\/-/', '/', strtolower(preg_replace('/(?<!^)(?=[A-Z])/', '-', $component))); // Change slashes to hyphens
        
        // Class path formatted to match nested directories
        $classPath = "App/Livewire/Page/" . str_replace('/', '/', $component);

        // View path in the correct nested format
        $viewPath = "resources/views/livewire/page/" . $slug . ".blade.php";

        $tag_slug = preg_replace('/\.-/', '.',  Str::kebab(str_replace('/', '.', $component)));
        // Tag format for Livewire component
        $tag = "<livewire:page.{$tag_slug} />";

        // Display the results in the console
        $this->line("<options=bold,reverse;fg=green> COMPONENT CREATED </> 🤙🏿 \n");
        $this->line("<options=bold;fg=green>CLASS:</> {$classPath}");
        $this->line("<options=bold;fg=green>VIEW:</> {$viewPath}");
        $this->line("<options=bold;fg=green>TAG:</> {$tag}");
    }

}