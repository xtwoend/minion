<?php

namespace Minion\Plugin\Commands;

use Illuminate\Support\Str;
use Minion\Plugin\Support\Migrations\NameParser;
use Minion\Plugin\Support\Migrations\SchemaParser;
use Minion\Plugin\Stub;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class MigrationCommand extends GeneratorCommand
{
    use PluginCommandTrait;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'plugin:make-migration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a new migration for the specified plugin.';

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array(
            array('name', InputArgument::REQUIRED, 'The migration name will be created.'),
            array('plugin', InputArgument::OPTIONAL, 'The name of plugin will be created.'),
        );
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array(
            array('fields', null, InputOption::VALUE_OPTIONAL, 'The specified fields table.', null),
            array('plain', null, InputOption::VALUE_NONE, 'Create plain migration.'),
        );
    }

    /**
     * Get schema parser.
     *
     * @return SchemaParser
     */
    public function getSchemaParser()
    {
        return new SchemaParser($this->option('fields'));
    }

    /**
     * @throws \InvalidArgumentException
     *
     * @return mixed
     */
    protected function getTemplateContents()
    {
        $parser = new NameParser($this->argument('name'));

        if ($parser->isCreate()) {
            return Stub::create('/migration/create.stub', [
                'class' => $this->getClass(),
                'table' => $parser->getTableName(),
                'fields' => $this->getSchemaParser()->render(),
            ]);
        } elseif ($parser->isAdd()) {
            return Stub::create('/migration/add.stub', [
                'class' => $this->getClass(),
                'table' => $parser->getTableName(),
                'fields_up' => $this->getSchemaParser()->up(),
                'fields_down' => $this->getSchemaParser()->down(),
            ]);
        } elseif ($parser->isDelete()) {
            return Stub::create('/migration/delete.stub', [
                'class' => $this->getClass(),
                'table' => $parser->getTableName(),
                'fields_down' => $this->getSchemaParser()->up(),
                'fields_up' => $this->getSchemaParser()->down(),
            ]);
        } elseif ($parser->isDrop()) {
            return Stub::create('/migration/drop.stub', [
                'class' => $this->getClass(),
                'table' => $parser->getTableName(),
                'fields' => $this->getSchemaParser()->render(),
            ]);
        }

        return Stub::create('/migration/plain.stub', [
            'class' => $this->getClass(),
        ]);
    }

    /**
     * @return mixed
     */
    protected function getDestinationFilePath()
    {
        $path = $this->laravel['plugins']->getPluginPath($this->getPluginName());

        $generatorPath = $this->laravel['plugins']->config('paths.generator.migration');

        return $path . $generatorPath . '/' . $this->getFileName() . '.php';
    }

    /**
     * @return string
     */
    private function getFileName()
    {
        return date('Y_m_d_His_') . $this->getSchemaName();
    }

    /**
     * @return array|string
     */
    private function getSchemaName()
    {
        return $this->argument('name');
    }

    /**
     * @return string
     */
    private function getClassName()
    {
        return Str::studly($this->argument('name'));
    }

    public function getClass()
    {
        return $this->getClassName();
    }

    /**
     * Run the command.
     */
    public function fire()
    {
        parent::fire();

        if (app()->environment() === 'testing') {
            return;
        }
        $this->call('optimize');
    }
}
