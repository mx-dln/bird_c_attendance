<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // 0=AM_IN, 1=AM_OUT, 2=PM_IN, 3=PM_OUT
            $table->tinyInteger('session')->nullable()->after('type')->comment('0=AM_IN,1=AM_OUT,2=PM_IN,3=PM_OUT');
            $table->index(['emp_id', 'attendance_date', 'session'], 'attendances_emp_date_session_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex('attendances_emp_date_session_idx');
            $table->dropColumn('session');
        });
    }
};
