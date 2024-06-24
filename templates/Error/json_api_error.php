<?php
/**
 * @var View $this
 * @var array $errors
 */
    use Cake\Core\Configure;
use Cake\View\View;

$this->response = $this->response->withHeader('Content-Type', 'application/vnd.api+json');
    if (Configure::read('debug')) {
        echo json_encode($errors, JSON_PRETTY_PRINT);
    } else {
        echo json_encode($errors);
    }
