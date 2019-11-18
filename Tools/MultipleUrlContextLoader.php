<?php
namespace Tools;

/**
 * 这个工具用来并行地获取多个链接的内容
 *
 * @author az13js
 * @version 0.0.1
 */
class MultipleUrlContextLoader
{
    /** @var string[] 将要获取内容的URL */
    private $urls = [];
    /**
     * @var array 与$urls对应，使用网络请求获得的远程内容。
     *
     * 也就是说这可能是字符串编码，也可能是二进制内容。
     * 如果获取失败，也可能是false
     */
    private $contents = [];
    /** @var string cookie，可以是空 */
    private $cookie = '';
    /** @var string reffer，可以是空 */
    private $reffer = '';
    /** @var string useragent，可以是空 */
    private $userAgent = '';
    /** @var array Header，可以是空 */
    private $headers = [];
    /** @var int 超时时间，单位是秒，默认是60秒 */
    private $timeout = 60;

    /**
     * 设置超时时间
     *
     * 不是必须设置的，默认为60秒
     *
     * @param int $timeout
     * @return bool 设置成功返回true，不对的时候返回false。
     * @author az13js
     */
    public function setTimeout(int $timeout): bool
    {
        $this->timeout = $timeout;
        return true;
    }

    /**
     * 设置所有请求链接携带的cookie信息
     *
     * 不是必须设置的，设置为空字符串或者不设置，请求的时候将不会传递cookie信息。
     *
     * @param string $cookie 设置的cookie，格式例如"user=example; id=120; token=ABCDEFG"
     * @return bool 设置成功返回true，不对的时候返回false。
     * @author az13js
     */
    public function setCookie(string $cookie): bool
    {
        $this->cookie = $cookie;
        return true;
    }

    /**
     * 设置Header参数
     *
     * 不是必须设置的。
     *
     * @param array $headers 格式：['Host: example.com', 'Accept: text/html']
     * @return bool 设置成功返回true，不对的时候返回false。
     * @author az13js
     */
    public function setHeaders(array $headers): bool
    {
        $this->headers = $headers;
        return true;
    }

    /**
     * 设置所有请求链接携带的refer信息
     *
     * 不是必须设置的，设置为空字符串或者不设置，请求的时候将不会传递refer信息。
     *
     * @param string $reffer 设置的reffer
     * @return bool 设置成功返回true，不对的时候返回false。
     * @author az13js
     */
    public function setReffer(string $reffer): bool
    {
        $this->reffer = $reffer;
        return true;
    }

    /**
     * 设置所有请求链接携带的user agent信息
     *
     * 不是必须设置的，设置为空字符串或者不设置，请求的时候将不会传递user agent信息。
     *
     * @param string $userAgent 设置的userAgent
     * @return bool 设置成功返回true，不对的时候返回false。
     * @author az13js
     */
    public function setUserAgent(string $userAgent): bool
    {
        $this->userAgent = $userAgent;
        return true;
    }

    /**
     * 一次性设置将要获取内容的URL
     *
     * @param string[] $url 一维字符串数组，URL，元素非空
     * @return bool 设置成功返回true，当URL内容不对的时候返回false。
     * @author az13js
     */
    public function setUrls(array $urls): bool
    {
        foreach ($urls as $url) {
            if (!is_string($url) || empty($url)) {
                return false;
            }
        }
        $this->urls = [];
        $this->contents = [];
        foreach ($urls as $url) {
            $this->urls[] = $url;
            $this->contents[] = false;
        }
        return true;
    }

    /**
     * 返回获取到的内容
     *
     * @return array 内容与urls对应
     * @author az13js
     */
    public function getContents(): array
    {
        return $this->contents;
    }

    /**
     * 执行请求资源的动作
     *
     * @return resource|bool CURL资源，当创建失败的时候将返回false
     * @author az13js
     */
    public function loadContent()
    {
        $total = count($this->urls);
        $handle = curl_multi_init();
        if (false === $handle) {
            return false;
        }
        curl_multi_setopt($handle, CURLMOPT_PIPELINING, 1);
        curl_multi_setopt($handle, CURLMOPT_MAXCONNECTS, 65535);
        $normalHandles = [];
        foreach ($this->urls as $address) {
            $normalHandle = $this->createNormalCurlHandle($address, $this->cookie, $this->reffer, $this->userAgent);
            if (false === $normalHandle) {
                curl_multi_close($handle);
                return false;
            }
            $normalHandles[] = $normalHandle;
            $addResult = curl_multi_add_handle($handle, $normalHandle);
            if (0 !== $addResult) {
                curl_multi_close($handle);
                return false;
            }
        }
        $running = 0;
        while (true) {
            curl_multi_exec($handle, $running);
            if ($running > 0) {
                curl_multi_select($handle, $this->timeout);
            } else {
                break;
            }
        }
        $this->contents = [];
        foreach ($normalHandles as $i => $normalHandle) {
            $content = curl_multi_getcontent($normalHandle);
            curl_close($normalHandle);
            $this->contents[] = $content;
        }
        curl_multi_close($handle);
        return true;
    }

    /**
     * 创建并返回一个依据给定的地址生成的CURL资源，该资源是用GET方法请求的
     *
     * @param string $address 请求的地址
     * @param string $cookie 可选的参数，会被设为请求携带的cookie，默认是空字符串
     * @param string $refer 可选的参数，会被设为请求携带的refer头，默认是空字符串
     * @param string $ua 可选的参数，会被设为请求携带的UserAgent，默认是空字符串
     * @return resource|bool CURL资源，当创建失败的时候将返回false
     * @author az13js
     */
    private function createNormalCurlHandle(string $address, string $cookie = '', string $refer = '', string $ua = '')
    {
        $handle = curl_init();
        if (false === $handle) {
            return false;
        }
        $setResult = curl_setopt_array($handle, [
            CURLOPT_AUTOREFERER => true,
            CURLOPT_COOKIESESSION => false,
            CURLOPT_FILETIME => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_FORBID_REUSE => false,
            CURLOPT_FRESH_CONNECT => false,
            CURLOPT_HTTPPROXYTUNNEL => false,
            CURLOPT_NETRC => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_UNRESTRICTED_AUTH => false,
            CURLOPT_UPLOAD => false,
            CURLOPT_VERBOSE => false,
            CURLOPT_CONNECTTIMEOUT => $this->timeout,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_DNS_CACHE_TIMEOUT => $this->timeout,
            CURLOPT_MAXCONNECTS => 65535,
            CURLOPT_MAXREDIRS => 20,
            /*CURLOPT_COOKIE => '',
            CURLOPT_REFERER => '',
            CURLOPT_USERAGENT => '',
            CURLOPT_HTTPHEADER => [],
            */
            CURLOPT_URL => $address,
        ]);
        if (!empty($cookie)) {
            curl_setopt($handle, CURLOPT_COOKIE, $cookie);
        }
        if (!empty($refer)) {
            curl_setopt($handle, CURLOPT_REFERER, $refer);
        }
        if (!empty($ua)) {
            curl_setopt($handle, CURLOPT_USERAGENT, $ua);
        }
        if (!empty($this->headers)) {
            curl_setopt($handle, CURLOPT_HTTPHEADER, $this->headers);
        }
        if (false === $setResult) {
            curl_close($handle);
            return false;
        }
        return $handle;
    }
}