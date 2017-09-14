<?php
	require_once __DIR__.'/../vendor/autoload.php';
	class Filter{
		private $from;
		private $to;
		private $worklogUsername;

		const BR_DATE_REGEXP = '/^([0-9]{2})\/([0-9]{2})\/[0-9]{4}$/';
		const BR_DATE_FORMAT = 'd/m/Y H:i:s';

		const DB_DATE_REGEXP = '/^([0-9]{4})\-([0-9]{2})\-([0-9]{2})$/';
		const DB_DATE_FORMAT = 'Y-m-d H:i:s';

		const ZERO_TIME = " 00:00:00";

		public function __construct(){
		}

		public static function create(){
			$instance = new self();
			return $instance;
		}

		public function validateWorklog($worklog = null){
			$this->worklogValidator($worklog);
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

		private function isValidFrom($worklog = null){
			$this->worklogValidator($worklog);
			if (!$this->hasFrom()){
				return true;
			}
			$started = $this->prepareStarted($worklog['created']);
			return $started >= $this->from;
		}

		private function isValidTo($worklog = null){
			$this->worklogValidator($worklog);
			if (!$this->hasTo()){
				return true;
			}
			$started = $this->prepareStarted($worklog['created']);
			return $started <= $this->to;
		}

		private function isFromUser($worklog = null){
			$this->worklogValidator($worklog);
			if ($this->worklogUsername == ""){
				return true;
			}
			return $worklog['user'] == $this->worklogUsername;
		}

		private function prepareStarted($date = null){
			if ($date === null){
				throw new Exception("You must set a date value");
			}
			return $this->dateHandler($date, self::BR_DATE_FORMAT);
		}

		private function dateHandler(string $date = "", string $dateFormat = null){
			if ($date === ""){
				return false;
			}

			$format = $dateFormat;
			if ($format === null){
				if (preg_match(self::BR_DATE_REGEXP, $date)){
					$format = self::BR_DATE_FORMAT;
				}else if (preg_match(self::DB_DATE_REGEXP, $date)){
					$format = self::DB_DATE_FORMAT;
				}
			}

			if ($format === null){
				return false;
			}
			
			return DateTime::createFromFormat($format, ($date . self::ZERO_TIME));
		}

		public function setFrom($from = ""){
			$from = $this->dateHandler($from);
			if ($from !== false){
				$this->from = $from;
			}
			return $this;
		}

		public function setTo($to = ""){
			$to = $this->dateHandler($to);
			if ($to !== false){
				$this->to = $to;
			}
			return $this;
		}

		public function setWorklogUsername($worklogUsername){
			$this->worklogUsername = $worklogUsername;
			return $this;
		}

		private function worklogValidator($worklog = null){
			if (!is_array($worklog)){
				throw new Exception("Worklog must be an array");
			}
			if ($worklog === null){
				throw new Exception("A worklog must be set");
			}
		}
	}