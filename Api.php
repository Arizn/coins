<?php
namespace Arizn;
use Arizn\Api\Provider;
use \BitWasp\Bitcoin\Network\Network;
use phpseclib\Math\BigInteger;
use Arizn\Api\ApiInterface;
use Illuminate\Support\Collection;

class Api 
{
    public $network;
    public $provider;
	public $minconf = 6;
	public $minDust = 600;
	public $max = 999999;

    public function __construct( ApiInterface $provider)
    {
        $this->provider = $provider;
		$this->network =  $provider->getNetwork();
        
    }
	
	public function sigHash( ){
		return $this->provider->sigHash();
	}
	public function toBTC($satoshi)
    {
        return bcdiv((int)(string)$satoshi, 100000000, 8);
    }
	
	public function addressTx(array $addresses=[], $blocks = []){
		return $this->provider->addressTx($addresses, $blocks);
	}
	
	public  function toSatoshi($btc)
    {
        $out = bcmul(sprintf("%.8f", (float)$btc), 100000000, 0);
		return (int)$out;
    }
	
	public function fillUTXOS(Collection $address){
			$utxos = $this->listunspent($address->pluck('add'));
			$utxos = collect($utxos)->groupBy('address');
			return $address->map(function($add , $id)use($utxos){
				$ok = $utxos->get($add->address);
				if(empty($ok )) return NULL;
				$bal = $ok->sum('amount');
				foreach ($ok as $utxo ){
					$utxo->balance = $bal;
					if( $utxo->confirmations > $this->minconf)
					$add->utxos[] = new UTXO($add,$utxo);
				}
				return $add;
			})->reject(function($value , $key){
				 return empty($value)||count($value->utxos) < 1 ;
			});
	}
	
	
	public function listunspent($address){
		$address = is_array($address)?$address:[$address];
		return $this->provider->listunspent($this->minconf, $address, $this->max);
	}
	
	public function getBalance($address){
		$address = is_array($address)?$address:[$address];
		return $this->provider->getBalance($this->minconf, $address);
	}
	
	public function importaddress($address,$wallet_name =null,$rescan =null){
		return $this->provider->importaddress($address,$wallet_name =null,$rescan =null);
	}
	
	public function sendrawtransaction( $hexRawTx ){
		return $this->provider->sendrawtransaction( $hexRawTx );
	}
	
	public function getBlock($blockHeigt){
		return $this->provider->getBlock($blockHeigt);
	}
	
	public function getTx($Hash){
		return $this->provider->getTx($Hash);
	}
	
	public function currentBlock(){
		return $this->provider->currentBlock();
	}
	
	public function feePerKB(){
		return $this->provider->feePerKB();
	}


   
}