<?php
class Tieba
{
    private $content = [];

    /**
     * @param string[] $tieba
     * @param string $cookie
     */
    public function __construct(array $tieba, string $cookie = '')
    {
        $this->content = $this->load($tieba, $cookie);
    }

    /**
     * 获取百度贴吧热门帖子
     *
     * @return array 二维数组
     */
    public function getTiebaHot(): array
    {
        $results = [];
        foreach ($this->content as $k => $text) {
            $results[] = empty($text) ? [] : $this->getHot($text);
        }
        return $results;
    }

    /**
     * 获取用户
     *
     * @return array
     */
    public function getUsers(): array
    {
        return array_values(array_unique(array_merge($this->getAuthors(), $this->getLastPostings()), SORT_LOCALE_STRING));
    }

    /**
     * 获取作者
     *
     * @return array
     */
    public function getAuthors(): array
    {
        $results = [];
        foreach ($this->content as $k => $text) {
            $results = array_merge($results, empty($text) ? [] : $this->getAuthor($text));
        }
        return array_values(array_unique($results, SORT_LOCALE_STRING));
    }

    /**
     * 获取作者
     *
     * @return array
     */
    public function getLastPostings(): array
    {
        $results = [];
        foreach ($this->content as $k => $text) {
            $results = array_merge($results, empty($text) ? [] : $this->getLastPosting($text));
        }
        return array_values(array_unique($results, SORT_LOCALE_STRING));
    }

    /**
     * 获取百度贴吧首页信息
     *
     * @param string[] $tieba
     * @param string $cookie
     * @return array
     */
    private function load(array $tieba, string $cookie = ''): array
    {
        foreach ($tieba as $v) {
            if (!is_string($v)) {
                return [];
            }
            // 这个用来作为reffer
            $address = 'http://tieba.baidu.com/f?' . http_build_query([
                'ie' => 'utf-8',
                'kw' => $v,
                'fr' => 'search',
            ]);
            break;
        }
        if (!isset($address)) {
            return [];
        }

        $tools = new Tools\MultipleUrlContextLoader();
        $tools->setTimeout(60 * count($tieba));
        $tools->setUserAgent('Mozilla/5.0 (Windows NT 6.1; WOW64; rv:20.0) Gecko/20100101 Firefox/20.0');
        $tools->setReffer($address);
        if (!empty($cookie)) {
            $tools->setCookie($cookie);
        }
        $tools->setHeaders([
            'Host: tieba.baidu.com',
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language: zh-CN,zh;q=0.8,zh-TW;q=0.7,zh-HK;q=0.5,en-US;q=0.3,en;q=0.2',
            'Connection: keep-alive',
        ]);

        $urls = [];
        foreach ($tieba as $v) {
            $urls[] = 'http://tieba.baidu.com/f?' . http_build_query([
                'ie' => 'utf-8',
                'kw' => $v,
                'fr' => 'search',
            ]);
        }
        $tools->setUrls($urls);
        $tools->loadContent();
        return $tools->getContents();
    }

    /**
     * 请求百度贴吧的PC页面，这个函数返回它的本吧热贴数据
     *
     * 这个函数安全的，总会返回字符串
     *
     * @param string $content
     * @return string
     * @author az13js
     */
    private function getHot(string $content): array
    {
        $len = mb_strlen('<meta name="description" content="');
        if (false === $len) {
            return [];
        }
        $start = mb_stripos($content, '<meta name="description" content="');
        if (false === $start) {
            return [];
        }
        $end = mb_stripos($content, '">', $start);
        if (false === $end) {
            return [];
        }
        $metaData = mb_substr($content, $start + $len, $end - $start - $len);

        $path = [];
        $results = [];
        $id = 0;
        do {
            $id++;
            $sign = " $id-";
            $match = mb_stripos($metaData, $sign);
            if (false !== $match) {
                $path[] = $match;
            } else {
                break;
            }
        } while (true);
        $total = count($path);
        for ($i = 0; $i < $total - 1; $i++) {
            $results[] = mb_substr($metaData, $path[$i] + mb_strlen(' ' . ($i + 1) . '-'), $path[$i + 1] - $path[$i] - mb_strlen(' ' . ($i + 1) . '-'));
        }
        if ($total > 0) {
            $i = $total - 1;
            $results[] = mb_substr($metaData, $path[$i] + mb_strlen(' ' . ($i + 1) . '-'), mb_strlen($metaData) - $path[$i] - mb_strlen(' ' . ($i + 1) . '-'));
        }
        return $results;
    }

    /**
     * 获取作者
     *
     * @return array
     */
    private function getAuthor(string $text): array
    {
        $head = '"主题作者: ';
        $tail = '"';
        $results = [];
        $offset = 0;
        $hLen = mb_strlen($head);
        $eLen = mb_strlen($tail);
        while (false !== ($start = mb_stripos($text, $head, $offset))) {
            $end = mb_stripos($text, $tail, $start + $hLen);
            if ($end !== false) {
                $results[] = mb_substr($text, $start + $hLen, $end - $start - $hLen);
                $offset = $end + $eLen;
            } else {
                $offset = $start + $hLen;
            }
        }
        return array_values(array_unique($results, SORT_LOCALE_STRING));
    }

    /**
     * 获取最后回复人
     *
     * @return array
     */
    private function getLastPosting(string $text): array
    {
        $head = '最后回复人: ';
        $tail = '"';
        $results = [];
        $offset = 0;
        $hLen = mb_strlen($head);
        $eLen = mb_strlen($tail);
        while (false !== ($start = mb_stripos($text, $head, $offset))) {
            $end = mb_stripos($text, $tail, $start + $hLen);
            if ($end !== false) {
                $results[] = mb_substr($text, $start + $hLen, $end - $start - $hLen);
                $offset = $end + $eLen;
            } else {
                $offset = $start + $hLen;
            }
        }
        return array_values(array_unique($results, SORT_LOCALE_STRING));
    }
}
