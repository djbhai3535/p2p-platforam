<?php

namespace App\Listeners;

use App\Models\AuditLog;
use App\Models\DeviceLogin;
use Illuminate\Auth\Events\Login;
use Illuminate\Http\Request;

class LogSuccessfulLogin
{
    protected Request $request;

    /**
     * Create the event listener.
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        $user = $event->user;

        // Log device login history
        DeviceLogin::create([
            'user_id' => $user->id,
            'ip_address' => $this->request->ip() ?? '127.0.0.1',
            'user_agent' => $this->request->userAgent() ?? 'Unknown',
            'location' => 'Unknown (Localhost)', // Can integrate GeoIP here in production
        ]);

        // Log Audit Trail
        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'LOGIN',
            'description' => 'User logged in from IP: '.($this->request->ip() ?? '127.0.0.1'),
            'ip_address' => $this->request->ip() ?? '127.0.0.1',
        ]);
    }
}
