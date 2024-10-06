<?php

namespace Database\Seeders;

use App\Models\KnowledgeBase;
use App\Models\Type;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KnowledgeBaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        DB::table('knowledge_base')->truncate();
        $types = Type::limit(5)->get();
        KnowledgeBase::factory(25)->create()->each(function ($kb) use($types){
            $kb->update(['type_id' => $types->random()->id]);
        });
    }
}
