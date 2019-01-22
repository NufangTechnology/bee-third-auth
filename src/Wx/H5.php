<?php
namespace Bee\ThirdAuth\Wx;

use Bee\ThirdAuth\Bridge\Http;
use Bee\ThirdAuth\Exception;

/**
 * H5
 *
 * @package Bee\ThirdAuth\OAuth
 */
class H5
{
    /**
     * 获取Access Token
     */
    const TOKEN_URL = 'https://api.weixin.qq.com/sns/oauth2/access_token';

    /**
     * 刷新Access Token
     */
    const REFRESH_TOKEN_URL = 'https://api.weixin.qq.com/sns/oauth2/refresh_token';

    /**
     * 检测 access_token 是否有效.
     */
    const IS_TOKEN_VALID_URL = 'https://api.weixin.qq.com/sns/auth';

    /**
     * 网页授权获取用户信息.
     */
    const USER_INFO_URL = 'https://api.weixin.qq.com/sns/userinfo';

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
     * 获取用户信息
     *
     * @param string $code
     * @param string $lang
     * @return array|string
     * @throws Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function get($code, $lang = 'zh_CN')
    {
        // 获取accessToken
        $response = $this->rawBody($code);

        // 获取用户信息
        $data = Http::request('GET', self::USER_INFO_URL)
            ->withQuery(
                [
                    'access_token' => $response['access_token'],
                    'openid'       => $response['openid'],
                    'lang'         => $lang,
                ]
            )->send();

        if (!empty($response['errcode'])) {
            throw new Exception($response['errmsg'], $response['errcode']);
        }

        return [
            'unionId'     => $data['unionid'],
            'openId'      => $data['openid'],
            'nickName'    => $data['nickName'],
            'sex'         => $data['sex'],
            'avatarUrl'   => $data['headimgurl'],
            'accessToken' => $response['access_token']
        ];
    }

    /**
     * 根据code获取 Access Token
     *
     * @param $code
     * @return array|string
     * @throws Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function rawBody($code)
    {
        $response = Http::request('GET', static::TOKEN_URL)
            ->withQuery(
                [
                    'appid' => $this->appId,
                    'secret' => $this->appSecret,
                    'code' => $code,
                    'grant_type' => 'authorization_code',
                ]
            )->send();

        if (!empty($response['errcode'])) {
            throw new Exception($response['errmsg'], $response['errcode']);
        }

        return $response;
    }
}
