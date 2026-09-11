<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('role')->default('editor');
            $table->json('permissions')->nullable();
            $table->boolean('is_active')->default(false);
            $table->text('app_authentication_secret')->nullable();
            $table->text('app_authentication_recovery_codes')->nullable();
        });

        // Uploaded files with the metadata the public site needs to render them
        // accessibly. `disk` records the storage driver used at upload time so that
        // changing the default provider never orphans an existing file.
        Schema::create('media', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('disk')->default('public');
            $table->string('path', 1024);
            $table->string('mime');
            $table->unsignedBigInteger('size');
            $table->string('alt')->nullable();
            $table->text('caption')->nullable();
            $table->string('credit')->nullable();
            $table->json('variants')->nullable();
            $table->boolean('approved')->default(false);
            $table->timestamps();
        });

        // One editorial record backs every published entity - pages, programs,
        // projects, articles, stories, awards, people, partners, publications and
        // policies - so publishing, provenance, SEO and review behave identically
        // across the whole site.
        Schema::create('contents', function (Blueprint $table): void {
            $table->id();
            $table->string('type')->index();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('body')->nullable();
            $table->foreignId('image_id')->nullable()->constrained('media')->nullOnDelete();
            $table->string('category')->nullable()->index();
            $table->json('tags')->nullable();
            $table->json('details')->nullable();
            $table->string('status')->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->boolean('featured')->default(false);
            $table->boolean('is_demo')->default(false);
            $table->unsignedInteger('position')->default(0);
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->text('source')->nullable();
            $table->text('review_notes')->nullable();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['type', 'status', 'published_at']);
        });

        // Programme delivery data that only projects carry, kept out of the shared
        // editorial record so its lifecycle and reporting fields stay explicit.
        Schema::create('projects', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('content_id')->unique()->constrained('contents')->cascadeOnDelete();
            $table->foreignId('program_id')->nullable()->constrained('contents')->nullOnDelete();
            $table->string('status')->default('planned')->index();

            foreach (['location', 'state', 'country', 'beneficiary_category', 'budget_currency'] as $field) {
                $table->string($field)->nullable();
            }

            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->unsignedInteger('beneficiary_count')->nullable();
            $table->unsignedBigInteger('public_budget_minor')->nullable();

            foreach (['objectives', 'activities', 'outcomes', 'key_results'] as $field) {
                $table->text($field)->nullable();
            }

            foreach (['sdgs', 'partners', 'donors', 'gallery', 'documents', 'related_posts', 'related_testimonials'] as $field) {
                $table->json($field)->nullable();
            }

            $table->foreignId('manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('campaigns', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('active')->default(false);
            $table->unsignedBigInteger('goal_minor')->nullable();
            $table->string('currency', 3)->default('NGN');
            $table->timestamps();
        });

        // Amounts are integer minor units throughout. `reference` is unpredictable
        // and is the only identifier exposed to donors or the payment provider;
        // `submission_key` makes a resubmitted form idempotent.
        Schema::create('donations', function (Blueprint $table): void {
            $table->id();
            $table->uuid('reference')->unique();
            $table->uuid('submission_key')->unique();
            $table->foreignId('campaign_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('email')->index();
            $table->string('phone')->nullable();
            $table->boolean('anonymous')->default(false);
            $table->text('message')->nullable();
            $table->unsignedBigInteger('amount_minor');
            $table->string('currency', 3);
            $table->string('status')->default('pending')->index();
            $table->string('provider_id')->nullable()->unique();
            $table->text('checkout_url')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('consented_at');
            $table->timestamp('receipt_sent_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'created_at']);
        });

        // Append-only provider event log. The unique `event_key` is what prevents a
        // replayed webhook or callback from settling the same donation twice.
        Schema::create('payment_transactions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('donation_id')->constrained()->cascadeOnDelete();
            $table->string('event_key')->unique();
            $table->string('status');
            $table->string('provider_id')->nullable();
            $table->timestamps();
        });

        Schema::create('impact_metrics', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->decimal('value', 16, 2)->nullable();
            $table->string('unit')->nullable();
            $table->unsignedSmallInteger('year')->nullable()->index();
            $table->string('geography')->nullable();
            $table->text('source')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->boolean('published')->default(false);
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table): void {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        Schema::create('enquiries', function (Blueprint $table): void {
            $table->id();
            $table->uuid('submission_key')->unique();
            $table->string('type')->index();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('organization')->nullable();
            $table->text('message');
            $table->string('status')->default('new')->index();
            $table->timestamp('consented_at');
            $table->timestamps();
        });

        Schema::create('subscribers', function (Blueprint $table): void {
            $table->id();
            $table->string('email')->unique();
            $table->string('status')->default('pending')->index();
            $table->timestamp('consented_at');
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('unsubscribed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action');
            $table->string('subject_type');
            $table->unsignedBigInteger('subject_id');
            $table->json('changed_fields')->nullable();
            $table->timestamps();
            $table->index(['subject_type', 'subject_id']);
        });

        Schema::create('redirects', function (Blueprint $table): void {
            $table->id();
            $table->string('from_path')->unique();
            $table->string('to_path');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        $tables = [
            'redirects', 'audit_logs', 'subscribers', 'enquiries', 'settings',
            'impact_metrics', 'payment_transactions', 'donations', 'campaigns',
            'projects', 'contents', 'media',
        ];

        foreach ($tables as $table) {
            Schema::dropIfExists($table);
        }

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['role', 'permissions', 'is_active', 'app_authentication_secret', 'app_authentication_recovery_codes']);
        });
    }
};
