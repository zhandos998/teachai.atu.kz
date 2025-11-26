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
        Schema::create('ai_logs', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('chat_id')->nullable();

            $table->text('question');                 // вопрос пользователя
            $table->json('matched_titles')->nullable(); // какие titles GPT выбрал
            $table->longText('context')->nullable(); // текст, который отправили GPT
            $table->longText('final_answer')->nullable(); // ответ GPT
            $table->text('error')->nullable();       // если GPT поломался
            $table->integer('duration_ms')->nullable(); // время выполнения AI

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_logs');
    }
};
