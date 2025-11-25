<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use Illuminate\Http\Request;

class SupportTicketController extends Controller
{
    // CREATE ticket
    public function store(Request $request)
    {
        $request->validate([
            'homeowner_id' => 'required|exists:homeowners,id',
            'subject' => 'required|string|max:255',
            'message' => 'required|string'
        ]);

        $ticket = SupportTicket::create([
            'homeowner_id' => $request->homeowner_id,
            'subject' => $request->subject,
            'message' => $request->message,
            'status' => 'open'
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Ticket created successfully',
            'data' => $ticket
        ], 201);
    }

    // READ all tickets
    public function index()
    {
        $tickets = SupportTicket::with('homeowner')->latest()->get();

        return response()->json($tickets, 200);
    }

    // READ single ticket
    public function show($id)
    {
        $ticket = SupportTicket::with('homeowner')->findOrFail($id);

        return response()->json($ticket, 200);
    }

    // UPDATE ticket
    public function update(Request $request, $id)
    {
        $ticket = SupportTicket::findOrFail($id);

        $request->validate([
            'status' => 'required|in:open,pending,resolved,closed'
        ]);

        $ticket->update([
            'status' => $request->status
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Ticket updated successfully',
            'data' => $ticket
        ], 200);
    }

    // DELETE ticket
    public function destroy($id)
    {
        $ticket = SupportTicket::findOrFail($id);
        $ticket->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Ticket deleted successfully'
        ], 200);
    }
}
