<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEfaasSessionsTable extends Migration
{
    public function up()
    {
        Schema::connection(config('efaas.database_connection'))->create(config('efaas.table_name'), function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('laravel_session_id')->unique();
            $table->string('efaas_sid')->index();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::connection(config('efaas.database_connection'))->dropIfExists(config('efaas.table_name'));
    }
}
