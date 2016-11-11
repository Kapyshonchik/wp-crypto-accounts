<?php

namespace wpblockchainaccounts;

require_once __DIR__."/../utils/CurlRequest.php";
require_once __DIR__."/AWallet.php";

use \Exception;

class BlockIoWallet extends AWallet {

	/**
	 * Setup.
	 */
	public function setup() {
		$thisurl=site_url()."/wp-content/plugins/wp-crypto-accounts/notification-block-io.php";

		$curl=$this->createRequest("get_notifications");
		$res=$curl->exec();
		if ($res["status"]!="success")
			throw new Exception("Unable to contact block.io: ".$res["error_message"]);

		foreach ($res["data"] as $data)
			if ($data["url"]==$thisurl)
				return "Block.io WebHook operational.";

		$curl=$this->createRequest("create_notification");
		$curl->setParam("type","account");
		$curl->setParam("url",$thisurl);
		$res=$curl->exec();

		if ($res["status"]!="success") {
			error_log(print_r($res,TRUE));
			throw new Exception("Unable to contact block.io: ".$res["error_message"]);
		}

		return "Block.io notification initialized.";
	}

	/**
	 * Create a request.
	 */
	private function createRequest($method) {
		$curl=new CurlRequest();
		$curl->setUrl("https://block.io/api/v2/".$method);
		$curl->setParam("api_key",get_option("blockchainaccounts_block_io_api_key"));
		$curl->setResultProcessing("json");

		return $curl;
	}
}