<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('farmer_public_profiles', function (Blueprint $table) {
            $table->id();

            // One public profile per farmer
            $table->foreignId('farmer_id')
                ->unique()
                ->constrained('users')
                ->cascadeOnDelete();

            // Which template is active (nullable — template not selected yet)
            $table->foreignId('profile_template_id')
                ->nullable()
                ->constrained('profile_templates')
                ->nullOnDelete();

            // Unique subdomain — e.g. "pakjoko" → pakjoko.padi.id
            $table->string('subdomain', 40)->nullable()->unique();

            // Public-facing business info
            $table->string('business_name', 150);
            $table->string('headline', 255)->nullable();
            $table->text('description')->nullable();    // plain text, sanitized

            // Media (stored in public disk)
            $table->string('logo_path', 500)->nullable();
            $table->string('cover_image_path', 500)->nullable();

            // Contact (explicit opt-in fields — farmer decides what to show)
            $table->string('whatsapp', 30)->nullable();    // normalized: 6281234567890
            $table->string('public_email', 150)->nullable();
            $table->string('public_phone', 30)->nullable();
            $table->text('public_address')->nullable();    // general area only

            // Social media (optional)
            $table->string('instagram_url', 255)->nullable();
            $table->string('facebook_url', 255)->nullable();

            // Privacy controls — JSON config, defaults to privacy-safe values
            // Keys: show_products, show_fields, show_harvests, show_productivity,
            //       show_location, show_gallery, show_contact, show_active_variety
            $table->json('section_settings')->nullable();

            // Website status lifecycle
            $table->enum('website_status', ['draft', 'review', 'published', 'suspended'])
                ->default('draft')
                ->index();

            // Verification by P.A.D.I. admin
            $table->enum('verification_status', ['unverified', 'verified', 'rejected'])
                ->default('unverified')
                ->index();

            $table->timestamp('published_at')->nullable();

            $table->timestamps();

            $table->index(['farmer_id', 'website_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('farmer_public_profiles');
    }
};
