<?php
use PHPUnit\Framework\TestCase;
use Mockery as m;

class SamediTest extends TestCase
{

    protected $provider;

    protected function setUp()
    {
        $this->provider = new \League\OAuth2\Client\Provider\Samedi([
            'clientId' => 'mock_client_id',
            'clientSecret' => 'mock_secret',
            'redirectUri' => 'mock_redirect_uri',
        ]);
    }
    public function testAuthorizationUrl()
    {
        $url = $this->provider->getAuthorizationUrl();
        $uri = parse_url($url);
        parse_str($uri['query'], $query);
        $this->assertArrayHasKey('client_id', $query);
        $this->assertArrayHasKey('response_type', $query);
        $this->assertArrayHasKey('redirect_uri', $query);
    }
    public function testGetBaseAccessTokenUrl()
    {
        $params = [];
        $url = $this->provider->getBaseAccessTokenUrl($params);
        $uri = parse_url($url);
        $this->assertEquals('/api/auth/v2/token', $uri['path']);
    }
    public function testGetAuthorizationUrl()
    {
        $url = $this->provider->getAuthorizationUrl();
        $uri = parse_url($url);
        $this->assertEquals('/api/auth/v2/authorize', $uri['path']);
    }
    public function testGetAccessToken()
    {
        $response = m::mock('Psr\Http\Message\ResponseInterface');
        $response->shouldReceive('getHeader')
            ->times(1)
            ->andReturn('application/json');
        $response->shouldReceive('getBody')
            ->times(1)
            ->andReturn('{"access_token":"mock_access_token","refresh_token":"mock_refresh_token","token_type":"bearer","expires_in":3600,"host":"mock_host"}');
        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')->times(1)->andReturn($response);
        $this->provider->setHttpClient($client);
        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
        $this->assertEquals('mock_access_token', $token->getToken());
        $this->assertEquals('mock_refresh_token', $token->getRefreshToken());
        $this->assertLessThanOrEqual(time() + 3600, $token->getExpires());
        $this->assertGreaterThanOrEqual(time(), $token->getExpires());
        $this->assertNull($token->getResourceOwnerId(), 'Samedi does not return user ID with access token. Expected null.');
    }

    public function testTrueAssertsToTrue() {
        $this->assertTrue(true);
        $stack = [];
               $this->assertSame(0, count($stack));

               array_push($stack, 'foo');
               $this->assertSame('foo', $stack[count($stack)-1]);
               $this->assertSame(1, count($stack));

               $this->assertSame('foo', array_pop($stack));
               $this->assertSame(0, count($stack));
    }
    public function testGetScopeSeparator() {
      $samedi = new \League\OAuth2\Client\Provider\Samedi;
      $this->assertEquals($samedi->getScopeSeparator() , ' ');



    }
}
 ?>
