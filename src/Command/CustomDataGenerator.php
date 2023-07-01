<?php

namespace Kakaprodo\CustomData\Command;

use Illuminate\Support\Str;
use Kakaprodo\CustomData\Helpers\Util;
use Illuminate\Console\GeneratorCommand;

class CustomDataGenerator extends GeneratorCommand
{
    protected $hidden = true;

    /**
     * type can be: BarChart, CardCoun, List, PieChart
     */
    protected $signature = 'custom-data {name}';

    protected $description = 'Generate a custom data class';

    protected function getStub()
    {
        return __DIR__ . "/Stubs/Data.php.stub";
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return Str::finish($rootNamespace, '\\') . Util::dataFolder();
    }

    public function handle()
    {

        parent::handle();

        $this->reformatHandlerClassContent();
    }

    protected function reformatHandlerClassContent()
    {
        // Get the fully qualified class name (FQN)
        $class = $this->qualifyClass($this->getNameInput());

        $classNameSpace = $this->getNamespace($class);

        // get the destination path, based on the default namespace
        $classPath = $this->getPath($class);

        $content = file_get_contents($classPath);

        $formattedContent = strtr($content, [
            '{name_space}' => str_replace('\\\\', '\\', $classNameSpace),
            '{class_name}' => collect(explode('\\', $class))->last(),
        ]);

        file_put_contents($classPath, $formattedContent);
    }
}
