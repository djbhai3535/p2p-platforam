<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\Order;
use App\Models\OrderMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    /**
     * Fetch all messages for the specified order trade room.
     */
    public function fetchMessages(Order $order, Request $request): JsonResponse
    {
        $user = $request->user();

        // Enforce membership
        if ($order->buyer_id !== $user->id && $order->seller_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized access.'], 403);
        }

        $messages = OrderMessage::with('sender')
            ->where('order_id', $order->id)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($msg) {
                return [
                    'id' => $msg->id,
                    'order_id' => $msg->order_id,
                    'sender_id' => $msg->sender_id,
                    'sender_name' => $msg->sender->name,
                    'message' => $msg->message,
                    'attachment_url' => $msg->attachment_path ? asset('storage/'.$msg->attachment_path) : null,
                    'created_at' => $msg->created_at->toIso8601String(),
                ];
            });

        return response()->json($messages);
    }

    /**
     * Send a new message to the trade room.
     */
    public function sendMessage(Order $order, Request $request): JsonResponse
    {
        $user = $request->user();

        // Enforce membership
        if ($order->buyer_id !== $user->id && $order->seller_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized access.'], 403);
        }

        // Prevent messaging in archived/cancelled orders
        if (in_array($order->status, ['completed', 'cancelled'])) {
            return response()->json(['error' => 'Trade order is completed or cancelled.'], 400);
        }

        $request->validate([
            'message' => ['nullable', 'string', 'max:1000'],
            'attachment' => ['nullable', 'file', 'image', 'max:5120'], // Max 5MB images
        ]);

        if (empty($request->message) && ! $request->hasFile('attachment')) {
            return response()->json(['error' => 'Cannot send an empty message.'], 400);
        }

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('chat_attachments', 'public');
        }

        $message = OrderMessage::create([
            'order_id' => $order->id,
            'sender_id' => $user->id,
            'message' => $request->message,
            'attachment_path' => $attachmentPath,
            'is_read' => false,
        ]);

        // Trigger Real-time WebSocket Broadcast
        broadcast(new MessageSent($message))->toOthers();

        return response()->json([
            'success' => true,
            'message' => [
                'id' => $message->id,
                'sender_id' => $message->sender_id,
                'sender_name' => $user->name,
                'message' => $message->message,
                'attachment_url' => $attachmentPath ? asset('storage/'.$attachmentPath) : null,
                'created_at' => $message->created_at->toIso8601String(),
            ],
        ]);
    }
}
