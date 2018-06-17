<?php
namespace Arizn\Api;
use Arizn\Api\Providers\Insight;
use Graze\GuzzleHttp\JsonRpc\Client;
use Arizn\Api\ApiInterface;
class ZEC implements ApiInterface
{
	public $bip44index = '133';
	private  $blockexplorer ,  // api provider 
			 $zcash ,  
			 $trezor1, 
			 $trezor2, 
			 $trezor3,
			 $net;

    public function __construct(  ) 
    {
		$this->net =  $this->network();
		$this->blockexplorer = new Insight('https://zcash.blockexplorer.com/api/'); //
		$this->zcash = new Insight('http://insight.mercerweiss.com/api/');
		$this->trezor1 = new Insight('https://zec-bitcore1.trezor.io/api/');  //listunspent
		$this->trezor2 = new Insight('https://zec-bitcore2.trezor.io/api/');	// balance 
		$this->trezor3 = new Insight('https://zec-bitcore3.trezor.io/api/');  // pushT
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
        return new Networks\Zcash();
		//return new Networks\ZcashTestnet();
    }
	
	
	//bitpay
	public function addressTx(array $addresses=[], $blocks = []){
		return $this->trezor3->addressTx($addresses, $blocks);
	}
	
	//trezor
	public function listunspent($minconf, array $addresses=[], $max = null){
		return $this->trezor1->listunspent($minconf, $addresses, $max);
	}
	
	public function getBalance($minConf, array $addresses=[]){
		return $this->trezor2->getBalance($minConf, $addresses );
	}
	
	public function importaddress($address,$wallet_name =null,$rescan =null){
		return false;
	}
	
	public function sendrawtransaction( $hexRawTx ){
		return $this->trezor3->sendrawtransaction( $hexRawTx );
	}
	
	// chainso
	public function getBlock($hash){
		return $this->trezor2->getBlock($hash);
	}
	
	public function getBlockByNumber($number){
		return $this->getBlock($number);
	}
	
	public function getTx($hash){
		return $this->blockexplorer->getTx($hash);
	}
	
	public function currentBlock(){
		return $this->trezor2->currentBlock();
	}
	
	public function feePerKB(){
		return $this->zcash->feePerKB();;
	}
	
	
	
	
}

