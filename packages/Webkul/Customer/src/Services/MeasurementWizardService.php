<?php

namespace Webkul\Customer\Services;

use Webkul\Customer\Contracts\Customer;
use Webkul\Customer\Data\MeasurementFields;
use Webkul\Customer\Models\MeasurementProfile;

/**
 * Bridges the store with the standalone Aso Measurement Service.
 *
 * Responsibilities:
 *  - mint short-lived, signed launch tokens (HS256 JWT) carrying the customer
 *    context and the canonical field definitions, and
 *  - verify the HMAC-signed callback the service sends back on completion,
 *  - decode a launch token echoed back in that callback to recover the
 *    trusted customer id server-side.
 *
 * The JWT is hand-rolled (HS256) to avoid adding a dependency; it is a standard
 * token the service decodes with PyJWT.
 */
class MeasurementWizardService
{
    public function __construct(
        protected MeasurementService $measurementService
    ) {}

    /**
     * Build the full wizard URL (with launch token) for a customer.
     */
    public function launchUrl(Customer $customer, ?int $profileId, ?string $returnUrl): string
    {
        $token = $this->issueToken($customer, $profileId, $returnUrl);

        $base = rtrim((string) config('measurement_service.url'), '/');

        return $base.'/?token='.rawurlencode($token);
    }

    /**
     * Mint a signed launch token.
     */
    public function issueToken(Customer $customer, ?int $profileId, ?string $returnUrl): string
    {
        $gender = MeasurementFields::resolveGender($customer->gender);

        $profile = $this->measurementService->resolveProfile($customer, $profileId);

        $fields = [];

        foreach (MeasurementFields::fieldsForGender($gender) as $slug => $field) {
            $fields[] = [
                'slug'  => $slug,
                'label' => $field['label'],
                'group' => $field['group'],
            ];
        }

        $payload = [
            'sub'              => (string) $customer->id,
            'name'             => $customer->first_name,
            'gender'           => $gender,
            'unit'             => $profile?->unit ?: MeasurementFields::UNIT_INCHES,
            'profile_id'       => $profileId,
            'fields'           => $fields,
            'fit_preferences'  => MeasurementFields::fitPreferenceOptions(),
            'fit_note_options' => MeasurementFields::fitNoteOptions(),
            'existing_profile' => $this->existingProfilePayload($profile),
            'return_url'       => $returnUrl,
            'iat'              => time(),
            'exp'              => time() + (int) config('measurement_service.token_ttl', 600),
        ];

        return $this->encodeJwt($payload);
    }

    /**
     * Decode + verify a launch token (used on the callback).
     *
     * @return array<string, mixed>|null
     */
    public function decodeToken(string $token): ?array
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            return null;
        }

        [$headerB64, $payloadB64, $signatureB64] = $parts;

        $expected = $this->b64UrlEncode(
            hash_hmac('sha256', $headerB64.'.'.$payloadB64, $this->secret(), true)
        );

        if (! hash_equals($expected, $signatureB64)) {
            return null;
        }

        $payload = json_decode($this->b64UrlDecode($payloadB64), true);

        if (! is_array($payload)) {
            return null;
        }

        if (isset($payload['exp']) && time() > (int) $payload['exp']) {
            return null;
        }

        return $payload;
    }

    /**
     * Verify a signed callback body.
     */
    public function verifyCallbackSignature(?string $signature, ?string $timestamp, string $rawBody): bool
    {
        if (! $signature || ! $timestamp) {
            return false;
        }

        $tolerance = (int) config('measurement_service.callback_tolerance', 300);

        if (abs(time() - (int) $timestamp) > $tolerance) {
            return false;
        }

        $expected = hash_hmac('sha256', $timestamp.'.'.$rawBody, $this->secret());

        return hash_equals($expected, $signature);
    }

    /**
     * Existing profile context for prefilling the wizard.
     *
     * @return array<string, mixed>|null
     */
    protected function existingProfilePayload(?MeasurementProfile $profile): ?array
    {
        if (! $profile) {
            return null;
        }

        return [
            'profile_id'      => $profile->id,
            'profile_name'    => $profile->name,
            'fit_preference'  => $profile->fit_preference,
            'fit_notes'       => $profile->fit_notes ?: [],
            'fit_notes_other' => $profile->fit_notes_other,
        ];
    }

    protected function encodeJwt(array $payload): string
    {
        $header = $this->b64UrlEncode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $body = $this->b64UrlEncode(json_encode($payload));

        $signature = $this->b64UrlEncode(
            hash_hmac('sha256', $header.'.'.$body, $this->secret(), true)
        );

        return $header.'.'.$body.'.'.$signature;
    }

    protected function secret(): string
    {
        return (string) config('measurement_service.secret');
    }

    protected function b64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    protected function b64UrlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/')) ?: '';
    }
}
