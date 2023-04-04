<?php

declare(strict_types=1);

namespace Aranyasen\LaravelSlack;

use Aranyasen\LaravelSlack\Exceptions\SlackNotificationException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

trait SlackApi
{
    private string $baseUrl = 'https://slack.com/api';

    private function chatPostMessage(array $data): void
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
    }

    private function getHttpClient(): PendingRequest
    {
        return Http::baseUrl($this->baseUrl)
            ->asJson()
            ->withToken(config('services.slack.token'));
    }
}
