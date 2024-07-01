<?php

namespace Javaabu\EfaasSocialite\Tests\TestSupport\database;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class CreateSessionsTable extends Migration {
    public function up()
    {
        Schema::create('sessions', function ($table) {
            $table->string('id')->unique();
            $table->unsignedInteger('user_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->text('payload');
            $table->integer('last_activity');
        });
    }

    public function down()
    {
        Schema::dropIfExists('sessions');
    }
}
