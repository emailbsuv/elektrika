<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddProfileToUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('contact_phone', 15)->nullable();
            $table->string('contact_first_name', 64)->nullable();
            $table->string('contact_middle_name', 64)->nullable();
            $table->string('contact_last_name', 64)->nullable();
            $table->string('vk_link', 64)->nullable();
            $table->string('ok_link', 64)->nullable();
            $table->string('fb_link', 64)->nullable();
            $table->string('web', 255)->nullable();
            $table->string('avatar', 64)->nullable();
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
            $table->dropColumn('contact_phone');
            $table->dropColumn('contact_first_name');
            $table->dropColumn('contact_middle_name');
            $table->dropColumn('contact_last_name');
            $table->dropColumn('vk_link');
            $table->dropColumn('ok_link');
            $table->dropColumn('fb_link');
            $table->dropColumn('web');
            $table->dropColumn('avatar');
        });
    }
}
