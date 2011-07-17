<?php

@session_start();

/**
 * Manages all session variables.
 * @author mangst
 */
class Session{
	public static function isLoggedIn(){
		return self::getHapi() != null;
	}
	
	public static function getHapi(){
		return self::get('hapi');
	}
	
	public static function setHapi($hapi){
		self::set('hapi', $hapi);
	}
	
	public static function getPlayer(){
		return self::get('player');
	}
	
	public static function setPlayer(\db\Player $player){
		self::set('player', $player);
	}
	
	public static function isMockEnabled(){
		return self::get('mock');
	}
	
	public static function setMockEnabled($mock){
		self::set('mock', $mock);
	}
	
	private static function get($key){
		return @$_SESSION[$key];
	}
	
	private static function set($key, $value){
		$_SESSION[$key] = $value;
	}
}
