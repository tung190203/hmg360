<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('groups')) {
            Schema::create('groups', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->json('permission_data')->nullable();
                $table->json('scope_data')->nullable();
                $table->timestamps();
            });
        } else {
            Schema::table('groups', function (Blueprint $table) {
                if (!Schema::hasColumn('groups', 'permission_data')) {
                    $table->json('permission_data')->nullable()->after('name');
                }
                if (!Schema::hasColumn('groups', 'scope_data')) {
                    $table->json('scope_data')->nullable()->after('permission_data');
                }
            });
        }

        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->foreignId('group_id')->nullable()->constrained('groups')->nullOnDelete();
                $table->string('name');
                $table->string('email')->unique();
                $table->string('phone')->nullable();
                $table->string('avatar')->nullable();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->boolean('is_approve')->default(false)->comment('1: approved, 0: not approved');
                $table->boolean('is_super_admin')->default(false);
                $table->string('status')->default('active')->index();
                $table->unsignedTinyInteger('approval_level')->default(0);
                $table->unsignedTinyInteger('max_approval')->default(2);
                $table->boolean('is_draft')->default(false);
                $table->foreignId('main_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('status_approve')->default('pending');
                $table->rememberToken();
                $table->timestamps();
            });

            return;
        }

        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'group_id')) {
                $table->foreignId('group_id')->nullable()->after('id')->constrained('groups')->nullOnDelete();
            }
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->nullable()->after('email');
            }
            if (!Schema::hasColumn('users', 'avatar')) {
                $table->string('avatar')->nullable()->after('phone');
            }
            if (!Schema::hasColumn('users', 'is_approve')) {
                $table->boolean('is_approve')->default(false)->after('password')->comment('1: approved, 0: not approved');
            }
            if (!Schema::hasColumn('users', 'is_super_admin')) {
                $table->boolean('is_super_admin')->default(false)->after('is_approve');
            }
            if (!Schema::hasColumn('users', 'status')) {
                $table->string('status')->default('active')->after('is_super_admin')->index();
            }
            if (!Schema::hasColumn('users', 'approval_level')) {
                $table->unsignedTinyInteger('approval_level')->default(0)->after('status');
            }
            if (!Schema::hasColumn('users', 'max_approval')) {
                $table->unsignedTinyInteger('max_approval')->default(2)->after('approval_level');
            }
            if (!Schema::hasColumn('users', 'is_draft')) {
                $table->boolean('is_draft')->default(false)->after('max_approval');
            }
            if (!Schema::hasColumn('users', 'main_id')) {
                $table->foreignId('main_id')->nullable()->after('is_draft')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('users', 'status_approve')) {
                $table->string('status_approve')->default('pending')->after('main_id');
            }
        });
    }

    public function down(): void
    {
        // Intentionally left blank to avoid dropping legacy tenant user data on rollback.
    }
};
