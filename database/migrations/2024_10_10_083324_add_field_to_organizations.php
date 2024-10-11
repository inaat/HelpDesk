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
        Schema::table('organizations', function (Blueprint $table) {
       $table->string('name_en')->after('name')->nullable();
        $table->string('customer_no', '50')->after('id')->nullable();
       $table->string('contact_person_1')->nullable()->after('phone'); // Assuming 'name' is a valid existing column
       $table->string('contact_person_2')->nullable()->after('contact_person_1');
       $table->string('add_1')->nullable()->after('contact_person_2');
       $table->string('add_2')->nullable()->after('add_1');
       $table->string('add_3')->nullable()->after('add_2');
       $table->string('add_4')->nullable()->after('add_3');
       $table->string('phone_1')->nullable()->after('add_4');
       $table->string('phone_2')->nullable()->after('phone_1');
       $table->string('phone_3')->nullable()->after('phone_2');
       $table->string('fax_1')->nullable()->after('phone_3');
       $table->string('fax_2')->nullable()->after('fax_1');
       $table->string('mobile_1')->nullable()->after('fax_2');
       $table->string('mobile_2')->nullable()->after('mobile_1');
       $table->string('web_site')->nullable()->after('mobile_2');
       $table->string('country', )->change();
        });
      
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
       
    }
};
