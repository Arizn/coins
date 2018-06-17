<?php
namespace Arizn\Api\Providers;
use Arizn\Api\Providers\Provider;
use Arizn\Api\ApiInterface;
use Graze\GuzzleHttp\JsonRpc\Client;

class Insight extends Provider implements ApiInterface
{
	

    public function __construct(string $insighturl)
    {
        parent::__construct( $insighturl );
    }	

	public function listunspent($minconf, array $addresses=[], $max = null){
		$endpoint = "addrs/".implode(',',$addresses)."/utxo";
		$result = collect($this->httpRequest($endpoint))->reject(function($v,$i)use($minconf){
			$v->confirmations < $minconf; 
		});
		return $result;

	}
	
	public function addressTx(Illuminate\Support\Collection $addresses , $blocks = []){
		$adrs =   $addresses->pluck('address');
		$endpoint = "addrs/".implode(',',$adrs)."/txs";
		$from = 0;
		$to = 50;
		$result = $this->httpRequest($endpoint."?from={$from}&to={$to}");
		var_dump($result->raw);
		$txs = collect($result->items);
		if($result->totalItems > 50 ){
			$loops = ceil($result->totalItems/50); // loop through all the pages
			for($i=2 ;$i == $loops; $i++ ){ 
				$from = $to;
				$to = $to*$i;
				$response = $this->httpRequest($endpoint."?from={$from}&to={$to}");
				$txs->concat($response->items);
			}
		}
		$valid = [];
		foreach ($result->items as $tx){
			if(count($blocks)&&!in_array($tx->blockheight, $blocks)) continue;
			$vin = collect($tx->vin);
			$vout = collect($tx->vout);
			$all_from = $vin->pluck('addr');
			$all_to = $vout->pluck('scriptPubKey')->pluck('addresses')->collapse();
			$mine_from = $all_from->intersect($adrs);
			$mine_to = $all_to->intersect($adrs);
			$src = $addresses->groupBy('address');
			
			/*if($all_from->count()){
				foreach($mine_from as $address){
					if(!isset($src[$address]))continue;
					$btx = new \Arizn\BTX;
					$btx->from = $address;
					$btx->type = 'send'; 
					$btx->to = $all_to->first();
					$btx->hash = $tx->txid ;
					$btx->fee = $this->toSatoshi($tx->fees);
					$btx->amount = $vin->whereIn('add', $mine_from)->sum('valueSat');
					$btx->confirmations = $tx->confirmations;
					$btx->blockHeight = $tx->blockheight;
					$btx->address = $src[$address];
					$valid[] = $btx;
				}
			}*/
			
			if($mine_to->count()){
				$vouts = $vout->reject(function($val, $key)use($mine_to){
						return $mine_to->intersect($val->scriptPubKey->addresses)->count() < 1;
				});
				foreach($vouts as $mine){
					foreach($mine->scriptPubKey->addresses as $ok){
						if(!isset($src[$ok]))continue;
						$btx = new \Arizn\BTX;
						$btx->from = collect($all_from)->first();
						$btx->type = 'receive'; 
						$btx->to = $ok;
						$btx->hash = $tx->txid ;
						$btx->fee = $this->toSatoshi($tx->fees);
						$btx->confirmations = $tx->confirmations;
						$btx->blockHeight = $tx->blockheight;
						$btx->amount =  $this->toSatoshi($mine->value);
						$btx->address = $src[$ok];
						$valid[] = $btx;
					}
				}
			}
	
		}
		return collect($valid);
	}
	
	public function importaddress($address,$wallet_name =null,$rescan =null){
		return false;
	}
	
	public function sendrawtransaction( $hexRawTx ){
		$endpoint = "tx/send";
		return $this->httpRequest($endpoint,['rawtx'=>$hexRawTx],'POST')->txid;
	}
	
	public function getBlock($hash){
		$endpoint = "txs/?block=".$hash;
		$result = $this->httpRequest($endpoint);
		$txs = collect($result->txs);
		if($result->pagesTotal > 1 ){
			for($i=2 ;$i == $result->pagesTotal; $i++ ){ 
				$response = $this->httpRequest($endpoint);
				$txs->concat($response->txs);
			}
		}
		return $result;
	}
	
	public function getBlockByNumber($number){
		$endpoint = "block-index/".$number;
		$hash = $this->httpRequest($endpoint)->blockHash;
		return $this->getBlock($hash);
	}
	
	public function getTx($hash){
		$endpoint = "tx/".$hash;
		return $this->httpRequest($endpoint);
	}
	
	public function currentBlock(){
		$endpoint = "status?q=getInfo";
		return $this->httpRequest($endpoint)->info;
	}
	
	public function feePerKB(){
		$endpoint = "utils/estimatefee";
		$fee = $this->httpRequest($endpoint,['nbBlocks'=>'2,6,12']);
		$fees = new \stdClass;
		$fees->high = $this->toSatoshi($fee->{'2'});
		$fees->medium = $this->toSatoshi($fee->{'6'});
		$fees->low = $this->toSatoshi($fee->{'12'});
		return $fees;
	}
	
	public function getBalance($minconf, array $addresses=[]){
		return $this->listunspent($minconf, $addresses)->sum('amount');
	}
	
	
}

