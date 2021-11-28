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
use Drupal\Core\Ajax\AjaxResponse;
use Symfony\Component\HttpFoundation\JsonResponse;


class MovieReservation extends ControllerBase{
  public function page(){
    return [
      '#theme' => 'movie_list',
      '#title' => 'Welcome to the movie reservation',
      '#movies' => $this->getMovieList(),
      '#genres' => $this->getTaxonomy('genres'),
      '#days' => $this->getTaxonomy('days'),
      '#reservation' => $this->saveReservation(),
      
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

  public function saveReservation(){

  $reservationData = \Drupal::request()->request->get('reservationData');
  $data = json_decode($reservationData);
  $name= $data->thename;
  $movie= $data->themovie;
  $day= $data->theday;
  $connection = \Drupal\Core\Database\Database::getConnection();

  $node_storage = \Drupal::entityTypeManager()->getStorage('node');
  $node = $node_storage->load($movie);
  $title= $node->title->value;
  $vid = $node->vid->value;

  
  if(!empty($reservationData)){
    $result = $connection->insert('reservations')
    ->fields([
      'day_of_reservation' => $day,
      'time_of_reservation' => date('Y-m-d H:i:s'),
      'reserved_movie_name' => $title,
      'reserved_movie_genre' => $vid,
      'customer_name' => $name,
    ])
    ->execute();

    if(!empty($result)){

      return new JsonResponse ([ 'data' => 'Success! Your reservation has been saved!', 'method' => 'GET', 'status'=> 'success']);

      }

  return new JsonResponse ([ 'Error! Your reservation has not been saved!', 'method' => 'GET', 'status'=> 'error']);
    }
  }

}

