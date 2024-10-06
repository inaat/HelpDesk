<?php

namespace Database\Seeders;

use App\Models\Blog;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BlogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('posts')->truncate();
        $users = User::limit(4)->orderBy('id')->get();
        $types = Category::limit(5)->orderBy('id')->get();
        Blog::factory(45)->create()->each(function ($post) use ($types, $users){
            $post->update(['type_id' => $types->random()->id]);
            $post->update(['author_id' => $users->random()->id]);
        });
    }
}
