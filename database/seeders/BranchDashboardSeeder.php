<?php

namespace Database\Seeders;

use App\Models\Branch\ChangeDeployment;
use App\Models\Branch\FeatureAccessLog;
use App\Models\Branch\FeatureHealthCheck;
use App\Models\Branch\SupportTicket;
use App\Models\User;
use App\Studio\Registry\Feature;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class BranchDashboardSeeder extends Seeder
{
    /**
     * Seed realistic test data for the branch operations dashboard.
     */
    public function run(): void
    {
        // ─── Resolve existing data ───

        // Get branch staff users (users with role 'branch_staff' or those with branch_id set)
        $staffUsers = User::where('role', 'branch_staff')->get();
        if ($staffUsers->isEmpty()) {
            $staffUsers = User::whereNotNull('branch_id')->get();
        }

        if ($staffUsers->isEmpty()) {
            $this->command->info('BranchDashboardSeeder: No branch staff users found. Skipping.');
            return;
        }

        // Get published features, fallback to all features
        $features = Feature::where('status', 'published')->get();
        if ($features->isEmpty()) {
            $features = Feature::all();
        }

        if ($features->isEmpty()) {
            $this->command->info('BranchDashboardSeeder: No features found. Skipping.');
            return;
        }

        // Get branches from staff user branch_ids
        $branchIds = $staffUsers->pluck('branch_id')->filter()->unique()->values();

        // Get admin user for deployed_by
        $admin = User::where('email', 'admin@arrahnu.com')->first();
        $deployedBy = $admin?->id ?? $staffUsers->first()->id;

        $this->command->info('BranchDashboardSeeder: Generating data...');
        $this->command->info("  Staff users: {$staffUsers->count()}, Features: {$features->count()}, Branches: {$branchIds->count()}");

        // ─── 1. FeatureAccessLog records (5-15 per staff member) ───

        $weekStart = now()->startOfWeek();
        $now = now();
        $accessLogCount = 0;

        foreach ($staffUsers as $user) {
            $count = rand(5, 15);
            for ($i = 0; $i < $count; $i++) {
                $accessedAt = Carbon::createFromTimestamp(
                    rand($weekStart->timestamp, $now->timestamp)
                );

                FeatureAccessLog::create([
                    'user_id' => $user->id,
                    'feature_id' => $features->random()->id,
                    'feature_version_id' => null,
                    'branch_id' => $user->branch_id,
                    'access_type' => 'view',
                    'session_duration_seconds' => rand(30, 600),
                    'accessed_at' => $accessedAt,
                ]);
                $accessLogCount++;
            }
        }

        $this->command->info("  Created {$accessLogCount} FeatureAccessLog records.");

        // ─── 2. FeatureHealthCheck records (2-4) ───

        $healthCheckCount = rand(2, 4);
        $healthStatuses = ['available', 'degraded', 'unavailable'];

        // Ensure at least one "available" with resolved_at set
        FeatureHealthCheck::create([
            'feature_id' => $features->random()->id,
            'status' => 'available',
            'error_message' => null,
            'checked_at' => now()->subHours(rand(1, 12)),
            'resolved_at' => now()->subMinutes(rand(10, 120)),
            'resolution_note' => 'Issue resolved after server restart.',
            'checked_by' => $deployedBy,
        ]);

        // Ensure at least one "degraded" with resolved_at null (unresolved)
        FeatureHealthCheck::create([
            'feature_id' => $features->random()->id,
            'status' => 'degraded',
            'error_message' => 'Intermittent timeout on form submission endpoint.',
            'checked_at' => now()->subMinutes(rand(15, 90)),
            'resolved_at' => null,
            'resolution_note' => null,
            'checked_by' => $deployedBy,
        ]);

        // Additional random health checks
        for ($i = 2; $i < $healthCheckCount; $i++) {
            $status = $healthStatuses[array_rand($healthStatuses)];
            $isResolved = $status === 'available' ? true : (bool) rand(0, 1);

            FeatureHealthCheck::create([
                'feature_id' => $features->random()->id,
                'status' => $status,
                'error_message' => $status !== 'available' ? 'Elevated error rate detected in processing pipeline.' : null,
                'checked_at' => now()->subHours(rand(1, 48)),
                'resolved_at' => $isResolved ? now()->subMinutes(rand(5, 300)) : null,
                'resolution_note' => $isResolved ? 'Resolved by IT team.' : null,
                'checked_by' => $deployedBy,
            ]);
        }

        $this->command->info("  Created {$healthCheckCount} FeatureHealthCheck records.");

        // ─── 3. ChangeDeployment records (3-5) ───

        $deploymentCount = rand(3, 5);
        $changeSummaries = [
            'Updated form validation rules for pledge intake',
            'Added new payment gateway support',
            'Improved search performance for customer lookup',
            'Fixed calculation rounding in valuation module',
            'Enhanced security for document upload process',
            'Added multi-language support for receipt printing',
            'Optimized database queries for facility listing',
        ];

        // Ensure at least one deployed within last 24 hours (for "New" badge)
        $randomFeature = $features->random();
        $featureVersionId = $randomFeature->currentVersion?->id
            ?? \App\Studio\Registry\FeatureVersion::where('feature_id', $randomFeature->id)->first()?->id;

        ChangeDeployment::create([
            'feature_id' => $randomFeature->id,
            'feature_version_id' => $featureVersionId,
            'deployed_by' => $deployedBy,
            'deployed_at' => now()->subHours(rand(1, 12)),
            'change_summary' => $changeSummaries[array_rand($changeSummaries)],
            'is_visible_to_branches' => true,
            'notified_at' => now()->subHours(rand(0, 6)),
        ]);

        // Remaining deployments
        for ($i = 1; $i < $deploymentCount; $i++) {
            $randomFeature = $features->random();
            $featureVersionId = $randomFeature->currentVersion?->id
                ?? \App\Studio\Registry\FeatureVersion::where('feature_id', $randomFeature->id)->first()?->id;

            ChangeDeployment::create([
                'feature_id' => $randomFeature->id,
                'feature_version_id' => $featureVersionId,
                'deployed_by' => $deployedBy,
                'deployed_at' => now()->subDays(rand(1, 6))->subHours(rand(0, 12)),
                'change_summary' => $changeSummaries[array_rand($changeSummaries)],
                'is_visible_to_branches' => true,
                'notified_at' => now()->subDays(rand(0, 5)),
            ]);
        }

        $this->command->info("  Created {$deploymentCount} ChangeDeployment records.");

        // ─── 4. SupportTicket records (3-5) ───

        $ticketCount = rand(3, 5);
        $categories = ['bug', 'feature_request', 'issue'];
        $priorities = ['low', 'medium', 'high', 'critical'];

        // Ensure at least one "open" ticket
        $openUser = $staffUsers->random();
        SupportTicket::create([
            'user_id' => $openUser->id,
            'branch_id' => $openUser->branch_id,
            'title' => 'Form submission timeout during peak hours',
            'description' => 'The pledge intake form times out when multiple staff submit simultaneously during morning rush.',
            'category' => 'bug',
            'priority' => 'high',
            'status' => 'open',
            'context_json' => ['page' => 'pledge-intake', 'browser' => 'Chrome 120'],
        ]);

        // Ensure at least one "resolved" ticket with response_note
        $resolvedUser = $staffUsers->random();
        SupportTicket::create([
            'user_id' => $resolvedUser->id,
            'branch_id' => $resolvedUser->branch_id,
            'title' => 'Request for bulk customer export feature',
            'description' => 'Need ability to export customer list to Excel for monthly reporting to regional office.',
            'category' => 'feature_request',
            'priority' => 'medium',
            'status' => 'resolved',
            'context_json' => ['module' => 'customer-management'],
            'it_responder_id' => $deployedBy,
            'response_note' => 'Export feature has been added to the customer listing page. Please refresh and check the new "Export" button.',
            'responded_at' => now()->subDays(1),
            'resolved_at' => now()->subHours(12),
        ]);

        // Additional random tickets
        $ticketTitles = [
            'Printer not connecting to receipt module',
            'Dashboard loading slowly after update',
            'Need training on new valuation workflow',
            'Error when scanning barcode for vault items',
            'Request for dark mode in branch portal',
        ];

        for ($i = 2; $i < $ticketCount; $i++) {
            $ticketUser = $staffUsers->random();
            $status = ['open', 'in_progress', 'resolved', 'closed'][array_rand(['open', 'in_progress', 'resolved', 'closed'])];

            SupportTicket::create([
                'user_id' => $ticketUser->id,
                'branch_id' => $ticketUser->branch_id,
                'title' => $ticketTitles[array_rand($ticketTitles)],
                'description' => 'Detailed description of the issue encountered during daily branch operations.',
                'category' => $categories[array_rand($categories)],
                'priority' => $priorities[array_rand($priorities)],
                'status' => $status,
                'context_json' => null,
                'it_responder_id' => in_array($status, ['resolved', 'closed']) ? $deployedBy : null,
                'response_note' => in_array($status, ['resolved', 'closed']) ? 'Issue has been addressed by the IT team.' : null,
                'responded_at' => in_array($status, ['resolved', 'closed']) ? now()->subDays(rand(0, 2)) : null,
                'resolved_at' => $status === 'resolved' ? now()->subHours(rand(1, 48)) : null,
            ]);
        }

        $this->command->info("  Created {$ticketCount} SupportTicket records.");
        $this->command->info('BranchDashboardSeeder: Done!');
    }
}
