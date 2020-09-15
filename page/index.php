<?php
namespace App\Page;

use App\Email\Subscriber;
use App\StoriesUnreadException;
use App\UI\FamousEmail;
use DateTime;
use Gt\DomTemplate\Element;
use Gt\Input\InputData\InputData;
use Gt\WebEngine\FileSystem\Path;
use Gt\WebEngine\Logic\Page;

class IndexPage extends Page {
	public function go():void {
		$this->bindFamousPersonEmail(
			$this->document->querySelector("form"),
			new FamousEmail()
		);
		$this->bindLatestVersion();
	}

	public function doSubscribe(InputData $data):void {
		$subscriber = new Subscriber(
			$this->config->get("mailjet.key"),
			$this->config->get("mailjet.secret"),
			$this->config->get("mailjet.list-id")
		);
		$code = $subscriber->createConfirmationCode(
			$data->getString("email")
		);

		try {
			$subscriber->confirmEmail(
				$data->getString("email"),
				$code
			);
		}
		catch(StoriesUnreadException $exception) {
			$this->redirect("/error");
		}

		$this->redirect("/thanks");
	}

	private function bindFamousPersonEmail(
		Element $bindTo,
		FamousEmail $famousEmail
	):void {
		$bindTo->bindKeyValue("famousEmail", $famousEmail->getRandom());
	}

	private function bindLatestVersion():void {
		$previewDir = implode("/", [
			Path::getAssetDirectory(),
			"preview"
		]);
		$htmlFiles = glob("$previewDir/*.html");
		sort($htmlFiles, SORT_NATURAL);
		end($htmlFiles);
		$latest = current($htmlFiles);
		$issueNum = pathinfo($latest, PATHINFO_FILENAME);
		$issueDate = new DateTime();
		$issueDate->setTimestamp(filectime($latest));

		$this->document->bindKeyValue("issueNum", $issueNum);
		$this->document->bindKeyValue("issueDate", $issueDate->format("jS F Y"));
	}
}