<?php

namespace Database\Seeders;

use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MessageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('messages')->truncate();
        $conversations = Conversation::limit(20)->get();
        foreach ($conversations as $conversation){
            Message::factory(2)->create()->each(function ($message) use($conversation){
                $message->update(['guid' => floor(rand(500,999)*10000) + $message->id]);
                $message->update(['conversation_id' => $conversation->id]);
                $message->update(['contact_id' => $conversation->contact_id]);
            });
        }
    }
}
