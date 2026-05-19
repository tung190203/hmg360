<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AiUsageAlertService
{
    public function checkCostThreshold(bool $force = false): array
    {
        if (!$this->isEnabled()) {
            return ['sent' => false, 'reason' => 'disabled'];
        }

        $threshold = $this->threshold();
        if ($threshold <= 0) {
            return ['sent' => false, 'reason' => 'missing_threshold'];
        }

        $emails = $this->emails();
        if (empty($emails)) {
            return ['sent' => false, 'reason' => 'missing_email'];
        }

        $response = app(AiChatService::class)->getAdminUsageSummary();
        if (!$response->successful()) {
            return ['sent' => false, 'reason' => 'summary_request_failed', 'status' => $response->status()];
        }

        $summary = $response->json() ?: [];
        $cost24h = (float) Arr::get($summary, 'by_window.24h.cost_usd', 0);
        if ($cost24h <= $threshold) {
            return ['sent' => false, 'reason' => 'below_threshold', 'cost_24h' => $cost24h, 'threshold' => $threshold];
        }

        $today = now()->toDateString();
        if (!$force && $this->setting('ai_usage_alert_last_sent_date') === $today) {
            return ['sent' => false, 'reason' => 'already_sent_today', 'cost_24h' => $cost24h, 'threshold' => $threshold];
        }

        $subject = '[AI Cost Alert] Chi phí AI 24h vượt ngưỡng';
        $mailData = [
            'badge' => 'Cost Alert',
            'badgeColor' => '#dc2626',
            'title' => 'Chi phí AI 24h vượt ngưỡng',
            'subtitle' => 'Hệ thống ghi nhận chi phí AI trong 24 giờ gần nhất đã vượt ngưỡng cảnh báo đang cấu hình.',
            'metrics' => [
                ['label' => 'Cost 24h', 'value' => '$' . number_format($cost24h, 6)],
                ['label' => 'Ngưỡng', 'value' => '$' . number_format($threshold, 6)],
                ['label' => 'Cost 7 ngày', 'value' => '$' . number_format((float) Arr::get($summary, 'by_window.7d.cost_usd', 0), 6)],
            ],
            'rows' => [
                ['label' => 'Cost 30 ngày', 'value' => '$' . number_format((float) Arr::get($summary, 'by_window.30d.cost_usd', Arr::get($summary, 'total_cost_usd', 0)), 6)],
                ['label' => 'Tổng tokens', 'value' => number_format((float) Arr::get($summary, 'total_tokens', 0))],
                ['label' => 'Thời gian kiểm tra', 'value' => now()->format('Y-m-d H:i:s')],
            ],
        ];

        try {
            $this->sendMail($emails, $subject, $mailData);
        } catch (\Throwable $exception) {
            Log::error('Failed to send AI usage cost alert email.', [
                'error' => $exception->getMessage(),
                'emails' => $emails,
            ]);

            return ['sent' => false, 'reason' => 'mail_failed', 'error' => $exception->getMessage()];
        }

        $this->saveSetting('ai_usage_alert_last_sent_date', $today);

        return ['sent' => true, 'cost_24h' => $cost24h, 'threshold' => $threshold, 'emails' => $emails];
    }

    public function notifyWebhookEvent(array $payload, string $eventType = ''): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        $emails = $this->emails();
        if (empty($emails)) {
            return false;
        }

        $status = (string) Arr::get($payload, 'status', '');
        $shouldAlert = in_array($eventType, ['sync_failed', 'knowledge_failed'], true)
            || in_array($status, ['failed', 'error'], true);

        if (!$shouldAlert) {
            return false;
        }

        $subject = '[AI Alert] ' . ($eventType ?: 'Webhook failed');
        $mailData = [
            'badge' => 'Webhook Alert',
            'badgeColor' => '#f59e0b',
            'title' => 'AI webhook báo lỗi cần kiểm tra',
            'subtitle' => 'Một sự kiện AI trả về trạng thái lỗi. Vui lòng kiểm tra job hoặc lịch đồng bộ liên quan.',
            'metrics' => [
                ['label' => 'Event', 'value' => $eventType ?: '-'],
                ['label' => 'Status', 'value' => $status ?: '-'],
                ['label' => 'Cost', 'value' => '$' . number_format((float) Arr::get($payload, 'cost_usd_total', 0), 6)],
            ],
            'rows' => [
                ['label' => 'Job ID', 'value' => Arr::get($payload, 'job_id') ?: '-'],
                ['label' => 'Doc ID', 'value' => Arr::get($payload, 'doc_id') ?: '-'],
                ['label' => 'File', 'value' => Arr::get($payload, 'source_filename') ?: '-'],
                ['label' => 'Thời gian nhận', 'value' => now()->format('Y-m-d H:i:s')],
            ],
        ];

        try {
            $this->sendMail($emails, $subject, $mailData);
        } catch (\Throwable $exception) {
            Log::error('Failed to send AI webhook alert email.', [
                'error' => $exception->getMessage(),
                'event_type' => $eventType,
                'emails' => $emails,
            ]);

            return false;
        }

        return true;
    }

    private function isEnabled(): bool
    {
        return in_array((string) $this->setting('ai_usage_alert_enabled', '0'), ['1', 'true', 'on'], true);
    }

    private function threshold(): float
    {
        $value = (string) $this->setting('ai_usage_alert_threshold_24h', 0);

        return (float) str_replace(',', '.', $value);
    }

    private function emails(): array
    {
        $value = (string) ($this->setting('ai_usage_alert_emails') ?: $this->setting('email'));
        $emails = array_filter(array_map('trim', explode(',', $value)));

        return array_values(array_filter($emails, fn ($email) => filter_var($email, FILTER_VALIDATE_EMAIL)));
    }

    private function setting(string $key, $default = '')
    {
        return Setting::getSettingByKey($key, $default);
    }

    private function saveSetting(string $key, string $value): void
    {
        $setting = Setting::where('skey', $key)->first();
        if (!$setting) {
            $setting = new Setting();
            $setting->skey = $key;
        }
        $setting->setTranslation('svalue', App::getLocale(), $value);
        $setting->save();

        unset(Setting::$cached['all_setting']);
    }

    private function sendMail(array $emails, string $subject, array $mailData): void
    {
        Mail::send('emails.ai-alert', $mailData, function ($message) use ($emails, $subject) {
            $message->to($emails)->subject($subject);
        });
    }
}
