<?php

namespace Kakaprodo\CustomData\Command;

use Illuminate\Support\Str;
use Kakaprodo\CustomData\Helpers\Util;
use Illuminate\Console\GeneratorCommand;

class CustomActionGenerator extends GeneratorCommand
{
    protected $hidden = true;

    /**
     * type can be: BarChart, CardCoun, List, PieChart
     */
    protected $signature = 'custom-data:action {name}';

    protected $description = 'Generate a custom data action class';

    protected function getStub()
    {
        return __DIR__ . "/Stubs/Action.php.stub";
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return Str::finish($rootNamespace, '\\') . Util::actionFolder();
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

        $dataClassFolderAndName = Str::finish(
            Str::replaceLast('Action', '', $this->getNameInput()),
            'Data'
        );

        $dataClassNamespace = 'App\\' . Util::dataFolder(
            str_replace('/', '\\', $dataClassFolderAndName)
        );

        $formattedContent = strtr($content, [
            '{name_space}' =>  str_replace('\\\\', '\\', $classNameSpace),
            '{class_name}' => collect(explode('\\', $class))->last(),
            '{data_class_name}' =>  collect(explode('\\', $dataClassNamespace))->last(),
            '{data_class_name_space}' => $dataClassNamespace
        ]);

        file_put_contents($classPath, $formattedContent);

        $this->call('custom-data', [
            'name' => $dataClassFolderAndName
        ]);
    }
}
