<?php

declare(strict_types=1);

namespace Ronald2Wing\LaravelGa4;

use Illuminate\Contracts\Support\Htmlable;
use Stringable;

/**
 * @phpstan-type Ga4Config array{
 *     measurement_id?: mixed,
 *     parameters?: mixed,
 * }
 */
final class Ga4Tag implements Htmlable, Stringable
{
    private const MEASUREMENT_ID_PATTERN = '/^G-[A-Z0-9_-]+$/';

    private const GTAG_SCRIPT_URL = 'https://www.googletagmanager.com/gtag/js?id=';

    private const JSON_ENCODE_FLAGS = JSON_THROW_ON_ERROR
        | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;

    private readonly ?string $id;

    /**
     * @param  array<string, mixed>  $parameters
     */
    public function __construct(
        ?string $id,
        private readonly array $parameters = [],
    ) {
        $this->id = self::normalizeId($id);
    }

    /**
     * @param  Ga4Config  $config
     */
    public static function fromConfig(array $config): self
    {
        $id = $config['measurement_id'] ?? null;
        $params = $config['parameters'] ?? [];

        return new self(
            id: is_string($id) ? $id : null,
            parameters: is_array($params) ? $params : [],
        );
    }

    public function isEnabled(): bool
    {
        return $this->id !== null;
    }

    public function render(): string
    {
        if ($this->id === null) {
            return '';
        }

        $scriptSrc = self::escapeAttribute(self::GTAG_SCRIPT_URL.rawurlencode($this->id));
        $encodedId = self::encodeJson($this->id);
        $encodedParams = $this->parameters === [] ? '' : ', '.self::encodeJson($this->parameters);

        return <<<HTML
            <!-- Google Analytics 4 tag via https://github.com/ronald2wing/Laravel-GA4 -->
            <script async src="{$scriptSrc}"></script>
            <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', {$encodedId}{$encodedParams});
            </script>
            HTML;
    }

    public function toHtml(): string
    {
        return $this->render();
    }

    public function __toString(): string
    {
        return $this->render();
    }

    private static function normalizeId(?string $id): ?string
    {
        if ($id === null) {
            return null;
        }

        $id = trim($id);

        return preg_match(self::MEASUREMENT_ID_PATTERN, $id) === 1 ? $id : null;
    }

    private static function escapeAttribute(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
    }

    private static function encodeJson(mixed $value): string
    {
        return json_encode($value, self::JSON_ENCODE_FLAGS);
    }
}
