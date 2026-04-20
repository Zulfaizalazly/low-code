<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('ic_number')->unique();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('customer_type')->default('individual');
            $table->string('status')->default('active');
            $table->unsignedBigInteger('entity_id');
            $table->unsignedBigInteger('branch_id');
            $table->timestamps();
            $table->index(['ic_number', 'status']);
            $table->index(['entity_id', 'branch_id']);
        });

        // 2. customer_contacts
        Schema::create('customer_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('contact_type'); // phone, email, whatsapp
            $table->string('value');
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
            $table->index(['customer_id', 'contact_type']);
        });

        // 3. facilities
        Schema::create('facilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained();
            $table->string('product_code');
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('entity_id');
            $table->string('facility_number')->unique();
            $table->decimal('principal_amount', 14, 2)->default(0);
            $table->integer('tenure_months')->default(6);
            $table->decimal('profit_rate', 8, 4)->default(0);
            $table->string('status')->default('draft');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('disbursed_at')->nullable();
            $table->timestamp('matured_at')->nullable();
            $table->timestamps();
            $table->index(['customer_id', 'status']);
            $table->index(['branch_id', 'status']);
            $table->index(['facility_number']);
        });

        // 4. facility_items
        Schema::create('facility_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facility_id')->constrained()->cascadeOnDelete();
            $table->string('item_type'); // gold_jewellery, gold_bar, gold_coin
            $table->string('description')->nullable();
            $table->decimal('weight_grams', 10, 4);
            $table->decimal('purity', 5, 2); // e.g., 91.60 for 916
            $table->decimal('valuation_amount', 14, 2)->default(0);
            $table->string('status')->default('active');
            $table->string('vault_location')->nullable();
            $table->timestamps();
            $table->index(['facility_id', 'status']);
        });

        // 5. facility_nominees
        Schema::create('facility_nominees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facility_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('ic_number');
            $table->string('relationship'); // spouse, child, parent, sibling, other
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
            $table->index(['facility_id']);
        });

        // 6. valuations
        Schema::create('valuations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facility_id')->constrained();
            $table->unsignedBigInteger('facility_item_id')->nullable();
            $table->decimal('gold_price_per_gram', 14, 2);
            $table->decimal('weight_grams', 10, 4);
            $table->decimal('purity_percentage', 5, 2);
            $table->decimal('gross_value', 14, 2);
            $table->decimal('ltv_percentage', 5, 2);
            $table->decimal('valuation_amount', 14, 2);
            $table->unsignedBigInteger('valued_by')->nullable();
            $table->timestamp('valued_at')->nullable();
            $table->timestamps();
            $table->index(['facility_id']);
        });

        // 7. approval_tasks
        Schema::create('approval_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('approvable_type');
            $table->unsignedBigInteger('approvable_id');
            $table->string('approval_tier'); // tier_1, tier_2, tier_3
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->string('assigned_role')->nullable();
            $table->string('status')->default('pending'); // pending, approved, rejected, escalated
            $table->string('decision')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();
            $table->index(['approvable_type', 'approvable_id']);
            $table->index(['assigned_to', 'status']);
            $table->index(['status']);
        });

        // 8. payment_transactions
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facility_id')->constrained();
            $table->string('payment_type'); // disbursement, repayment, profit, penalty, refund
            $table->decimal('amount', 14, 2);
            $table->string('payment_method')->nullable(); // cash, bank_transfer, cheque
            $table->string('reference_number')->nullable();
            $table->unsignedBigInteger('received_by')->nullable();
            $table->unsignedBigInteger('branch_id');
            $table->string('status')->default('pending'); // pending, completed, failed, reversed
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->index(['facility_id', 'payment_type']);
            $table->index(['branch_id', 'status']);
        });

        // 9. journal_entries
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->string('entry_number')->unique();
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('posted_by')->nullable();
            $table->timestamp('posted_at')->nullable();
            $table->boolean('is_balanced')->default(false);
            $table->timestamps();
            $table->index(['reference_type', 'reference_id']);
            $table->index(['entry_number']);
        });

        // 10. journal_entry_lines
        Schema::create('journal_entry_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_entry_id')->constrained()->cascadeOnDelete();
            $table->string('account_code');
            $table->string('account_name');
            $table->decimal('debit_amount', 14, 2)->default(0);
            $table->decimal('credit_amount', 14, 2)->default(0);
            $table->text('description')->nullable();
            $table->timestamps();
            $table->index(['journal_entry_id']);
            $table->index(['account_code']);
        });

        // 11. documents
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('documentable_type');
            $table->unsignedBigInteger('documentable_id');
            $table->string('document_type'); // contract, receipt, letter, report
            $table->unsignedBigInteger('template_id')->nullable();
            $table->integer('template_version')->nullable();
            $table->string('file_path');
            $table->string('file_name');
            $table->unsignedBigInteger('generated_by')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();
            $table->index(['documentable_type', 'documentable_id']);
        });

        // 12. notification_logs
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->string('notifiable_type');
            $table->unsignedBigInteger('notifiable_id');
            $table->string('channel'); // sms, email, push, whatsapp
            $table->string('recipient');
            $table->string('subject')->nullable();
            $table->text('body')->nullable();
            $table->string('status')->default('pending'); // pending, sent, delivered, failed
            $table->timestamp('sent_at')->nullable();
            $table->text('failed_reason')->nullable();
            $table->timestamps();
            $table->index(['notifiable_type', 'notifiable_id']);
            $table->index(['channel', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
        Schema::dropIfExists('documents');
        Schema::dropIfExists('journal_entry_lines');
        Schema::dropIfExists('journal_entries');
        Schema::dropIfExists('payment_transactions');
        Schema::dropIfExists('approval_tasks');
        Schema::dropIfExists('valuations');
        Schema::dropIfExists('facility_nominees');
        Schema::dropIfExists('facility_items');
        Schema::dropIfExists('facilities');
        Schema::dropIfExists('customer_contacts');
        Schema::dropIfExists('customers');
    }
};
