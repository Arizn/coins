<?php
namespace Arizn;

class HD{
	
	
	
	
	public $bip44; /* Wallet  bip44 format*/
	public $api; /* api object*/
	public $network; /* Bitcoin network*/
	/* Wallet  Private Key*/
	private $xpriv;
	/* Wallet  PrvteKey*/
	private $xprivKey;
	/* Wallet  Public Key*/
	private $xpubKey;
	/* Wallet  Public Key*/
	private $xpub; 
	/* Wallet Password*/
	private $password; 
	/* Wallet  Master ext Private Key*/
	private $master_xpriv;
	/* Wallet  Master Private Key*/
	private $master_xprivKey;
	/* Wallet  Master Private Key*/
	private $mnemonic;
	/* Wallet  Master Private Key*/
	private $master_xpub;
	
	function __construct( \Arizn\Api $api){
		$this->api = $api;
		$this->network = $api->network;
		$this->bip44 = "m/44'/".$api->provider->bip44index."'/0'";
		\BitWasp\Bitcoin\Bitcoin::setNetwork($this->network);
	}
	
	
	function getXpub(){
		if(is_null($this->xpub))throw new Exception('xpub not set');
		return $this->xpub;
	}
	
	function getXpubKey(){
		if(is_null($this->xpubKey))throw new Exception('xpub not set');
		return $this->xpubKey;
	}
		
 	function getMnemonic(){
		if(is_null($this->mnemonic))throw new Exception('mnemonic not set');
		return $this->mnemonic;
	}
	
	function getXpriv(){
		if(is_null($this->xpriv))throw new Exception('xpriv not set');
		return $this->xpriv;
	}
	function getXprivKey(){
		if(is_null($this->xprivKey))throw new Exception('xpriv not set');
		return $this->xprivKey;
	}
	function getMasterXpriv(){
		if(is_null($this->master_xpriv))throw new Exception('master_xpriv not set');
		return $this->master_xpriv;
	}
	
	function getMasterXprivKey(){
		if(is_null($this->master_xprivKey))throw new Exception('master_xpriv Key not set');
		return $this->master_xprivKey;
	}
	
	function getPassword(){
		if(is_null($this->password))throw new Exception('password not set');
		return $this->password;
	}

	public function randomSeed($password=null){
		if(!is_null($password)){
			$this->password = $password;
		}
		$ecAdapter =  \BitWasp\Bitcoin\Bitcoin::getEcAdapter();
		$math = new \BitWasp\Bitcoin\Math\Math();
		$random = new \BitWasp\Bitcoin\Crypto\Random\Random();
		$entropy = $random->bytes(64);
		$bip39 = \BitWasp\Bitcoin\Mnemonic\MnemonicFactory::bip39();
		$seedGenerator = new \BitWasp\Bitcoin\Mnemonic\Bip39\Bip39SeedGenerator($bip39);
		// Get the mnemonic
		$mnemonic = $bip39->entropyToMnemonic($entropy);
		$this->mnemonic = $mnemonic;
		// Derive a seed from mnemonic/password
		if(is_null($this->password)){
			$pass = $random->bytes(8);
			$this->password = $pass->getHex();
		}
		$seed = $seedGenerator->getSeed($this->mnemonic, $this->password);
		$master = \BitWasp\Bitcoin\Key\Deterministic\HierarchicalKeyFactory::fromEntropy($seed);
		return $this->masterSeed($master->toExtendedPrivateKey($this->network));
	}
	
	public function recover($mnemonic, $password){
		$this->mnemonic = $mnemonic;
		$this->password = $pasword;
		$bip39 = \BitWasp\Bitcoin\Mnemonic\MnemonicFactory::bip39();
		$seedGenerator = new \BitWasp\Bitcoin\Mnemonic\Bip39\Bip39SeedGenerator($bip39);
		$seed = $seedGenerator->getSeed($this->mnemonic, $this->password);
		$master = \BitWasp\Bitcoin\Key\Deterministic\HierarchicalKeyFactory::fromEntropy($seed);
		
		return $this->masterSeed($master->toExtendedPrivateKey($this->network));
	}
	
	public function masterSeed($master){
		//Master xpriv
		$master = \BitWasp\Bitcoin\Key\Deterministic\HierarchicalKeyFactory::fromExtended($master,$this->network);
		$this->master_xprivKey = $master;
		$master_xpriv = $master->toExtendedPrivateKey($this->network);
		$this->master_xprivKey = $master;
		$this->master_xpriv = $master_xpriv;
		$master_xpub = $master->toExtendedPublicKey($this->network); // path is master''
		$this->master_xpub  = $master_xpub;
		$hardened = $master->derivePath($this->bip44);
		$this->xpub = $hardened->toExtendedPublicKey($this->network);
		$this->xpriv = $hardened->toExtendedPrivateKey($this->network);
		$this->xpubKey = $hardened;
		$this->xprivKey = $hardened;
		return $this;
	}
	
	public function privateSeed($xpriv){
		//Master xpriv
		$this->xpriv = $xpriv;
		$xpriv = \BitWasp\Bitcoin\Key\Deterministic\HierarchicalKeyFactory::fromExtended($xpriv);
		$this->xpub = $xpriv->toExtendedPublicKey($this->network);
		$this->xpubKey = $xpriv;
		$this->xprivKey = $xpriv;
		return $this;
	}
	
	public function publicSeed($xpub){
		//Master xpriv
		$this->xpub = $xpub;
		$key = \BitWasp\Bitcoin\Key\Deterministic\HierarchicalKeyFactory::fromExtended($this->xpub);
		$this->xpubKey = $key;
		return $this;
	}
	
	public function getAddress($index){
		if(empty($this->xpub))throw new \Exception('Public Key is missing');
		$key = \BitWasp\Bitcoin\Key\Deterministic\HierarchicalKeyFactory::fromExtended($this->xpub);
		if(strpos($index,'/')!==false)
		$xpub = $key->derivePath($index);
		else
		$xpub = $key->deriveChild($index);
		$publicKey = $xpub->getPublicKey($this->network);
		return $publicKey->getAddress()->getAddress($this->network);
	}
	
	public function privateKey($index){
		if(empty($this->xpriv))throw new \Exception('HD->derive($index) // Private Key is missing');
		$key = \BitWasp\Bitcoin\Key\Deterministic\HierarchicalKeyFactory::fromExtended($this->xpriv);
		if(strpos($index,'/')!==false)
		$xpriv = $key->derivePath($index);
		else
		$xpriv = $key->deriveChild($index);
		return $xpriv;
	}
	
	
	
	
	
}
