<?php


namespace Drupal\movie_reservation\Controller;
use Drupal\Code\Database\Database;
use Drupal\node\Entity\Node;
use Drupal;
use Drupal\taxonomy\Entity\Term;

use Drupal\Core\Controller\ControllerBase;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\Core\Database\Driver\mysql\Select;
use Drupal\Core\Database\Connection;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Ajax\AjaxResponse;



class MovieReservation extends ControllerBase{
  public function page(){
    return [
      '#theme' => 'movie_list',
      '#title' => 'Welcome to the movie reservation',
      '#movies' => $this->getMovieList(),
      '#genres' => $this->getTaxonomy('genres'),
      '#days' => $this->getTaxonomy('days'),
      '#reservation' => $this->saveReservation(),
      '#nid' => $this->getMovieId('nid'),

    ];
  }


  public function getMovieList(){
   $selectedGenre= \Drupal::request()->request->get('selectedGenre');
   if (!empty($selectedGenre)){
      $allNodeIds = \Drupal::entityQuery('node')->condition('type', 'movies')->condition('field_genres', strtolower($selectedGenre))->execute();
      }else{
            $allNodeIds = \Drupal::entityQuery('node')->condition('type', 'movies')->execute();
      }
     return Node::loadMultiple($allNodeIds);
  }

  public function getTaxonomy($var){
    $taxonomy = \Drupal::entityQuery('taxonomy_term')->condition('vid', $var)->execute();
    return Term::loadMultiple($taxonomy);
  }

  public function getMovieId($nid){
    $movieid = \Drupal::entityQuery('node')->condition('nid', $nid)->execute();
    return Node::loadMultiple($movieid);
  }

  public function queryForId(){

  $movieid = db_query('SELECT movies FROM {node} WHERE nid = :nid', array(':nid' => $nid))->fetchField();
  return $movieid;
  }

  public function saveReservation(){
  $reservationData = \Drupal::request()->request->get('reservationData');
  $connection = \Drupal\Core\Database\Database::getConnection();

  if(!empty($reservationData)){
    $result = $connection->insert('reservations')
    ->fields([
      'id' => $nid,
      'day_of_reservation' => $reservationData[selectedDay],
      'time_of_reservation' => ('Y-m-d h:i:s'),
      'reserved_movie_name' => $movieid[selectedMovie],
      'reserved_movie_genre' => $movieid[selectedMovie],
      'customer_name' => $reservationData[name],

    ])
     ->execute();
    return new JsonResponse ([ 'data' => 'Success! Your reservation has been saved!', 'method' => 'GET', 'status'=> 'success']);

  }else{
    return new JsonRepsone ([ 'data' => 'Error! Your reservation has not been saved!', 'method' => 'GET', 'status'=> 'error']);
  }
  }

}

