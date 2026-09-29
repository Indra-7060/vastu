<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\Subscriber;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'subscribe');
        if (! in_array($tab, ['subscribe', 'contact'], true)) {
            $tab = 'subscribe';
        }

        $perPage = $this->perPage($request);
        $subscribers = null;
        $messages = null;
        $sort = null;
        $dir = null;

        if ($tab === 'subscribe') {
            [$sort, $dir] = $this->sortParams($request, ['email', 'created_at'], 'created_at', 'desc');

            $query = Subscriber::query();

            if ($search = trim((string) $request->get('search'))) {
                $query->where('email', 'like', "%{$search}%");
            }

            $subscribers = $query->orderBy($sort, $dir)->orderBy('id', 'desc')
                ->paginate($perPage)
                ->withQueryString();
        } else {
            [$sort, $dir] = $this->sortParams($request, [
                'name', 'email', 'subject', 'message', 'created_at',
            ], 'created_at', 'desc');

            $query = ContactMessage::query();

            if ($search = trim((string) $request->get('search'))) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('subject', 'like', "%{$search}%")
                        ->orWhere('message', 'like', "%{$search}%");
                });
            }

            $messages = $query->orderBy($sort, $dir)->orderBy('id', 'desc')
                ->paginate($perPage)
                ->withQueryString();
        }

        return view('admin.contacts.index', compact(
            'tab',
            'perPage',
            'subscribers',
            'messages',
            'sort',
            'dir'
        ));
    }

    public function destroySubscriber(Subscriber $subscriber)
    {
        $subscriber->delete();

        return redirect()
            ->route('admin.contacts.index', ['tab' => 'subscribe'])
            ->with('success', 'Subscriber deleted successfully.');
    }

    public function destroyMessage(ContactMessage $message)
    {
        $message->delete();

        return redirect()
            ->route('admin.contacts.index', ['tab' => 'contact'])
            ->with('success', 'Contact message deleted successfully.');
    }

    public function bulkSubscribers(Request $request)
    {
        $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:subscribers,id'],
        ]);

        $count = Subscriber::whereIn('id', $request->ids)->delete();

        return redirect()
            ->route('admin.contacts.index', ['tab' => 'subscribe'])
            ->with('success', "{$count} subscriber(s) deleted.");
    }

    public function bulkMessages(Request $request)
    {
        $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:contact_messages,id'],
        ]);

        $count = ContactMessage::whereIn('id', $request->ids)->delete();

        return redirect()
            ->route('admin.contacts.index', ['tab' => 'contact'])
            ->with('success', "{$count} message(s) deleted.");
    }

    private function perPage(Request $request): int
    {
        $perPage = (int) $request->get('per_page', 10);

        return in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 10;
    }

    /**
     * @param  array<int, string>  $allowed
     * @return array{0:string,1:string}
     */
    private function sortParams(Request $request, array $allowed, string $defaultSort, string $defaultDir = 'asc'): array
    {
        $sort = (string) $request->get('sort', $defaultSort);
        if (! in_array($sort, $allowed, true)) {
            $sort = $defaultSort;
        }

        $dir = strtolower((string) $request->get('dir', $defaultDir)) === 'desc' ? 'desc' : 'asc';

        return [$sort, $dir];
    }
}
