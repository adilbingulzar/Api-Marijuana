<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSobrietyDatesTable extends Migration
{
    /**
     * Run the migrations.
     *z
     * @return void
     */
    public function up()
    {
        Schema::create('sobriety_dates', function (Blueprint $table) {
            $table->id();
            $table->string('device_id')->unique();
            $table->date('date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sobriety_dates');
    }
}
