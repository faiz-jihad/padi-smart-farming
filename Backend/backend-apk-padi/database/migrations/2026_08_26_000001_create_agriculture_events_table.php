<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agriculture_events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->string('category', 50)->default('workshop'); // workshop, field_day, bazaar, irrigation
            $table->date('event_date');
            $table->string('event_time', 50)->default('08:30 - 12:30 WIB');
            $table->string('location_name');
            $table->string('location_address')->nullable();
            $table->boolean('is_online')->default(false);
            $table->string('organizer');
            $table->string('speaker')->nullable();
            $table->integer('quota')->default(50);
            $table->integer('registered_count')->default(0);
            $table->string('price_type', 20)->default('free');
            $table->string('asset_image')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('status', 30)->default('upcoming'); // upcoming, ongoing, completed, cancelled
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agriculture_events');
    }
};
