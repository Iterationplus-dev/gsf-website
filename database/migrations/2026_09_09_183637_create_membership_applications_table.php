<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Applications from co-operative societies seeking to affiliate with the
     * foundation. Like every other public submission, `submission_key` makes a
     * resubmitted form idempotent so a double click cannot create a second
     * application, and `consented_at` records that the applicant confirmed the
     * declaration before anything was stored.
     */
    public function up(): void
    {
        Schema::create('membership_applications', function (Blueprint $table): void {
            $table->id();
            $table->uuid('submission_key')->unique();
            $table->string('contact_name');
            $table->string('contact_phone');
            $table->string('organisation_name');
            $table->text('organisation_address');
            $table->string('telephone')->nullable();
            $table->string('email')->index();
            $table->string('cooperative_type')->index();
            $table->string('organisation_website')->nullable();

            // The society's own declared size. A co-operative may not be
            // registered in Nigeria with fewer than seven members, so the form
            // rejects anything lower rather than storing an ineligible claim.
            $table->unsignedInteger('member_count');

            $table->string('ethnic_group')->nullable();
            $table->text('message')->nullable();
            $table->string('status')->default('new')->index();
            $table->text('review_notes')->nullable();
            $table->timestamp('consented_at');
            $table->timestamps();
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('membership_applications');
    }
};
