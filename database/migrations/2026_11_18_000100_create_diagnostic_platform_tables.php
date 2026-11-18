<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 32)->default('client')->after('email'); // admin | consultant | client
            $table->string('phone', 64)->nullable()->after('role');
            $table->string('job_title', 128)->nullable()->after('phone');
        });

        Schema::create('framework_versions', function (Blueprint $table) {
            $table->id();
            $table->string('version', 32)->unique();
            $table->string('name');
            $table->json('definition');
            $table->boolean('is_active')->default(false)->index();
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('ulid', 26)->unique();
            $table->string('name');
            $table->string('sector', 64)->default('general')->index();
            $table->string('stage', 32)->default('growth')->index();
            $table->string('size_band', 32)->nullable();
            $table->string('revenue_band', 32)->nullable();
            $table->string('country', 64)->default('Ghana');
            $table->string('city', 64)->nullable();
            $table->string('contact_name')->nullable();
            $table->string('contact_email')->nullable()->index();
            $table->string('contact_phone', 64)->nullable();
            $table->text('objectives')->nullable();
            $table->text('challenges')->nullable();
            $table->foreignId('owner_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('assigned_consultant_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('retention_policy', 32)->default('18_months');
            $table->timestamps();
        });

        Schema::create('company_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('company_role', 32)->default('owner'); // owner | member | viewer
            $table->timestamps();
            $table->unique(['company_id', 'user_id']);
        });

        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->string('ulid', 26)->unique();
            $table->string('access_token', 64)->unique();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('framework_version_id')->nullable()->constrained('framework_versions')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('mode', 32)->default('comprehensive'); // comprehensive | targeted | team
            $table->json('modules_in_scope');
            $table->json('symptoms')->nullable();
            $table->json('answers')->nullable();
            $table->text('custom_issue')->nullable();
            $table->json('score_snapshot')->nullable();
            $table->integer('health_index')->nullable()->index();
            $table->string('health_band', 64)->nullable();
            $table->boolean('ai_consent')->default(false);
            $table->timestamp('ai_consented_at')->nullable();
            $table->string('status', 32)->default('in_progress')->index(); // in_progress | scored | reviewed | published
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('team_invites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained('assessments')->cascadeOnDelete();
            $table->string('token', 64)->unique();
            $table->string('respondent_name');
            $table->string('respondent_email')->nullable();
            $table->string('respondent_role', 128)->nullable();
            $table->json('symptoms')->nullable();
            $table->json('answers')->nullable();
            $table->text('notes')->nullable();
            $table->string('status', 32)->default('pending'); // pending | submitted
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
        });

        Schema::create('diagnostic_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained('assessments')->cascadeOnDelete();
            $table->string('ai_provider', 32)->default('framework'); // openai | framework
            $table->string('ai_model', 64)->nullable();
            $table->json('ai_analysis');
            $table->json('consultant_amendments')->nullable();
            $table->text('executive_summary')->nullable();
            $table->text('consultant_notes')->nullable();
            $table->string('review_status', 32)->default('ai_draft')->index(); // ai_draft | under_review | approved | published
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        Schema::create('uploaded_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('assessment_id')->nullable()->constrained('assessments')->nullOnDelete();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('category', 64)->default('financial_statement'); // financial_statement | business_plan | management_accounts | governance_doc | other
            $table->string('original_name');
            $table->string('storage_path');
            $table->string('mime_type', 128)->nullable();
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->string('sha256', 64)->nullable();
            $table->longText('extracted_text')->nullable();
            $table->json('ai_extraction')->nullable();
            $table->string('extraction_status', 32)->default('pending'); // pending | analyzed | purged
            $table->timestamp('retention_expires_at')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('benchmarks', function (Blueprint $table) {
            $table->id();
            $table->string('sector', 64)->index();
            $table->string('stage', 32)->default('growth')->index();
            $table->string('module_key', 16)->index();
            $table->integer('p25_score');
            $table->integer('median_score');
            $table->integer('p75_score');
            $table->integer('top_decile_score')->default(85);
            $table->string('sample_note')->nullable();
            $table->timestamps();
            $table->unique(['sector', 'stage', 'module_key']);
        });

        Schema::create('action_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('assessment_id')->nullable()->constrained('assessments')->nullOnDelete();
            $table->string('module_key', 16)->index();
            $table->string('criterion_key', 16)->nullable();
            $table->string('programme_key', 32)->nullable();
            $table->string('title');
            $table->text('intervention')->nullable();
            $table->string('priority', 16)->default('High'); // Critical | High | Medium | Low
            $table->string('horizon', 32)->default('0-30 days'); // 0-30 days | 31-90 days | 90-180 days
            $table->string('owner_name', 128)->nullable();
            $table->string('target_metric')->nullable();
            $table->string('status', 32)->default('open')->index(); // open | in_progress | completed | blocked
            $table->integer('net_delta')->default(0);
            $table->date('due_date')->nullable();
            $table->timestamps();
        });

        Schema::create('action_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('action_item_id')->constrained('action_items')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->integer('delta')->default(0); // -1 | 0 | 1 | 2
            $table->text('note');
            $table->timestamps();
        });

        Schema::create('consultation_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('assessment_id')->nullable()->constrained('assessments')->nullOnDelete();
            $table->string('contact_name');
            $table->string('contact_email');
            $table->string('contact_phone', 64)->nullable();
            $table->string('recommended_programme')->nullable();
            $table->string('business_line')->nullable();
            $table->string('estimated_value_ghs')->nullable();
            $table->string('preferred_channel', 32)->default('video'); // video | in_person_accra | phone
            $table->string('preferred_window', 128)->nullable();
            $table->text('client_message')->nullable();
            $table->string('status', 32)->default('new')->index(); // new | contacted | discovery_booked | proposal_sent | active_engagement | closed
            $table->foreignId('assigned_consultant_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('internal_notes')->nullable();
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->foreignId('assessment_id')->nullable()->constrained('assessments')->nullOnDelete();
            $table->string('event', 64)->index();
            $table->string('actor_label')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('consultation_requests');
        Schema::dropIfExists('action_logs');
        Schema::dropIfExists('action_items');
        Schema::dropIfExists('benchmarks');
        Schema::dropIfExists('uploaded_documents');
        Schema::dropIfExists('diagnostic_reports');
        Schema::dropIfExists('team_invites');
        Schema::dropIfExists('assessments');
        Schema::dropIfExists('company_user');
        Schema::dropIfExists('companies');
        Schema::dropIfExists('framework_versions');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'phone', 'job_title']);
        });
    }
};
