<?php
namespace Bee\ThirdAuth\Wx;

use Bee\ThirdAuth\Bridge\Http;
use Bee\ThirdAuth\Exception;

class Mini
{
    /**
     * 身份信息URL
     */
    const USER_INFO_URL = 'https://api.weixin.qq.com/sns/jscode2session';

    /**
     * @var string
     */
    protected $appId = '';

    /**
     * @var string
     */
    protected $appSecret = '';

    /**
     * @var string
     */
    protected $ed = '';

    /**
     * @var string
     */
    protected $iv = '';

    /**
     * H5 constructor.
     *
     * @param $appId
     * @param $appSecret
     * @param $ed
     * @param $iv
     */
    public function __construct($appId, $appSecret, $ed, $iv)
    {
        $this->appId     = $appId;
        $this->appSecret = $appSecret;
        $this->ed        = $ed;
        $this->iv        = $iv;
    }

    /**
     * 获取用户信息
     *
     * @param $code
     * @return array|false
     * @throws Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function get($code)
    {
        $response  = $this->rawBody($code);

        $decodeKey = base64_decode($response['session_key']);
        $decodeIv  = base64_decode($this->iv);
        $decodeEd  = base64_decode($this->ed);

        // 获取加密结果
        $result    = openssl_decrypt($decodeEd, 'AES-128-CBC', $decodeKey, 1, $decodeIv);
        $data      = json_decode($result, true);

        if (empty($data)) {
            return false;
        }

        return [
            'unionId'    => $data['unionId'],
            'openId'     => $data['openId'],
            'nickName'   => $data['nickName'],
            'sex'        => $data['gender'],
            'avatarUrl'  => $data['avatarUrl'],
            'sessionKey' => $response['session_key'],
        ];
    }

    /**
     * 获取原始信息
     *
     * @param $code
     * @return array|string
     * @throws Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function rawBody($code)
    {
        $response = Http::request('GET', static::USER_INFO_URL)
            ->withQuery(
                [
                    'appid'      => $this->appId,
                    'secret'     => $this->appSecret,
                    'js_code'    => $code,
                    'grant_type' => 'authorization_code'
                ]
            )->send();

        if (!empty($response['errcode'])) {
            throw new Exception($response['errmsg'], $response['errcode']);
        }

        return $response;
    }
}