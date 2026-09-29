<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name', 100);
            $table->string('email');
            $table->string('phone', 30);
            $table->string('interest', 60);
            $table->text('message')->nullable();
            $table->string('status', 20)->default('new');
            $table->text('admin_note')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });

        // Move enquiries sent through the pop-up before this table existed (saved as contact messages).
        $prefix = 'Consultation — ';
        DB::table('contact_messages')->where('subject', 'like', $prefix.'%')->orderBy('id')->get()
            ->each(function ($row) use ($prefix) {
                DB::table('consultations')->insert([
                    'name' => $row->name,
                    'email' => $row->email,
                    'phone' => $row->phone ?? '',
                    'interest' => mb_substr((string) $row->subject, mb_strlen($prefix)) ?: 'Other',
                    'message' => $row->message === '(No message)' ? null : $row->message,
                    'status' => 'new',
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ]);
                DB::table('contact_messages')->where('id', $row->id)->delete();
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultations');
    }
};
