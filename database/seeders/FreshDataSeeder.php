<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class FreshDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        $this->call([
            LanguageSeeder::class,
            FrontPageSeeder::class,
            UserSeeder::class,
            OrganizationSeeder::class,
            ContactSeeder::class,
            CategorySeeder::class,
            DepartmentSeeder::class,
            TypeSeeder::class,
            PrioritySeeder::class,
            StatusSeeder::class,
            KnowledgeBaseSeeder::class,
            FaqSeeder::class,
            BlogSeeder::class,
            TicketSeeder::class,
            ConversationSeeder::class,
            MessageSeeder::class,
            NoteSeeder::class,
        ]);
    }
}
