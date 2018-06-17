<?php
namespace Arizn;
class UTXO
{
	public $address,$txId,$index,$scriptPubKey,$value,$confirmations;
	public $size = 297;
	public function __construct(\Arizn\Address $address,  $utxo )
	{
		$this->txId = $utxo->txid;
		$this->index = $utxo->vout;
		$this->scriptPubKey=\BitWasp\Bitcoin\Script\ScriptFactory::fromHex($utxo->scriptPubKey);
		$this->value = $address->api->toSatoshi($utxo->amount);
		$this->confirmations = $utxo->confirmations;
		$this->balance =$address->api->toSatoshi( $utxo->balance);
        $this->address = $address;
  	}

	
	
}

