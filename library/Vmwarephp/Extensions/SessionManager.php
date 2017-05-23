<?php
namespace Vmwarephp\Extensions;

class SessionManager extends \Vmwarephp\ManagedObject {

	private $cloneTicketFile;
	private $session;
        private $userName;
                
        function acquireSession($userName, $password) {
		if (empty($this->userName)) {
                    $this->userName=$userName;
                }
                if ($this->userName === $userName) {
                    if ($this->session) {
                            return $this->session;
                    }
                    try {
                            $this->session = $this->acquireSessionUsingCloneTicket();
                    } catch (\Exception $e) {
                            $this->session = $this->acquireANewSession($userName, $password);
                    }
                } else {
                    $this->session = $this->acquireANewSession($userName, $password);
                }
                return $this->session;
	}

	private function acquireSessionUsingCloneTicket() {
		$cloneTicket = $this->readCloneTicket();
		if (!$cloneTicket) {
			throw new \Exception('Cannot find any clone ticket.');
		}
		return $this->CloneSession(array('cloneTicket' => $cloneTicket));
	}

	private function acquireANewSession($userName, $password) {
		$session = $this->Login(array('userName' => $userName, 'password' => $password, 'locale' => null));
		$cloneTicket = $this->AcquireCloneTicket();
		$this->saveCloneTicket($cloneTicket);
		return $session;
	}

	private function saveCloneTicket($cloneTicket) {
		if (!file_put_contents($this->getCloneTicketFile(), $cloneTicket))
			throw new \Exception(sprintf('There was an error writing to the clone ticket path. Check the permissions of the cache directory(%s)', __DIR__ . '/../'));
	}

	private function readCloneTicket() {
		$ticketFile = $this->getCloneTicketFile();
		if (file_exists($ticketFile)) {
			return file_get_contents($ticketFile);
		}
	}

	private function getCloneTicketFile() {
		if (!$this->cloneTicketFile) {
			$this->cloneTicketFile = __DIR__ . '/../.' . $this->getUserNameForFileName() .'_clone_ticket.cache';
		}
		return $this->cloneTicketFile;
	}
        private function getUserNameForFileName() {
            if (empty($this->userName)) {
                throw new Exception('UserName is Empty');
            }
            if (strpos($this->userName, "\\") !== FALSE) {
                $tmpUser= explode("\\", $this->userName)[1];
            } elseif (strpos($this->userName, "@") !== FALSE) {
                $tmpUser= explode("@", $this->userName)[0];
            } else {
                $tmpUser= $this->userName;
            }
            return $tmpUser;
        }
}
