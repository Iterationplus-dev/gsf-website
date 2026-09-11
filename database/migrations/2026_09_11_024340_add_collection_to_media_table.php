<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Which curated set a file belongs to, if any.
     *
     * Approval governs whether a file may be shown at all; it says nothing about
     * where. Without this, every approved image — leadership portraits, award
     * certificates, section backgrounds — appeared in the public photo gallery
     * simply by virtue of being approved. A gallery is a curated set, so
     * membership of it is now recorded explicitly.
     */
    public function up(): void
    {
        Schema::table('media', function (Blueprint $table): void {
            $table->string('collection')->nullable()->index()->after('credit');
        });
    }

    public function down(): void
    {
        Schema::table('media', function (Blueprint $table): void {
            $table->dropIndex(['collection']);
            $table->dropColumn('collection');
        });
    }
};
