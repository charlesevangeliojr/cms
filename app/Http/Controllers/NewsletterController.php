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

        $subscribers = NewsletterSubscriber::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('email', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                });
            })
            ->when($status === 'active', fn ($query) => $query->where('is_active', true))
            ->when($status === 'inactive', fn ($query) => $query->where('is_active', false))
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
        ]);
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
