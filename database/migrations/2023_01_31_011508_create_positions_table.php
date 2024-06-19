<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePositionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('positions_stg', function (Blueprint $table) {
            $table->string('position_nbr')->unique();
            $table->string('descr')->nullable();
            $table->string('descrshort')->nullable();
            $table->string('reports_to')->nullable();
            $table->timestamps();
            $table->index(['reports_to', 'position_nbr']);
            $table->index(['descr', 'position_nbr']);
            $table->index(['descrshort', 'position_nbr']);
        });
        Schema::create('positions', function (Blueprint $table) {
            $table->string('position_nbr')->unique();
            $table->string('descr')->nullable();
            $table->string('descrshort')->nullable();
            $table->string('reports_to')->nullable();
            $table->timestamp('date_deleted')->nullable();
            $table->timestamps();
            $table->index(['reports_to', 'position_nbr']);
            $table->index(['descr', 'position_nbr']);
            $table->index(['descrshort', 'position_nbr']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('positions_stg');
        Schema::dropIfExists('positions');
    }
}
