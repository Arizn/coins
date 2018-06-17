<?php

namespace BitWasp\Bitcoin\Network\Networks;

use BitWasp\Bitcoin\Network\Network;
use BitWasp\Bitcoin\Script\ScriptType;

class Multichain extends Network
{
	protected $bip32ScriptTypeMap = [
        self::BIP32_PREFIX_XPUB => ScriptType::P2PKH,
        self::BIP32_PREFIX_XPRV => ScriptType::P2PKH,
    ];
	
	/**
     * Load network data, throw exception if it's not provided
     *
     * @param string $addressByte
     * @param string $p2shByte
     * @param string $privByte
     * @param string $hdPubByte
	 * @param string $hdPrivByte
	 * @param string $p2pMagic
	 * @param string $SegwitBech32Prefix
	 * @param string $signedMessagePrefix 
     * @throws \Exception
     */
    public function __construct($addressByte, $p2shByte, $privByte, $hdPubByte ,$hdPrivByte, $p2pMagic, $SegwitBech32Prefix = NULL, $signedMessagePrefix ='')
    {
        if (!(ctype_xdigit($addressByte) && strlen($addressByte) === 2)) {
            throw new \InvalidArgumentException('address byte must be 1 hexadecimal byte');
        }

        if (!(ctype_xdigit($p2shByte) && strlen($p2shByte) === 2)) {
            throw new \InvalidArgumentException('p2sh byte must be 1 hexadecimal byte');
        }

        if (!(ctype_xdigit($privByte) && strlen($privByte) === 2)) {
            throw new \InvalidArgumentException('priv byte must be 1 hexadecimal byte');
        }

		$this->base58PrefixMap = [
			self::BASE58_ADDRESS_P2PKH => $addressByte,
			self::BASE58_ADDRESS_P2SH => $p2shByte,
			self::BASE58_WIF => $privByte,
		];
		$bech32 = empty($SegwitBech32Prefix)?[]:[self::BECH32_PREFIX_SEGWIT => $SegwitBech32Prefix];
		$this->$bech32PrefixMap = $bech32 ;
		
		$this->bip32PrefixMap = [
			self::BIP32_PREFIX_XPUB => $hdPubByte,
			self::BIP32_PREFIX_XPRV => $hdPrivByte
		];
		
		$this->signedMessagePrefix = $signedMessagePrefix;
		$this->p2pMagic = $p2pMagic;
        
		parent::__construct();
       
    }

    

}
