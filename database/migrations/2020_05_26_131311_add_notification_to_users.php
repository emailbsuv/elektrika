<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNotificationToUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('notification_review_mail')->default(true);
            $table->boolean('notification_review_sms')->default(true);
            $table->boolean('notification_review_push')->default(true);
            $table->boolean('notification_claim_mail')->default(true);
            $table->boolean('notification_claim_sms')->default(true);
            $table->boolean('notification_claim_push')->default(true);
            $table->boolean('notification_offer_mail')->default(true);
            $table->boolean('notification_offer_sms')->default(true);
            $table->boolean('notification_offer_push')->default(true);
            $table->boolean('notification_order_mail')->default(true);
            $table->boolean('notification_order_sms')->default(true);
            $table->boolean('notification_order_push')->default(true);
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
            $table->dropColumn('notification_review_mail');
            $table->dropColumn('notification_review_sms');
            $table->dropColumn('notification_review_push');
            $table->dropColumn('notification_claim_mail');
            $table->dropColumn('notification_claim_sms');
            $table->dropColumn('notification_claim_push');
            $table->dropColumn('notification_offer_mail');
            $table->dropColumn('notification_offer_sms');
            $table->dropColumn('notification_offer_push');
            $table->dropColumn('notification_order_mail');
            $table->dropColumn('notification_order_sms');
            $table->dropColumn('notification_order_push');
        });
    }
}
