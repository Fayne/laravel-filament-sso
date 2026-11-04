<?php

namespace Fayne\Sso\Parsers;

use Fayne\Sso\Contracts\ParserInterface;
use Illuminate\Http\Request;
use Laravel\Socialite\Contracts\User as UserInterface;
use Laravel\Socialite\Two\User;
use LightSaml\Model\Assertion\Assertion;
use LightSaml\Model\Context\DeserializationContext;
use LightSaml\Model\Protocol\Response;
use LightSaml\Model\XmlDSig\SignatureXmlReader;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

abstract class AbstractSamlParser implements ParserInterface
{
    /**
     * Request.
     */
    protected Request $request;

    /**
     * Provider name.
     */
    protected string $providerName;

    /**
     * XML string.
     */
    protected string $xml = '';

    /**
     * Response|null.
     */
    protected ?Response $response = null;

    /**
     * Assertion|null.
     */
    protected ?Assertion $assertion = null;

    public function __construct(Request $request, string $providerName)
    {
        $this->request = $request;
        $this->providerName = $providerName;
        $this->parseSaml();
    }

    public function getUser(): ?UserInterface
    {
        if (! $this->assertion || ! $this->verifyCertificate() || ! $this->verifyResponseConditions()) {
            return null;
        }

        $subject = $this->getSubjectValue();

        return (new User)->setRaw([
            'id' => $subject,
            'email' => $subject,
            'nickname' => $subject,
            'name' => $subject,
            'avatar' => '',
        ])->map([
            'id' => $subject,
            'email' => $subject,
            'nickname' => $subject,
            'name' => $subject,
            'avatar' => '',
        ]);
    }

    public static function redirect(): SymfonyResponse
    {
        // This method will be overridden by child classes
        throw new \Exception('Redirect method must be implemented in the child parser.');
    }

    /**
     * Verify certificate.
     */
    public function verifyCertificate(): bool
    {
        $signatureReader = $this->getSignatureReader();
        $certificates = $signatureReader?->getAllCertificates() ?? [];
        $configKey = sprintf('filament-sso.%s.saml.x509cert', $this->providerName);
        $configuredCertificate = preg_replace('/-----BEGIN CERTIFICATE-----|-----END CERTIFICATE-----|\s+/', '', (string) config($configKey));

        if (! current($certificates) || ! hash_equals($configuredCertificate, current($certificates))) {
            return false;
        }

        $algorithm = $signatureReader->getAlgorithm();
        if (! in_array($algorithm, [
            'http://www.w3.org/2001/04/xmldsig-more#rsa-sha256',
            'http://www.w3.org/2001/04/xmldsig-more#rsa-sha384',
            'http://www.w3.org/2001/04/xmldsig-more#rsa-sha512',
        ], true)) {
            return false;
        }

        $certificatePem = "-----BEGIN CERTIFICATE-----\n".chunk_split($configuredCertificate, 64, "\n")."-----END CERTIFICATE-----\n";
        $key = new XMLSecurityKey($algorithm, ['type' => 'public']);
        $key->loadKey($certificatePem, false, true);

        return $signatureReader->validate($key);
    }

    /**
     * Get XML string.
     */
    public function getXml(): string
    {
        return $this->xml;
    }

    /**
     * Parse SAML response.
     */
    protected function parseSaml(): self
    {
        $saml = $this->request->input('SAMLResponse');
        if (! $saml) {
            return $this;
        }

        $this->xml = base64_decode($saml, true) ?: '';

        try {
            if ($this->xml) {
                $deserializationContext = new DeserializationContext;
                $deserializationContext->getDocument()->loadXML($this->xml);
                $response = new Response;
                $response->deserialize($deserializationContext->getDocument()->firstChild, $deserializationContext);
                $this->response = $response;
                $this->assertion = $response->getFirstAssertion();
            }

            return $this;
        } catch (\Throwable) {
            throw new \RuntimeException('SAML response is invalid.');
        }
    }

    /**
     * Get certificates from response.
     */
    protected function getCertificates(): array
    {
        /** @var SignatureXmlReader $signature */
        $signature = $this->response->getSignature();

        return optional($signature)->getAllCertificates() ?? [];
    }

    private function getSignatureReader(): ?SignatureXmlReader
    {
        $signature = $this->response?->getSignature();

        return $signature instanceof SignatureXmlReader ? $signature : null;
    }

    private function verifyResponseConditions(): bool
    {
        $configuredIssuer = config(sprintf('filament-sso.%s.saml.issuer', $this->providerName));
        $responseIssuer = $this->response?->getIssuer()?->getValue();
        $assertionIssuer = $this->assertion?->getIssuer()?->getValue();

        if (blank($configuredIssuer) || $responseIssuer !== $configuredIssuer || $assertionIssuer !== $configuredIssuer) {
            return false;
        }

        $conditions = $this->assertion?->getConditions();
        if (! $conditions) {
            return false;
        }

        $now = time();
        $notBefore = $conditions->getNotBeforeTimestamp();
        $notOnOrAfter = $conditions->getNotOnOrAfterTimestamp();

        return ($notBefore === null || $notBefore <= $now)
            && ($notOnOrAfter === null || $notOnOrAfter > $now);
    }

    /**
     * Get subject value from assertion.
     */
    protected function getSubjectValue(): ?string
    {
        if (! $this->assertion) {
            return null;
        }

        return $this->assertion->getSubject()->getNameID()->getValue();
    }
}
