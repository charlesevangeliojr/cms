<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    /**
     * List newsletter subscribers from the database.
     */
    public function index(Request $request)
    {
        $searchValue = $request->query('q');
        $search = is_string($searchValue) ? trim($searchValue) : '';
        $statusValue = $request->query('status');
        $status = in_array($statusValue, ['active', 'inactive'], true)
            ? $statusValue
            : null;
        [$from, $to, $dateFilter] = $this->dateFilters($request);

        $subscribers = $this->filteredSubscribers($search, $status, $dateFilter, $from, $to)
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('backend.newsletters.index', [
            'subscribers' => $subscribers,
            'totalSubscribers' => NewsletterSubscriber::count(),
            'activeSubscribers' => NewsletterSubscriber::where('is_active', true)->count(),
            'inactiveSubscribers' => NewsletterSubscriber::where('is_active', false)->count(),
            'search' => $search,
            'status' => $status,
            'from' => $from,
            'to' => $to,
            'dateFilter' => $dateFilter,
        ]);
    }

    public function export(Request $request)
    {
        $searchValue = $request->query('q');
        $search = is_string($searchValue) ? trim($searchValue) : '';
        $status = in_array($request->query('status'), ['active', 'inactive'], true) ? $request->query('status') : null;
        [$from, $to, $dateFilter] = $this->dateFilters($request);
        $ids = $request->query('ids', []);
        $subscribers = is_array($ids) && count($ids)
            ? NewsletterSubscriber::whereIn('id', array_map('intval', $ids))->latest()->get()
            : $this->filteredSubscribers($search, $status, $dateFilter, $from, $to)->latest()->get();

        return response(view('backend.newsletters.export', compact('subscribers'))->render(), 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="newsletter-subscribers.xls"',
        ]);
    }

    public function bulk(Request $request)
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'distinct', 'exists:newsletter_subscribers,id'],
            'action' => ['required', 'in:active,inactive,delete'],
        ]);
        $action = $validated['action'];
        $permission = $action === 'delete' ? 'delete' : 'edit';
        abort_unless($request->user()?->canAccess('newsletters', $permission), 403, "You are not allowed to {$action} newsletter subscribers.");

        $subscribers = NewsletterSubscriber::whereIn('id', $validated['ids']);
        if ($action === 'delete') {
            $subscribers->delete();
            $notice = 'Selected subscribers deleted successfully.';
        } else {
            $subscribers->update(['is_active' => $action === 'active']);
            $notice = 'Selected subscribers marked '.($action === 'active' ? 'active' : 'inactive').'.';
        }

        return redirect()->route('newsletters.index')->with('success', $notice);
    }

    private function dateFilters(Request $request): array
    {
        $fromValue = $request->query('from');
        $toValue = $request->query('to');
        $from = is_string($fromValue) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fromValue) && strtotime($fromValue) ? $fromValue : '';
        $to = is_string($toValue) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $toValue) && strtotime($toValue) ? $toValue : '';
        return [$from, $to, $from !== '' || $to !== ''];
    }

    private function filteredSubscribers(string $search, ?string $status, bool $dateFilter, string $from, string $to)
    {
        return NewsletterSubscriber::query()
            ->when($search !== '', fn ($query) => $query->where('email', 'like', "%{$search}%"))
            ->when($status === 'active', fn ($query) => $query->where('is_active', true))
            ->when($status === 'inactive', fn ($query) => $query->where('is_active', false))
            ->when($dateFilter && $from !== '', fn ($query) => $query->whereDate('created_at', '>=', $from))
            ->when($dateFilter && $to !== '', fn ($query) => $query->whereDate('created_at', '<=', $to));
    }

    /**
     * Store a public newsletter subscription from the landing page.
     */
    public function storePublic(Request $request)
    {
        $wantsJson = $request->expectsJson() || $request->ajax() || $request->wantsJson();

        // Reject inspect-element tricks (e.g. changing an input to type=file).
        if ($request->allFiles()) {
            if ($wantsJson) {
                return response()->json([
                    'message' => 'Invalid submission.',
                    'errors' => ['email' => ['Invalid submission.']],
                ], 422);
            }

            return back()->withErrors(['email' => 'Invalid submission.'])->withInput();
        }

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255', 'unique:newsletter_subscribers,email'],
        ]);

        NewsletterSubscriber::create([
            'email' => $validated['email'],
            'is_active' => true,
        ]);

        if ($wantsJson) {
            return response()->json(['message' => 'Thank you for subscribing to our newsletter.']);
        }

        return back()->with('success', 'Thank you for subscribing to our newsletter.');
    }

    /**
     * Activate or deactivate a newsletter subscriber.
     */
    public function update(Request $request, NewsletterSubscriber $newsletter)
    {
        $validated = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        $newsletter->update($validated);

        return redirect()
            ->route('newsletters.index')
            ->with('success', 'Subscriber status updated successfully.');
    }

    /**
     * Delete a newsletter subscriber from the database.
     */
    public function destroy(NewsletterSubscriber $newsletter)
    {
        $newsletter->delete();

        return redirect()
            ->route('newsletters.index')
            ->with('success', 'Subscriber deleted successfully.');
    }
}
