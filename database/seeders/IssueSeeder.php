<?php

namespace Database\Seeders;

use App\Models\Issue;
use App\Services\IssueAIService;
use Illuminate\Database\Seeder;

class IssueSeeder extends Seeder
{
    public function run(): void
    {
        $ai = app(IssueAIService::class);

        $issues = [
            [
                'title'       => 'Production database connection pool exhausted',
                'description' => 'The main PostgreSQL connection pool is consistently hitting its maximum limit (100 connections) during peak hours between 09:00–11:00 UTC. Queries are timing out and users are seeing 503 errors on the checkout page. This started occurring after the traffic spike from the Black Friday campaign.',
                'priority'    => 'critical',
                'category'    => 'infrastructure',
                'status'      => 'open',
                'created_at'  => now()->subHours(26), // older than 24h → will be escalated
            ],
            [
                'title'       => 'XSS vulnerability in comment rendering',
                'description' => 'User-supplied HTML in the ticket comment field is rendered without sanitisation. A proof-of-concept shows it is possible to inject a script tag that executes arbitrary JS in the context of other logged-in users, enabling session hijacking.',
                'priority'    => 'critical',
                'category'    => 'security',
                'status'      => 'in_progress',
                'created_at'  => now()->subHours(3),
            ],
            [
                'title'       => 'CSV export silently drops rows with unicode characters',
                'description' => 'When exporting the customer list to CSV, any row where the customer name contains non-ASCII characters (e.g. accented letters, CJK) is silently omitted from the output file. Confirmed on Windows with Excel; macOS users are unaffected.',
                'priority'    => 'high',
                'category'    => 'bug',
                'status'      => 'open',
                'created_at'  => now()->subHours(30), // older than 24h → escalated
            ],
            [
                'title'       => 'Add bulk-assign owner to multiple tickets',
                'description' => 'Support agents frequently need to reassign batches of tickets when a team member is out of office. Currently they must open each ticket individually. A checkbox-select + bulk-action dropdown on the ticket list would save significant time.',
                'priority'    => 'medium',
                'category'    => 'feature',
                'status'      => 'open',
                'created_at'  => now()->subDays(2),
            ],
            [
                'title'       => 'Nightly backup job failing on node-2',
                'description' => 'The cron job that snapshots the application database to S3 has been failing silently on node-2 since the OS upgrade on 2026-04-20. The script exits with code 1 but no alert fires because the monitoring agent was not restarted after the upgrade.',
                'priority'    => 'high',
                'category'    => 'infrastructure',
                'status'      => 'open',
                'created_at'  => now()->subHours(5),
            ],
            [
                'title'       => 'Password reset emails landing in spam',
                'description' => 'Multiple customers have reported that password reset emails are being classified as spam by Gmail and Outlook. SPF and DKIM records appear correctly configured; suspect the issue is with the shared IP reputation of our transactional email provider.',
                'priority'    => 'medium',
                'category'    => 'bug',
                'status'      => 'in_progress',
                'created_at'  => now()->subDays(1),
            ],
            [
                'title'       => 'Dashboard slow to load for accounts with >10k tickets',
                'description' => 'Enterprise accounts with large ticket volumes experience load times exceeding 12 seconds on the main dashboard. Profiling shows an N+1 query problem on the ticket statistics widget – it fires one query per ticket rather than aggregating in a single query.',
                'priority'    => 'high',
                'category'    => 'bug',
                'status'      => 'open',
                'created_at'  => now()->subHours(48), // >24h high + open → escalated
            ],
            [
                'title'       => 'Add dark-mode support to the web app',
                'description' => 'Several power users have requested a dark mode option. A system-preference-aware CSS media query approach would satisfy most users without requiring a manual toggle, though a persistent user preference toggle would be ideal.',
                'priority'    => 'low',
                'category'    => 'feature',
                'status'      => 'open',
                'created_at'  => now()->subDays(5),
            ],
            [
                'title'       => 'API rate-limit headers missing from responses',
                'description' => 'The REST API does not return X-RateLimit-Limit or X-RateLimit-Remaining headers. Third-party integrators are building their own throttling logic but have no reliable signal for when to back off, leading to avoidable 429 errors.',
                'priority'    => 'medium',
                'category'    => 'feature',
                'status'      => 'resolved',
                'created_at'  => now()->subDays(3),
            ],
            [
                'title'       => 'Outdated TLS 1.0/1.1 still accepted on the API gateway',
                'description' => 'A TLS scan reveals that the API gateway still accepts TLS 1.0 and 1.1 connections. PCI-DSS 3.2.1 requires these to be disabled. Customers on enterprise plans have flagged this in their annual security questionnaires.',
                'priority'    => 'high',
                'category'    => 'security',
                'status'      => 'open',
                'created_at'  => now()->subHours(2),
            ],
        ];

        foreach ($issues as $data) {
            $createdAt = $data['created_at'];
            unset($data['created_at']);

            $generated = $ai->generate(
                $data['title'],
                $data['description'],
                $data['priority'],
                $data['category']
            );

            $issue = Issue::create([
                ...$data,
                'summary'        => $generated['summary'],
                'next_action'    => $generated['next_action'],
                'summary_source' => $generated['source'],
                'created_at'     => $createdAt,
                'updated_at'     => $createdAt,
            ]);

            if ($issue->needsEscalation()) {
                $issue->escalate();
            }
        }

        $this->command->info('Seeded ' . count($issues) . ' demo issues.');
    }
}
