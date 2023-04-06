<?php

declare(strict_types=1);

namespace Aranyasen\LaravelSlack;

use Aranyasen\LaravelSlack\Exceptions\SlackNotificationException;

trait MessageComposition
{
    private string $text = '';
    private array $blocks = [];
    private array $section = [];
    private array|null $fields = null;

    public function block(array $block): self
    {
        if ($this->blocks) {
            throw new SlackNotificationException('This method can not exist with other composition methods');
        }
        $this->blocks = $block;
        return $this;
    }

    public function text(string $text): self
    {
        $this->text = $text;
        return $this;
    }

    public function lists(array $items, string $marker = '•'): self
    {
        $markedItems = preg_filter('/^/', "$marker ", $items);
        $text = implode("\n", $markedItems);
        $this
            ->section($text)
            ->endSection();
        return $this;
    }

    public function header(string $header): self
    {
        if ($this->section) {
            throw new SlackNotificationException('A header can not be inside a section');
        }

        $this->blocks[] = [
            'type' => 'header',
            'text' => [
                'type' => 'plain_text',
                'text' => $header
            ]
        ];
        return $this;
    }

    /**
     * Context with a single text element
     */
    public function context(string $text): self
    {
        $this->blocks[] = [
            'type' => 'context',
            'elements' => [
                [
                    'type' => 'mrkdwn',
                    'text' => $text
                ]
            ]
        ];
        return $this;
    }

    /**
     * Context with multiple elements
     * Ref: https://api.slack.com/reference/block-kit/blocks#context
     */
    public function contextElements(array $contents): self
    {
        if (count($contents) > 10) {
            throw new SlackNotificationException("Maximum number of items in context elements is 10");
        }
        $elements = [];
        foreach ($contents as $content) {
            $elements[] = $content;
        }
        $this->blocks[] = [
            'type' => 'context',
            'elements' => $elements
        ];
        return $this;
    }

    public function divider(): self
    {
        $this->blocks[] = [
            'type' => 'divider',
        ];
        return $this;
    }

    /**
     * @param  bool  $verbatim  when false (default), URLs will be auto-converted into links, conversation names will
     * be link-ified, and certain mentions will be automatically parsed.
     * @throws SlackNotificationException
     */
    public function markdown(string $text, bool $verbatim = false): self
    {
        if ($this->fields === null) {
            throw new SlackNotificationException('markdown can not exist outside a field');
        }
        $this->fields[] = [
            'type' => 'mrkdwn',
            'verbatim' => $verbatim,
            'text' => $text
        ];
        return $this;
    }

    /**
     * @param  bool  $emoji  When true, emojis show as emoji, else as text
     * @throws SlackNotificationException
     */
    public function plainText(string $text, bool $emoji = false): self
    {
        if ($this->fields === null) {
            throw new SlackNotificationException('plain_text can not exist outside a field');
        }
        $this->fields[] = [
            'type' => 'plain_text',
            'emoji' => $emoji,
            'text' => $text
        ];
        return $this;
    }

    /**
     * Ref: https://api.slack.com/reference/block-kit/blocks#section
     *
     * @throws SlackNotificationException
     */
    public function section(string $text = ''): self
    {
        if (filled($this->section)) {
            throw new SlackNotificationException("Nested sections are not supported yet");
        }

        $section['type'] = 'section';

        if ($text) {
            $section['text'] = [
                'type' => 'mrkdwn',
                'text' => $text
            ];
        }
        $this->section = $section;
        return $this;
    }

    public function endSection(): self
    {
        $this->blocks[] = $this->section; // Push to $blocks
        $this->section = []; // and reset
        return $this;
    }

    public function fields(): self
    {
        $this->fields = [];
        return $this;
    }

    public function endFields(): self
    {
        $this->section['fields'] = $this->fields; // Push to $section
        $this->fields = null; // and reset
        return $this;
    }

    private function finalize(): array
    {
        if ($this->section) {
            throw new SlackNotificationException("A section was opened but never closed in the message");
        }
        if ($this->fields !== null) {
            throw new SlackNotificationException("A fields was opened but never closed in the message");
        }

        $data = [];
        if (filled($this->text)) {
            $data['text'] = $this->text;
        }
        if (filled($this->blocks)) {
            $data['blocks'] = $this->blocks;
        }
        $this->cleanUp();
        return $data;
    }

    private function cleanUp(): void
    {
        $this->text = '';
        $this->blocks = [];
        $this->section = [];
        $this->fields = null;
    }
}
