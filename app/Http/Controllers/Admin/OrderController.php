<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\OrderDeliveryDateUpdatedMail;
use App\Mail\OrderStatusUpdatedMail;
use App\Models\Order;
use App\Models\OrderStatusLog;
use App\Support\OrderStatuses;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $this->perPage($request);
        [$sort, $dir] = $this->sortParams($request, [
            'order_number', 'user_name', 'user_phone', 'ordered_at', 'status', 'payment_status', 'payable_amount',
        ], 'ordered_at', 'desc');

        $query = Order::query()->with('user');

        if ($search = trim((string) $request->get('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('user_name', 'like', "%{$search}%")
                    ->orWhere('user_phone', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhere('shipping_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($date = trim((string) $request->get('date'))) {
            try {
                $parsed = Carbon::createFromFormat('d-m-Y', $date)->startOfDay();
                $query->whereDate('ordered_at', $parsed->toDateString());
            } catch (\Throwable $e) {
                // ignore invalid date
            }
        }

        $query->orderBy($sort, $dir)->orderBy('id', 'desc');

        $orders = $query->paginate($perPage)->withQueryString();
        $statuses = OrderStatuses::all();

        return view('admin.orders.index', compact('orders', 'perPage', 'sort', 'dir', 'statuses'));
    }

    public function show(Order $order)
    {
        $order->load(['items', 'statusLogs', 'user']);

        return view('admin.orders.show', compact('order'));
    }

    public function print(Order $order)
    {
        $order->load(['items', 'statusLogs', 'user']);

        return view('admin.orders.print', compact('order'));
    }

    public function destroy(Order $order)
    {
        $order->delete();

        return redirect()->route('admin.orders.index')->with('success', 'Order deleted successfully.');
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => ['required', 'in:delete'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:orders,id'],
        ]);

        $count = Order::whereIn('id', $request->ids)->count();
        Order::whereIn('id', $request->ids)->delete();

        return back()->with('success', "{$count} order(s) deleted.");
    }

    public function updateDeliveryDate(Request $request, Order $order)
    {
        $data = $request->validate([
            'expected_delivery_date' => ['required', 'date_format:d-m-Y'],
        ]);

        $order->update([
            'expected_delivery_date' => Carbon::createFromFormat('d-m-Y', $data['expected_delivery_date'])->toDateString(),
        ]);

        $order = $order->fresh(['user']);
        $formattedDate = $order->expected_delivery_date?->format('d-m-Y') ?: $data['expected_delivery_date'];
        $this->notifyCustomer(
            $order,
            new OrderDeliveryDateUpdatedMail($order, $formattedDate),
            'delivery date'
        );

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Expected delivery date updated. Customer notified by email.',
                'date' => $formattedDate,
            ]);
        }

        return back()->with('success', 'Expected delivery date updated. Customer notified by email.');
    }

    public function updateStatus(Request $request, Order $order)
    {
        $allowed = array_keys($order->nextStatusOptions());

        $data = $request->validate([
            'status' => ['required', Rule::in($allowed ?: ['_none_'])],
            'description' => ['nullable', 'string', 'max:1000'],
        ], [
            'status.in' => 'This status change is not allowed for the current order status.',
        ]);

        if (empty($allowed)) {
            return back()->with('error', 'No further status updates are allowed for this order.');
        }

        $description = $data['description'] ?: OrderStatuses::defaultMessage($data['status']);
        $statusLabel = OrderStatuses::label($data['status']);

        DB::transaction(function () use ($order, $data, $description, $statusLabel) {
            $order->update(['status' => $data['status']]);
            $order->items()->update(['status' => $data['status']]);

            OrderStatusLog::create([
                'order_id' => $order->id,
                'status' => $data['status'],
                'title' => $statusLabel,
                'description' => $description,
                'logged_at' => now(),
            ]);
        });

        $order = $order->fresh(['user', 'items']);
        $this->notifyCustomer(
            $order,
            new OrderStatusUpdatedMail($order, $statusLabel, $description),
            'status'
        );

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Order status updated. Customer notified by email.',
                'redirect' => route('admin.orders.show', $order),
            ]);
        }

        return back()->with('success', 'Order status updated. Customer notified by email.');
    }

    public function destroyStatusLog(Order $order, OrderStatusLog $statusLog)
    {
        abort_unless($statusLog->order_id === $order->id, 404);

        // Don't allow deleting the first "placed" log
        $firstId = $order->statusLogs()->orderBy('id')->value('id');
        if ((int) $statusLog->id === (int) $firstId) {
            return back()->with('error', 'Cannot delete the initial placed status.');
        }

        $statusLog->delete();

        return back()->with('success', 'Status history entry deleted.');
    }

    public function statusPayload(Order $order)
    {
        $order->load('statusLogs');

        return response()->json([
            'id' => $order->id,
            'order_number' => $order->order_number,
            'status' => $order->status,
            'status_label' => $order->status_label,
            'expected_delivery_date' => $order->expected_delivery_date?->format('d-m-Y'),
            'next_statuses' => $order->nextStatusOptions(),
            'can_update' => count($order->nextStatusOptions()) > 0,
            'logs' => $order->statusLogs->map(fn ($log) => [
                'id' => $log->id,
                'status' => $log->status,
                'title' => $log->title ?: OrderStatuses::label($log->status),
                'description' => $log->description,
                'date' => ($log->logged_at ?: $log->created_at)?->format('d-m-Y'),
                'can_delete' => $log->status !== OrderStatuses::PLACED,
            ]),
            'update_status_url' => route('admin.orders.status', $order),
            'update_delivery_url' => route('admin.orders.delivery-date', $order),
        ]);
    }

    private function notifyCustomer(Order $order, object $mailable, string $context): void
    {
        $customerEmail = $order->shipping_email ?: $order->user_email ?: optional($order->user)->email;

        if (! $customerEmail) {
            Log::warning('Order '.$context.' email skipped — no recipient', [
                'order' => $order->order_number,
            ]);

            return;
        }

        try {
            Mail::to($customerEmail)->send($mailable);
            Log::info('Order '.$context.' email sent', [
                'order' => $order->order_number,
                'to' => $customerEmail,
            ]);
        } catch (\Throwable $e) {
            Log::error('Order '.$context.' email failed: '.$e->getMessage(), [
                'order' => $order->order_number,
                'to' => $customerEmail,
            ]);
        }
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
