<?php
/**
 * Fonctions utile au module BFW_SQL
 * @author Vermeulen Maxime <bulton.fr@gmail.com>
 * @package bfw-sql
 * @version 1.0
 */

/**
 * Permet de prot�ger une donn�e pour les requ�tes
 * 
 * @param string $data : La donn�e � prot�ger
 * 
 * @return string
 */
function DB_protect($data)
{
	global $DB;
	
	if(!is_null($DB)) {return $DB->protect($data);}
	else {return $data}
}