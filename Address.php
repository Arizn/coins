<?php
namespace Arizn;
use arizn\Api;

class Address
{
	public $api = NULL;
	public $privateKeys =[];
	public $publicKeys = [];
	public $address = NULL;
	public $add = NULL;
	public $utxos = [];
	public $balance = NULL;
	public $type = NULL;
	public $multisig = NULL; //Address $HDMultisig object
	public function __construct( \Arizn\Multisig $multisig, $update = false){
		$this->api = $multisig->api;
		$this->multisig = $multisig;
		$this->privateKeys = $multisig->privateKeys;
		$this->address = $multisig->xpub->getAddress();
		$this->add = $multisig->address;
		if($update)
		$this->getUpdate();
	}
	
	public function getUpdate(){ // update the address balance
		$utxos = $this->api->listunspent($this->add);
		$utxos = collect($utxos);
		$bal = $utxos->sum('amount');
		foreach( $utxos as $utxo){
			$utxo->balance = $bal;
			if( $utxo->confirmations > $this->api->minconf)
			$this->utxos[] = new UTXO($this,$utxo);
		}
		$all = collect($this->utxos);
		$this->balance = $all->count()?$all->sum('value'):0;
	}
	
	
	
	
	
	
}

