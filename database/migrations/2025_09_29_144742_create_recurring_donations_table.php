<?php

use App\Models\User;
use App\RecurringDonationStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('recurring_donations', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class);
            $table->decimal('amount', total: 8, places: 2);
            $table->string('currency', length: 3);
            $table->string('schedule');
            $table->timestampTz('start_date');
            $table->timestampTz('end_date')->nullable();
            $table->enum(
                'status',
                array_column(RecurringDonationStatus::cases(), 'value')
            )->default(RecurringDonationStatus::ACTIVE->value);
            $table->timestamps();
        });

        // Add a check constraint for cron format: ^([*d/,-]+s){4}[*d/,-]+$
        DB::statement(
           "ALTER TABLE recurring_donations
            ADD CONSTRAINT chk_schedule_cron_format
            CHECK (schedule REGEXP '^([\\*\\d\\/,\\-]+\\s){4}[\\*\\d\\/,\\-]+$')"
        );

        Schema::table('donations', function (Blueprint $table) {
            $table->foreignId('recurring_donation_id')
                ->nullable()
                ->constrained('recurring_donations')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('donations', function (Blueprint $table) {
            $table->dropForeign(['recurring_donation_id']);
            $table->dropColumn('recurring_donation_id');
        });

        Schema::dropIfExists('recurring_donations');
    }
};
