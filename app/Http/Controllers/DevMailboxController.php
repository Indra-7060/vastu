<?php

namespace App\Http\Controllers;

use App\Mail\Transport\OutboxTransport;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/** Local test inbox: shows emails caught by the "outbox" mailer (local environment, admins only). */
class DevMailboxController extends Controller
{
    private function dir(): string
    {
        return storage_path('app/'.OutboxTransport::DIR);
    }

    private function all(): array
    {
        $files = glob($this->dir().'/*.json') ?: [];
        rsort($files);

        return array_values(array_filter(array_map(fn ($f) => json_decode((string) file_get_contents($f), true), $files)));
    }

    public function index(?string $id = null): View
    {
        $emails = $this->all();
        $current = $id ? collect($emails)->firstWhere('id', $id) : ($emails[0] ?? null);

        return view('dev.mailbox', ['emails' => $emails, 'current' => $current]);
    }

    public function clear(): RedirectResponse
    {
        foreach (glob($this->dir().'/*.json') ?: [] as $file) {
            @unlink($file);
        }

        return redirect()->route('dev.mailbox');
    }
}
