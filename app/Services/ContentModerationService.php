<?php

namespace App\Services;

class ContentModerationService
{
    /**
     * Bad words list (multilingual: English, French, Arabic, German)
     * App Store Compliance: Content Moderation REQUIRED!
     */
    protected array $badWords = [
        // English
        'fuck', 'shit', 'bitch', 'ass', 'dick', 'pussy', 'cock', 'cunt', 'bastard', 'damn',
        'sex', 'porn', 'nude', 'naked', 'drug', 'cocaine', 'weed', 'kill', 'murder', 'rape',

        // French
        'merde', 'putain', 'con', 'connard', 'salope', 'pute', 'chatte', 'bite', 'couille',
        'enculé', 'sexe', 'porno', 'nu', 'drogue', 'tuer', 'viol',

        // Arabic (transliterated)
        'sharmouta', 'kuss', 'ayr', 'khara', 'kalbة', 'himar', 'fahisha',

        // German
        'scheiße', 'arsch', 'fotze', 'schwanz', 'fick', 'hure', 'schlampe', 'sau',

        // Contact info patterns
        'whatsapp', 'telegram', 'snapchat', 'instagram', 'facebook', 'skype',
        'phone', 'number', 'call me', 'email', '@', 'http', 'www',
    ];

    /**
     * Check if text contains inappropriate content
     *
     * @param string $text
     * @return bool
     */
    public function containsBadWords(string $text): bool
    {
        $text = strtolower($text);

        foreach ($this->badWords as $badWord) {
            if (strpos($text, strtolower($badWord)) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Filter bad words from text (replace with ***)
     *
     * @param string $text
     * @return string
     */
    public function filterBadWords(string $text): string
    {
        $filtered = $text;

        foreach ($this->badWords as $badWord) {
            $pattern = '/' . preg_quote($badWord, '/') . '/i';
            $replacement = str_repeat('*', strlen($badWord));
            $filtered = preg_replace($pattern, $replacement, $filtered);
        }

        return $filtered;
    }

    /**
     * Check if text contains contact information (phone, email, social media)
     * App stores prohibit sharing contact info to bypass in-app chat!
     *
     * @param string $text
     * @return bool
     */
    public function containsContactInfo(string $text): bool
    {
        // Phone number patterns
        $phonePatterns = [
            '/\+?[0-9]{10,15}/',
            '/\d{3}[-.\s]?\d{3}[-.\s]?\d{4}/',
            '/\(\d{3}\)\s?\d{3}[-.\s]?\d{4}/',
        ];

        foreach ($phonePatterns as $pattern) {
            if (preg_match($pattern, $text)) {
                return true;
            }
        }

        // Email pattern
        if (preg_match('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $text)) {
            return true;
        }

        // URL pattern
        if (preg_match('/(https?:\/\/)?(www\.)?[a-zA-Z0-9-]+\.[a-zA-Z]{2,}/', $text)) {
            return true;
        }

        // Social media keywords
        $socialKeywords = ['whatsapp', 'telegram', 'snapchat', 'instagram', 'facebook', 'snap:', 'insta:', 'fb:'];
        $textLower = strtolower($text);

        foreach ($socialKeywords as $keyword) {
            if (strpos($textLower, $keyword) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Validate message content
     * Returns true if message is safe, false if it should be blocked
     *
     * @param string $text
     * @return array ['safe' => bool, 'reason' => string|null]
     */
    public function validateMessage(string $text): array
    {
        if ($this->containsBadWords($text)) {
            return [
                'safe' => false,
                'reason' => 'inappropriate_language',
            ];
        }

        if ($this->containsContactInfo($text)) {
            return [
                'safe' => false,
                'reason' => 'contact_info_not_allowed',
            ];
        }

        return [
            'safe' => true,
            'reason' => null,
        ];
    }

    /**
     * Get moderation stats for user (for admin)
     *
     * @param int $userId
     * @return array
     */
    public function getUserModerationStats(int $userId): array
    {
        // Count violations
        $violations = \DB::table('messages')
            ->where('sender_id', $userId)
            ->where('is_flagged', true)
            ->count();

        $reports = \DB::table('reports')
            ->where('reported_user_id', $userId)
            ->count();

        return [
            'violations' => $violations,
            'reports' => $reports,
            'risk_level' => $this->calculateRiskLevel($violations, $reports),
        ];
    }

    /**
     * Calculate user risk level
     *
     * @param int $violations
     * @param int $reports
     * @return string
     */
    protected function calculateRiskLevel(int $violations, int $reports): string
    {
        $totalIssues = $violations + ($reports * 2); // Reports count double

        if ($totalIssues >= 5) {
            return 'high';
        } elseif ($totalIssues >= 2) {
            return 'medium';
        }

        return 'low';
    }
}
