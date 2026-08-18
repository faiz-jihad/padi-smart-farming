<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('market_listings', function (Blueprint $table) {
            $table->text('sales_link')->nullable()->after('description');
            $table->text('image_url')->nullable()->after('sales_link');
        });
    }

    public function down(): void
    {
        Schema::table('market_listings', function (Blueprint $table) {
            $table->dropColumn(['sales_link', 'image_url']);
        });
    }
};
