<?php

declare(strict_types=1);

namespace Aranyasen\LaravelSlack;

use Aranyasen\LaravelSlack\Exceptions\SlackNotificationException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

trait SlackApi
{
    private const BASE_URL = 'https://slack.com/api';

    private function chatPostMessage(array $data): array
    {
        $response = $this
            ->getHttpClient()
            ->post('chat.postMessage', [
                'channel' => $this->channelName, # https://api.slack.com/methods/chat.postMessage#arg_channel
                ...$data
            ]);

        if ($response->failed()) {
            throw new SlackNotificationException("Failed to send Slack message: '" . $response->body() . "'");
        }

        if ($response->json('ok') === false) {
            throw new SlackNotificationException("Failed to send Slack message: '" . $response->json('error') . "'");
        }

        return $response->json();
    }

    private function getHttpClient(): PendingRequest
    {
        return Http::baseUrl(self::BASE_URL)
            ->asJson()
            ->withToken(config('laravel-slack.token'));
    }

    /**
     * For testing
     */
    public static function fake(): void
    {
        config(['services.slack.token' => fake()->uuid()]);
        Http::fake([
            self::BASE_URL . "/conversations.list" => Http::response([]),
            self::BASE_URL . "/chat.postMessage" => Http::response([]),
        ]);
    }
}
