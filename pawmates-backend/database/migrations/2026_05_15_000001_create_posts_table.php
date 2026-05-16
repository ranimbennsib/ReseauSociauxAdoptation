<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->longText('image')->nullable();
            $table->string('category');
            $table->enum('city', [
                'Tunis', 'Sfax', 'Sousse', 'Kairouan', 'Bizerte',
                'Gabes', 'Ariana', 'Gafsa', 'Monastir', 'Ben_Arous',
                'Kasserine', 'Medenine', 'Nabeul', 'Tataouine',
                'Beja', 'Jendouba', 'El_Kef', 'Mahdia',
                'Sidi_Bouzid', 'Siliana', 'Zaghouan',
                'Tozeur', 'Manouba', 'Kebili'
            ])->default('Tunis');
            $table->string('localisation_detail')->nullable();
            $table->boolean('is_adoption')->default(true);
            $table->boolean('is_donation')->default(false);
            $table->string('card_number')->nullable();
            $table->string('card_holder_name')->nullable();
            $table->enum('status', ['available', 'adopted', 'removed', 'closed'])->default('available');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
