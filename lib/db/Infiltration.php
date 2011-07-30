<?php
namespace db;

/**
 * A row from the "infiltrations" table.
 * @author mangstadt
 */
class Infiltration{
	public $id;
	
	/**
	 * The report that this infiltration belongs to.
	 * @var Report
	 */
	public $report;
	
	public $planetName;
	public $planetTag;
	public $x;
	public $y;
	public $level;
	public $security;
	public $captive;
}