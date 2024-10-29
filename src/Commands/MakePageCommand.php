<?php

namespace Koverae\KoveraeBuilder\Commands;

use Illuminate\Support\Str;

class MakePageCommand extends BaseCommand
{
    protected $signature = 'koverae:make-page {component} {module?} {--inline}';
    protected $description = 'Create a new page for Koverae Builder.';

    public function handle(): int
    {
        
        // Extract component path and class
        $component = Str::studly($this->argument('component'));

        $module = $this->argument('module') ?? null;

        $path = $this->getPath($component);

        $viewPath = $this->getViewPath($component);

        // Generate directory and class if they do not exist
        if ($this->files->exists($path)) {
            $this->error("Component [{$component}] already exists!");
            return 0;
        }

        // Create the class file and view file
        $this->makeDirectory($path);
        $this->files->put($path, $this->getStubContent($component));
        
        if(!$this->option('inline')){
            $this->makeDirectory($viewPath);
            $this->files->put($viewPath, $this->getViewContent($component));
        }

        // Display the class, view, and tag
        $this->displayComponentInfo($component);
        
        return 0;
    }

    protected function getPath(string $component): string
    {
        $basePath = config('koverae-builder.page_maker.default_path');
        $componentPath = str_replace('/', DIRECTORY_SEPARATOR, $component);
        return app_path($basePath . $componentPath . '.php');
    }

    protected function getViewPath(string $component): string
    {
        $basePath = config('koverae-builder.page_maker.default_view_path');
        $componentPath = preg_replace('/\/-/', '/', strtolower(preg_replace('/(?<!^)(?=[A-Z])/', '-', $component)));
        
        return resource_path($basePath . $componentPath. ".blade.php"); // Keep the nested structure
    }

    protected function getStubContent(string $component): string
    {
        $namespace = $this->getNamespace($component);
        $class = Str::afterLast($component, '/');
        $viewName = config('koverae-builder.page_maker.default_tag_path') . preg_replace('/\.-/', '.',  Str::kebab(str_replace('/', '.', $component))); // Ensure correct view name for stubs

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
        $baseNamespace = config('koverae-builder.page_maker.namespace');
        $componentParts = explode('/', $component);
        array_pop($componentParts); // Remove the class name part
        $namespace = implode('\\', $componentParts);
    
        return $baseNamespace . ($namespace ? "\\" . $namespace : "");
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

    protected function displayComponentInfo(string $component) {
        // Kebab-cased view path for Livewire conventions
        $slug = preg_replace('/\/-/', '/', strtolower(preg_replace('/(?<!^)(?=[A-Z])/', '-', $component))); // Change slashes to hyphens
        
        // Class path formatted to match nested directories
        $classPath = config('koverae-builder.page_maker.default_path') . str_replace('/', '/', $component);

        // View path in the correct nested format
        $viewPath = config('koverae-builder.page_maker.default_view_path') . $slug . ".blade.php";

        $tag_slug = preg_replace('/\.-/', '.',  Str::kebab(str_replace('/', '.', $component)));
        // Tag format for Livewire component
        $tag = "<". config('koverae-builder.page_maker.default_tag_path') . $tag_slug ." />";

        // Display the results in the console
        $this->line("<options=bold,reverse;fg=green> COMPONENT CREATED </> 🤙🏿 \n");
        $this->line("<options=bold;fg=green>CLASS:</> {$classPath}");

        if (!$this->option('inline')) {
            $this->line("<options=bold;fg=green>VIEW:</> {$viewPath}");
        }

        $this->line("<options=bold;fg=green>TAG:</> {$tag}");
    }

}