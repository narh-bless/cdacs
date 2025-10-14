<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Announcement;
use App\Models\Event;
use App\Models\Contribution;
use App\Models\Donation;
use App\Models\Sermon;
use App\Models\TeachingNote;
use App\Models\User;

class SampleDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pastor = User::where('email', 'pastor@church.com')->first();
        $admin = User::where('email', 'admin@church.com')->first();
        $financeMember = User::where('email', 'finance@church.com')->first();
        $members = User::whereHas('roles', function ($query) {
            $query->where('name', 'member');
        })->get();

        // Create sample announcements
        $announcements = [
            [
                'title' => 'Welcome to Our New Members!',
                'content' => 'We are excited to welcome our new members who joined us this month. Please make them feel at home and introduce yourselves.',
                'type' => 'general',
                'priority' => 'medium',
                'is_published' => true,
                'published_at' => now()->subDays(2),
                'author_id' => $pastor->id,
            ],
            [
                'title' => 'Annual Church Conference',
                'content' => 'Join us for our annual church conference on March 15-17. Registration is now open. Early bird pricing available until February 28.',
                'type' => 'event',
                'priority' => 'high',
                'is_published' => true,
                'published_at' => now()->subDays(5),
                'expires_at' => now()->addDays(30),
                'author_id' => $pastor->id,
            ],
            [
                'title' => 'Building Fund Update',
                'content' => 'Thank you for your generous contributions to our building fund. We have reached 75% of our goal. Let\'s continue to work together.',
                'type' => 'financial',
                'priority' => 'medium',
                'is_published' => true,
                'published_at' => now()->subDays(7),
                'author_id' => $financeMember->id,
            ],
            [
                'title' => 'Prayer Request',
                'content' => 'Please keep the Johnson family in your prayers as they go through a difficult time. Prayer meeting will be held this Wednesday.',
                'type' => 'prayer',
                'priority' => 'urgent',
                'is_published' => true,
                'published_at' => now()->subHours(12),
                'author_id' => $pastor->id,
            ],
        ];

        foreach ($announcements as $announcement) {
            Announcement::create($announcement);
        }

        // Create sample events
        $events = [
            [
                'title' => 'Sunday Service',
                'description' => 'Weekly Sunday worship service',
                'start_date' => now()->nextWeekday(0)->setTime(10, 0),
                'end_date' => now()->nextWeekday(0)->setTime(12, 0),
                'location' => 'Main Sanctuary',
                'type' => 'service',
                'status' => 'published',
                'requires_registration' => false,
                'organizer_id' => $pastor->id,
            ],
            [
                'title' => 'Youth Group Meeting',
                'description' => 'Weekly youth group gathering with games, discussion, and fellowship',
                'start_date' => now()->nextWeekday(5)->setTime(19, 0),
                'end_date' => now()->nextWeekday(5)->setTime(21, 0),
                'location' => 'Youth Room',
                'type' => 'ministry',
                'status' => 'published',
                'requires_registration' => true,
                'max_attendees' => 30,
                'organizer_id' => $pastor->id,
            ],
            [
                'title' => 'Community Outreach Day',
                'description' => 'Join us as we serve our community through various outreach activities',
                'start_date' => now()->addDays(14)->setTime(9, 0),
                'end_date' => now()->addDays(14)->setTime(15, 0),
                'location' => 'Community Center',
                'type' => 'outreach',
                'status' => 'published',
                'requires_registration' => true,
                'max_attendees' => 50,
                'organizer_id' => $pastor->id,
            ],
            [
                'title' => 'Church Board Meeting',
                'description' => 'Monthly church board meeting to discuss church business and planning',
                'start_date' => now()->addDays(7)->setTime(18, 0),
                'end_date' => now()->addDays(7)->setTime(20, 0),
                'location' => 'Conference Room',
                'type' => 'meeting',
                'status' => 'published',
                'requires_registration' => false,
                'organizer_id' => $admin->id,
            ],
        ];

        foreach ($events as $event) {
            Event::create($event);
        }

        // Create sample contributions
        $contributionTypes = ['tithe', 'offering', 'donation', 'building_fund', 'mission'];
        $paymentMethods = ['cash', 'check', 'card', 'bank_transfer'];

        foreach ($members as $member) {
            for ($i = 0; $i < rand(5, 15); $i++) {
                Contribution::create([
                    'user_id' => $member->id,
                    'type' => $contributionTypes[array_rand($contributionTypes)],
                    'amount' => rand(50, 500),
                    'currency' => 'USD',
                    'payment_method' => $paymentMethods[array_rand($paymentMethods)],
                    'description' => 'Monthly contribution',
                    'contribution_date' => now()->subDays(rand(1, 90)),
                    'status' => 'confirmed',
                    'recorded_by' => $financeMember->id,
                ]);
            }
        }

        // Create sample donations
        $donationTypes = ['general', 'building_fund', 'mission', 'special_project'];
        $donorNames = ['Anonymous', 'John Smith', 'Mary Johnson', 'David Brown', 'Sarah Wilson'];

        for ($i = 0; $i < 20; $i++) {
            Donation::create([
                'user_id' => rand(0, 1) ? $members->random()->id : null,
                'donor_name' => $donorNames[array_rand($donorNames)],
                'donor_email' => rand(0, 1) ? 'donor' . $i . '@example.com' : null,
                'type' => $donationTypes[array_rand($donationTypes)],
                'amount' => rand(100, 1000),
                'currency' => 'USD',
                'payment_method' => $paymentMethods[array_rand($paymentMethods)],
                'description' => 'Generous donation',
                'donation_date' => now()->subDays(rand(1, 60)),
                'status' => 'confirmed',
                'is_anonymous' => rand(0, 1),
                'recorded_by' => $financeMember->id,
            ]);
        }

        // Create sample sermons
        $sermons = [
            [
                'title' => 'The Power of Faith',
                'content' => 'A message about the importance of faith in our daily lives and how it can transform our circumstances.',
                'scripture_references' => 'Hebrews 11:1, Matthew 17:20',
                'sermon_date' => now()->subDays(7),
                'preacher_id' => $pastor->id,
                'status' => 'published',
            ],
            [
                'title' => 'Walking in Love',
                'content' => 'Learning to love others as Christ loved us and the impact it has on our community.',
                'scripture_references' => '1 Corinthians 13:4-7, John 13:34-35',
                'sermon_date' => now()->subDays(14),
                'preacher_id' => $pastor->id,
                'status' => 'published',
            ],
            [
                'title' => 'Hope in Difficult Times',
                'content' => 'Finding hope and strength when facing life\'s challenges and trials.',
                'scripture_references' => 'Romans 8:28, Jeremiah 29:11',
                'sermon_date' => now()->subDays(21),
                'preacher_id' => $pastor->id,
                'status' => 'published',
            ],
        ];

        foreach ($sermons as $sermon) {
            Sermon::create($sermon);
        }

        // Create sample teaching notes
        $teachingNotes = [
            [
                'title' => 'Understanding Prayer',
                'content' => 'A comprehensive study on the different types of prayer and how to develop a meaningful prayer life.',
                'scripture_references' => 'Matthew 6:9-13, Philippians 4:6-7',
                'teaching_date' => now()->subDays(10),
                'teacher_id' => $pastor->id,
                'status' => 'published',
            ],
            [
                'title' => 'The Fruit of the Spirit',
                'content' => 'Exploring the nine fruits of the Spirit and how to cultivate them in our daily lives.',
                'scripture_references' => 'Galatians 5:22-23',
                'teaching_date' => now()->subDays(17),
                'teacher_id' => $pastor->id,
                'status' => 'published',
            ],
        ];

        foreach ($teachingNotes as $note) {
            TeachingNote::create($note);
        }
    }
}
