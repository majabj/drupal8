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

  /**
   * Function that provides page 'movie-reservation' that has list of all movies from database.
   * @return array
   */
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
  /**
   * Function that provides page 'all-reservation' that has list of all reservations from database.
   * @return array
   */
  public function reservationPage(){
    return [
      '#theme' => 'reservation_list',
      '#title' => 'List of all reservations:',
      '#reservations' => $this->getReservationList(),
    ];
  }

  public function getMovieList(){
    $selectedGenre= \Drupal::request()->query->get('selectedGenre');
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
    $data = json_decode($reservationData, true);
    $connection = \Drupal\Core\Database\Database::getConnection();
    $query = $connection->select('node_field_data');
    $query->fields('node_field_data', ['vid', 'title']);
    $query->condition('nid', $data["themovie"]);
    $result = $query->execute();
    $values = $result->fetchAll();  

    if(!empty($reservationData)){
      $result = $connection->insert('reservations')
      ->fields([
      'day_of_reservation' => $data["theday"],
      'time_of_reservation' => date('Y-m-d H:i:s'),
      'reserved_movie_name' => $values[0]->title,
      'reserved_movie_genre' => $values[0]->vid,
      'customer_name' => $data["thename"],
      ])
      ->execute();
      return new JsonResponse ([ 'data' => 'Success! Your reservation has been saved!', 'method' => 'GET', 'status'=> 'success']);
      } else {
        return new JsonResponse ([ 'data' => 'Error! Your reservation has not been saved!', 'method' => 'GET', 'status'=> 'error']);
      }
  }

  public function getReservationList(){
    $selectedSort= \Drupal::request()->query->get('selectedSort');
    $database=  \Drupal\Core\Database\Database::getConnection();

    if($selectedSort == 'reserved_movie_name|ASC'){
      $query = $database->query('SELECT * FROM reservations ORDER BY reserved_movie_name ASC');
      } elseif ($selectedSort == 'reserved_movie_name|DESC'){
          $query= $database->query('SELECT * FROM reservations ORDER BY reserved_movie_name DESC');
            } elseif ($selectedSort == 'id|DESC'){
                $query= $database->query('SELECT * FROM reservations ORDER BY id DESC');
                  } else{
                      $query = $database->query('SELECT * FROM reservations');
                    }
    $reservations = $query->fetchAll();
    return $reservations;
  }
 }


