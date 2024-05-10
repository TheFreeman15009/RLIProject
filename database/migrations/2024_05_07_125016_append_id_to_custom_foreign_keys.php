<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AppendIdToCustomForeignKeys extends Migration
{
    private function renameUp($column)
    {
        return function (Blueprint $table) use ($column) {
            $table->renameColumn($column, $column . '_id');
        };
    }
    private function renameDown($column)
    {
        return function (Blueprint $table) use ($column) {
            $table->renameColumn($column . '_id', $column);
        };
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('circuits', $this->renameUp('series'));
        Schema::table('constructors', $this->renameUp('series'));
        Schema::table('races', $this->renameUp('points'));
        Schema::table('seasons', $this->renameUp('series'));
        Schema::table('signups', $this->renameUp('season'));
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('signups', $this->renameDown('season'));
        Schema::table('seasons', $this->renameDown('series'));
        Schema::table('races', $this->renameDown('points'));
        Schema::table('constructors', $this->renameDown('series'));
        Schema::table('circuits', $this->renameDown('series'));
    }
}
