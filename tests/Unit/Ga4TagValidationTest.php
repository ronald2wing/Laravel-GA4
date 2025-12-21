<?php

declare(strict_types=1);

namespace Ronald2Wing\LaravelGa4\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ronald2Wing\LaravelGa4\Ga4Tag;

class Ga4TagValidationTest extends TestCase
{
    public static function validIdProvider(): array
    {
        return [
            'standard alphanumeric' => ['G-1234567890'],
            'uppercase letters' => ['G-ABC123DEF4'],
            'hyphenated' => ['G-TEST-123-ABC'],
            'underscores' => ['G-123_ABC_456'],
            'numbers only' => ['G-9876543210'],
            'all zeros' => ['G-0000000000'],
        ];
    }

    #[Test]
    #[DataProvider('validIdProvider')]
    public function accepts_valid_ids(string $id): void
    {
        $script = Ga4Tag::fromConfig(['measurement_id' => $id])->render();

        $this->assertStringContainsString($id, $script);
        $this->assertStringContainsString('googletagmanager.com/gtag/js', $script);
    }

    #[Test]
    public function accepts_extremely_long_id(): void
    {
        $longId = 'G-'.str_repeat('A', 497);
        $script = (new Ga4Tag($longId))->render();

        $this->assertStringContainsString($longId, $script);
    }

    public static function externalWhitespaceProvider(): array
    {
        return [
            'leading space' => [' G-TEST123'],
            'trailing space' => ['G-TEST123 '],
            'both' => [' G-TEST123 '],
            'tabs' => ["\tG-TEST123\t"],
            'newlines' => ["\nG-TEST123\n"],
        ];
    }

    #[Test]
    #[DataProvider('externalWhitespaceProvider')]
    public function trims_external_whitespace(string $id): void
    {
        $script = Ga4Tag::fromConfig(['measurement_id' => $id])->render();

        $this->assertStringContainsString('G-TEST123', $script);
    }

    public static function dangerousPayloadProvider(): array
    {
        return [
            'script tag' => ['G-TEST"><script>alert("xss")</script>'],
            'img onerror' => ['G-TEST\'"`><img src=x onerror=alert(1)>'],
            'js statement' => ['G-TEST";alert("xss");//'],
            'single quote' => ['G-TEST\';alert("xss");//'],
            'backtick' => ['G-TEST`;alert("xss");//'],
        ];
    }

    #[Test]
    #[DataProvider('dangerousPayloadProvider')]
    public function rejects_dangerous_payloads(string $id): void
    {
        $this->assertEmpty(Ga4Tag::fromConfig(['measurement_id' => $id])->render());
    }

    public static function unicodeProvider(): array
    {
        return [
            'greek' => ['G-TEST-αβγδε'],
            'japanese' => ['G-TEST-日本語'],
            'emoji' => ['G-TEST-😀🎉'],
            'accented' => ['G-TEST-café'],
            'cyrillic' => ['G-TEST-русский'],
            'arabic' => ['G-TEST-العربية'],
        ];
    }

    #[Test]
    #[DataProvider('unicodeProvider')]
    public function rejects_unicode(string $id): void
    {
        $this->assertEmpty(Ga4Tag::fromConfig(['measurement_id' => $id])->render());
    }

    public static function specialCharProvider(): array
    {
        return [
            'ampersand' => ['G-TEST&param=value'],
            'hash' => ['G-TEST#fragment'],
            'question mark' => ['G-TEST?query=string'],
            'plus' => ['G-TEST+plus'],
            'percent' => ['G-TEST%20encoded'],
            'at symbol' => ['G-TEST@email'],
            'equals' => ['G-TEST=value'],
            'semicolon' => ['G-TEST;param'],
            'comma' => ['G-TEST,separated'],
            'angle brackets' => ['G-TEST<angle>'],
        ];
    }

    #[Test]
    #[DataProvider('specialCharProvider')]
    public function rejects_special_characters(string $id): void
    {
        $this->assertEmpty(Ga4Tag::fromConfig(['measurement_id' => $id])->render());
    }

    public static function embeddedWhitespaceProvider(): array
    {
        return [
            'newline' => ["G-TEST\nNEWLINE"],
            'tab' => ["G-TEST\tTAB"],
            'CRLF' => ["G-TEST\r\nCRLF"],
            'mixed' => ["G-TEST\n\tMIXED"],
        ];
    }

    #[Test]
    #[DataProvider('embeddedWhitespaceProvider')]
    public function rejects_embedded_whitespace(string $id): void
    {
        $this->assertEmpty(Ga4Tag::fromConfig(['measurement_id' => $id])->render());
    }

    public static function whitespaceOnlyProvider(): array
    {
        return [
            'spaces' => ['   '],
            'tab' => ["\t"],
            'newline' => ["\n"],
            'CRLF' => ["\r\n"],
            'mixed' => [" \t\n\r "],
            'empty' => [''],
        ];
    }

    #[Test]
    #[DataProvider('whitespaceOnlyProvider')]
    public function rejects_whitespace_only(string $id): void
    {
        $this->assertEmpty(Ga4Tag::fromConfig(['measurement_id' => $id])->render());
    }

    public static function malformedPrefixProvider(): array
    {
        return [
            'no prefix' => ['1234567890'],
            'prefix only' => ['G-'],
            'lowercase prefix' => ['g-ABC123'],
            'mixed case body' => ['G-AbC123DeF'],
        ];
    }

    #[Test]
    #[DataProvider('malformedPrefixProvider')]
    public function rejects_malformed_prefix(string $id): void
    {
        $this->assertEmpty(Ga4Tag::fromConfig(['measurement_id' => $id])->render());
    }
}
