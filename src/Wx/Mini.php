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
     */
    public function get($code)
    {
        $decodeIv  = base64_decode($this->iv);
        $decodeEd  = base64_decode($this->ed);

        // 检查iv
        if (strlen($decodeIv) != 16) {
            $decodeIv = base64_decode(urldecode($this->iv));

            if (strlen($decodeIv) != 16) {
                throw new Exception('iv长度错误: [iv]' . $this->iv);
            }
        }

        // 获取session key
        $response  = $this->rawBody($code);

        // 检查session key
        $sessionKey = $response['session_key'] ?? '';
        if (strlen($sessionKey) != 20) {
            throw new Exception('sesskon_key错误: ' . $sessionKey, 401900);
        }
        $decodeKey = base64_decode($response['session_key']);

        // 获取加密结果
        $result    = openssl_decrypt($decodeEd, 'AES-128-CBC', $decodeKey, 1, $decodeIv);
        $data      = json_decode($result, true);

        if (empty($data)) {
            throw new Exception('微信返回数据解密失败: [key]' . $decodeKey . ', [iv]' . $decodeIv . ', [ed]' . $decodeEd, 401901);
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