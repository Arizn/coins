<?php

namespace Arizn\Api;



interface ApiInterface
{
	public function listunspent($minconf, array $addresses=[], $max = null);
	public function importaddress($address,$wallet_name =null,$rescan =null);
	public function sendrawtransaction( $hexRawTx );
	public function getBlock($blockHeigt);
	public function getBlockByNumber($no);
	public function getTx($Hash);
	public function currentBlock();
	public function feePerKB();
	public function sigHash();
	public function getBalance($minConf, array $addresses=[]);
}