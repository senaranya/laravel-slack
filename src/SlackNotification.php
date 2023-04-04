<?php

declare(strict_types=1);

namespace Aranyasen\LaravelSlack;

use Aranyasen\LaravelSlack\Exceptions\SlackNotificationException;

class SlackNotification
{
    use SlackApi;
    use MessageComposition;

    private string $channelName;

    public function to(string $channelName): self
    {
        $this->channelName = $channelName;
        return $this;
    }

    /**
     * @throws SlackNotificationException
     */
    public function send(): void
    {
        $data = $this->finalize();
        $this->chatPostMessage($data);
    }

    public function dump(bool $asJson = false): self
    {
        $asJson
            ? dump(json_encode($this->toArray()))
            : dump($this->toArray());

        return $this;
    }

    /**
     * @throws SlackNotificationException
     */
    public function toArray(): array
    {
        return $this->finalize();
    }
}
