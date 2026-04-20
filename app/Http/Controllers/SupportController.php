<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SupportController extends Controller
{
    /**
     * Display error report form.
     */
    public function reportIssue(Request $request)
    {
        $context = $request->query('context');
        $decodedContext = $context ? json_decode($context, true) : [];

        return view('studio.support.report-issue', [
            'context' => $decodedContext,
        ]);
    }

    /**
     * Submit error report.
     */
    public function submitReport(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'context' => 'nullable|json',
        ]);

        // Log the issue
        Log::channel('support')->error('AI Generation Issue Reported', [
            'user_id' => auth()->id(),
            'title' => $validated['title'],
            'description' => $validated['description'],
            'context' => json_decode($validated['context'] ?? '{}', true),
            'reported_at' => now(),
        ]);

        // In production, you might want to:
        // - Send email to support team
        // - Create a ticket in your support system
        // - Store in a support_tickets table

        return redirect()->back()->with('success', 'Issue reported successfully. Our team will investigate.');
    }
}
