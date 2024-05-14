<?php

namespace Tests\Console\Commands;

use Mockery;
use Tests\TestCase;
use App\Actions\LocalDbDump;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Config;

class DatabaseBackupTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (config('filesystems.cloud') != 'google')
            $this->markTestSkipped('Google is not set as the cloud filesystems driver');
    }

    public function testFailedDbDump()
    {
        $localDbDumpMock = Mockery::mock()->makePartial()
                                          ->shouldReceive('createDump')
                                          ->andThrow(new \Exception("Disk space full"))
                                          ->getMock();
        $this->app->bind(LocalDbDump::class, function () use ($localDbDumpMock) {
            return $localDbDumpMock;
        });

        $this->artisan('db:backup')
             ->expectsOutput('Error in backing up DB. Error message: Disk space full')
             ->assertExitCode(1);
    }

    public function testInvalidCredentialsFile()
    {
        $file = storage_path('backups' . DIRECTORY_SEPARATOR . 'google_drive_fake_credentials.json');
        $fileHandler = fopen($file, "w");
        fwrite($fileHandler, "{\"type\": \"service_account\"}");
        fclose($fileHandler);

        Config::set('filesystems.disks.google.clientSecret', $file);
        $localDbDumpMock = $this->getMockBuilder(LocalDbDump::class)
                                  ->onlyMethods([ 'createDump' ])
                                  ->getMock();
        $localDbDumpMock->method('createDump')->willReturn([ 75, 0, $file ]);
        $this->app->bind(LocalDbDump::class, function () use ($localDbDumpMock) {
            return $localDbDumpMock;
        });

        $this->artisan('db:backup')
             ->assertExitCode(1);

        unlink($file);
    }

    public function testFileNotFound()
    {
        $localDbDumpMock = $this->getMockBuilder(LocalDbDump::class)
                                  ->onlyMethods([ 'createDump' ])
                                  ->getMock();
        $fileName = 'tmp' . DIRECTORY_SEPARATOR . 'gdrive_backup.sql.gz';
        $localDbDumpMock->method('createDump')->willReturn([ 75, 0, DIRECTORY_SEPARATOR . $fileName ]);
        $this->app->bind(LocalDbDump::class, function () use ($localDbDumpMock) {
            return $localDbDumpMock;
        });

        $this->artisan('db:backup')
             ->expectsOutput('Error in backing up DB. Error message: Unable to read file from location: ' . $fileName . '.')
             ->assertExitCode(1);
    }

    public function testWriteToCloud()
    {
        $fileName = 'google_drive_test_' . date('Y-m-d--H_i_s') . '.txt';
        $filePath = storage_path('backups' . DIRECTORY_SEPARATOR . $fileName);
        $fileHandler = fopen($filePath, "w");
        fwrite($fileHandler, "File created at " . date('Y-m-d_H:i'));
        fclose($fileHandler);

        $localDbDumpMock = $this->getMockBuilder(LocalDbDump::class)
                                  ->onlyMethods([ 'createDump' ])
                                  ->getMock();
        $localDbDumpMock->method('createDump')->willReturn([ 75, 0, $filePath ]);

        $remoteFilePath = config('filesystems.disks.google.dbBackupsFolder') . DIRECTORY_SEPARATOR . $fileName;
        $this->app->bind(LocalDbDump::class, function () use ($localDbDumpMock) {
            return $localDbDumpMock;
        });

        $this->artisan('db:backup')
             ->assertExitCode(0);
        $this->assertTrue(Storage::disk('google')->exists($remoteFilePath));

        Storage::disk('google')->delete($remoteFilePath);
        $this->assertFalse(Storage::disk('google')->exists($remoteFilePath));
        unlink($filePath);
    }


    /**
     * @param  String $disk Optional
     * @return Filesystem
     */
    protected function mockStorageDisk($disk = 'mock')
    {
        Storage::extend('mock', function () {
            return \Mockery::mock(\Illuminate\Filesystem\FilesystemAdapter::class);
        });

        Config::set('filesystems.disks.' . $disk, ['driver' => 'mock']);
        Config::set('filesystems.default', $disk);

        return Storage::disk($disk);
    }
}
