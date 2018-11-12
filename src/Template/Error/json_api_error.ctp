<?php
/**
 * @var \Cake\View\View $this
 * @var array $errors
 */
    use Cake\Core\Configure;

    $this->response = $this->response->withHeader('Content-Type', 'application/vnd.api+json');
    if (Configure::read('debug')) {
        echo json_encode($errors, JSON_PRETTY_PRINT);
    } else {
        echo json_encode($errors);
    }
