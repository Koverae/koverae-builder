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
        $componentPath = Str::kebab(str_replace('/', DIRECTORY_SEPARATOR, $component));
        return resource_path("views/livewire/page/{$componentPath}.blade.php");
    }

    protected function makeDirectory(string $path): void
    {
        if (!$this->files->isDirectory(dirname($path))) {
            $this->files->makeDirectory(dirname($path), 0755, true);
        }
    }

    protected function getStubContent(string $component): string
    {
        $namespace = 'App\\Livewire\\Page\\' . str_replace('/', '\\', $component);
        $class = Str::afterLast($component, '/');
        $viewName = Str::kebab(str_replace('/', '.', $component)); // Converts "Reservation/Lists" to "reservation.lists"

        return str_replace(
            ['{{namespace}}', '{{class}}', '{{viewName}}'],
            [$namespace, $class, $viewName],
            $this->files->get($this->getStubPath())
        );
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
        return "<div>\n    <!-- Page Content -->\n</div>";
    }

    protected function displayComponentInfo(string $page)
    {
        $slug = Str::kebab($page);
        $classPath = "App/Livewire/Page/{$page}";
        $tag = "<livewire:page.{$slug} />";

        $this->line("<options=bold,reverse;fg=green> COMPONENT CREATED </> 🤙🏿 \n");
        $this->line("<options=bold;fg=green>CLASS:</> {$classPath}");
        $this->line("<options=bold;fg=green>TAG:</> {$tag}");
    }

}