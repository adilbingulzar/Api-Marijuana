<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSupportFormsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('support_forms', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['member', 'app']); // Form type: member or app support
            $table->string('name'); // User's name
            $table->string('email'); // User's email address
            $table->text('message'); // Support message or issue description
            $table->boolean('email_sent')->default(false); // Track if email was sent successfully
            $table->timestamp('email_sent_at')->nullable(); // When email was sent
            $table->timestamps();
            
            // Index for better query performance
            $table->index(['type', 'created_at']);
            $table->index('email_sent');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('support_forms');
    }
}
