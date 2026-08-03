<?php
/**
 * CharymeldService — Local RAG implementation.
 *
 * Replaces the former Anthropic Claude API call.
 * All knowledge is sourced directly from the archive database.
 * No external API keys required.
 */

require_once BASE_PATH . '/services/ArchiveIntelligence.php';

class CharymeldService
{
    private ArchiveIntelligence $intelligence;

    public function __construct()
    {
        $this->intelligence = new ArchiveIntelligence(Database::getInstance());
    }

    /**
     * Process a chat message and return an archive-grounded reply.
     *
     * @param string $message  The user's message
     * @param string $context  Legacy param (ignored — intelligence queries DB directly)
     * @param array  $history  Conversation history for follow-up awareness
     * @return string|null
     */
    public function chat(string $message, string $context = '', array $history = []): ?string
    {
        try {
            return $this->intelligence->chat($message, $context, $history);
        } catch (\Throwable $e) {
            error_log('CharymeldService error: ' . $e->getMessage());
            return null;
        }
    }
}
