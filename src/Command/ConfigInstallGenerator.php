<?php

namespace Kakaprodo\CustomData\Command;

use Illuminate\Console\Command;

class ConfigInstallGenerator extends Command
{
    protected $hidden = true;

    /**
     * type can be: BarChart, CardCoun, List, PieChart
     */
    protected $signature = 'custom-data:install {--force}';

    protected $description = 'Generate the configuration file of the package';

    public function handle()
    {
        $params = [
            '--provider' => "Kakaprodo\CustomData\CustomDataServiceProvider",
            '--tag' => "custom-data-config"
        ];

        if ($this->option('force') === true) {
            $params['--force'] = true;
        }

        $this->call('vendor:publish', $params);
    }
}
