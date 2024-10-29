<?php

namespace Koverae\KoveraeBuilder\Commands;

use Illuminate\Support\Str;

class MakeFormCommand extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'koverae:make-form {component} {module?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new form component for Koverae UI Builder.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        // Extract component path and class
        $component = Str::studly($this->argument('component'));

        $module = $this->argument('module') ?? null;

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
        $basePath = config('koverae-builder.form_maker.default_path');
        $componentPath = str_replace('/', DIRECTORY_SEPARATOR, $component);
        return app_path($basePath . $componentPath . '.php');
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
        $baseNamespace = config('koverae-builder.form_maker.namespace');
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
        return __DIR__ . '/stubs/form/form.stub';
    }

    /**
     * Display the class, view, and tag.
     *
     * @param string $component
     */
    protected function displayComponentInfo(string $component)
    {
        // Class path formatted to match nested directories
        $classPath = config('koverae-builder.form_maker.default_path') . str_replace('/', '/', $component);

        $slug = preg_replace('/\.-/', '.',  Str::kebab(str_replace('/', '.', $component)));
        // Tag format for Livewire component
        $tag = "<". config('koverae-builder.form_maker.default_tag_path') . $slug ." />";

        // Display the results in the console
        $this->line("<options=bold,reverse;fg=green> COMPONENT CREATED </> 🤙🏿 \n");
        $this->line("<options=bold;fg=green>CLASS:</> {$classPath}");
        $this->line("<options=bold;fg=green>TAG:</> {$tag}");
    }

}