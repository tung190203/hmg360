<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = [
        'legal_document',
        'posts',
        'investment_guides',
    ];

    private function anchorColumn(string $tableName): string
    {
        return match ($tableName) {
            'legal_document' => 'download',
            default => 'content',
        };
    }

    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                $anchor = $this->anchorColumn($tableName);

                if (!Schema::hasColumn($tableName, 'extracted_text')) {
                    $table->longText('extracted_text')->nullable()->after($anchor);
                }
                if (!Schema::hasColumn($tableName, 'extracted_summary')) {
                    $table->longText('extracted_summary')->nullable()->after('extracted_text');
                }
                if (!Schema::hasColumn($tableName, 'extracted_language')) {
                    $table->string('extracted_language', 10)->nullable()->after('extracted_summary');
                }
                if (!Schema::hasColumn($tableName, 'extracted_at')) {
                    $table->dateTime('extracted_at')->nullable()->after('extracted_language');
                }
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                foreach (['extracted_text', 'extracted_summary', 'extracted_language', 'extracted_at'] as $column) {
                    if (Schema::hasColumn($tableName, $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
