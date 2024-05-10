<?php

namespace App\Console\Commands;

use App\Actions\LocalDbDump;
use Illuminate\Console\Command;

class DatabaseDump extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:dump {--dumpFolder= : Location where you\'d like to store the database dump}';

    /**
     * Aliases of the console command.
     *
     * @var string
     */
    protected $aliases = [ 'db:localDump' ];

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Takes a local dump of the database. Deletes older dumps to ensure not more than 75% of the disk is full";

    private $localDbDump;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->setAliases($this->aliases);
        $this->localDbDump = app()->make(LocalDbDump::class);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->localDbDump->setLocalBackupFolder($this->option('dumpFolder'));

        try {
            $dbDumpAttributes = $this->localDbDump->createDump();

            $commandOutput = $this->localDbDump->outputFromDump(...$dbDumpAttributes);
            $infoOutput = explode("\n", $commandOutput);

            foreach ($infoOutput as $line) {
                $this->info($line);
            }
        } catch (\Exception $e) {
            $this->error("Exception in dumping DB. Error message: " . $e->getMessage());

            // TODO: Send Discord notifications to Admins

            return 1;
        }

        return 0;
    }
}
