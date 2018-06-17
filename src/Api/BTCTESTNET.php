<?php
namespace Arizn\Api;
use Arizn\Api\Providers\Insight;
use Arizn\Api\Providers\Chainso;
use Graze\GuzzleHttp\JsonRpc\Client;
use Arizn\Api\ApiInterface;

class BTCTESTNET implements ApiInterface
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
		$this->blockexplorer = new Insight('https://testnet.blockexplorer.com/api/'); //
		$this->bitpay = new Insight('https://test-insight.bitpay.com/api/');  // getTx
		$this->fbitpay = new Insight('https://insight.bitpay.com/api/');  // getTx
		
	}
	
	 /**
     * @return NetworkInterface
     * @throws \Exception
     */
    private function network()
    {
         return \BitWasp\Bitcoin\Network\NetworkFactory::bitcoinTestnet();
    }

   
	
	public function getNetwork(){
		return $this->net;
	}
	
	//bitpay
	public function addressTx(array $addresses=[], $blocks = []){
		return $this->bitpay->addressTx($addresses, $blocks);
	}
	
	public function sigHash(){
		return  \BitWasp\Bitcoin\Transaction\SignatureHash\SigHash::ALL;
	}
	
	//trezor
	public function listunspent($minconf, array $addresses=[], $max = null){
		return $this->bitpay->listunspent($minconf, $addresses, $max);
	}
	
	public function getBalance($minConf, array $addresses=[]){
		$this->blockexplorer->getBalance($minConf, $addresses );
	}
	
	public function importaddress($address,$wallet_name =null,$rescan =null){
		return false;
	}
	
	public function sendrawtransaction( $hexRawTx ){
		return $this->bitpay->sendrawtransaction( $hexRawTx );
	}
	
	// chainso
	public function getBlock($hash){
		return $this->blockexplorer->getBlock($hash);
	}
	
	public function getBlockByNumber($number){
		return $this->getBlock($number);
	}
	
	public function getTx($hash){
		return $this->blockexplorer->getTx($hash);
	}
	
	public function currentBlock(){
		return $this->fbitpay->currentBlock();
	}
	
	public function feePerKB(){
		return $this->fbitpay->feePerKB();;
	}
	
	
	
	
}

