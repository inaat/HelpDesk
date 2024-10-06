<?php

namespace Database\Seeders;

use App\Models\Note;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NoteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('notes')->truncate();
        $users = User::limit(4)->orderBy('id')->get();
        Note::factory(45)->create()->each(function ($post) use ($users){
            $post->update(['user_id' => $users->random()->id]);
        });
    }
}
