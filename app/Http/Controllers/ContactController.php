<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    /**
     * List contact messages from the database.
     */
    public function index(Request $request)
    {
        $searchValue = $request->query('q');
        $search = is_string($searchValue) ? trim($searchValue) : '';
        $statusValue = $request->query('status');
        $status = in_array($statusValue, ['read', 'unread'], true)
            ? $statusValue
            : null;

        $messages = ContactMessage::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('subject', 'like', "%{$search}%")
                        ->orWhere('message', 'like', "%{$search}%");
                });
            })
            ->when($status === 'read', fn ($query) => $query->where('is_read', true))
            ->when($status === 'unread', fn ($query) => $query->where('is_read', false))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('backend.contacts.index', [
            'messages' => $messages,
            'totalMessages' => ContactMessage::count(),
            'unreadMessages' => ContactMessage::where('is_read', false)->count(),
            'readMessages' => ContactMessage::where('is_read', true)->count(),
            'search' => $search,
            'status' => $status,
        ]);
    }

    /**
     * Mark a contact message as read or unread.
     */
    public function update(Request $request, ContactMessage $contact)
    {
        $validated = $request->validate([
            'is_read' => ['required', 'boolean'],
        ]);

        $contact->update($validated);

        return redirect()
            ->route('contacts.index')
            ->with('success', 'Message status updated successfully.');
    }

    /**
     * Delete a contact message from the database.
     */
    public function destroy(ContactMessage $contact)
    {
        $contact->delete();

        return redirect()
            ->route('contacts.index')
            ->with('success', 'Message deleted successfully.');
    }
}
