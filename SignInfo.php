<?php

namespace Arizn;

class SignInfo {

    /**
     * @var ScriptInterface
     */
    public $redeemScript;

    /**
     * @var TransactionOutput
     */
    public $output;
	
	/**
     * @var privatekeys
     */
    public $keys;
	
	/**
     * @var privatekeys
     */
    public $utxoData;
	
	
	
    public function __construct($keys, \BitWasp\Bitcoin\Transaction\TransactionOutput $output, $redeemScript=NULL ) {
		$this->keys = $keys;
        $this->redeemScript = $redeemScript;
        $this->output = $output;
    }
}
