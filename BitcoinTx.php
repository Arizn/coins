<?php
namespace Arizn;
use \Illuminate\Support\Collection;
use \BitWasp\Bitcoin\Address\AddressCreator;

class BitcoinTx
{
    public $Fee;
    public $to;
	public $from;
	public $amount;
	public $change;
	public $txHash;
	public $coin;
	public $api;
	public $utxos;
	public $info;
	public $fees;
	public $changeAddress;
	
    public function __construct( Collection $to , Collection $from , \Arizn\Api $api, $changeAddress, $fees = "medium")
    {
		$this->to = $to ;
		$this->api = $api ;
		$this->from = $from ;
		$this->Fee = $fees;
		$this->changeAddress = $changeAddress;
		$this->info = $api->currentBlock();
	}
	
	
	private function minDust(){
		$relayFee = $this->api->toSatoshi($this->info->relayfee);
		return 546 * $relayFee /1000; 
	}
	
	private function minFee(){
		$this->api->toSatoshi($this->info->relayfee);
	}
	
	private function FeePerKB(){
		$fee = $this->Fee;
		if(!empty( $this->Fee )&& is_numeric($this->Fee)){
			return $this->Fee;
		}
		if(empty( $this->Fee )){
			$fee = 'high';
		}
		 $res = $this->api->feePerKB();
		 switch($fee){
			 case 'high':
			 $this->Fee = $res->high/1000;
			 break;
			 case 'medium':
			  $this->Fee = $res->medium/1000;
			 break;
			 case 'low':
			  $this->Fee = $res->low/1000;
			 break;
		 }
		 return $this->Fee;
	}
	
		  /**
     * create, sign and send a transaction
     *
    
     */
    public function send() {
		$rawtx = $this->rawTx();
		die(var_dump($rawtx->getHex(), $this->etx->getHex()));
		try{
			$finished = $this->api->sendrawtransaction($rawtx->getHex());
		}catch(\Exception $e ){
			throw $e;
		}
       $this->txHash =$finished ;
	   return $this;
	}
	
	
	public function rawTx(){
		return $this->selectUTXOS()->getTX($this->to , $this->utxos);
	}
	
	public function selectUTXOS () {
		$sorted = $this->from->pluck('utxos')
					   ->collapse()
					   ->sortByDesc(function($utxo,$key){
					   		return $utxo->value*$utxo->confirmations;
					     });
        $total = 0;
		$target = $this->to->sum('amount');
		$selected =[];
		$OutSize = 16; // base tx size;
		$OutSize += 34*$this->to->count();; //outputs
		$fee = (int)ceil($OutSize * $this->FeePerKB());
		$changeFee = (int)ceil(34 * $this->FeePerKB());
		$to = $this->to;
        foreach ($sorted as $utxo ){
			$feez = (int)ceil($utxo->size * $this->FeePerKB());
			$fee +=  $feez;
            $selected[] = $utxo;
            $total += ($utxo->value - $feez);
			if ($total >=$target ){ 
				$change = $total - $target;
				if($change <= $changeFee ||$change < $this->minDust()||$change - $changeFee < $this->minDust() ){
					$change = 0;// its more expensive to add a chnage output
				}else{
					$fee+=$changeFee;
					$to = $this->to->concat([['amount'=>$change, 'address'=> $this->changeAddress]]);
					$total = collect($sorted)->sum('value');
 					$this->etx = $this->getTX($to ,  collect($selected));
 					$tt = strlen($this->etx->getHex())/2;
					$this->fees = ceil( $tt*$this->FeePerKB());
					$xchange = $total-$target-$this->fees;
					$this->to = $this->to->concat([['amount'=>(int)$xchange, 'address'=> $this->changeAddress]]);
				}
				$this->change = $change;
				$this->utxos = collect($selected);
				return $this;
				break;
			}
        }
		$msg = 'Insufficient Balance. Total Bal:'.$this->from->sum('balance')
			 . ' Required:'.$target
			 . ' Plus Fee:'.$fee;
		throw new \Exception($msg);
	 }
	 
	

	public function getTX($to , $utxos){
		\BitWasp\Bitcoin\Bitcoin::setNetwork($this->api->network);
		$AddressCreator =  new AddressCreator;
        $TX = new \BitWasp\Bitcoin\Transaction\Factory\TxBuilder();
	        foreach ($to as $out) {
				$TX->payToAddress($out['amount'],$AddressCreator->fromString($out['address'], $this->api->network));
		}
		$signInfo = [];
		$privateKeys = [];
        foreach ($utxos as $utxo) {
			foreach ($utxo->address->multisig->xpub->getKeys() as $key) {
				try{
					$privateKeys[] = $key->getPrivateKey();
				}catch(\Exception $e){
				}
			}
			if (count($privateKeys) < 2 ) throw new \Exception('Address '.$utxo->address->add.' requires at least two private Keys');
			$signInfo[] = new SignInfo($privateKeys, new \BitWasp\Bitcoin\Transaction\TransactionOutput($utxo->value, $utxo->scriptPubKey),$utxo->address->multisig->redeemscript);
			
			$TX->spendOutPoint(new \BitWasp\Bitcoin\Transaction\OutPoint(\BitWasp\Buffertools\Buffer::hex($utxo->txId), $utxo->index), $utxo->scriptPubKey);
			
        }
		$rawtx = $TX->get();
		$signer = new \BitWasp\Bitcoin\Transaction\Factory\Signer($rawtx, \BitWasp\Bitcoin\Bitcoin::getEcAdapter());
		$sigHash = $this->api->sigHash();
	   foreach ($signInfo as $idx => $info) {
			$redeemScript = $info->redeemScript;
			$txOut = $info->output;
			$keys = $info->keys;
			$signData = (new \BitWasp\Bitcoin\Transaction\Factory\SignData())->p2sh($redeemScript);
			foreach($keys as $key){
				$signer->sign($idx, $key, $txOut, $signData , $sigHash);
			}
        }
        return $signer->get();
    }
	
	

}

