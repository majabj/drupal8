<?php


namespace Drupal\movie_reservation\Controller;
use Drupal\Code\Database\Database;
use Drupal\node\Entity\Node;
use Drupal;
use Drupal\taxonomy\Entity\Term;

use Drupal\Core\Controller\ControllerBase;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\Core\Database\Driver\mysql\Select;

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





}
