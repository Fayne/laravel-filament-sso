<?php

namespace Fayne\Sso\Drivers\Parsers;

use Fayne\Sso\Contracts\ParserInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Laravel\Socialite\Contracts\User as UserInterface;
use Laravel\Socialite\Two\User;
use LightSaml\Model\Assertion\Assertion;
use LightSaml\Model\Context\DeserializationContext;
use LightSaml\Model\Protocol\Response;
use LightSaml\Model\XmlDSig\SignatureXmlReader;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class SamlParser implements ParserInterface
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var string
     */
    protected $xml = '';

    /**
     * @var Response|null
     */
    protected $response = null;

    /**
     * @var Assertion|null
     */
    protected $assertion = null;

    /**
     * SamlParser constructor.
     *
     * @throws \Exception
     */
    public function __construct($request)
    {
        $this->request = $request;
        $this->parseSaml();
    }

    public function getUser(): ?UserInterface
    {
        if (! $this->assertion) {
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
        return new RedirectResponse(config('filament-sso.okta.saml.redirect'));
    }

    public function verifyCertificate(): bool
    {
        $certificates = $this->getCertificates();

        return current($certificates) && current($certificates) === config('filament-sso.okta.saml.x509cert');
    }

    public function getXml(): string
    {
        return $this->xml;
    }

    /**
     * @return $this;
     *
     * @throws \Exception
     */
    protected function parseSaml(): self
    {
        $saml = $this->request->input('SAMLResponse');
        if (! $saml) {
            // Handle case where SAMLResponse is missing (e.g. initiating login)
            return $this;
        }

        $this->xml = base64_decode($saml);

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
        } catch (\Exception $exception) {
            throw new \Exception('Saml is invalid:'.$saml);
        }
    }

    protected function getSubjectValue(): ?string
    {
        return $this->assertion->getSubject()->getNameID()->getValue();
    }

    protected function getAttributes(): array
    {
        $attributes = optional($this->assertion->getFirstAttributeStatement())->getAllAttributes() ?? [];

        foreach ($attributes as $attribute) {
            $labels[] = $attribute->getName();
            $values[] = $attribute->getFirstAttributeValue();
        }

        if (empty($labels)) {
            return [];
        }

        return array_combine($labels ?? [], $values ?? []);
    }

    protected function getCertificates(): array
    {
        /** @var SignatureXmlReader $signature */
        $signature = $this->response->getSignature();

        return optional($signature)->getAllCertificates() ?? [];
    }
}
