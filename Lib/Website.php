<?php 

/**
* 
*/
class Website
{
  
  public static function get( $path)
  {
    return Configure::read( "Website.current.Site.$path");
  }
}