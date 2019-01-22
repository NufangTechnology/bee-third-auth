<?php
namespace Bee\ThirdAuth;

use Bee\ThirdAuth\Bridge\Http;

/**
 * Class Ticket
 *
 * @package Bee\ThirdAuth
 */
class Ticket
{
    /**
     * @see http://mp.weixin.qq.com/wiki/11/74ad127cc054f6b80759c40f77ec03db.html（附录 1）.
     */
    const JS_API_TICKET_URL = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket';

    /**
     * @var string
     */
    protected $accessToken = '';

    /**
     * Ticket constructor.
     *
     * @param $accessToken
     */
    public function __construct($accessToken)
    {
        $this->accessToken = $accessToken;
    }


    /**
     * todo: 待完成
     *
     * @return string
     */
    public function get()
    {
    }

    /**
     * 发起获取ticket请求
     *
     * @return array
     *
     * @throws Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function rawBody() : array
    {
        $response = Http::request('GET', static::JS_API_TICKET_URL)
            ->withAccessToken($this->accessToken)
            ->withQuery(
                [
                    'type' => 'jsapi'
                ]
            )->send();

        if (!empty($response['errcode'])) {
            throw new Exception($response['errmsg'], $response['errcode']);
        }

        return $response;
    }
}
