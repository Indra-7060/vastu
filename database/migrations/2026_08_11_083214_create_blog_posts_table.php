<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->foreignId('news_type_id')->nullable()->constrained('news_types')->nullOnDelete();
            $table->string('image')->nullable();
            $table->string('banner_image')->nullable();
            $table->string('author_name')->default('Admin');
            $table->text('excerpt')->nullable();
            $table->longText('content')->nullable();
            $table->unsignedInteger('comments_count')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_posts');
    }
};
