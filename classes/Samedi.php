<?php
namespace League\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;


class Samedi extends AbstractProvider
{
    use BearerAuthorizationTrait;
    /**
     * @var string
     */
    protected $apiVersionAuth = 'v2';
    /**
     * @var string
     */
    protected $apiVersionBooking = 'v3';

    /**
     * @param array $options
     * @param array $collaborators
     */
    public function __construct(array $options = [], array $collaborators = [])
    {
        parent::__construct($options, $collaborators);
        foreach ($options as $option => $value) {
            if (property_exists($this, $option)) {
                $this->{$option} = $value;
            }
        }
    }
    /**
    * @Override
    */
    public function getBaseAuthorizationUrl(){
        return 'https://patient.samedi.de/api/auth/'.$this->apiVersionAuth.'/authorize';
    }
    public function getBaseAccessTokenUrl(array $params) {
        return 'https://patient.samedi.de/api/auth/'.$this->apiVersionAuth.'/token';
    }
    public function getResourceOwnerDetailsUrl(AccessToken $token) {
        return 'https://patient.samedi.de/api/booking/'.$this->apiVersionBooking.'/user';
    }

    protected function getDefaultScopes() {
        return ['public'];
    }

    /**
     * Returns the string that should be used to separate scopes when building
     * the URL for requesting an access token.
     *
     * @return string Scope separator, defaults to ','
     */
    public function getScopeSeparator()
    {
        return ' ';
    }

    protected function checkResponse(ResponseInterface $response, $data){
      if (isset($data['error'])) {
        $statusCode = $response->getStatusCode();
        $error = $data['error'];

        $errorDescription = $data['error_description'];
        $errorLink = (isset($data['error_uri']) ? $data['error_uri'] : false);
        throw new IdentityProviderException(
            $statusCode . ' - ' . $errorDescription . ': ' . $error . ($errorLink ? ' (see: ' . $errorLink . ')' : ''),
            $response->getStatusCode(),
            $response
        );
      }
    }

    /**
     * Generate a user object from a successful user details request.
     *
     * @param array $response
     * @param AccessToken $token
     *
     * @return League\OAuth2\Client\Provider\ResourceOwnerInterface
     */
    protected function createResourceOwner(array $response, AccessToken $token) {
        return new SamediResourceOwner($response);
    }

    /**
     * Returns a prepared request for requesting an access token.
     *
     * @param array $params
     *
     * @return Psr\Http\Message\RequestInterface
     */
    protected function getAccessTokenRequest(array $params)
    {
        $request = parent::getAccessTokenRequest($params);
        $uri = $request->getUri()
            ->withUserInfo($this->clientId, $this->clientSecret);
        return $request->withUri($uri);
    }
    /**
     * Returns the default headers used by this provider.
     *
     * Typically this is used to set 'Accept' or 'Content-Type' headers.
     *
     * @return array
     */
    protected function getDefaultHeaders()
    {
        return [
            'Accept'          => 'application/json',
            'Accept-Encoding' => 'gzip',
        ];
    }
}
