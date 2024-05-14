<?php

namespace Tests\Console\Commands;

use Mockery;
use Tests\TestCase;
use App\Actions\CleanupFolder;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Artisan;
use App\Helpers\FilesystemHelpers as FH;

class DatabaseDumpTest extends TestCase
{
    public function testAccessDeniedException()
    {
        $s = env('DB_CONNECTION');
        Config::set('database.default', $s);
        Config::set('database.connections.testsql', [
            'host' => 'irc-db',
            'database' => 'popo',
            'username' => 'taikwondo',
            'password' => 'who?'
        ]);

        $this->artisan('db:dump')
             ->assertExitCode(1);
    }

    public function testConnectionRefusedException()
    {
        $s = env('DB_CONNECTION');
        Config::set('database.default', $s);
        Config::set('database.connections.testsql', [
            'host' => 'irc-admin',
            'database' => 'popo',
            'username' => 'taikwondo',
            'password' => 'who?'
        ]);

        $this->artisan('db:dump')
             ->assertExitCode(1);
    }

    public function testNoFilesRemovedInDefaultLocation()
    {
        $cleanupFolderMock = Mockery::mock()->expects()->__invoke()->andReturn([ 0, 44 ])->getMock();
        $this->app->bind(CleanupFolder::class, function () use ($cleanupFolderMock) {
            return $cleanupFolderMock;
        });

        $this->withoutMockingConsoleOutput()
             ->artisan('db:dump');

        $this->assertArtisanResult(0, 44);
    }

    public function testInTestBackupFolder()
    {
        $storagePath = FH::trailSlash(storage_path('test_backups'));
        FH::createDirIfNotExists($storagePath);

        $cleanupFolderMock = Mockery::mock()->expects()->__invoke()->andReturn([ 0, 44 ])->getMock();
        $this->app->bind(CleanupFolder::class, function () use ($cleanupFolderMock) {
            return $cleanupFolderMock;
        });

        $this->withoutMockingConsoleOutput()
             ->artisan("db:dump --dumpFolder={$storagePath}");

        $this->assertArtisanResult(0, 44);

        FH::deleteDir($storagePath);
    }

    public function testWithAlias()
    {
        $storagePath = FH::trailSlash(storage_path('test_backups'));
        FH::createDirIfNotExists($storagePath);

        $cleanupFolderMock = Mockery::mock()->expects()->__invoke()->andReturn([ 0, 44 ])->getMock();
        $this->app->bind(CleanupFolder::class, function () use ($cleanupFolderMock) {
            return $cleanupFolderMock;
        });

        $this->withoutMockingConsoleOutput()
             ->artisan("db:localDump --dumpFolder={$storagePath}");

        $this->assertArtisanResult(0, 44);

        FH::deleteDir($storagePath);
    }

    public function testNonZeroFilesRemoved()
    {
        $cleanupFolderMock = Mockery::mock()->expects()->__invoke()->andReturn([ 2, 50 ])->getMock();
        $this->app->bind(CleanupFolder::class, function () use ($cleanupFolderMock) {
            return $cleanupFolderMock;
        });

        $this->withoutMockingConsoleOutput()
             ->artisan('db:dump');

        $this->assertArtisanResult(2, 50);
    }

    private function assertArtisanResult($singleDigitArgument, $doubleDigitArgument)
    {
        $result = Artisan::output();
        $result = substr($result, 0, -1);
        $lastNewLine = strripos($result, "\n");
        $backupLocation = substr($result, $lastNewLine + 1);

        $firstNewLine = stripos($result, "\n");
        $this->assertEquals(strval($doubleDigitArgument), substr($result, $firstNewLine - 3, 2));
        $this->assertEquals('/', $result[$lastNewLine + 1]);
        $this->assertFileExists($backupLocation);

        if ($singleDigitArgument)
            $this->assertEquals(strval($singleDigitArgument), $result[$firstNewLine + 1]);

        unlink($backupLocation);
    }
}
