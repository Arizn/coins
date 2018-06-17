<?php
namespace Arizn\Api;
use Arizn\Api\Providers\Insight;
use Graze\GuzzleHttp\JsonRpc\Client;
use BitWasp\Bitcoin\Network\NetworkFactory;
use Arizn\Api\ApiInterface;
use \BitWasp\Bitcoin\Transaction\SignatureHash\SigHash;
use \Btccom\BitcoinCash\Transaction\SignatureHash\SigHash as BchSigHash;
use \Btccom\BitcoinCash\Network\Networks\BitcoinCash;
class BCH implements ApiInterface
{
	public $bip44index = '145';
	private  $blockdozer ,  // api providers
			 $bitpay , 
			 $bitcoin ,  
			 $trezor1, 
			 $trezor2, 
			 $trezor3,
			 $coinspace, 
			 $blockexplorer,
			 $net;

    public function __construct() 
    {
		$this->net = $this->network();
		$this->coinspace = new Insight('https://bch.coin.space/api/'); 
		$this->bitcoin = new Insight('https://explorer.bitcoin.com/api/bch/');
		$this->bitpay = new Insight('https://bch-insight.bitpay.com/api/');
		$this->blockexplorer = new Insight('https://bitcoincash.blockexplorer.com/api/'); 
		$this->blockdozer = new Insight('https://blockdozer.com/insight/api/'); 
		$this->trezor1 = new Insight('https://bch-bitcore1.trezor.io/api/');   
		$this->trezor2 = new Insight('https://bch-bitcore2.trezor.io/api/');	
		$this->trezor3 = new Insight('https://bch-bitcore3.trezor.io/api/'); 
		
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
        return new BitcoinCash();
    }
	
	public function sigHash(){
		return SigHash::BITCOINCASH | SigHash::ALL;
	}

   

	//chainso
	public function addressTx(array $addresses=[], $blocks = []){
		return $this->blockexplorer->addressTx($addresses, $blocks);
	}
	
	// dash
	public function listunspent($minconf, array $addresses=[], $max = null){
		return $this->bitcoin->listunspent($minconf, $addresses, $max);
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
		return $this->blockdozer->getTx($hash);
	}
	
	public function currentBlock(){
		return $this->coinspace->currentBlock();
	}
	
	public function feePerKB(){
		return $this->bitpay->feePerKB();;
	}
	//
	public function importaddress($address,$wallet_name =null,$rescan =null){
		return false;
	}
	
	
}

