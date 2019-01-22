<?php
namespace Bee\ThirdAuth;

use Bee\ThirdAuth\Bridge\Http;

/**
 * H5
 *
 * @package Bee\ThirdAuth
 */
class AccessToken
{
    /**
     * access token获取地址
     *
     * @see http://mp.weixin.qq.com/wiki/14/9f9c82c1af308e3b14ba9b973f99a8ba.html.
     */
    const ACCESS_TOKEN_URL = 'https://api.weixin.qq.com/cgi-bin/token';

    /**
     * @var string
     */
    protected $appId = '';

    /**
     * @var string
     */
    protected $appSecret = '';

    /**
     * H5 constructor.
     *
     * @param $appId
     * @param $appSecret
     */
    public function __construct($appId, $appSecret)
    {
        $this->appId     = $appId;
        $this->appSecret = $appSecret;
    }

    /**
     * @return string
     */
    public function getAppSecret(): string
    {
        return $this->appSecret;
    }

    /**
     * @return string
     */
    public function getAppId(): string
    {
        return $this->appId;
    }

    /**
     * 获取Access Token
     *
     * @return string
     * @throws Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function get() : string
    {
        $rawBody = $this->rawBody();

        return $rawBody['access_token'];
    }

    /**
     * 发起获取Access Token请求
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws Exception
     */
    public function rawBody() : array
    {
        $response = Http::request('GET', static::ACCESS_TOKEN_URL)
            ->withQuery(
                [
                    'grant_type' => 'client_credential',
                    'appid'      => $this->appId,
                    'secret'     => $this->appSecret,
                ]
            )->send();

        if (!empty($response['errcode'])) {
            throw new Exception($response['errmsg'], $response['errcode']);
        }

        return $response;
    }
}
