<?php
namespace Arizn\Api;
use Arizn\Api\Providers\Insight;
use Graze\GuzzleHttp\JsonRpc\Client;
use Arizn\Api\ApiInterface;
use \BitWasp\Bitcoin\Transaction\SignatureHash\SigHash;
use \Btccom\BitcoinCash\Transaction\SignatureHash\SigHash as BchSigHash;
class BTGTESTNET implements ApiInterface
{
	public $bip44index = '156';
	private  $btgexplorer ,  // api providers
			 $trezor1, 
			 $trezor2, 
			 $trezor3,
			 $bitcoingold,
			 $net;

    public function __construct(   ) 
    {
		$this->net =  $this->network();
		$this->bitcoingold = new Insight('https://test-explorer.bitcoingold.org/insight-api/'); 
	}
	
	
	public function getNetwork(){
		return $this->net;
	}
	
	public function sigHash(){
		return SigHash::ALL | BchSigHash::BITCOINCASH;
	}
	
	 /**
     * @return NetworkInterface
     * @throws \Exception
     */
    private function network()
    {
		return new Networks\BitcoingoldTestnet();
    }

   
	//chainso
	public function addressTx(array $addresses=[], $blocks = []){
		return $this->bitcoingold->addressTx($addresses, $blocks);
	}
	
	//dash
	public function listunspent($minconf, array $addresses=[], $max = null){
		return $this->bitcoingold->listunspent($minconf, $addresses, $max);
	}
	
	//trezor
	public function getBalance($minConf, array $addresses=[]){
		return $this->bitcoingold->getBalance($minConf, $addresses );
	}
	
	public function sendrawtransaction( $hexRawTx ){
		return $this->bitcoingold->sendrawtransaction( $hexRawTx );
	}
	
	public function getBlock($hash){
		return $this->bitcoingold->getBlock($hash);
	}
	
	public function getBlockByNumber($number){
		return $this->getBlock($number);
	}
	
	public function getTx($hash){
		return $this->bitcoingold->getTx($hash);
	}
	
	public function currentBlock(){
		return $this->bitcoingold->currentBlock();
	}
	
	public function feePerKB(){
		return $this->bitcoingold->feePerKB();
	}
	//
	public function importaddress($address,$wallet_name =null,$rescan =null){
		return false;
	}
	
	
}

