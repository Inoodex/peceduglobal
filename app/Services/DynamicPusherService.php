<?php

namespace App\Services;

use App\Models\ChatSetting;
use Pusher\Pusher;
use Illuminate\Support\Facades\Log;

class DynamicPusherService
{
    protected $pusher;

    public function __construct()
    {
        $this->initializePusher();
    }

    /**
     * Initialize Pusher using credentials from the database.
     */
    private function initializePusher()
    {
        try {
            $appId = $this->getSetting('pusher_app_id');
            $key = $this->getSetting('pusher_key');
            $secret = $this->getSetting('pusher_secret');
            $driver = $this->getSetting('pusher_driver') ?: 'pusher';
            $cluster = $this->getSetting('pusher_cluster');
            $host = $this->getSetting('pusher_host');
            $port = $this->getSetting('pusher_port');
            $scheme = $this->getSetting('pusher_scheme') ?: 'https';

            if (!$key || !$secret) {
                Log::warning('Pusher credentials are not fully configured in the database.');
                return;
            }

            $options = [
                'useTLS' => $scheme === 'https'
            ];

            if ($driver === 'pusher') {
                $options['cluster'] = $cluster;
            } else if ($driver === 'custom') {
                if ($host) {
                    $options['host'] = $host;
                    $options['port'] = $port ?: 6001;
                    $options['scheme'] = $scheme;
                }
            }

            $this->pusher = new Pusher(
                $key,
                $secret,
                $appId,
                $options
            );
        } catch (\Exception $e) {
            Log::error('Failed to initialize Dynamic Pusher Service: ' . $e->getMessage());
        }
    }

    /**
     * Send a real-time event to a specific channel.
     */
    public function trigger($channel, $event, $data)
    {
        if (!$this->pusher) {
            Log::error('Pusher is not initialized. Please check admin settings.');
            return false;
        }

        try {
            return $this->pusher->trigger($channel, $event, json_encode($data));
        } catch (\Exception $e) {
            Log::error('Pusher trigger failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Helper to get setting value from database.
     */
    private function getSetting($key)
    {
        return ChatSetting::where('key', $key)->value('value');
    }
}
