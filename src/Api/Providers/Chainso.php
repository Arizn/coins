<?php
namespace Arizn\Api\Providers;
use Arizn\Api\Providers\Provider;
use Arizn\Api\ApiInterface;
use Graze\GuzzleHttp\JsonRpc\Client;

class Chainso extends Provider implements ApiInterface
{
    protected $coin;
    public function __construct($coin)
    {
		$this->coin = $coin;
        parent::__construct( "https://chain.so/api/v2/" );
    }
	
	private function validate($request){
		
		if($request->status == "fail") {
			$msg =  "An Error Ocurred";
			foreach ($request->data as $k=>$v)
			$msg .= $k.":".$v;
			throw new Exception($msg);
		}
		return $request->data;
	}

	public function listunspent($minconf, array $addresses=[], $max = null){
		$return = [];
		foreach($addresses as $address){
			$utxo = $this->get_unspent($address);
			if(count($utxo->txs) < 1)continue;
			foreach($utxo->txs as $utx  ){
				if($utx->confirmations < $minconf)continue;
				$o = new \stdClass;
				$o->address = $utxo->address;
				$o->txid = $utx->txid;
				$o->vout = $utx->output_no;
				$o->scriptPubKey = $utx->script_hex;
				$o->amount = $utx->value;
				$o->satoshis = $this->toSatoshi($utx->value);
				$o->confirmations = $utx->confirmations;
				$o->ts = $utx->time;
				$return[] = $o;
			}
		}
		return $return;
		
	}
	
	private function get_unspent($address){
		$endpoint = "get_tx_unspent/{$this->coin}/".$address;
		$request = $this->httpRequest($endpoint);
		return $this->validate($request);
	}
	
	public function importaddress($address,$wallet_name =null,$rescan =null){
		return false;
	}
	
	public function sendrawtransaction( $hexRawTx ){
		$endpoint = "send_tx/{$this->coin}/".$hexRawTx;
		$request = $this->httpRequest($endpoint);
		return $this->validate($request)->txid;
	}
	
	
	public function addressTx(array $addresses=[], $blocks = []){
		$valid = [];
		foreach($blocks as $blck){
			$block = $this->getBlock($blck);
			die(var_dump($block));
			foreach($block->txs as $tx){
				$vout  = collect($tx->outputs);
				$vin  = collect($tx->inputs);
				$all_from = $vin->pluck('address');
				$all_to = $vout->pluck('address');
				$mine_from = $all_from->intersect($addresses);
				$mine_to = $all_to->intersect($addresses);
				
				if($mine_from->count()){
					$btx = new \Arizn\BTX;
					$btx->from = $mine_from;
					$btx->type = 'send'; 
					$btx->to = $all_to;
					$btx->hash = $tx->txid ;
					$btx->fee = $tx->fee;
					$btx->amount = $this->toSatoshi($vin->whereIn('address', $mine_from)->sum('value'));
					$btx->confirmations = isset($block->confirmations)?$block->confirmations:0;
					$btx->blockHeight = $block->block_no;
					$valid[] = $btx;
				}
				
				if($mine_to->count()){
					$btx = new \Arizn\BTX;
					$btx->from = $all_from;
					$btx->type = 'receive'; 
					$btx->to = $mine_to;
					$btx->fee = $tx->fee;
					$btx->hash = $tx->txid;
					$btx->amount = $this->toSatoshi($vout->whereIn('address', $mine_to)->sum('value'));
					$btx->confirmations = isset($block->confirmations)?$block->confirmations:0;
					$btx->blockHeight = $block->block_no;
					$valid[] = $btx;
				}
			}
		}
		die(var_dump($valid));
		return collect($valid);
	}
	public function getBlock($hash){
		$endpoint = "block/{$this->coin}/".$hash;
		$request = $this->httpRequest($endpoint); 
		return $this->validate($request);
	}
	
	public function getBlockByNumber($number){
		return $this->getBlock($number);
	}
	
	
	public function getTx($hash){
		$endpoint = "get_tx/{$this->coin}/".$hash;
		$request = $this->httpRequest($endpoint);
		return $this->validate($request);
	}
	
	public function currentBlock(){
		$endpoint = "get_info/{$this->coin}/";
		$request = $this->httpRequest($endpoint);
		return $this->validate($request);
	}
	
	public function feePerKB(){
		throw new Exception('SoChain Has No fees API');
	}
	
	public function getBalance($minconf, array $addresses=[]){
		$balance = 0;
		foreach($addresses as $address){
			$utxo = $this->get_unspent($address);
			foreach($utxo->data->txs as $tx){
				if($utx->confirmations < $minconf)continue;
				$balance = bcadd($balance , $tx->value );
			}
 		}
		return $balance;
	}
	
	
	
}

