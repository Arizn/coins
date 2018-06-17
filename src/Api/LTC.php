<?php
namespace Arizn\Api;
use Arizn\Api\Providers\Insight;
use Arizn\Api\Providers\Chainso;
use Graze\GuzzleHttp\JsonRpc\Client;
use Arizn\Api\ApiInterface;
class LTC implements ApiInterface
{
	public $bip44index = '2';
	private  $litecoin ,  
			 $trezor1, 
			 $trezor2, 
			 $trezor3,
			 $chainso, 
			 $coinspace,
			 $net;

    public function __construct(  ) 
    {
		$this->net = $this->network();
		
		$this->litecoin = new Insight('https://insight.litecore.io/api/');
		$this->trezor1 = new Insight('https://ltc-bitcore1.trezor.io/api/');  //listunspent
		$this->trezor2 = new Insight('https://ltc-bitcore2.trezor.io/api/');	// balance 
		$this->trezor3 = new Insight('https://ltc-bitcore3.trezor.io/api/');  // pushTx
		$this->chainso = new  Chainso('LTC');
		$this->coinspace = new Insight('https://ltc.coin.space/api/');  //get Block
	}
	public function getNetwork(){
		return $this->net;
	}
	
	 /**
     * @return NetworkInterface
     * @throws \Exception
     */
    private function network()
    {
        return \BitWasp\Bitcoin\Network\NetworkFactory::litecoin();
		//return \BitWasp\Bitcoin\Network\NetworkFactory::litecoinTestnet();
    }
	
	public function sigHash(){
		return  \BitWasp\Bitcoin\Transaction\SignatureHash\SigHash::ALL;
	}
	
	
    
	public function addressTx(array $addresses=[], $blocks = []){
		return $this->trezor3->addressTx($addresses, $blocks);
	}
	
	// litecoin
	public function listunspent($minconf, array $addresses=[], $max = null){
		return $this->litecoin->listunspent($minconf, $addresses, $max);
	}
	
	//trezor
	public function getBalance($minConf, array $addresses=[]){
		return $this->trezor2->getBalance($minConf, $addresses );
	}
	
	public function sendrawtransaction( $hexRawTx ){
		return $this->trezor3->sendrawtransaction( $hexRawTx );
	}
	
	public function getBlock($hash){
		return $this->trezor1->getBlock($hash);
	}
	
	public function getBlockByNumber($number){
		return $this->getBlock($number);
	}
	
	public function getTx($hash){
		return $this->coinspace->getTx($hash);
	}
	
	public function currentBlock(){
		return $this->coinspace->currentBlock();
	}
	
	public function feePerKB(){
		return $this->coinspace->feePerKB();;
	}
	
	//
	public function importaddress($address,$wallet_name =null,$rescan =null){
		return false;
	}
	
	
}

