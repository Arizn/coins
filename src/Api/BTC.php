<?php
namespace Arizn\Api;
use Arizn\Api\Providers\Insight;
use Arizn\Api\Providers\Chainso;
use Graze\GuzzleHttp\JsonRpc\Client;
use Arizn\Api\ApiInterface;

class BTC implements ApiInterface
{
	
	public $bip44index = '0';
	private  $blockexplorer ,  // api providers
			 $bitpay , 
			 $bitcoin ,  
			 $trezor1, 
			 $trezor2, 
			 $trezor3,
			 $chainso, 
			 $coinspace, 
			 $net;

    public function __construct() // well use varoius api to handle rate limmiting
    {
		$this->net = $this->network();
		$this->blockexplorer = new Insight('https://blockexplorer.com/api/'); //
		$this->bitpay = new Insight('https://insight.bitpay.com/api/');  // getTx
		$this->bitcoin = new Insight('https://explorer.bitcoin.com/api/btc/');
		$this->trezor1 = new Insight('https://btc-bitcore1.trezor.io/api/');  //listunspent
		$this->trezor2 = new Insight('https://btc-bitcore2.trezor.io/api/');	// balance 
		$this->chainso = new  Chainso('BTC');
		$this->trezor3 = new Insight('https://btc-bitcore3.trezor.io/api/');  // pushTx
		$this->coinspace = new Insight('https://btc.coin.space/api/');  //get Block
	}
	
	 /**
     * @return NetworkInterface
     * @throws \Exception
     */
    private function network()
    {
        return \BitWasp\Bitcoin\Network\NetworkFactory::bitcoin();
    }
	
	public function sigHash(){
		return  \BitWasp\Bitcoin\Transaction\SignatureHash\SigHash::ALL;
	}

   
	
	public function getNetwork(){
		return $this->net;
	}
	
	//bitpay
	public function addressTx(array $addresses=[], $blocks = []){
		return $this->bitpay->addressTx($addresses, $blocks);
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
		return $this->blockexplorer->getBlock($hash);
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
		return $this->blockexplorer->feePerKB();;
	}
	
	
	
	
}

