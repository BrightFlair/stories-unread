<?php
namespace App\Email;

use Gt\WebEngine\FileSystem\Path;
use Mailjet\Client as MailjetClient;
use Mailjet\Resources;

class Subscriber {
	private MailjetClient $mailjet;
	private int $listId;

	public function __construct(string $key, string $secret, int $listId) {
		$this->mailjet = new MailjetClient(
			$key,
			$secret,
			true,
			["version" => "v3"]
		);
		$this->listId = $listId;
	}

	public function createConfirmationCode(string $email):string {
		$code = bin2hex(random_bytes(8));
		$this->storeCode($code, $email);
		return $code;
	}

	public function getEmailFromCode(string $code):string {
		$email = $this->loadEmail($code);
		if(!$email) {
			throw new InvalidCodeException($code);
		}

		return $email;
	}

	public function removeCode(string $code):void {
		$dir = implode("/", [
			Path::getDataDirectory(),
			"code",
		]);

		$filePath = "$dir/$code";
		if(is_file($filePath)) {
			unlink($filePath);
		}
	}

	public function confirmEmail(string $email, string $code) {
		$body = [
			"FromEmail" => "stories@storiesunread.com",
			"FromName" => "Stories Unread Newsletter",
			"Recipients" => [
				[
					"Email" => $email,
				]
			],
			"MJ-TemplateID" => 1628043,
			"MJ-TemplateLanguage" => true,
			"Subject" => "Confirm your subscription to Stories Unread Newsletter",
			"Vars" => [
				"code" => $code
			]
		];

		$response = $this->mailjet->post(
			Resources::$Email,
			["body" => $body]
		);

		if(!$response->success()) {
			throw new CodeSendException($response->getData());
		}
	}

	public function subscribeEmail(string $email):void {
		try {
			$contactId = $this->createContact($email);
		}
		catch(CreateContactException $exception) {
			$contactId = $this->getContactId($email);
		}

		$this->addContactToList($contactId, $this->listId);
	}

	private function createContact(string $email):int {
		$body = [
			"Email" => $email,
		];
		$response = $this->mailjet->post(
			Resources::$Contact,
			["body" => $body]
		);

		if(!$response->success()) {
			throw new CreateContactException(implode(" ", [
				$response->getStatus(),
				$response->getReasonPhrase(),
			]));
		}

		return $response->getData()["ID"];
	}

	private function getContactId(string $email):int {
		$response = $this->mailjet->get(
			Resources::$Contactdata,
			[
				"ID" => $email,
			]
		);

		if(!$response->success()) {
			throw new GetContactException($email);
		}

		return $response->getData()[0]["ContactID"];
	}

	private function addContactToList(int $contactId, int $listId):void {
		$body = [
			"ContactsLists" => [
				[
					"ListID" => $listId,
					"Action" => "addforce",
				]
			]
		];

		$response = $this->mailjet->post(
			Resources::$ContactManagecontactslists,
			[
				"id" => $contactId,
				"body" => $body
			]
		);

		if(!$response->success()) {
			throw new AddContactException();
		}
	}

	private function storeCode(string $code, string $email):void {
		$dir = implode("/", [
			Path::getDataDirectory(),
			"code",
		]);
		if(!is_dir($dir)) {
			mkdir($dir, 0775, true);
		}

		file_put_contents("$dir/$code", $email);
	}

	private function loadEmail(string $code):?string {
		$dir = implode("/", [
			Path::getDataDirectory(),
			"code",
		]);
		return file_get_contents("$dir/$code");
	}
}