<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPhoneToUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 14)->unique()->nullable();
            $table->string('verification_code', 8)->nullable();
            $table->timestamp('code_created_at')->nullable();
            $table->timestamp('phone_verified_at')->nullable();
            $table->string('email')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('phone');
            $table->dropColumn('verification_code');
            $table->dropColumn('code_created_at');
            $table->dropColumn('phone_verified_at');
        });
    }
}
