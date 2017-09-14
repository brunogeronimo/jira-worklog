<?php
	require_once __DIR__.'/../vendor/autoload.php';
	class Filter{
		private $from;
		private $to;
		private $worklogUsername;

		private const BR_DATE_REGEXP = '/^([0-9]{2})\/([0-9]{2})\/[0-9]{4}$/';
		private const BR_DATE_FORMAT = 'd/m/Y H:i:s';
		private const ZERO_TIME = " 00:00:00";

		public function __construct(){
		}

		public static function create(){
			$instance = new self();
			return $instance;
		}

		public function validateWorklog(array $worklog = null){
			$result = $this->isValidFrom($worklog);
			$result = $result && $this->isValidTo($worklog);
			$result = $result && $this->isFromUser($worklog);
			return $result;
		}

		private function hasFrom(){
			return $this->from !== null && $this->from !== "";
		}

		private function hasTo(){
			return $this->to !== null && $this->to !== "";
		}

		private function hasWorklogUsername(){
			return $this->worklogUsername !== null && $this->worklogUsername !== "";
		}

		public function hasFiltersFilled(){
			return $this->hasFrom() || $this->hasTo() || $this->hasWorklogUsername();
		}

		private function isValidFrom(array $worklog = null){
			if (!$this->hasFrom()){
				return true;
			}
			$started = $this->prepareStarted($worklog['created']);
			return $started >= $this->from;
		}

		private function isValidTo(array $worklog = null){
			if (!$this->hasTo()){
				return true;
			}
			$started = $this->prepareStarted($worklog['created']);
			return $started <= $this->to;
		}

		private function isFromUser(array $worklog = null){
			if ($this->worklogUsername == ""){
				return true;
			}
			return $worklog['user'] == $this->worklogUsername;
		}

		private function prepareStarted($date = null){
			if ($date === null){
				throw new Exception("You must set a date value");
			}
			$date = DateTime::createFromFormat(self::BR_DATE_FORMAT, ($date . self::ZERO_TIME));
			return $date;
		}


		public function setFrom($from = ""){
			if (preg_match(self::BR_DATE_REGEXP, $from)){
				$this->from = DateTime::createFromFormat(self::BR_DATE_FORMAT, ($from . self::ZERO_TIME));
			}
			return $this;
		}

		public function setTo($to = ""){
			if (preg_match(self::BR_DATE_REGEXP, $to)){
				$this->to = DateTime::createFromFormat(self::BR_DATE_FORMAT, ($to . self::ZERO_TIME));
			}
			return $this;
		}

		public function setWorklogUsername($worklogUsername){
			$this->worklogUsername = $worklogUsername;
			return $this;
		}
	}