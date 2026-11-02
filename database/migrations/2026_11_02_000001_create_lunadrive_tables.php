<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provider_configs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('provider', 32)->default('google_drive');
            $table->text('client_id_encrypted');
            $table->text('client_secret_encrypted');
            $table->text('redirect_uri');
            $table->json('scopes')->nullable();
            $table->string('status', 32)->default('active');
            $table->timestamps();
            $table->index('provider');
        });

        Schema::create('oauth_states', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->uuid('provider_config_id');
            $table->string('flow', 32)->default('connect');
            $table->string('state_hash')->unique();
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->timestamps();

            $table->foreign('provider_config_id')->references('id')->on('provider_configs')->cascadeOnDelete();
        });

        Schema::create('connected_accounts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->uuid('provider_config_id')->nullable();
            $table->string('provider', 32)->default('google_drive');
            $table->string('provider_account_id');
            $table->string('email');
            $table->string('display_name')->nullable();
            $table->text('avatar_url')->nullable();
            $table->text('access_token_encrypted')->nullable();
            $table->text('refresh_token_encrypted')->nullable();
            $table->timestamp('token_expires_at')->nullable();
            $table->json('scopes')->nullable();
            $table->string('status', 32)->default('connected');
            $table->text('last_error')->nullable();
            $table->string('app_folder_id')->nullable();
            $table->timestamps();

            $table->foreign('provider_config_id')->references('id')->on('provider_configs')->nullOnDelete();
            $table->unique(['user_id', 'provider', 'provider_account_id']);
        });

        Schema::create('storage_accounts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('connected_account_id')->unique();
            $table->unsignedBigInteger('quota_bytes')->nullable();
            $table->unsignedBigInteger('used_bytes')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->foreign('connected_account_id')->references('id')->on('connected_accounts')->cascadeOnDelete();
        });

        Schema::create('upload_routing_policies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('mode', 32)->default('most_available');
            $table->json('priority_account_ids')->nullable();
            $table->unsignedInteger('round_robin_cursor')->default(0);
            $table->timestamps();
        });

        Schema::create('folders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->uuid('parent_id')->nullable();
            $table->string('name');
            $table->string('color', 32)->nullable();
            $table->string('icon', 64)->nullable();
            $table->string('status', 32)->default('active');
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'parent_id', 'status']);
        });

        Schema::table('folders', function (Blueprint $table) {
            $table->foreign('parent_id')->references('id')->on('folders')->nullOnDelete();
        });

        Schema::create('files', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->uuid('folder_id')->nullable();
            $table->uuid('connected_account_id');
            $table->string('name');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->string('provider_file_id');
            $table->string('status', 32)->default('active');
            $table->boolean('starred')->default(false);
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();

            $table->foreign('folder_id')->references('id')->on('folders')->nullOnDelete();
            $table->foreign('connected_account_id')->references('id')->on('connected_accounts')->cascadeOnDelete();
            $table->index(['user_id', 'folder_id', 'status']);
            $table->index(['user_id', 'name']);
        });

        Schema::create('upload_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->uuid('connected_account_id')->nullable();
            $table->uuid('folder_id')->nullable();
            $table->string('file_name');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->unsignedBigInteger('bytes_received')->default(0);
            $table->text('resumable_uri')->nullable();
            $table->string('status', 32)->default('pending');
            $table->timestamps();

            $table->foreign('connected_account_id')->references('id')->on('connected_accounts')->nullOnDelete();
            $table->foreign('folder_id')->references('id')->on('folders')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('upload_sessions');
        Schema::dropIfExists('files');
        Schema::dropIfExists('folders');
        Schema::dropIfExists('upload_routing_policies');
        Schema::dropIfExists('storage_accounts');
        Schema::dropIfExists('connected_accounts');
        Schema::dropIfExists('oauth_states');
        Schema::dropIfExists('provider_configs');
    }
};
