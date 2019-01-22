<?php
namespace Bee\ThirdAuth\Bridge;

use GuzzleHttp\Client;

class Http
{
    /**
     * Request Url.
     */
    protected $uri;

    /**
     * Request Method.
     */
    protected $method;

    /**
     * Request Body.
     */
    protected $body;

    /**
     * Request Query.
     */
    protected $query = [];

    /**
     * Query With H5.
     */
    protected $accessToken;

    /**
     * SSL 证书.
     */
    protected $sslCert;
    protected $sslKey;

    /**
     * Http constructor.
     *
     * @param $method
     * @param $uri
     */
    public function __construct($method, $uri)
    {
        $this->uri = $uri;
        $this->method = strtoupper($method);
    }

    /**
     * Create Client Factory.
     *
     * @param $method
     * @param $uri
     * @return Http
     */
    public static function request($method, $uri)
    {
        return new static($method, $uri);
    }

    /**
     * Request Query.
     *
     * @param array $query
     * @return Http
     */
    public function withQuery(array $query) : Http
    {
        $this->query = array_merge($this->query, $query);

        return $this;
    }

    /**
     * Request Json Body.
     * @param array $body
     * @return Http
     */
    public function withBody(array $body) : Http
    {
        $this->body = Serializer::jsonEncode($body);

        return $this;
    }

    /**
     * Request Xml Body.
     * @param array $body
     * @return Http
     */
    public function withXmlBody(array $body) : Http
    {
        $this->body = Serializer::xmlEncode($body);

        return $this;
    }

    /**
     * Query With H5.
     *
     * @param string $token
     * @return Http
     */
    public function withAccessToken($token) : Http
    {
        $this->query['access_token'] = $token;

        return $this;
    }

    /**
     * Request SSL Cert.
     *
     * @param $sslCert
     * @param $sslKey
     * @return Http
     */
    public function withSSLCert($sslCert, $sslKey) : Http
    {
        $this->sslCert = $sslCert;
        $this->sslKey = $sslKey;

        return $this;
    }

    /**
     * Send Request.
     *
     * @param bool $asArray
     * @return array|string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function send($asArray = true) : array
    {
        $options = [];

        // query
        if (!empty($this->query)) {
            $options['query'] = $this->query;
        }

        // body
        if (!empty($this->body)) {
            $options['body'] = $this->body;
        }

        // ssl cert
        if ($this->sslCert && $this->sslKey) {
            $options['cert'] = $this->sslCert;
            $options['ssl_key'] = $this->sslKey;
        }

        $response = (new Client())->request($this->method, $this->uri, $options);
        $contents = $response->getBody()->getContents();

        if (!$asArray) {
            return $contents;
        }

        $array = Serializer::parse($contents);

        return $array;
    }
}
