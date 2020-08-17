<?php

	if (!isset($argv[1])) {
		die("missing ip\n");
	}
	if (!isset($argv[2])) {
		$prefix	= $argv[1];
	} else {
		$prefix	= $argv[2];
	}

	$ip		= $argv[1];
	$res	= pingtest($ip);
	$x		= new PingGraph($ip, "pings/". $prefix ."-");

	while (true) {
		#printf("%.4f  ", microtime(true));
		$x->update();
		#printf("   %.4f", microtime(true));
		#print "\n";
	}


	class PingGraph {
		public $ip			= null;
		public $prefix		= null;
		public $date		= null;
		public $dateObj		= null;
		public $filename	= null;
		public $midnight	= null;
		public $image		= null;

		public function __construct($ip, $prefix = null) {
			$this->ip		= $ip;
			$this->prefix	= ($prefix === null ? ($ip ."-") : $prefix);
			$this->updateDate(time());
			$this->newImage();
		}

		public function newImage() {
			$filename	= $this->prefix . $this->date .".png";

			if ($this->image) {
				imagedestroy($image);
			}

			if (file_exists($filename)) {
				$this->image	= imagecreatefrompng($filename);
			} else {
				$this->image	= imagecreatetruecolor(320, 288);	// = 86400
				imagefilledrectangle($this->image, 0, 0, 299, 288, 0x7F7F7F);
			}

			imageline($this->image, 300, 0, 319, 0, 0xFFFFFF);

			for ($i = 1; $i <= 24; $i++) {
				$y	= (288 / 24) * $i;
				if ($i == 12) {
					$w	= 4;
					$c	= 0xFFFFFF;
				} elseif ($i % 3) {
					$w	= 1;
					$c	= 0x666666;
				} else {
					$w	= 2;
					$c	= 0xAAAAAA;

				}

				imageline($this->image, 300, $y, 300 + $w, $y, $c);
			}

			imagestringup($this->image, 3, 305, 74, $this->date, 0xEECCFF);
			imagestringup($this->image, 1, 309, 152, str_pad($this->ip, 15, " ", STR_PAD_LEFT), 0xAA88EE);

			$this->filename		= $filename;
		}



		public function updateImage($ts, $v) {
			$x	= $ts % 300;
			$y	= floor($ts / 300);

			if ($v === false) {
				#print "XXXXX";
				$c	= 0xFF0000;
			} elseif ($v <= 100) {
				#printf("%5.1f", $v);
				$c	= floor($v / 100 * 0xFF) << 8;
			} else {
				#printf("%5.1f", $v);
				$tmp	= min(400, $v - 100);
				$c		= (floor($tmp / 400 * 0xFF) << 16) + 0xFF00;
			}



			imagesetpixel($this->image, $x, $y, $c);
			$t		= "/tmp/". md5(mt_rand(0, 99999999) . time()) .".png";
			imagepng($this->image, $t);
			rename($t, $this->filename);

		}



		public function update() {
			$ts		= time();
			$res	= pingtest($this->ip);
			$sec	= $this->secondsSinceMidnight($ts);
			if ($sec >= 86400) {
				$this->updateDate($ts);
				$sec	= $this->secondsSinceMidnight($ts);
				$this->newImage();
			}
			$this->updateImage($sec, $res);
			$this->sleep($ts);
		}


		public function sleep($ts) {
			$t		= microtime(true);
			$ti		= floor($ts + 1) - $t;
			if ($ti > 0) {
				usleep($ti * 1000000);
			}

		}

		public function updateDate($ts) {
			$now			= $ts;
			if ($this->dateObj === null) {
				$dateObj		= new DateTime(); 
				$dateObj->setTimestamp($now);
				$dateObj->setTimezone(new DateTimeZone('America/Los_Angeles'));
			}

			$dateObj->modify('today midnight');
			$this->midnight = $dateObj->getTimestamp();

			$dateObj->setTimestamp($now);

			$this->date	= $dateObj->format("Y-m-d");

		}

		public function secondsSinceMidnight($ts) {
			return $ts - $this->midnight;

		}


	}





	function pingtest($ip) {
		$cmd	= "fping -c 1 -r 0 -q $ip 2>&1";
		exec($cmd, $output, $return);
		if ($return !== 0) {
			return false;
		}

		$stats	= statssplit($output[0]);
		return $stats[1];
	}

	function statssplit($s) {
		$x		= explode(":", $s);
		$statsA	= explode(", ", trim($x[1]));
		$stats	= [];

		foreach ($statsA as $k => $v) {
			$t	= explode(" = ", $v);
			$t2	= explode("/", $t[1]);
			$stats[$k]	= $t2[0];
		}

		if (!isset($stats[1])) {
			$stats[1]	= null;
		}

		return $stats;
	}



