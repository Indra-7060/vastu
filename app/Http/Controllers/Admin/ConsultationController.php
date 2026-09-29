<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Consultation;
use App\Models\User;
use App\Notifications\ConsultationUpdated;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ConsultationController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status');
        $query = Consultation::query()->latest()->orderByDesc('id');

        if ($status && array_key_exists($status, Consultation::STATUSES)) {
            $query->where('status', $status);
        } else {
            $status = null;
        }

        if ($search = trim((string) $request->get('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('interest', 'like', "%{$search}%");
            });
        }

        $consultations = $query->paginate(15)->withQueryString();
        $counts = Consultation::query()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        return view('admin.consultations.index', compact('consultations', 'counts', 'status'));
    }

    public function show(Consultation $consultation)
    {
        return view('admin.consultations.show', compact('consultation'));
    }

    public function update(Request $request, Consultation $consultation)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(Consultation::STATUSES))],
            'admin_note' => ['nullable', 'string', 'max:2000'],
            'customer_message' => ['nullable', 'string', 'max:2000'],
        ]);

        $statusChanged = $consultation->status !== $data['status'];
        $message = trim((string) ($data['customer_message'] ?? '')) ?: null;
        $consultation->update(['status' => $data['status'], 'admin_note' => $data['admin_note'] ?? null]);

        $flash = 'Consultation updated.';
        if ($statusChanged || $message) {
            $event = $statusChanged ? ConsultationUpdated::EVENT_STATUS : ConsultationUpdated::EVENT_MESSAGE;
            $flash .= ' '.$this->notifyCustomer($consultation, $event, $message);
        }

        return back()->with('success', $flash);
    }

    public function updateStatus(Request $request, Consultation $consultation)
    {
        $data = $request->validate(['status' => ['required', Rule::in(array_keys(Consultation::STATUSES))]]);
        if ($consultation->status === $data['status']) {
            return back();
        }
        $consultation->update($data);

        return back()->with('success', 'Status changed to '.$consultation->status_label.'. '.$this->notifyCustomer($consultation, ConsultationUpdated::EVENT_STATUS));
    }

    public function destroy(Consultation $consultation)
    {
        $notice = $this->notifyCustomer($consultation, ConsultationUpdated::EVENT_DELETED);
        $consultation->delete();

        return redirect()->route('admin.consultations.index')->with('success', 'Consultation deleted. '.$notice);
    }

    /**
     * Email the customer and, when they have a website account, add a notification to it.
     * Returns a short note for the admin flash message. Mail problems never block the admin action.
     */
    private function notifyCustomer(Consultation $consultation, string $event, ?string $message = null): string
    {
        $notification = new ConsultationUpdated($consultation, $event, $message);
        $user = $consultation->user
            ?? User::where('email', $consultation->email)->get()->first(fn (User $u) => $u->isCustomer());

        try {
            if ($user) {
                $user->notify($notification);

                return 'Customer notified by email and in their account.';
            }
            Notification::route('mail', $consultation->email)->notify($notification);

            return 'Customer notified by email.';
        } catch (\Throwable $e) {
            Log::warning('Consultation notification failed: '.$e->getMessage(), ['consultation' => $consultation->id]);
            if ($user) {
                // Still record it in the account even if the email could not be sent.
                try {
                    $user->notifyNow($notification, ['database']);

                    return 'Customer notified in their account (email could not be sent).';
                } catch (\Throwable $e2) {
                }
            }

            return 'The customer could not be notified (email failed).';
        }
    }
}
