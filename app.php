<?php

class Trip {
	private $trip_id;
	private $driver_id;
	private $start_datetime;
	private $end_datetime;

	public function __construct(int $trip_id, int $driver_id, string $start_datetime, string $end_datetime)
	{
		$this->setTripId($trip_id);
		$this->setDriverId($driver_id);
		$this->setStartDatetime($start_datetime);
		$this->setEndDatetime($end_datetime);
	}

	public function setTripId(int $trip_id): void
	{
		$this->trip_id = $trip_id;
	}
	
	public function setDriverId(int $driver_id): void
	{
		$this->driver_id = $driver_id;
	}

	public function getTripId(): int
	{
		return $this->trip_id;
	}
	
	public function getDriverId(): int
	{
		return $this->driver_id;
	}

	public function setStartDatetime(string $datetime): void
	{
		$this->start_datetime = new DateTime($datetime);
	}

	public function setEndDatetime(string $datetime): void
	{
		$this->end_datetime = new DateTime($datetime);
	}

	public function getStartDatetime()
	{
		return $this->start_datetime->format('c');
	}

	public function getEndDatetime()
	{
		return $this->end_datetime->format('c');
	}

	public function inDatetimeRange(string $datetime): bool
	{
		if($this->start_datetime >= $datetime && $this->end_datetime <= $datetime) {
			return true;
		} else {
			return false;
		}
	}

	public function getDuration()
	{
		return $this->end_datetime->getTimestamp() - $this->start_datetime->getTimestamp();
	}
}


class Driver {
	private int $driver_id;
	public $trips = array();

	public function __construct(int $driver_id)
	{
		$this->driver_id = $driver_id;
	}

	public function getId(): int
	{
		return $this->driver_id;
	}

	public function addTrip(int $trip_id, int $driver_id, string $start_datetime, string $end_datetime)
	{
		$trip = new Trip($trip_id, $driver_id, $start_datetime, $end_datetime);
		$this->trips[] = $trip;
		return $trip;
	}

	public function countTrips(): int
	{
		return count($this->trips);
	}
	
	public function getFullTripsDuration(): int
	{
		$duration = 0;

		foreach($this->trips as $trip) {
			$duration += $trip->getDuration();
		}

		return $duration;
	}
}

class DriverCollection {
	private $drivers = array();

	public function add(int $driver_id): Driver
	{
		$driver = new Driver($driver_id);
		$this->drivers[$driver_id] = $driver;

		return $driver;
	}

	public function has(int $driver_id): bool
	{
		return isset($this->drivers[$driver_id]);
	}

	public function get(int $driver_id): Driver
	{
		return $this->drivers[$driver_id];
	}

	public function all()
	{
		return $this->drivers;
	}
}

/*
	CHECK INPUT PARAMS
*/

if(!isset($argv[1]) || !isset($argv[2])) {
	echo "Syntax: php app.php input_file output_file" . PHP_EOL;
	exit;
}

$input_csv = $argv[1];
$output_csv = $argv[2];

if (($fp = fopen($input_csv, "r")) !== FALSE) {
	$i = 0;

	$drivers = new DriverCollection();

	// O(n)
	while (($row = fgetcsv($fp, 1000, ",")) !== FALSE) {
		if($i === 0) {
			$i++;

			continue;
		}

		$trip = new Trip((int) $row[0], (int) $row[1], $row[2], $row[3]);

		if(!$drivers->has($trip->getDriverId())) {
			$drivers->add($trip->getDriverId());
		}

		$driver = $drivers->get($trip->getDriverId());

		if($driver->countTrips() === 0 ) {
			$driver->addTrip($trip->getTripId(), $trip->getDriverId(), $trip->getStartDatetime(), $trip->getEndDatetime());
		} else {
			$modified_existed = false;

			foreach($driver->trips as $driver_trip) {
				$tmp_start_datetime =  $driver_trip->getStartDatetime();
				$tmp_end_datetime = $driver_trip->getEndDatetime();

				if($driver_trip->inDatetimeRange($trip->getEndDatetime()) && $driver_trip->getStartDatetime() > $trip->getStartDatetime()) {
					$tmp_start_datetime = $trip->getStartDatetime();
				}

				if($driver_trip->inDatetimeRange($trip->getStartDatetime()) && $driver_trip->getEndDatetime() < $trip->getEndDatetime()) {
					$tmp_end_datetime = $trip->getEndDatetime();
				}

				if($tmp_start_datetime !== $driver_trip->getStartDatetime() || $tmp_end_datetime !== $driver_trip->getEndDatetime()) {
					$modified_existed = true;
					echo "match for " . $driver_trip->getId() . PHP_EOL;
				}

				$driver_trip->setStartDatetime($tmp_start_datetime);
				$driver_trip->setEndDatetime($tmp_end_datetime);

				if($modified_existed === true) break;
			}

			if($modified_existed === false) {
				$driver->addTrip($trip->getTripId(), $trip->getDriverId(), $trip->getStartDatetime(), $trip->getEndDatetime());
			}
		}
	}

	/*
		WRITE TO CSV
	*/

	$fp_csv = fopen($output_csv, 'w');
	fwrite($fp_csv, "driver_id,total_minutes_with_passenger");

	foreach($drivers->all() as $key => $driver) {
		fwrite($fp_csv, PHP_EOL . $driver->getId() . "," . gmdate('H:i:s', $driver->getFullTripsDuration()));
	}

	fclose($fp_csv);
	fclose($fp);
}