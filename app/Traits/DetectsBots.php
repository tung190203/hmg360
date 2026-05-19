<?php

namespace App\Traits;

trait DetectsBots
{
    /**
     * Determine if the user agent belongs to a bot.
     */
    protected function isBot(string $userAgent): bool
    {
        if (empty($userAgent)) {
            return true; // Không có User-Agent thường là bot hoặc script tự động
        }

        $bots = [
            // Search Engines
            'googlebot', 'bingbot', 'yandex', 'duckduckbot', 'baiduspider', 'sogou', 'exabot', 'ia_archiver',
            // Social Media Bots
            'facebookexternalhit', 'zalo', 'telegrambot', 'whatsapp', 'twitterbot', 'linkedinbot', 'discordbot', 'slackbot', 'facebot',
            // SEO & Marketing Tools
            'ahrefsbot', 'semrushbot', 'rogerbot', 'mj12bot', 'dotbot', 'screaming frog', 'uipbot', 'petalbot', 'adsbot',
            // Development & Common Libraries
            'curl', 'wget', 'php', 'python', 'guzzle', 'go-http', 'java', 'ruby', 'httpclient', 'urllib', 'scrapy', 'headless',
            // General Crawlers
            'bot', 'crawl', 'spider', 'slurp', 'archive', 'pinger', 'inspect', 'lighthouse'
        ];

        $userAgent = strtolower($userAgent);

        foreach ($bots as $bot) {
            if (str_contains($userAgent, $bot)) {
                return true;
            }
        }

        return false;
    }
}
