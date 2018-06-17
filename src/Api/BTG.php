<?php
namespace Arizn\Api;
use Arizn\Api\Providers\Insight;
use Graze\GuzzleHttp\JsonRpc\Client;
use Arizn\Api\ApiInterface;
use \BitWasp\Bitcoin\Transaction\SignatureHash\SigHash;
class BTG implements ApiInterface
{
	public $bip44index = '156';
	private  $btgexplorer ,  // api providers
			 $trezor1, 
			 $trezor2, 
			 $trezor3,
			 $bitcoingold,
			 $net;

    public function __construct( $testnet = false ) 
    {
		$this->net = $testnet?$this->testnet():$this->network();
		$this->bitcoingold = new Insight('https://explorer.bitcoingold.org/insight-api'); 
		$this->btgexplorer = new Insight('https://btgexplorer.com/api'); 
		$this->trezor1 = new Insight('https://btg-bitcore1.trezor.io/api');   
		$this->trezor2 = new Insight('https://btg-bitcore2.trezor.io/api');	
		$this->trezor3 = new Insight('https://btg-bitcore3.trezor.io/api'); 
		
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
        return new Networks\Bitcoingold();
    }

    /**
     * @return NetworkInterface
     * @throws \Exception
     */
    public static function testnet()
    {
        return new Networks\BitcoingoldTestnet();
    }
	public function sigHash(){
		return SigHash::ALL | SigHash::BITCOINCASH;
	}
	//chainso
	public function addressTx(array $addresses=[], $blocks = []){
		return $this->trezor3->addressTx($addresses, $blocks);
	}
	
	// dash
	public function listunspent($minconf, array $addresses=[], $max = null){
		return $this->bitcoingold->listunspent($minconf, $addresses, $max);
	}
	
	//trezor
	public function getBalance($minConf, array $addresses=[]){
		$this->trezor2->getBalance($minConf, $addresses );
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
		return $this->btgexplorer->getTx($hash);
	}
	
	public function currentBlock(){
		return $this->btgexplorer->currentBlock();
	}
	
	public function feePerKB(){
		return $this->trezor1->feePerKB();
	}
	//
	public function importaddress($address,$wallet_name =null,$rescan =null){
		return false;
	}
	
	
}

