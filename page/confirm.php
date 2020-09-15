<?php
namespace App\Page;

use App\Email\AddContactException;
use App\Email\CreateContactException;
use App\Email\InvalidCodeException;
use App\Email\Subscriber;
use App\StoriesUnreadException;
use Gt\WebEngine\Logic\Page;

class ConfirmPage extends Page {
	public function go():void {
		if($code = $this->input->getString("code")) {
			try {
				$this->checkCode($code);
			}
			catch(CreateContactException $exception) {
				$this->redirect("/error?type=create&error=" . $exception->getMessage());
			}
			catch(AddContactException $exception) {
				$this->redirect("/error?type=add");
			}
			catch(InvalidCodeException $exception) {
				$this->redirect("/error?type=code");
			}
			catch(StoriesUnreadException $exception) {
				$this->redirect("/error");
			}
		}
		else {
			$this->redirect("/");
		}
	}

	private function checkCode(string $code):void {
		$subscriber = new Subscriber(
			$this->config->get("mailjet.key"),
			$this->config->get("mailjet.secret"),
			$this->config->get("mailjet.list-id")
		);

		$email = $subscriber->getEmailFromCode($code);
		$this->completeSubscription($subscriber, $email, $code);
	}

	private function completeSubscription(
		Subscriber $subscriber,
		string $email,
		string $code
	):void {
		$subscriber->subscribeEmail($email);
		$subscriber->removeCode($code);
	}
}