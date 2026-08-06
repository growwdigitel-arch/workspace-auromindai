<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('employee_details', 'verification_key')) {
            Schema::table('employee_details', function (Blueprint $table) {
                $table->string('verification_key', 32)->nullable()->after('employee_id')->unique();
            });
        }

        DB::table('employee_details')
            ->select('id')
            ->whereNull('verification_key')
            ->orderBy('id')
            ->chunkById(100, function ($employees) {
                foreach ($employees as $employee) {
                    do {
                        $key = Str::upper(Str::random(8));
                    } while (DB::table('employee_details')->where('verification_key', $key)->exists());

                    DB::table('employee_details')
                        ->where('id', $employee->id)
                        ->update(['verification_key' => $key]);
                }
            });
    }

    public function down(): void
    {
        if (Schema::hasColumn('employee_details', 'verification_key')) {
            Schema::table('employee_details', function (Blueprint $table) {
                $table->dropUnique(['verification_key']);
                $table->dropColumn('verification_key');
            });
        }
    }
};
