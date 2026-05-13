<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Subscription metadata on users — Cashier-compatible column shape.
 *
 * When Laravel Cashier is installed, its Billable trait reads these
 * columns directly. When Cashier is NOT installed, the SubscriptionController
 * still has the data it needs to display plan state and gate features.
 *
 *   stripe_id                    Stripe customer ID (cus_…)
 *   pm_type                      Last-used payment method type (card, etc.)
 *   pm_last_four                 Last four of the saved payment method
 *   trial_ends_at                Datetime; null if no trial active
 *
 * Subscription itself lives in a separate `subscriptions` table that
 * Cashier owns. We create a thin stand-in here so the app boots without
 * Cashier installed; once Cashier is added, drop this table and run
 * `cashier:install` instead.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('stripe_id')->nullable()->index()->after('plan');
            $table->string('pm_type')->nullable()->after('stripe_id');
            $table->string('pm_last_four', 4)->nullable()->after('pm_type');
            $table->timestamp('trial_ends_at')->nullable()->after('pm_last_four');
        });

        // Lightweight stand-in for Cashier's subscriptions table.
        // When you `composer require laravel/cashier` and run
        // `php artisan cashier:install`, drop this and use Cashier's
        // canonical schema.
        if (! Schema::hasTable('subscriptions')) {
            Schema::create('subscriptions', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('type')->default('default'); // "default" matches Cashier convention
                $table->string('stripe_id')->unique();
                $table->string('stripe_status');           // active | trialing | past_due | canceled | incomplete
                $table->string('stripe_price')->nullable();
                $table->integer('quantity')->nullable();
                $table->timestamp('trial_ends_at')->nullable();
                $table->timestamp('ends_at')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'stripe_status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['stripe_id', 'pm_type', 'pm_last_four', 'trial_ends_at']);
        });
    }
};
