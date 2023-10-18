<?php

declare(strict_types=1);

namespace Aranyasen\LaravelSlack;

use Aranyasen\LaravelSlack\Exceptions\SlackNotificationException;

trait FileUpload
{
    private string $path = '';
    private string $contents = '';
    private string $filename = '';
    private string $initialComment = '';
    private string $title = '';

    public function file(string $filePath, string $filename): self
    {
        $this->path = $filePath;
        $this->contents = file_get_contents($filePath);
        $this->filename = $filename;
        return $this;
    }

    public function withInitialComment(string $comment): self
    {
        $this->initialComment = $comment;
        return $this;
    }

    public function withTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    private function finalizeFileData(): array
    {
        if (!$this->path) {
            throw new SlackNotificationException("File path was not provided");
        }
        if (!$this->contents) {
            throw new SlackNotificationException("File could not be read or empty");
        }
        if (!$this->filename) {
            throw new SlackNotificationException("Need a file name");
        }

        $fileData['contents'] = $this->contents;
        $fileData['filename'] = $this->filename;

        $miscData['initial_comment'] = $this->initialComment;
        $miscData['title'] = $this->title;

        $this->cleanupFileData();
        return [$fileData, $miscData];
    }

    private function cleanupFileData(): void
    {
        $this->path = '';
        $this->contents = '';
        $this->filename = '';
        $this->initialComment = '';
        $this->title = '';
    }
}
