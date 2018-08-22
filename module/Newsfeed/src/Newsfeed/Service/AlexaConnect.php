<?php


namespace Newsfeed\Service;

include __DIR__.'/libs/phpQuery-onefile.php';

class AlexaConnect
{

    protected $objUser;
    protected $objEntityManager;
    protected $strBasicUrl = 'https://alexa.amazon.de';
    protected $arrAlexas = [];
    protected $arrCookies = [];


    /**
     * AlexaConnect constructor.
     * @param \Doctrine\ORM\EntityManager $objEntityManager
     * @param \Newsfeed\Entity\User $objUser
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function __construct(\Doctrine\ORM\EntityManager $objEntityManager, \Newsfeed\Entity\User $objUser)
    {
        $this->objEntityManager = $objEntityManager;
        $this->objUser = $objUser;
        $this->objUser->AmazonStore = json_decode($objUser->AmazonStore, true);
        $this->arrAlexas = isset($this->objUser->AmazonStore['alexas']) ? $this->objUser->AmazonStore['alexas'] : [];
        $this->arrCookies = isset($this->objUser->AmazonStore['cookies']) ? $this->objUser->AmazonStore['cookies'] : [];
        //
        $this->login();
    }

    /**
     * @param bool $boolForceLogin
     * @return bool
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function login($boolForceLogin = false)
    {
        //
        // max age 1 hour
        if((time() - $this->objUser->AmazonStore['timestamp']) > 3600)
        {
            $this->arrCookies = [];
        }

        //
        if(!$this->arrCookies || $boolForceLogin)
        {
            // fetch alexa site ...
            $objFirstFetch = $this->doRequest($this->strBasicUrl);

            // fetch the login page
            \phpQuery::newDocumentHTML(gzdecode($this->doRequest($objFirstFetch->headers['Location'])->response));

            // build login post array
            $arrPost = [];
            foreach(pq('form[name=\'signIn\'] input') AS $row)
            {
                $strValue = '';
                switch (pq($row)->attr('name'))
                {
                    case 'email':
                        $strValue = $this->objUser->AmazonUsername;
                        break;
                    case 'password':
                        $strValue = $this->objUser->AmazonPassword;
                        break;
                    default:
                        $strValue = pq($row)->attr('value');
                        break;

                }
                //
                $arrPost[pq($row)->attr('name')] = $strValue;
            }

            // send login
            $objLogin = $this->doRequest(pq('form[name=\'signIn\']')->attr('action'), $arrPost, ['Referer' => $objFirstFetch->headers['Location']]);

            // fetch alexa dashboard
            $this->doRequest($objLogin->headers['Location']);
        }

        // fetch alecas to get csrf
        if(!isset($this->arrCookies['csrf']))
        {
            $this->fetchAlexas();
        }

        //
        return count($this->arrCookies) ? true : false;
    }

    /**
     * @param bool $boolFetchLive
     * @return array|mixed
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function fetchAlexas($boolFetchLive = false)
    {
        // request all alxas, this will also provide csrf cookie
        if(!isset($this->arrCookies['csrf']) || $boolFetchLive)
        {
            $objResult = $this->doRequest($this->strBasicUrl. '/api/devices-v2/device?cached=false', null, null, false);
            // erstmal cookies speichern
            $this->storeAmazonData();
            //
            $arrTmp = json_decode(gzdecode($objResult->response), true);
            foreach($arrTmp ['devices'] AS $objAlexa)
            {
                if(in_array($objAlexa->deviceFamily, ['WHA','VOX','FIRE_TV','TABLET']))
                {
                    continue;
                }
                //
                $this->arrAlexas[$objAlexa->deviceAccountId] = $objAlexa;
            }
            //
            $this->storeAmazonData();
        }
        //
        return $this->arrAlexas;
    }

    /**
     * @param $arrAlexas
     * @param $strText
     */
    public function letAlexasSaySomething($arrAlexas, $strText)
    {
        foreach ($arrAlexas AS $strAlexaId)
        {
            $this->letAlexaSaySomething($strAlexaId, $strText);
        }
    }

    /**
     * @param $strAlexaId
     * @param null $strText
     */
    public function letAlexaSaySomething($strAlexaId, $strText = null)
    {
        if(isset($this->arrAlexas[$strAlexaId]))
        {
            //
            $strDeviceType = $this->arrAlexas[$strAlexaId]['deviceType'];
            $strSerialNumber = $this->arrAlexas[$strAlexaId]['serialNumber'];
            $strDeviceOwnerCustomerId = $this->arrAlexas[$strAlexaId]['deviceOwnerCustomerId'];
            //
            $objSend = $this->doRequest(
                $this->strBasicUrl.'/api/behaviors/preview',
                '{"behaviorId":"PREVIEW","sequenceJson":"{\"@type\":\"com.amazon.alexa.behaviors.model.Sequence\",\"startNode\":{\"@type\":\"com.amazon.alexa.behaviors.model.OpaquePayloadOperationNode\",\"type\":\"Alexa.Speak\",\"operationPayload\":{\"deviceType\":\"'.$strDeviceType.'\",\"deviceSerialNumber\":\"'.$strSerialNumber.'\",\"locale\":\"de-DE\",\"customerId\":\"'.$strDeviceOwnerCustomerId.'\",\"textToSpeak\":\"'.$this->clear_string($strText).'\"}}}","status":"ENABLED"}',
                [
                    'DNT'               =>  '1',
                    'Content-Type'      =>  'application/json; charset=UTF-8',
                    'Referer'           =>  $this->strBasicUrl.'/spa/index.html',
                    'Origin'            =>  $this->strBasicUrl.'',
                    'csrf'              =>  $this->arrCookies['csrf'].'',
                    'Cache-Control'     =>  'no-cache'
                ]
            );
        }
    }

    /**
     * @param $str
     * @param string $how
     * @return mixed|string
     */
    protected function clear_string($str, $how = '_'){
        $search = array("ä", "ö", "ü", "ß", "Ä", "Ö",
            "Ü", "&", "é", "á", "ó",
            " :)", " :D", " :-)", " :P",
            " :O", " ;D", " ;)", " ^^",
            " :|", " :-/", ":)", ":D",
            ":-)", ":P", ":O", ";D", ";)",
            "^^", ":|", ":-/", "(", ")", "[", "]",
            "<", ">", "!", "\"", "§", "$", "%", "&",
            "/", "(", ")", "=", "?", "`", "´", "*", "'",
            "_", ":", ";", "²", "³", "{", "}",
            "\\", "~", "#", "+", ".", ",",
            "=", ":", "=)");

        $replace = array("ae", "oe", "ue", "ss", "Ae", "Oe",
            "Ue", "und", "e", "a", "o", "", "",
            "", "", "", "", "", "", "", "", "",
            "", "", "", "", "", "", "", "", "",
            "", "", "", "", "", "", "", "", "",
            "", "", "", "", "", "", "", "", "",
            "", "", "", "", "", "", "", "", "",
            "", "", "", "", "", "", "", "", "", "");

        $str = str_replace($search, $replace, $str);
        $str = strtolower(preg_replace("/[^a-zA-Z0-9]+/", trim($how), $str));
        return $str;
    }

    /**
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    protected function storeAmazonData()
    {
        // store cookies
        $this->objUser->AmazonStore = json_encode([
            'timestamp' =>  time(),
            'alexas'    =>  $this->arrAlexas,
            'cookies'   =>  $this->arrCookies,
        ]);
        $this->objEntityManager->merge($this->objUser);
        $this->objEntityManager->flush();
    }

    /**
     * @param $url
     * @param array $arrPost
     * @param array $arrAdditionalHeaders
     * @param bool $boolAllowZip
     * @return \stdClass
     */
    protected function doRequest($url, $arrPost = [], $arrAdditionalHeaders = [], $boolAllowZip = false)
    {
        // send request to server
        $ch = curl_init ();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_UNRESTRICTED_AUTH, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10); //times out after 10s
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLINFO_HEADER_OUT, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        //
        $arrHeaders = array(
            'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10.9; rv:25.0) Gecko/20100101 Firefox/25.0',
            'Accept: */*',
            'Accept-Language: de-de,de;q=0.8,en-us;q=0.5,en;q=0.3',
            'Accept-Encoding: gzip'.($boolAllowZip ? ', deflate, br' : ''),
            'Connection: keep-alive',
        );

        foreach ($arrAdditionalHeaders AS $strKey=>$strVal)
        {
            $arrHeaders[] = $strKey.': '.$strVal;
        }

        //
        if($this->arrCookies)
        {
            $strCookies= '';
            foreach ($this->arrCookies AS $strKey=>$strVal)
            {
                $strCookies.= $strKey.'='.$strVal.'; ';
            }
            $arrHeaders[] = 'Cookie: '.$strCookies;
        }

        //
        if($arrPost)
        {
            //
            if(is_array($arrPost))
            {
                $arrPosts=array();
                foreach ($arrPost as $key => $value)
                {
                    $arrPosts[]=$key."=".urlencode($value);
                }
                curl_setopt($ch,CURLOPT_POSTFIELDS,implode("&", $arrPosts));
                $arrHeaders[] = 'Content-Type: application/x-www-form-urlencoded';
            }
            else
            {
                curl_setopt($ch,CURLOPT_POSTFIELDS, ($arrPost));
                $arrHeaders[] = 'Content-Type: application/x-www-form-urlencoded; charset=UTF-8';
            }
            //
            curl_setopt($ch,CURLOPT_POST,1);
        }

        //
        curl_setopt($ch,CURLOPT_HTTPHEADER,$arrHeaders);
        $strRealResponse = curl_exec($ch);
        $headersend = curl_getinfo($ch, CURLINFO_HEADER_OUT);
        curl_close($ch);

        //
        list($header, $response) = explode("\r\n\r\n", $strRealResponse);

        //
        if(strstr($header, 'HTTP/1.1 100 Continue'))
        {
            $strRealResponse = substr($strRealResponse, strpos($strRealResponse, "\r\n\r\n")+4);
            list($header, $response) = explode("\r\n\r\n", $strRealResponse);
        }

        //
        $headers = array();
        foreach (explode("\r\n", $header) as $i => $line)
        {
            if ($i === 0)
            {
                $headers['http_code'] = $line;
            }
            else
            {
                list ($key, $value) = explode(': ', $line);
                //
                if($key=='Set-Cookie')
                {
                    //
                    $value = substr($value, 0, strpos($value, ';'));
                    $strkey = substr($value, 0, strpos($value, '='));
                    $strval = substr($value, strpos($value, '=')+1);
                    //
                    $this->arrCookies[$strkey] = $strval;
                }
                else
                {
                    $headers[$key] = $value;
                }
            }
        }
        //
        $ret = new \stdClass();
        $ret->headers = $headers;
        $ret->response = $response;
        return $ret;
        // ende
    }

}