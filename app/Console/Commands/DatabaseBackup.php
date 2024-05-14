<?php

namespace App\Console\Commands;

use App\Actions\LocalDbDump;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class DatabaseBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:backup';

    /**
     * Aliases of the console command.
     *
     * @var string
     */
    protected $aliases = [ 'db:cloudBackup', 'db:googleDriveBackup' ];

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Backups up the database in the cloud.";

    protected $localDbDump;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->localDbDump = app()->make(LocalDbDump::class);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            $dbDumpAttributes = $this->localDbDump->createDump();
            $dumpCommandOutput = $this->localDbDump->outputFromDump(...$dbDumpAttributes);
            $lastNewLine = strripos($dumpCommandOutput, "\n");

            if ($dumpCommandOutput[$lastNewLine + 1] != DIRECTORY_SEPARATOR) {
                // DB dump failed
                $this->error($dumpCommandOutput);
                return 2;
            } else {
                $this->info($dumpCommandOutput);
            }

            $backupLocation = substr($dumpCommandOutput, $lastNewLine + 1);
            $localAdapter = new \League\Flysystem\Local\LocalFilesystemAdapter(DIRECTORY_SEPARATOR);
            $localfs = new \League\Flysystem\Filesystem($localAdapter);

            // Assuming there are no / in the name of the file
            $lastBackSlash = strripos($backupLocation, DIRECTORY_SEPARATOR);
            $remoteBackupLocation = config('filesystems.disks.google.dbBackupsFolder') .
                                        DIRECTORY_SEPARATOR . substr($backupLocation, $lastBackSlash + 1);

            Storage::disk('google')->writeStream(
                $remoteBackupLocation,
                $localfs->readStream($backupLocation)
            );

            $this->info('DB dump backed up successfully.');
        } catch (\Exception $e) {
            $this->error('Error in backing up DB. Error message: ' . $e->getMessage());
            // TODO: Send notification to admins

            return 1;
        }

        return 0;
    }
}
