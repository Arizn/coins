<?php
namespace Arizn\Api\Providers;
use Graze\GuzzleHttp\JsonRpc\Client;
class Provider
{
    protected $rpcClient;
	protected $httpClient;
	protected $throttled = NULL;
    protected $id = 0;
	protected $api = 0;

    public function __construct(string $url , string $username = NULL, string $password = NULL)
    {
		$options = [ "rpc_error" => true, "debug" => false ];
		if($username && $password)
		$options['auth'] = [$username,$password ];
        $this->rpcClient = Client::factory($url, $options);
		$this->httpClient = new \GuzzleHttp\Client(array_merge($options, ['base_uri' => $url]));
	
    }
	
	 /**
     * @return NetworkInterface
     * @throws \Exception
     */
    public static function network()
    {
        return \BitWasp\Bitcoin\Network\NetworkFactory::bitcoin();
    }
	
	public function sigHash(){
		return  \BitWasp\Bitcoin\Transaction\SignatureHash\SigHash::ALL;
	}

    /**
     * @return NetworkInterface
     * @throws \Exception
     */
    public static function testnet()
    {
        return \BitWasp\Bitcoin\Network\NetworkFactory::bitcoinTestnet();
    }
	
	public function toBTC($satoshi)
    {
        return bcdiv((int)(string)$satoshi, 100000000, 8);
    }
	
	public  function toSatoshi($btc)
    {
        $out = bcmul(sprintf("%.8f", (float)$btc), 100000000, 0);
		return (int)$out;
    }
	

    /**
     * @param string $method
     * @param null|array $params
     * @return mixed
     */
   public function jsonRequest($method, $params = null)
   {
        $this->id++;
		$client = $this->rpcClient;
		try {
			$request = $client->request($this->id, $method, $params);
        	$response = $client->send($request);
		} catch (\GuzzleHttp\Exception\TransferException $e) {
			$err ="";
			if ($e->hasResponse()) {
				$err.=" ". \GuzzleHttp\Psr7\str($e->getResponse());
			}
			$err = " ERROR: ".$e->getMessage();
			throw new \Exception ($err);
		}
        return \Graze\GuzzleHttp\JsonRpc\json_decode($response->getBody());
    }
	
	public function httpRequest($endpoint ='/', $req =[], $type = 'GET'){
		$url = $endpoint;
		$client = $this->httpClient;
		$request = $type =='GET'?'query':'form_params';
		if($this->throttled)
		$this->throttle();
		try {
			$response = $client->request($type, $url, [
				$request => $req,
				'debug' => false
			]);
		} catch (\GuzzleHttp\Exception\TransferException $e) {
			$err =" Client Error > ". \GuzzleHttp\Psr7\str($e->getRequest());
			if ($e->hasResponse()) {
				$err.=" ". \GuzzleHttp\Psr7\str($e->getResponse());
			}
			throw new \Exception ($err);
		}
		$json = preg_replace('/\: *([0-9]+\.?[0-9e+\-]*)/', ':"\\1"', $response->getBody());
		$obj = json_decode($json);
		if(is_object($obj))
		$obj->raw = $json;
        return  $obj;
	}
	
	private function throttle(){ // throttle http api requests 5 per second
		if(!isset($_SESSION['calls'])){
			$_SESSION['calls'] = 0;
			$_SESSION['last'] = time();
			return true;
		}
		if ($_SESSION['calls'] >= 5) {
			 if(time()==$_SESSION['last'] )
			 sleep(1); 
			 $_SESSION['calls'] = 0;
		 }else{
			$_SESSION['calls'] ++;
		 }
		$_SESSION['last'] = time();
	}
	
	protected function getTxObj(){
		
	}
	

}