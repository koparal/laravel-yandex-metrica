<?php

namespace YandexMetrica\Foundation;

use DateTime;
use Cache;
use Log;
use Carbon\Carbon;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ClientException;

class YandexMetrica
{
    /**
     * URL of Yandex Metrica
     * @var string
     */
    protected $url = 'https://api-metrika.yandex.com.tr/';


    /**
     * Cache Time
     * @var mixed
     */
    protected $cache;

    /**
     * Counter ID
     * @var
     */
    protected $counter_id;


    /**
     * OAuth token
     * @var
     */
    protected $token;

    /**
     * Data Set
     * @var
     */
    public $data;


    /**
     * YandexMetrica constructor.
     */
    public function __construct()
    {
        $this->cache = config('yandex-metrica.cache');
        $this->token = config('yandex-metrica.token');
        $this->counter_id = config('yandex-metrica.counter_id');
    }

    /**
     * @param int $days
     * @param int $maxResult
     * @return array|bool
     */
    public function getGeneralAnalytics($days=7, $maxResult = 365)
    {
        list($startDate, $endDate) = $this->calculateDays($days);
        $cacheName = md5(serialize('general-analytics'.$startDate->format('Y-m-d').$endDate->format('Y-m-d').$maxResult));
        $urlParams = [
            'date1'         => $startDate->format('Y-m-d'),
            'date2'         => $endDate->format('Y-m-d'),
            'metrics'       => 'ym:s:visits,ym:s:pageviews,ym:s:users',
            'dimensions'    => 'ym:s:date',
            'sort'          => 'ym:s:date',
            'ids'           => $this->counter_id,
            'oauth_token'   => $this->token
        ];

        $requestUrl = $this->url.'stat/v1/data?'.urldecode(http_build_query($urlParams));
        $this->data = $this->request($requestUrl, $cacheName);
        return $this->adaptGeneralAnalytics();
    }

    /**
     * @param int $days
     * @param int $maxResult
     * @return array
     */
    public function getOrganicData($days=7, $maxResult = 365)
    {
        list($startDate, $endDate) = $this->calculateDays($days);
        $cacheName = md5(serialize('organic-data'.$startDate->format('Y-m-d').$endDate->format('Y-m-d').$maxResult));

        $urlParams = [
            'date1'         => $startDate->format('Y-m-d'),
            'date2'         => $endDate->format('Y-m-d'),
            'metrics'       => 'ym:s:visits,ym:s:users',
            'filters'       => "trafficSource=='organic'",
            'dimensions'    => 'ym:s:searchEngine',
            'ids'           => $this->counter_id,
            'oauth_token'   => $this->token
        ];

        $requestUrl = $this->url.'stat/v1/data?'.urldecode(http_build_query($urlParams));
        $this->data = $this->request($requestUrl, $cacheName);
        return $this->adaptOrganicData();
    }

    /**
     * @param int $days
     * @param int $maxResult
     * @return array
     */
    public function getDurationData($days = 7, $maxResult = 365)
    {
        list($startDate, $endDate) = $this->calculateDays($days);
        $cacheName = md5(serialize('duration-data'.$startDate->format('Y-m-d').$endDate->format('Y-m-d').$maxResult));

        $urlParams = [
            'ids'           => $this->counter_id,
            'oauth_token'   => $this->token,
            'date1'         => $startDate->format('Y-m-d'),
            'date2'         => $endDate->format('Y-m-d'),
            'preset'        => 'sources_summary'

        ];

        $requestUrl = $this->url.'stat/v1/data?'.urldecode(http_build_query($urlParams));

        $this->data = $this->request($requestUrl, $cacheName);

        return $this->adaptDurationData();
    }

    /**
     * @param int $days
     * @param int $maxResults
     * @return array
     */
    public function getPageAnalytics($days=7, $maxResults = 365)
    {
        list($startDate, $endDate) = $this->calculateDays($days);
        $cacheName = md5(serialize('page-analytics'.$startDate->format('Y-m-d').$endDate->format('Y-m-d').$maxResults));

        $urlParams = [
            'ids'           => $this->counter_id,
            'oauth_token'   => $this->token,
            'date1'         => $startDate->format('Y-m-d'),
            'date2'         => $endDate->format('Y-m-d'),
            'metrics'       => 'ym:pv:pageviews',
            'dimensions'    => 'ym:pv:URLPathFull,ym:pv:title',
            'sort'          => '-ym:pv:pageviews',
            'limit'         => $maxResults

        ];

        $requestUrl = $this->url.'stat/v1/data?'.urldecode(http_build_query($urlParams));

        $this->data = $this->request($requestUrl, $cacheName);

        return $this->adaptPageAnalytics();
    }

    /**
     * @param int $days
     * @param int $maxResults
     * @return array
     */
    public function getCountries($days=7, $maxResults = 365)
    {
        list($startDate, $endDate) = $this->calculateDays($days);
        $cacheName = md5(serialize('countries'.$startDate->format('Y-m-d').$endDate->format('Y-m-d').$maxResults));

        $urlParams = [
            'ids'           => $this->counter_id,
            'oauth_token'   => $this->token,
            'date1'         => $startDate->format('Y-m-d'),
            'date2'         => $endDate->format('Y-m-d'),
            'dimensions'    => 'ym:s:regionCountry,ym:s:regionArea',
            'metrics'       => 'ym:s:visits',
            'sort'          => '-ym:s:visits',
        ];

        $requestUrl = $this->url.'stat/v1/data?'.urldecode(http_build_query($urlParams));

        $this->data = $this->request($requestUrl, $cacheName);
        return $this->adaptCountries();
    }

    /**
     * @param int $days
     * @param int $maxResults
     * @return array
     */
    public function getBrowserAndSystems($days=7, $maxResults = 365)
    {
        list($startDate, $endDate) = $this->calculateDays($days);
        $cacheName = md5(serialize('browser-and-systems'.$startDate->format('Y-m-d').$endDate->format('Y-m-d').$maxResults));

        $urlParams = [
            'ids'           => $this->counter_id,
            'oauth_token'   => $this->token,
            'date1'         => $startDate->format('Y-m-d'),
            'date2'         => $endDate->format('Y-m-d'),
            'preset'        => 'tech_platforms',
            'dimensions'    => 'ym:s:browser',
        ];

        $requestUrl = $this->url.'stat/v1/data?'.urldecode(http_build_query($urlParams));

        $this->data = $this->request($requestUrl, $cacheName);

        return $this->adaptBrowserAndSystems();
    }

    /**
     * @return array|bool
     */
    public function adaptGeneralAnalytics()
    {
        $itemArray = [];

        if($this->data['data']){
            foreach($this->data['data'] as $item)
            {
                $itemArray['date'][] = Carbon::createFromFormat('Y-m-d', $item['dimensions'][0]['name'])->formatLocalized('%d.%m.%Y');
                $itemArray['visits'][] = $item['metrics'][0];
                $itemArray['pageviews'][] = $item['metrics'][1];
                $itemArray['users'][] = $item['metrics'][2];
            }
        }
        else{
            $itemArray['date'][] = "-";
            $itemArray['visits'][] = 0;
            $itemArray['pageviews'][] = 0;
            $itemArray['users'][] = 0;
        }

        $itemArray['totals'] = [
            'visits'        => $this->data['totals'][0],
            'pageviews'    => $this->data['totals'][1],
            'users'     => $this->data['totals'][2],
        ];

        if($itemArray['totals']['visits']==null || $itemArray['totals']['pageviews']==null || $itemArray['totals']['users']==null){
            return false;
        }

        return $itemArray;
    }

    /**
     * @return array
     */
    public function adaptOrganicData()
    {
        $itemArray = [];

        $itemArray['totals'] = [
            'organicVisits'        => $this->data['totals'][0],
            'organicUsers'    => $this->data['totals'][1],
        ];
        return $itemArray;
    }

    /**
     * @return array
     */
    public function adaptDurationData()
    {
        $dataArray = [];
        if ($this->data['data'])
        {
            foreach($this->data['data'] as $item)
            {
                $dataArray['data'][] = [
                    'trafficSource' => $item['dimensions'][0]['name'],
                    'sourceEngine'  => $item['dimensions'][1]['name'],
                    'visits'        => $item['metrics'][0],
                    'bounceRate'    => $item['metrics'][1],
                    'pageDepth'     => $item['metrics'][2],
                    'avgVisitDurationSeconds'    => $item['metrics'][3]
                ];
            }
        }
        else {
            $dataArray['data'][] = [
                'trafficSource' => "-",
                'sourceEngine'  => "-",
                'visits'        => "-",
                'bounceRate'    => 0,
                'pageDepth'     => 0,
                'avgVisitDurationSeconds'    => 0
            ];
        }


        $dataArray['totals'] = [
            'visits'        => $this->data['totals'][0],
            'bounceRate'    => $this->data['totals'][1],
            'pageDepth'     => $this->data['totals'][2],
            'avgVisitDurationSeconds'    => $this->data['totals'][3],
        ];

        return $dataArray;
    }

    /**
     * @return array
     */
    public function adaptPageAnalytics()
    {
        $dataArray = [];

        if($this->data['data'])
        {
            foreach($this->data['data'] as $item)
            {
                $dataArray[] = [
                    'url'       => $item['dimensions'][0]['name'],
                    'title'     => $item['dimensions'][1]['name'],
                    'pageviews' => $item['metrics'][0]
                ];
            }
        }
        else {
            $dataArray[] = [
                'url'       => "-",
                'title'     => "-",
                'pageviews' => 0
            ];
        }


        return $dataArray;
    }

    /**
     * @return array
     */
    public function adaptCountries()
    {
        $key_array = [];
        $idArray = [];

        if($this->data['data'])
        {
            foreach($this->data['data'] as $value) {
                if ( !in_array( $value['dimensions'][0]['id'], $key_array ) ) {
                    $key_array[] = $value['dimensions'][0]['id'];
                    $idArray[] = $value['dimensions'][0];
                }
            }
        }
        else
        {
            $key_array[] = 0;
            $idArray[] = 0;
        }


        $cnt = count($idArray);
        $dataArray = [];
        $drilldownArray = [];

        if($cnt!=0)
        {
            for($i = 0; $i < $cnt; $i++)
            {
                $dataArray[$i] = [ 'name' => $idArray[$i]['name'], 'y' => 0, 'drilldown' => $idArray[$i]['name'] ];
                $drilldownArray[$i] = ['name' => $idArray[$i]['name'], 'id' => $idArray[$i]['name'], 'data' => []];

                if($this->data['data'])
                {
                    foreach ($this->data['data'] as $item) {

                        if($item['dimensions'][0]['id'] == $idArray[$i]['id']){
                            $dataArray[$i]['y'] += $item['metrics'][0];

                            if( $item['dimensions'][1]['name'] ){
                                $region = $item['dimensions'][1]['name'];
                            }else{
                                $region = 'Не определено';
                            }

                            $drilldownArray[$i]['data'][] = [ $region, $item['metrics'][0] ];
                        }
                    }
                }

            }
        }

        return $dataArray;
    }

    /**
     * @return array
     */
    public function adaptBrowserAndSystems()
    {
        $dataArray = [];

        if($this->data['data'])
        {
            foreach($this->data['data'] as $item)
            {
                $dataArray[] = [
                    'browser'      => $item['dimensions'][0]['name'],
                    'visits'            => $item['metrics'][0],
                    'bounceRate'        => $item['metrics'][1],
                    'pageDepth'         => $item['metrics'][2],
                    'avgVisitDurationSeconds'    => date("i:s", $item['metrics'][3])
                ];
            }
        }
        else {
            $dataArray[] = [
                'browser' => "-",
                'visits' => 0,
                'bounceRate' => 0,
                'pageDepth' => 0,
                'avgVisitDurationSeconds' => 0
            ];
        }
        return $dataArray;
    }

    /**
     * @param $numberOfDays
     * @return array
     */
    public function calculateDays($numberOfDays)
    {
        $endDate = Carbon::today();
        $startDate = Carbon::today()->subDays($numberOfDays);
        return [$startDate, $endDate];
    }

    /**
     * @param $url
     * @param $cacheName
     * @return mixed
     */
    public function request($url, $cacheName)
    {
        return Cache::remember($cacheName, $this->cache, function() use($url){
            try
            {
                $client = new GuzzleClient();
                $response = $client->request('GET', $url);

                $result = json_decode($response->getBody(), true);

            } catch (ClientException $e)
            {
                Log::error('Yandex Metrica: '.$e->getMessage());

                $result = null;
            }
            return $result;
        });
    }
}
