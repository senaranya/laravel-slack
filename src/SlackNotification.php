<?php

declare(strict_types=1);

namespace Aranyasen\LaravelSlack;

use Aranyasen\LaravelSlack\Exceptions\SlackNotificationException;

class SlackNotification
{
    use SlackApi;
    use MessageComposition;
    use FileUpload;

    private string $channelName;

    public function channel(string $channelName): self
    {
        $this->channelName = $channelName;
        return $this;
    }

    /**
     * @throws SlackNotificationException
     */
    public function send(): array
    {
        if (empty($this->channelName)) {
            throw new SlackNotificationException("Channel name not provided");
        }
        $data = $this->finalize();
        return $this->chatPostMessage($data);
    }

    public function upload(): array
    {
        if (empty($this->channelName)) {
            throw new SlackNotificationException("Channel name not provided");
        }
        [$fileData, $miscData] = $this->finalizeFileData();
        return $this->filesUpload($fileData, $miscData);
    }

    public function dump(bool $asJson = false): self
    {
        $asJson
            ? dump(json_encode($this->toArray(), JSON_PRETTY_PRINT))
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
