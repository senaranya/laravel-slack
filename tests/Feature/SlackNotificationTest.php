<?php

declare(strict_types=1);

namespace Aranyasen\LaravelSlack\Tests\Feature;

use Aranyasen\LaravelSlack\Exceptions\SlackNotificationException;
use Aranyasen\LaravelSlack\SlackNotification;
use Aranyasen\LaravelSlack\Tests\TestCase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class SlackNotificationTest extends TestCase
{
    protected SlackNotification $slackNotification;

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        SlackNotification::fake();
        $this->slackNotification = (new SlackNotification())->channel('channel-1');
    }

    /** @test */
    public function target_channels_can_be_provided(): void
    {
        (new SlackNotification())
            ->channel('channel-1')
            ->send();
        Http::assertSent(static fn(Request $request) => $request['channel'] === 'channel-1');
    }

    /** @test */
    public function it_should_throw_exception_when_trying_to_send_without_setting_a_channel_name(): void
    {
        $this->expectException(SlackNotificationException::class);
        (new SlackNotification())
            ->send();
        Http::assertNothingSent();
    }

    /** @test */
    public function a_text_message_can_be_sent(): void
    {
        $sentence = fake()->sentence();
        $this->slackNotification
            ->text($sentence)
            ->send();
        Http::assertSent(static fn(Request $request) => $request['text'] === $sentence);
    }

    /** @test */
    public function a_header_can_be_added_to_a_message(): void
    {
        $sentence = fake()->sentence();
        $this->slackNotification
            ->header($sentence)
            ->send();

        Http::assertSent(static function (Request $request) use ($sentence) {
            return is_array($request['blocks']) &&
                self::areArraysSame(
                    $request['blocks'][0],
                    ['type' => 'header', 'text' => ['type' => 'plain_text', 'text' => $sentence]]
                );
        });
    }

    /** @test */
    public function a_context_can_be_added_to_a_message(): void
    {
        $sentence = fake()->sentence();
        $this->slackNotification
            ->context($sentence)
            ->send();

        Http::assertSent(static function (Request $request) use ($sentence) {
            return is_array($request['blocks']) &&
                self::areArraysSame(
                    $request['blocks'][0],
                    ['type' => 'context', 'elements' => [['type' => 'mrkdwn', 'text' => $sentence]]]
                );
        });
    }

    /** @test */
    public function a_content_divider_can_be_added_in_a_message(): void
    {
        $this->slackNotification
            ->divider()
            ->send();

        Http::assertSent(static function (Request $request) {
            return is_array($request['blocks']) && self::areArraysSame($request['blocks'][0], ['type' => 'divider']);
        });
    }

    /** @test */
    public function multiple_blocks_can_be_added_to_a_message(): void
    {
        $header = fake()->sentence();
        $context = fake()->sentence();
        $this->slackNotification
            ->header($header)
            ->context($context)
            ->send();

        Http::assertSent(static function (Request $request) use ($header, $context) {
            return is_array($request['blocks']) &&
                self::areArraysSame($request['blocks'], [
                    ['type' => 'header', 'text' => ['type' => 'plain_text', 'text' => $header]],
                    ['type' => 'context', 'elements' => [['type' => 'mrkdwn', 'text' => $context]]],
                ]);
        });
    }

    /** @test */
    public function a_section_can_be_added(): void
    {
        $sentence = fake()->sentence();
        $this->slackNotification
            ->section()
            ->fields()
            ->markdown($sentence)
            ->endFields()
            ->endSection()
            ->send();

        Http::assertSent(static function (Request $request) use ($sentence) {
            return is_array($request['blocks']) &&
                self::areArraysSame($request['blocks'][0], [
                    'type' => 'section',
                    'fields' => [
                        ['type' => 'mrkdwn', 'verbatim' => false, 'text' => $sentence]
                    ],
                ]);
        });
    }

    /** @test */
    public function it_cleans_up_properly_after_sending_message(): void
    {
        $slackNotification = new SlackNotification();
        self::assertSame(
            ['blocks' => [['type' => 'header', 'text' => ['type' => 'plain_text', 'text' => 'aaaa']]]],
            $slackNotification->header('aaaa')->toArray()
        );
        self::assertSame(
            ['blocks' => [['type' => 'header', 'text' => ['type' => 'plain_text', 'text' => 'bbbb']]]],
            $slackNotification->header('bbbb')->toArray(),
            'It should have created a fresh message'
        );
    }
    // /** @test */
    // public function for_a_given_token_the_channels_are_cached_for_a_day(): void
    // {
    //     // Not required as we can directly use the channel's name to send the message
    //     // Ref: https://api.slack.com/methods/chat.postMessage#arg_channel
    // }

    // /** @test */
    // public function channel_cache_can_be_regenerated(): void
    // {
    //     // Not required as we can directly use the channel's name to send the message
    //     // Ref: https://api.slack.com/methods/chat.postMessage#arg_channel
    // }
    // TODO: Create a separate unit test SlackMessageCompositionTest, and use the dump() method to test it. The feature
    //   test should only test anything to do with http, like send()

    /** @test */
    public function a_file_can_be_sent(): void
    {
        $testFile = 'testFileOriginal.txt';
        file_put_contents($testFile, 'test content');
        $this->slackNotification
            ->file($testFile)
            ->withFileName('testname.txt')
            ->withInitialComment('test comment')
            ->withTitle('test title')
            ->upload();

        Http::assertSent(function (Request $request) {
            return $request->hasFile('file', 'test content', 'testname.txt')
                && $this->requestHasFormData($request, 'channels', 'channel-1')
                && $this->requestHasFormData($request, 'title', 'test title')
                && $this->requestHasFormData($request, 'initial_comment', 'test comment');
        });
        File::delete($testFile);
    }

    private function requestHasFormData(Request $request, string $name, string $content): bool
    {
        foreach ($request->data() as $row) {
            if ($row['name'] === $name && $row['contents'] === $content) {
                return true;
            }
        }
        return false;
    }
}
