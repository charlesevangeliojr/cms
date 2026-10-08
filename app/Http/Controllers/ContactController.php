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
        [$from, $to, $dateFilter] = $this->dateFilters($request);

        $messages = $this->filteredMessages($search, $status, $dateFilter, $from, $to)
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
            'from' => $from,
            'to' => $to,
            'dateFilter' => $dateFilter,
        ]);
    }

    public function export(Request $request)
    {
        $searchValue = $request->query('q');
        $search = is_string($searchValue) ? trim($searchValue) : '';
        $status = in_array($request->query('status'), ['read', 'unread'], true) ? $request->query('status') : null;
        [$from, $to, $dateFilter] = $this->dateFilters($request);
        $ids = $request->query('ids', []);
        $messages = is_array($ids) && count($ids)
            ? ContactMessage::whereIn('id', array_map('intval', $ids))->latest()->get()
            : $this->filteredMessages($search, $status, $dateFilter, $from, $to)->latest()->get();

        $html = view('backend.contacts.export', compact('messages'))->render();
        return response($html, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="contact-messages.xls"',
        ]);
    }

    public function bulk(Request $request)
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'distinct', 'exists:contact_messages,id'],
            'action' => ['required', 'in:read,unread,delete'],
        ]);
        $action = $validated['action'];
        $permission = $action === 'delete' ? 'delete' : 'edit';
        abort_unless($request->user()?->canAccess('contacts', $permission), 403, "You are not allowed to {$action} contact messages.");

        $messages = ContactMessage::whereIn('id', $validated['ids']);
        if ($action === 'delete') {
            $messages->delete();
            $notice = 'Selected messages deleted successfully.';
        } else {
            $messages->update(['is_read' => $action === 'read']);
            $notice = 'Selected messages marked '.($action === 'read' ? 'read' : 'unread').'.';
        }

        return redirect()->route('contacts.index')->with('success', $notice);
    }

    private function dateFilters(Request $request): array
    {
        $fromValue = $request->query('from');
        $toValue = $request->query('to');
        $from = is_string($fromValue) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fromValue) && strtotime($fromValue) ? $fromValue : '';
        $to = is_string($toValue) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $toValue) && strtotime($toValue) ? $toValue : '';
        return [$from, $to, $from !== '' || $to !== ''];
    }

    private function filteredMessages(string $search, ?string $status, bool $dateFilter, string $from, string $to)
    {
        return ContactMessage::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(fn ($query) => $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('contact', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%")
                    ->orWhere('message', 'like', "%{$search}%"));
            })
            ->when($status === 'read', fn ($query) => $query->where('is_read', true))
            ->when($status === 'unread', fn ($query) => $query->where('is_read', false))
            ->when($dateFilter && $from !== '', fn ($query) => $query->whereDate('created_at', '>=', $from))
            ->when($dateFilter && $to !== '', fn ($query) => $query->whereDate('created_at', '<=', $to));
    }

    /**
     * Store a public contact message from the landing page.
     */
    public function storePublic(Request $request)
    {
        $wantsJson = $request->expectsJson() || $request->ajax() || $request->wantsJson();

        // Reject inspect-element tricks (e.g. changing an input to type=file).
        if ($request->allFiles()) {
            if ($wantsJson) {
                return response()->json([
                    'message' => 'Invalid submission.',
                    'errors' => ['message' => ['Invalid submission.']],
                ], 422);
            }

            return back()->withErrors(['message' => 'Invalid submission.'])->withInput();
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'not_regex:/<[^>]*>/', 'not_regex:/\bhttps?:\/\/|www\./i', 'not_regex:/\.(png|jpe?g|webp|gif|bmp|svg|pdf|docx?|xlsx?|exe|zip|rar|mp4|mp3|avi|mov|txt)$/i'],
            'email' => ['required', 'email', 'max:255'],
            'contact_country' => ['nullable', 'string', 'max:10'],
            'contact' => ['nullable', 'string', 'max:20', 'regex:/^\d{1,10}$/'],
            'subject' => ['required', 'string', 'max:255', 'not_regex:/<[^>]*>/', 'not_regex:/\bhttps?:\/\/|www\./i', 'not_regex:/\.(png|jpe?g|webp|gif|bmp|svg|pdf|docx?|xlsx?|exe|zip|rar|mp4|mp3|avi|mov|txt)$/i'],
            'message' => ['required', 'string', 'max:5000', 'not_regex:/<[^>]*>/', 'not_regex:/\bhttps?:\/\/|www\./i', 'not_regex:/\.(png|jpe?g|webp|gif|bmp|svg|pdf|docx?|xlsx?|exe|zip|rar|mp4|mp3|avi|mov|txt)$/i'],
        ], [
            'name.not_regex' => 'The name contains disallowed content.',
            'contact.regex' => 'The contact number must be up to 10 digits.',
            'subject.not_regex' => 'The subject contains disallowed content.',
            'message.not_regex' => 'The message contains disallowed content.',
        ]);

        $validated = array_map(
            fn ($value) => is_string($value) ? trim(strip_tags($value)) : $value,
            $validated
        );

        if (empty($validated['contact'])) {
            $validated['contact'] = null;
            $validated['contact_country'] = null;
        } elseif (empty($validated['contact_country'])) {
            $validated['contact_country'] = null;
        }

        ContactMessage::create([
            ...$validated,
            'is_read' => false,
        ]);

        if ($wantsJson) {
            return response()->json(['message' => 'Thank you! Your message has been sent.']);
        }

        return back()->with('success', 'Thank you! Your message has been sent.');
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
