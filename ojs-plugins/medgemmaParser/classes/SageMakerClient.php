<?php

/**
 * @file plugins/generic/medgemmaParser/classes/SageMakerClient.php
 *
 * Distributed under the GNU GPL v3.
 *
 * @class SageMakerClient
 *
 * @brief Minimal client for SageMaker Runtime InvokeEndpoint, signed with
 *  AWS Signature Version 4. Avoids bundling the whole AWS SDK in the plugin.
 */

namespace APP\plugins\generic\medgemmaParser\classes;

use APP\core\Application;
use Exception;
use GuzzleHttp\Exception\RequestException;

class SageMakerClient
{
    /** OpenAI-compatible chat payload (vLLM, TGI Messages API, JumpStart) */
    public const FORMAT_MESSAGES = 'messages';

    /** Hugging Face TGI "inputs" payload with a Gemma chat prompt */
    public const FORMAT_INPUTS = 'inputs';

    private const SERVICE = 'sagemaker';

    public function __construct(
        private string $region,
        private string $accessKeyId,
        private string $secretAccessKey,
        private string $endpointName,
        private string $payloadFormat = self::FORMAT_MESSAGES,
        private ?string $sessionToken = null,
        private int $timeout = 120,
    ) {
    }

    /**
     * Send a system + user prompt and return the generated text.
     */
    public function generate(string $systemPrompt, string $userPrompt, int $maxTokens): string
    {
        $payload = $this->payloadFormat === self::FORMAT_INPUTS
            ? [
                'inputs' => "<start_of_turn>user\n{$systemPrompt}\n\n{$userPrompt}<end_of_turn>\n<start_of_turn>model\n",
                'parameters' => [
                    'max_new_tokens' => $maxTokens,
                    'do_sample' => false,
                    'return_full_text' => false,
                ],
            ]
            : [
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $userPrompt],
                ],
                'max_tokens' => $maxTokens,
                'temperature' => 0,
            ];

        $response = $this->invoke(json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
        return $this->extractText($response);
    }

    /**
     * Call InvokeEndpoint with a JSON body and return the decoded response.
     */
    public function invoke(string $body): mixed
    {
        $host = "runtime.sagemaker.{$this->region}.amazonaws.com";
        $path = '/endpoints/' . rawurlencode($this->endpointName) . '/invocations';
        $headers = $this->signHeaders('POST', $host, $path, $body);

        try {
            $response = Application::get()->getHttpClient()->request('POST', "https://{$host}{$path}", [
                'headers' => $headers,
                'body' => $body,
                'timeout' => $this->timeout,
            ]);
        } catch (RequestException $e) {
            $detail = $e->hasResponse() ? (string) $e->getResponse()->getBody() : $e->getMessage();
            throw new Exception('SageMaker InvokeEndpoint failed: ' . mb_substr($detail, 0, 2000), 0, $e);
        }

        $decoded = json_decode((string) $response->getBody(), true);
        if ($decoded === null) {
            throw new Exception('SageMaker returned a non-JSON response.');
        }
        return $decoded;
    }

    /**
     * Build the headers for an AWS SigV4 signed request.
     */
    private function signHeaders(string $method, string $host, string $path, string $body): array
    {
        $amzDate = gmdate('Ymd\THis\Z');
        $date = substr($amzDate, 0, 8);
        $payloadHash = hash('sha256', $body);

        $headers = [
            'accept' => 'application/json',
            'content-type' => 'application/json',
            'host' => $host,
            'x-amz-content-sha256' => $payloadHash,
            'x-amz-date' => $amzDate,
        ];
        if ($this->sessionToken) {
            $headers['x-amz-security-token'] = $this->sessionToken;
        }
        ksort($headers);

        $canonicalHeaders = '';
        foreach ($headers as $name => $value) {
            $canonicalHeaders .= $name . ':' . trim($value) . "\n";
        }
        $signedHeaders = implode(';', array_keys($headers));

        // Non-S3 services expect every path segment to be URI-encoded twice.
        $canonicalUri = implode('/', array_map('rawurlencode', explode('/', $path)));

        $canonicalRequest = implode("\n", [$method, $canonicalUri, '', $canonicalHeaders, $signedHeaders, $payloadHash]);
        $scope = "{$date}/{$this->region}/" . self::SERVICE . '/aws4_request';
        $stringToSign = implode("\n", ['AWS4-HMAC-SHA256', $amzDate, $scope, hash('sha256', $canonicalRequest)]);

        $signingKey = hash_hmac('sha256', 'aws4_request',
            hash_hmac('sha256', self::SERVICE,
                hash_hmac('sha256', $this->region,
                    hash_hmac('sha256', $date, 'AWS4' . $this->secretAccessKey, true),
                    true),
                true),
            true);
        $signature = hash_hmac('sha256', $stringToSign, $signingKey);

        $headers['authorization'] = "AWS4-HMAC-SHA256 Credential={$this->accessKeyId}/{$scope}, SignedHeaders={$signedHeaders}, Signature={$signature}";
        unset($headers['host']);
        return $headers;
    }

    /**
     * Pull the generated text out of the response shapes used by
     * vLLM/TGI Messages API and by the TGI generate API.
     */
    private function extractText(mixed $response): string
    {
        $text = $response['choices'][0]['message']['content']
            ?? $response['choices'][0]['text']
            ?? $response[0]['generated_text']
            ?? $response['generated_text']
            ?? $response['predictions'][0]
            ?? null;

        if (!is_string($text) || $text === '') {
            throw new Exception('Unexpected SageMaker response: ' . mb_substr(json_encode($response), 0, 1000));
        }
        return $text;
    }
}
